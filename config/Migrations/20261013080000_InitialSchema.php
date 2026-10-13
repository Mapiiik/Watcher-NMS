<?php
declare(strict_types=1);

use Migrations\BaseMigration;
use Migrations\Db\Literal;
use Migrations\Migration\IrreversibleMigrationException;

/**
 * Where 5.x begins: the schema the migrations of 4.x built, in one step.
 *
 * An empty database is given all of it. One that 4.x brought to its last migration has it already,
 * but for a few names some installations went their own way with, and those are put right. Then it
 * only forgets the steps that led there. Any other is turned away, as those steps are no longer here
 * to take it the rest of the way.
 */
class InitialSchema extends BaseMigration
{
    /**
     * The last migration of 4.x.
     *
     * @var int
     */
    private const LAST_OF_4X = 20261013070000;

    /**
     * Up Method.
     *
     * @return void
     */
    public function up(): void
    {
        if ($this->hasTable('phinxlog')) {
            throw new RuntimeException(
                'The database keeps the record of its migrations in the phinxlog tables of 4.x.'
                . ' Move it with `bin/cake migrations upgrade --drop-tables`, then migrate again.',
            );
        }

        $last = $this->fetchRow(sprintf(
            'SELECT 1 FROM cake_migrations WHERE plugin IS NULL AND version = %d',
            self::LAST_OF_4X,
        ));
        if ($last !== false) {
            $this->alignNames();

            // the accounts were created by CakeDC/Users once, and are the application's own now
            $this->execute(sprintf(
                "DELETE FROM cake_migrations WHERE (plugin IS NULL AND version < %d) OR plugin = 'CakeDC/Users'",
                $this->getVersion(),
            ));

            return;
        }

        if ($this->hasTable('users')) {
            throw new RuntimeException(
                'The database has not been through the last migration of 4.x.'
                . ' Update the application to the last 4.x release and migrate there first.',
            );
        }

        $this->createTables();
    }

    /**
     * Down Method.
     *
     * @return void
     */
    public function down(): void
    {
        throw new IrreversibleMigrationException('What 5.x begins with is not taken apart.');
    }

    /**
     * What some databases of 4.x named or wrote their own way, as here. The migrations of 4.x never
     * compared names, so nothing else would bring them in line.
     *
     * @return void
     */
    private function alignNames(): void
    {
        // the name the migrations gave this key changed with their version
        $from = 'landlord_payments_electricity_details_landlord_payment_id_fk';
        $to = 'landlord_payments_electricity_details_landlord_payment_id_fkey';
        if ($this->fetchRow(sprintf("SELECT 1 FROM pg_constraint WHERE conname = '%s'", $from)) !== false) {
            $this->execute(sprintf(
                'ALTER TABLE landlord_payments_electricity_details RENAME CONSTRAINT %s TO %s',
                $from,
                $to,
            ));
        }

        // times with the precision written out, which is the one they have anyway
        $columns = $this->fetchAll(
            'SELECT c.relname AS table_name, a.attname AS column_name FROM pg_attribute a'
            . " JOIN pg_class c ON c.oid = a.attrelid AND c.relkind = 'r'"
            . ' WHERE c.relnamespace = current_schema()::regnamespace'
            . " AND a.atttypid = 'timestamptz'::regtype AND a.atttypmod <> -1",
        );
        foreach ($columns as $column) {
            $this->execute(sprintf(
                'ALTER TABLE %s ALTER COLUMN %s TYPE timestamptz',
                $column['table_name'],
                $column['column_name'],
            ));
        }
    }

    /**
     * The tables as 4.x left them.
     *
     * @return void
     */
    private function createTables(): void
    {
        $this->execute('CREATE EXTENSION IF NOT EXISTS "uuid-ossp"');

        $this->table('access_point_contacts', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', ['default' => Literal::from('uuid_generate_v4()'), 'null' => false])
            ->addColumn('name', 'string', ['null' => true])
            ->addColumn('access_point_id', 'uuid', ['null' => true])
            ->addColumn('phone', 'string', ['null' => true])
            ->addColumn('email', 'string', ['null' => true])
            ->addColumn('customer_number', 'string', ['null' => true])
            ->addColumn('contract_number', 'string', ['null' => true])
            ->addColumn('note', 'text', ['null' => true])
            ->addColumn('created', 'timestamptimezone', ['null' => true])
            ->addColumn('modified', 'timestamptimezone', ['null' => true])
            ->addColumn('created_by', 'uuid', ['null' => true])
            ->addColumn('modified_by', 'uuid', ['null' => true])
            ->addIndex(['access_point_id'], ['name' => 'access_point_contacts_access_point_id'])
            ->create();

        $this->table('access_point_power_outages', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', ['default' => Literal::from('uuid_generate_v4()'), 'null' => false])
            ->addColumn('access_point_id', 'uuid', ['null' => false])
            ->addColumn('power_outage_id', 'uuid', ['null' => false])
            ->addColumn('certainty', 'string', [
                'limit' => 16,
                'null' => false,
                'comment' => 'Whether the outage is known to be this mast or only likely to be.',
            ])
            ->addColumn('matched_by', 'string', ['limit' => 16, 'null' => false])
            ->addColumn('match_note', 'string', [
                'null' => true,
                'comment' => 'What the match rests on, for whoever doubts it.',
            ])
            ->addColumn('distance_metres', 'integer', ['null' => true])
            ->addColumn('access_point_supply_address_id', 'uuid', [
                'null' => true,
                'comment' => 'Which of the addresses around the mast the outage was found on.',
            ])
            ->addColumn('created', 'timestamptimezone', ['null' => true])
            ->addColumn('modified', 'timestamptimezone', ['null' => true])
            ->addIndex(['access_point_id', 'power_outage_id'], [
                'name' => 'access_point_power_outages_access_point_id_power_outage_id',
                'unique' => true,
            ])
            ->addIndex(['power_outage_id'], ['name' => 'access_point_power_outages_power_outage_id'])
            ->create();

        $this->table('access_point_supply_addresses', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', ['default' => Literal::from('uuid_generate_v4()'), 'null' => false])
            ->addColumn('access_point_id', 'uuid', ['null' => false])
            ->addColumn('rank', 'integer', [
                'null' => false,
                'comment' => 'Where the address stands in the answer, nearest first.',
            ])
            ->addColumn('distance_metres', 'integer', ['null' => true])
            ->addColumn('registry_ref', 'string', [
                'null' => true,
                'comment' => 'What the address registry keeps the address under.',
            ])
            ->addColumn('town_code', 'integer', [
                'null' => true,
                'comment' => 'The registry number of the municipality, which outages are asked for by.',
            ])
            ->addColumn('town_name', 'string', ['null' => true])
            ->addColumn('town_part_name', 'string', ['null' => true])
            ->addColumn('street_name', 'string', ['null' => true])
            ->addColumn('house_number', 'integer', ['null' => true])
            ->addColumn('orientation_number', 'integer', ['null' => true])
            ->addColumn('orientation_letter', 'string', ['limit' => 8, 'null' => true])
            ->addColumn('number_type', 'string', [
                'limit' => 16,
                'null' => true,
                'comment' => 'Whether the house number is a house number or a registration number.',
            ])
            ->addColumn('formatted_address', 'string', ['null' => true])
            ->addColumn('created', 'timestamptimezone', ['null' => true])
            ->addColumn('modified', 'timestamptimezone', ['null' => true])
            ->addIndex(['access_point_id', 'rank'], [
                'name' => 'access_point_supply_addresses_access_point_id_rank',
                'unique' => true,
            ])
            ->addIndex(['town_code'], ['name' => 'access_point_supply_addresses_town_code'])
            ->create();

        $this->table('access_point_types', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', ['default' => Literal::from('uuid_generate_v4()'), 'null' => false])
            ->addColumn('name', 'string', ['null' => true])
            ->addColumn('color', 'string', ['default' => '#ffffff', 'limit' => 7, 'null' => false])
            ->addColumn('note', 'text', ['null' => true])
            ->addColumn('created', 'timestamptimezone', ['null' => true])
            ->addColumn('created_by', 'uuid', ['null' => true])
            ->addColumn('modified', 'timestamptimezone', ['null' => true])
            ->addColumn('modified_by', 'uuid', ['null' => true])
            ->create();

        $this->table('access_points', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', ['default' => Literal::from('uuid_generate_v4()'), 'null' => false])
            ->addColumn('name', 'string', ['null' => true])
            ->addColumn('device_name', 'string', ['null' => true])
            ->addColumn('gps_x', 'float', ['null' => true])
            ->addColumn('gps_y', 'float', ['null' => true])
            ->addColumn('note', 'text', ['null' => true])
            ->addColumn('created', 'timestamptimezone', ['null' => true])
            ->addColumn('modified', 'timestamptimezone', ['null' => true])
            ->addColumn('month_of_electricity_meter_reading', 'integer', ['null' => true])
            ->addColumn('parent_access_point_id', 'uuid', ['null' => true])
            ->addColumn('contract_conditions', 'text', ['null' => true])
            ->addColumn('created_by', 'uuid', ['null' => true])
            ->addColumn('modified_by', 'uuid', ['null' => true])
            ->addColumn('access_point_type_id', 'uuid', ['null' => true])
            ->addColumn('archived', 'timestamptimezone', ['null' => true])
            ->addColumn('archived_by', 'uuid', ['null' => true])
            ->addColumn('electricity_ean', 'string', [
                'limit' => 18,
                'null' => true,
                'comment' => 'The EAN of the supply point, which is what the distributor answers about.',
            ])
            ->addColumn('electricity_meter_number', 'string', ['limit' => 32, 'null' => true])
            ->addColumn('supply_resolved', 'timestamptimezone', [
                'null' => true,
                'comment' => 'When the addresses around the access point were last looked up.',
            ])
            ->addColumn('supply_resolution_failed', 'string', [
                'null' => true,
                'comment' => 'Why the last look-up of the addresses got nowhere, where it got nowhere.',
            ])
            ->addColumn('supply_resolved_gps_x', 'float', ['null' => true])
            ->addColumn('supply_resolved_gps_y', 'float', ['null' => true])
            ->addIndex(['parent_access_point_id'], ['name' => 'access_points_parent_access_point_id'])
            ->addIndex(['access_point_type_id'], ['name' => 'access_points_access_point_type_id'])
            ->addIndex(['archived_by'], ['name' => 'access_points_archived_by'])
            ->addIndex(['electricity_ean'], ['name' => 'access_points_electricity_ean'])
            ->create();

        $this->table('antenna_types', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', ['default' => Literal::from('uuid_generate_v4()'), 'null' => false])
            ->addColumn('name', 'string', ['null' => true])
            ->addColumn('radio_unit_band_id', 'uuid', ['null' => true])
            ->addColumn('manufacturer_id', 'uuid', ['null' => true])
            ->addColumn('antenna_gain', 'integer', ['null' => true])
            ->addColumn('part_number', 'string', ['null' => true])
            ->addColumn('note', 'text', ['null' => true])
            ->addColumn('created', 'timestamptimezone', ['null' => true])
            ->addColumn('modified', 'timestamptimezone', ['null' => true])
            ->addColumn('created_by', 'uuid', ['null' => true])
            ->addColumn('modified_by', 'uuid', ['null' => true])
            ->create();

        $this->table('audit_logs', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', ['default' => Literal::from('uuid_generate_v4()'), 'null' => false])
            ->addColumn('transaction_key', 'uuid', ['null' => false])
            ->addColumn('type', 'string', ['limit' => 7, 'null' => false])
            ->addColumn('primary_key', 'json', ['null' => true])
            ->addColumn('display_value', 'string', ['null' => true])
            ->addColumn('source', 'string', ['null' => false])
            ->addColumn('parent_source', 'string', ['null' => true])
            ->addColumn('user_display', 'string', ['null' => true])
            ->addColumn('original', 'json', ['null' => true])
            ->addColumn('changed', 'json', ['null' => true])
            ->addColumn('meta', 'json', ['null' => true])
            ->addColumn('created', 'timestamptimezone', ['null' => true])
            ->addColumn('user_id', 'uuid', ['null' => true])
            ->addIndex(['transaction_key'], ['name' => 'audit_logs_transaction'])
            ->addIndex(['type'], ['name' => 'audit_logs_type'])
            ->addIndex(['primary_key'], ['name' => 'audit_logs_primary_key'])
            ->addIndex(['display_value'], ['name' => 'audit_logs_display_value'])
            ->addIndex(['source'], ['name' => 'audit_logs_source'])
            ->addIndex(['parent_source'], ['name' => 'audit_logs_parent_source'])
            ->addIndex(['user_display'], ['name' => 'audit_logs_username'])
            ->addIndex(['created'], ['name' => 'audit_logs_created'])
            ->addIndex(['user_id'], ['name' => 'audit_logs_user_id'])
            ->create();

        $this->table('customer_connection_ips', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', ['default' => Literal::from('uuid_generate_v4()'), 'null' => false])
            ->addColumn('name', 'string', ['null' => true])
            ->addColumn('customer_connection_id', 'uuid', ['null' => true])
            ->addColumn('ip_address', 'inet', ['null' => true])
            ->addColumn('note', 'text', ['null' => true])
            ->addColumn('created', 'timestamptimezone', ['null' => true])
            ->addColumn('modified', 'timestamptimezone', ['null' => true])
            ->addColumn('created_by', 'uuid', ['null' => true])
            ->addColumn('modified_by', 'uuid', ['null' => true])
            ->addIndex(['customer_connection_id'], ['name' => 'customer_connection_ips_customer_connection_id'])
            ->create();

        $this->table('customer_connections', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', ['default' => Literal::from('uuid_generate_v4()'), 'null' => false])
            ->addColumn('name', 'string', ['null' => true])
            ->addColumn('customer_point_id', 'uuid', ['null' => true])
            ->addColumn('customer_number', 'string', ['null' => true])
            ->addColumn('contract_number', 'string', ['null' => true])
            ->addColumn('note', 'text', ['null' => true])
            ->addColumn('created', 'timestamptimezone', ['null' => true])
            ->addColumn('modified', 'timestamptimezone', ['null' => true])
            ->addColumn('access_point_id', 'uuid', ['null' => true])
            ->addColumn('created_by', 'uuid', ['null' => true])
            ->addColumn('modified_by', 'uuid', ['null' => true])
            ->addColumn('customer_url', 'string', ['null' => true])
            ->addColumn('contract_url', 'string', ['null' => true])
            ->addColumn('archived', 'timestamptimezone', ['null' => true])
            ->addColumn('archived_by', 'uuid', ['null' => true])
            ->addIndex(['customer_point_id'], ['name' => 'customer_connections_customer_point_id'])
            ->addIndex(['access_point_id'], ['name' => 'customer_connections_access_point_id'])
            ->addIndex(['archived'], ['name' => 'customer_connections_archived'])
            ->addIndex(['archived_by'], ['name' => 'customer_connections_archived_by'])
            ->create();

        $this->table('customer_points', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', ['default' => Literal::from('uuid_generate_v4()'), 'null' => false])
            ->addColumn('name', 'string', ['null' => true])
            ->addColumn('gps_x', 'float', ['null' => true])
            ->addColumn('gps_y', 'float', ['null' => true])
            ->addColumn('note', 'text', ['null' => true])
            ->addColumn('created', 'timestamptimezone', ['null' => true])
            ->addColumn('modified', 'timestamptimezone', ['null' => true])
            ->addColumn('created_by', 'uuid', ['null' => true])
            ->addColumn('modified_by', 'uuid', ['null' => true])
            ->create();

        $this->table('device_types', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', ['default' => Literal::from('uuid_generate_v4()'), 'null' => false])
            ->addColumn('name', 'string', ['null' => true])
            ->addColumn('identifier', 'string', ['null' => true])
            ->addColumn('snmp_community', 'string', ['null' => true])
            ->addColumn('note', 'text', ['null' => true])
            ->addColumn('created', 'timestamptimezone', ['null' => true])
            ->addColumn('modified', 'timestamptimezone', ['null' => true])
            ->addColumn('assign_access_point_by_device_name', 'boolean', ['default' => false, 'null' => false])
            ->addColumn('assign_customer_connection_by_ip', 'boolean', ['default' => false, 'null' => false])
            ->addColumn('allow_technicians_access', 'boolean', ['default' => false, 'null' => false])
            ->addColumn('automatically_set_a_unique_password', 'boolean', ['default' => false, 'null' => false])
            ->addColumn('created_by', 'uuid', ['null' => true])
            ->addColumn('modified_by', 'uuid', ['null' => true])
            ->create();

        $this->table('documentation_types', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', ['default' => Literal::from('uuid_generate_v4()'), 'null' => false])
            ->addColumn('name', 'string', ['null' => false])
            ->addColumn('position', 'integer', ['default' => 0, 'null' => false])
            ->addColumn('currently_offered', 'boolean', ['default' => true, 'null' => false])
            ->addColumn('date_required', 'boolean', ['default' => false, 'null' => false])
            ->addColumn('access_point_required', 'boolean', ['default' => false, 'null' => false])
            ->addColumn('note', 'text', ['null' => true])
            ->addColumn('created', 'timestamptimezone', ['null' => true])
            ->addColumn('created_by', 'uuid', ['null' => true])
            ->addColumn('modified', 'timestamptimezone', ['null' => true])
            ->addColumn('modified_by', 'uuid', ['null' => true])
            ->addIndex(['position'], ['name' => 'documentation_types_position'])
            ->create();

        $this->table('documentations', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', ['default' => Literal::from('uuid_generate_v4()'), 'null' => false])
            ->addColumn('access_point_id', 'uuid', ['null' => true])
            ->addColumn('documentation_type_id', 'uuid', ['null' => false])
            ->addColumn('happened_on', 'date', ['null' => true])
            ->addColumn('name', 'string', ['null' => true])
            ->addColumn('note', 'text', ['null' => true])
            ->addColumn('created', 'timestamptimezone', ['null' => true])
            ->addColumn('created_by', 'uuid', ['null' => true])
            ->addColumn('modified', 'timestamptimezone', ['null' => true])
            ->addColumn('modified_by', 'uuid', ['null' => true])
            ->addIndex(['access_point_id', 'happened_on'], ['name' => 'documentations_access_point_id_happened_on'])
            ->addIndex(['documentation_type_id', 'happened_on'], [
                'name' => 'documentations_documentation_type_id_happened_on',
            ])
            ->create();

        $this->table('electricity_meter_readings', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', ['default' => Literal::from('uuid_generate_v4()'), 'null' => false])
            ->addColumn('name', 'string', ['null' => true])
            ->addColumn('access_point_id', 'uuid', ['null' => true])
            ->addColumn('reading_date', 'date', ['null' => false])
            ->addColumn('reading_value', 'float', ['null' => true])
            ->addColumn('note', 'text', ['null' => true])
            ->addColumn('created', 'timestamptimezone', ['null' => true])
            ->addColumn('modified', 'timestamptimezone', ['null' => true])
            ->addColumn('created_by', 'uuid', ['null' => true])
            ->addColumn('modified_by', 'uuid', ['null' => true])
            ->addIndex(['access_point_id'], ['name' => 'electricity_meter_readings_access_point_id'])
            ->create();

        $this->table('failed_password_attempts', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', ['null' => false])
            ->addColumn('user_id', 'uuid', ['null' => false])
            ->addColumn('created', 'timestamptimezone', ['null' => false])
            ->create();

        $this->table('ip_address_ranges', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', ['default' => Literal::from('uuid_generate_v4()'), 'null' => false])
            ->addColumn('created', 'timestamptimezone', ['null' => true])
            ->addColumn('created_by', 'uuid', ['null' => true])
            ->addColumn('modified', 'timestamptimezone', ['null' => true])
            ->addColumn('modified_by', 'uuid', ['null' => true])
            ->addColumn('name', 'string', ['null' => true])
            ->addColumn('ip_network', 'cidr', ['null' => false])
            ->addColumn('ip_gateway', 'inet', ['null' => true])
            ->addColumn('access_point_id', 'uuid', ['null' => true])
            ->addColumn('parent_ip_address_range_id', 'uuid', ['null' => true])
            ->addColumn('note', 'text', ['null' => true])
            ->addColumn('for_subnets', 'boolean', ['default' => false, 'null' => false])
            ->addColumn('for_customer_addresses_set_via_radius', 'boolean', ['default' => false, 'null' => false])
            ->addColumn('for_customer_addresses_set_manually', 'boolean', ['default' => false, 'null' => false])
            ->addColumn('for_technology_addresses_set_manually', 'boolean', ['default' => false, 'null' => false])
            ->addColumn('for_customer_networks_set_via_radius', 'boolean', ['default' => false, 'null' => false])
            ->addColumn('for_customer_networks_set_manually', 'boolean', ['default' => false, 'null' => false])
            ->addColumn('for_technology_networks_set_manually', 'boolean', ['default' => false, 'null' => false])
            ->addIndex(['access_point_id'], ['name' => 'ip_address_ranges_access_point_id'])
            ->addIndex(['parent_ip_address_range_id'], ['name' => 'ip_address_ranges_parent_ip_address_range_id'])
            ->create();

        $this->table('landlord_payments', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', ['default' => Literal::from('uuid_generate_v4()'), 'null' => false])
            ->addColumn('access_point_id', 'uuid', ['null' => true])
            ->addColumn('payment_purpose_id', 'uuid', ['null' => true])
            ->addColumn('payment_date', 'date', ['null' => false])
            ->addColumn('amount_paid', 'decimal', ['precision' => 8, 'scale' => 2, 'null' => true])
            ->addColumn('note', 'text', ['null' => true])
            ->addColumn('created', 'timestamptimezone', ['null' => true])
            ->addColumn('created_by', 'uuid', ['null' => true])
            ->addColumn('modified', 'timestamptimezone', ['null' => true])
            ->addColumn('modified_by', 'uuid', ['null' => true])
            ->addColumn('period_from', 'date', ['null' => true])
            ->addColumn('period_until', 'date', ['null' => true])
            ->addIndex(['access_point_id'], ['name' => 'landlord_payments_access_point_id'])
            ->addIndex(['payment_purpose_id'], ['name' => 'landlord_payments_payment_purpose_id'])
            ->create();

        $this->table('landlord_payments_electricity_details', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', ['default' => Literal::from('uuid_generate_v4()'), 'null' => false])
            ->addColumn('landlord_payment_id', 'uuid', ['null' => false])
            ->addColumn('low_rate_kwh_used', 'decimal', ['precision' => 8, 'scale' => 3, 'null' => true])
            ->addColumn('low_rate_price_per_kwh', 'decimal', ['precision' => 8, 'scale' => 2, 'null' => true])
            ->addColumn('high_rate_kwh_used', 'decimal', ['precision' => 8, 'scale' => 3, 'null' => true])
            ->addColumn('high_rate_price_per_kwh', 'decimal', ['precision' => 8, 'scale' => 2, 'null' => true])
            ->addColumn('created', 'timestamptimezone', ['null' => false])
            ->addColumn('created_by', 'uuid', ['null' => true])
            ->addColumn('modified', 'timestamptimezone', ['null' => false])
            ->addColumn('modified_by', 'uuid', ['null' => true])
            ->addIndex(['landlord_payment_id'], ['name' => 'landlord_payments_electricity_details_landlord_payment_id'])
            ->create();

        $this->table('manufacturers', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', ['default' => Literal::from('uuid_generate_v4()'), 'null' => false])
            ->addColumn('name', 'string', ['null' => true])
            ->addColumn('note', 'text', ['null' => true])
            ->addColumn('created', 'timestamptimezone', ['null' => true])
            ->addColumn('modified', 'timestamptimezone', ['null' => true])
            ->addColumn('created_by', 'uuid', ['null' => true])
            ->addColumn('modified_by', 'uuid', ['null' => true])
            ->create();

        $this->table('payment_purposes', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', ['default' => Literal::from('uuid_generate_v4()'), 'null' => false])
            ->addColumn('name', 'string', ['null' => true])
            ->addColumn('note', 'text', ['null' => true])
            ->addColumn('created', 'timestamptimezone', ['null' => true])
            ->addColumn('created_by', 'uuid', ['null' => true])
            ->addColumn('modified', 'timestamptimezone', ['null' => true])
            ->addColumn('modified_by', 'uuid', ['null' => true])
            ->create();

        $this->table('power_outage_scopes', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', ['default' => Literal::from('uuid_generate_v4()'), 'null' => false])
            ->addColumn('power_outage_id', 'uuid', ['null' => false])
            ->addColumn('scope', 'string', [
                'limit' => 64,
                'null' => false,
                'comment' => 'The reading the outage was seen by.',
            ])
            ->addColumn('created', 'timestamptimezone', ['null' => true])
            ->addColumn('modified', 'timestamptimezone', ['null' => true])
            ->addIndex(['power_outage_id', 'scope'], [
                'name' => 'power_outage_scopes_power_outage_id_scope',
                'unique' => true,
            ])
            ->addIndex(['scope'], ['name' => 'power_outage_scopes_scope'])
            ->create();

        $this->table('power_outages', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', ['default' => Literal::from('uuid_generate_v4()'), 'null' => false])
            ->addColumn('distributor', 'string', ['default' => 'CEZD', 'limit' => 16, 'null' => false])
            ->addColumn('outage_number', 'string', [
                'limit' => 32,
                'null' => false,
                'comment' => 'What the distributor keeps the outage under.',
            ])
            ->addColumn('begins_at', 'timestamptimezone', ['null' => true])
            ->addColumn('ends_at', 'timestamptimezone', ['null' => true])
            ->addColumn('cancelled', 'boolean', ['default' => false, 'null' => false])
            ->addColumn('cancelled_at', 'timestamptimezone', ['null' => true])
            ->addColumn('announcement_url', 'string', [
                'null' => true,
                'comment' => 'Where the official announcement of the outage is published.',
            ])
            ->addColumn('town_code', 'integer', ['null' => true])
            ->addColumn('town_name', 'string', ['null' => true])
            ->addColumn('district', 'string', ['null' => true])
            ->addColumn('summary', 'string', [
                'null' => true,
                'comment' => 'One line of where the outage is, for listings and mail.',
            ])
            ->addColumn('places', 'json', ['null' => true])
            ->addColumn('raw', 'json', ['null' => true])
            ->addColumn('created', 'timestamptimezone', ['null' => true])
            ->addColumn('modified', 'timestamptimezone', ['null' => true])
            ->addIndex(['distributor', 'outage_number'], [
                'name' => 'power_outages_distributor_outage_number',
                'unique' => true,
            ])
            ->addIndex(['begins_at'], ['name' => 'power_outages_begins_at'])
            ->addIndex(['town_code'], ['name' => 'power_outages_town_code'])
            ->create();

        $this->table('power_supplies', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', ['default' => Literal::from('uuid_generate_v4()'), 'null' => false])
            ->addColumn('name', 'string', ['null' => true])
            ->addColumn('power_supply_type_id', 'uuid', ['null' => true])
            ->addColumn('access_point_id', 'uuid', ['null' => true])
            ->addColumn('serial_number', 'string', ['null' => true])
            ->addColumn('battery_count', 'float', ['null' => true])
            ->addColumn('battery_voltage', 'float', ['null' => true])
            ->addColumn('battery_capacity', 'float', ['null' => true])
            ->addColumn('battery_replacement', 'date', ['null' => true])
            ->addColumn('battery_duration', 'float', ['null' => true])
            ->addColumn('note', 'text', ['null' => true])
            ->addColumn('created', 'timestamptimezone', ['null' => true])
            ->addColumn('modified', 'timestamptimezone', ['null' => true])
            ->addColumn('created_by', 'uuid', ['null' => true])
            ->addColumn('modified_by', 'uuid', ['null' => true])
            ->addIndex(['power_supply_type_id'], ['name' => 'power_supplies_power_supply_type_id'])
            ->addIndex(['access_point_id'], ['name' => 'power_supplies_access_point_id'])
            ->create();

        $this->table('power_supply_types', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', ['default' => Literal::from('uuid_generate_v4()'), 'null' => false])
            ->addColumn('name', 'string', ['null' => true])
            ->addColumn('manufacturer_id', 'uuid', ['null' => true])
            ->addColumn('voltage', 'float', ['null' => true])
            ->addColumn('current', 'float', ['null' => true])
            ->addColumn('part_number', 'string', ['null' => true])
            ->addColumn('note', 'text', ['null' => true])
            ->addColumn('created', 'timestamptimezone', ['null' => true])
            ->addColumn('modified', 'timestamptimezone', ['null' => true])
            ->addColumn('created_by', 'uuid', ['null' => true])
            ->addColumn('modified_by', 'uuid', ['null' => true])
            ->create();

        $this->table('radar_interferences', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', ['default' => Literal::from('uuid_generate_v4()'), 'null' => false])
            ->addColumn('name', 'string', ['null' => true])
            ->addColumn('mac_address', 'macaddr', ['null' => true])
            ->addColumn('ssid', 'string', ['null' => true])
            ->addColumn('signal', 'integer', ['null' => true])
            ->addColumn('radio_name', 'string', ['null' => true])
            ->addColumn('created', 'timestamptimezone', ['null' => true])
            ->addColumn('modified', 'timestamptimezone', ['null' => true])
            ->addColumn('created_by', 'uuid', ['null' => true])
            ->addColumn('modified_by', 'uuid', ['null' => true])
            ->create();

        $this->table('radio_links', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', ['default' => Literal::from('uuid_generate_v4()'), 'null' => false])
            ->addColumn('name', 'string', ['null' => true])
            ->addColumn('distance', 'integer', ['null' => true])
            ->addColumn('authorization_number', 'string', ['null' => true])
            ->addColumn('note', 'text', ['null' => true])
            ->addColumn('created', 'timestamptimezone', ['null' => true])
            ->addColumn('modified', 'timestamptimezone', ['null' => true])
            ->addColumn('created_by', 'uuid', ['null' => true])
            ->addColumn('modified_by', 'uuid', ['null' => true])
            ->create();

        $this->table('radio_unit_bands', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', ['default' => Literal::from('uuid_generate_v4()'), 'null' => false])
            ->addColumn('name', 'string', ['null' => true])
            ->addColumn('note', 'text', ['null' => true])
            ->addColumn('created', 'timestamptimezone', ['null' => true])
            ->addColumn('modified', 'timestamptimezone', ['null' => true])
            ->addColumn('color', 'string', ['default' => '#ffffff', 'limit' => 7, 'null' => false])
            ->addColumn('created_by', 'uuid', ['null' => true])
            ->addColumn('modified_by', 'uuid', ['null' => true])
            ->addColumn('minimum_frequency', 'integer', [
                'null' => true,
                'comment' => 'Lowest frequency of the band, in MHz as the radio units record it.',
            ])
            ->addColumn('maximum_frequency', 'integer', [
                'null' => true,
                'comment' => 'Highest frequency of the band, in MHz as the radio units record it.',
            ])
            ->addColumn('devices_require_radio_unit', 'boolean', [
                'default' => false,
                'null' => false,
                'comment' => 'Whether a device radio on this band has to be recorded as a radio unit.',
            ])
            ->addColumn('units_require_rlan_registration', 'boolean', [
                'default' => false,
                'null' => false,
                'comment' => 'Whether a radio unit on this band has to be registered with the regulator.',
            ])
            ->create();

        $this->table('radio_unit_types', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', ['default' => Literal::from('uuid_generate_v4()'), 'null' => false])
            ->addColumn('name', 'string', ['null' => true])
            ->addColumn('radio_unit_band_id', 'uuid', ['null' => true])
            ->addColumn('manufacturer_id', 'uuid', ['null' => true])
            ->addColumn('part_number', 'string', ['null' => true])
            ->addColumn('note', 'text', ['null' => true])
            ->addColumn('created', 'timestamptimezone', ['null' => true])
            ->addColumn('modified', 'timestamptimezone', ['null' => true])
            ->addColumn('created_by', 'uuid', ['null' => true])
            ->addColumn('modified_by', 'uuid', ['null' => true])
            ->create();

        $this->table('radio_units', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', ['default' => Literal::from('uuid_generate_v4()'), 'null' => false])
            ->addColumn('name', 'string', ['null' => true])
            ->addColumn('radio_unit_type_id', 'uuid', ['null' => true])
            ->addColumn('access_point_id', 'uuid', ['null' => true])
            ->addColumn('radio_link_id', 'uuid', ['null' => true])
            ->addColumn('antenna_type_id', 'uuid', ['null' => true])
            ->addColumn('polarization', 'string', ['limit' => 1, 'null' => true])
            ->addColumn('channel_width', 'integer', ['null' => true])
            ->addColumn('tx_frequency', 'integer', ['null' => true])
            ->addColumn('rx_frequency', 'integer', ['null' => true])
            ->addColumn('tx_power', 'integer', ['null' => true])
            ->addColumn('rx_signal', 'integer', ['null' => true])
            ->addColumn('operating_speed', 'integer', ['null' => true])
            ->addColumn('maximal_speed', 'integer', ['null' => true])
            ->addColumn('acm', 'boolean', ['null' => true])
            ->addColumn('atpc', 'boolean', ['null' => true])
            ->addColumn('firmware_version', 'string', ['null' => true])
            ->addColumn('serial_number', 'string', ['null' => true])
            ->addColumn('station_address', 'string', ['null' => true])
            ->addColumn('expiration_date', 'date', ['null' => true])
            ->addColumn('ip_address', 'inet', ['null' => true])
            ->addColumn('device_login', 'string', ['null' => true])
            ->addColumn('device_password', 'string', ['null' => true])
            ->addColumn('note', 'text', ['null' => true])
            ->addColumn('created', 'timestamptimezone', ['null' => true])
            ->addColumn('modified', 'timestamptimezone', ['null' => true])
            ->addColumn('created_by', 'uuid', ['null' => true])
            ->addColumn('modified_by', 'uuid', ['null' => true])
            ->addColumn('authorization_number', 'string', ['null' => true])
            ->addColumn('customer_connection_id', 'uuid', [
                'null' => true,
                'comment' => 'The customer the unit stands at, where it does not stand at an access point.',
            ])
            ->addIndex(['radio_unit_type_id'], ['name' => 'radio_units_radio_unit_type_id'])
            ->addIndex(['access_point_id'], ['name' => 'radio_units_access_point_id'])
            ->addIndex(['radio_link_id'], ['name' => 'radio_units_radio_link_id'])
            ->addIndex(['antenna_type_id'], ['name' => 'radio_units_antenna_type_id'])
            ->addIndex(['customer_connection_id'], ['name' => 'radio_units_customer_connection_id'])
            ->create();

        $this->table('rlan_stations', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', ['default' => Literal::from('uuid_generate_v4()'), 'null' => false])
            ->addColumn('station_id', 'integer', [
                'null' => false,
                'comment' => 'The number the register keeps the station under.',
            ])
            ->addColumn('user_id', 'integer', [
                'null' => true,
                'comment' => 'The account the station is registered to.',
            ])
            ->addColumn('station_pair_id', 'integer', [
                'null' => true,
                'comment' => 'The other end of the link, where the station is one end of a pair.',
            ])
            ->addColumn('master_id', 'integer', ['null' => true])
            ->addColumn('pair_position', 'string', [
                'limit' => 16,
                'null' => true,
                'comment' => 'Which end of the pair this is.',
            ])
            ->addColumn('type', 'string', [
                'null' => true,
                'comment' => 'The kind of station, as the register names it.',
            ])
            ->addColumn('type_name', 'string', [
                'null' => true,
                'comment' => 'The kind of station written out, as the register shows it.',
            ])
            ->addColumn('name', 'string', ['null' => true, 'comment' => 'What the station is called in the register.'])
            ->addColumn('latitude', 'float', ['null' => true])
            ->addColumn('longitude', 'float', ['null' => true])
            ->addColumn('mac_address', 'string', ['null' => true])
            ->addColumn('status', 'string', ['limit' => 64, 'null' => true])
            ->addColumn('is_ap', 'boolean', ['null' => true])
            ->addColumn('direction', 'integer', [
                'null' => true,
                'comment' => 'Main direction of radiation, in degrees.',
            ])
            ->addColumn('antenna_gain', 'decimal', [
                'precision' => 6,
                'scale' => 2,
                'null' => true,
                'comment' => 'Gain of the antenna the station was registered with, in dBi.',
            ])
            ->addColumn('channel_width', 'decimal', [
                'precision' => 9,
                'scale' => 3,
                'null' => true,
                'comment' => 'Occupied bandwidth, in MHz.',
            ])
            ->addColumn('power', 'decimal', [
                'precision' => 6,
                'scale' => 2,
                'null' => true,
                'comment' => 'Mean power delivered to the antenna, in dBm.',
            ])
            ->addColumn('eirp', 'decimal', ['precision' => 6, 'scale' => 2, 'null' => true])
            ->addColumn('frequency', 'integer', [
                'null' => true,
                'comment' => 'Transmit frequency in MHz, which only the fixed point-to-point stations carry.',
            ])
            ->addColumn('ratio_signal_interference', 'integer', ['null' => true])
            ->addColumn('parameters_read', 'timestamptimezone', [
                'null' => true,
                'comment' => 'When the technical parameters of the station were last read.',
            ])
            ->addColumn('raw', 'json', ['null' => true])
            ->addColumn('created', 'timestamptimezone', ['null' => true])
            ->addColumn('modified', 'timestamptimezone', ['null' => true])
            ->addIndex(['station_id'], ['name' => 'rlan_stations_station_id', 'unique' => true])
            ->addIndex(['user_id'], ['name' => 'rlan_stations_user_id'])
            ->addIndex(['name'], ['name' => 'rlan_stations_name'])
            ->addIndex(['mac_address'], ['name' => 'rlan_stations_mac_address'])
            ->create();

        $this->table('routeros_device_interfaces', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', ['default' => Literal::from('uuid_generate_v4()'), 'null' => false])
            ->addColumn('routeros_device_id', 'uuid', ['null' => true])
            ->addColumn('name', 'string', ['null' => true])
            ->addColumn('comment', 'string', ['null' => true])
            ->addColumn('mac_address', 'macaddr', ['null' => true])
            ->addColumn('ssid', 'string', ['null' => true])
            ->addColumn('band', 'string', ['null' => true])
            ->addColumn('frequency', 'integer', ['null' => true])
            ->addColumn('noise_floor', 'integer', ['null' => true])
            ->addColumn('client_count', 'integer', ['null' => true])
            ->addColumn('overall_tx_ccq', 'integer', ['null' => true])
            ->addColumn('interface_index', 'integer', ['null' => true])
            ->addColumn('interface_type', 'integer', ['null' => true])
            ->addColumn('interface_admin_status', 'integer', ['null' => true])
            ->addColumn('interface_oper_status', 'integer', ['null' => true])
            ->addColumn('created', 'timestamptimezone', ['null' => true])
            ->addColumn('modified', 'timestamptimezone', ['null' => true])
            ->addColumn('bssid', 'macaddr', ['null' => true])
            ->addColumn('created_by', 'uuid', ['null' => true])
            ->addColumn('modified_by', 'uuid', ['null' => true])
            ->addIndex(['routeros_device_id'], ['name' => 'routeros_device_interfaces_routeros_device_id'])
            ->create();

        $this->table('routeros_device_ips', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', ['default' => Literal::from('uuid_generate_v4()'), 'null' => false])
            ->addColumn('routeros_device_id', 'uuid', ['null' => true])
            ->addColumn('name', 'string', ['null' => true])
            ->addColumn('ip_address', 'inet', ['null' => true])
            ->addColumn('interface_index', 'integer', ['null' => true])
            ->addColumn('created', 'timestamptimezone', ['null' => true])
            ->addColumn('modified', 'timestamptimezone', ['null' => true])
            ->addColumn('created_by', 'uuid', ['null' => true])
            ->addColumn('modified_by', 'uuid', ['null' => true])
            ->addIndex(['routeros_device_id'], ['name' => 'routeros_device_ips_routeros_device_id'])
            ->create();
        // computed by the database, which the table builder cannot ask for
        $this->execute(
            'ALTER TABLE routeros_device_ips ADD COLUMN ip_network cidr'
            . ' GENERATED ALWAYS AS (network(ip_address)) STORED',
        );

        $this->table('routeros_devices', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', ['default' => Literal::from('uuid_generate_v4()'), 'null' => false])
            ->addColumn('name', 'string', ['null' => true])
            ->addColumn('access_point_id', 'uuid', ['null' => true])
            ->addColumn('device_type_id', 'uuid', ['null' => true])
            ->addColumn('ip_address', 'inet', ['null' => true])
            ->addColumn('system_description', 'string', ['null' => true])
            ->addColumn('board_name', 'string', ['null' => true])
            ->addColumn('serial_number', 'string', ['null' => true])
            ->addColumn('software_version', 'string', ['null' => true])
            ->addColumn('firmware_version', 'string', ['null' => true])
            ->addColumn('created', 'timestamptimezone', ['null' => true])
            ->addColumn('modified', 'timestamptimezone', ['null' => true])
            ->addColumn('customer_connection_id', 'uuid', ['null' => true])
            ->addColumn('created_by', 'uuid', ['null' => true])
            ->addColumn('modified_by', 'uuid', ['null' => true])
            ->addIndex(['access_point_id'], ['name' => 'routeros_devices_access_point_id'])
            ->addIndex(['device_type_id'], ['name' => 'routeros_devices_device_type_id'])
            ->addIndex(['customer_connection_id'], ['name' => 'routeros_devices_customer_connection_id'])
            ->create();

        $this->table('sessions', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'string', ['limit' => 40, 'null' => false])
            ->addColumn('data', 'binary', ['null' => true])
            ->addColumn('expires', 'integer', ['null' => true])
            ->addColumn('created', 'timestamptimezone', ['null' => true])
            ->addColumn('modified', 'timestamptimezone', ['null' => true])
            ->create();

        $this->table('social_accounts', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', ['null' => false])
            ->addColumn('user_id', 'uuid', ['null' => false])
            ->addColumn('provider', 'string', ['null' => false])
            ->addColumn('username', 'string', ['null' => true])
            ->addColumn('reference', 'string', ['null' => false])
            ->addColumn('avatar', 'text', ['null' => true])
            ->addColumn('description', 'text', ['null' => true])
            ->addColumn('link', 'string', ['null' => false])
            ->addColumn('token', 'string', ['limit' => 500, 'null' => false])
            ->addColumn('token_secret', 'string', ['limit' => 500, 'null' => true])
            ->addColumn('token_expires', 'timestamptimezone', ['null' => true])
            ->addColumn('active', 'boolean', ['default' => true, 'null' => false])
            ->addColumn('data', 'text', ['null' => false])
            ->addColumn('created', 'timestamptimezone', ['null' => false])
            ->addColumn('modified', 'timestamptimezone', ['null' => false])
            ->create();

        $this->table('task_collaborators', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', ['default' => Literal::from('uuid_generate_v4()'), 'null' => false])
            ->addColumn('task_id', 'uuid', ['null' => false])
            ->addColumn('user_id', 'uuid', ['null' => false])
            ->addColumn('created', 'timestamptimezone', ['null' => true])
            ->addColumn('created_by', 'uuid', ['null' => true])
            ->addColumn('modified', 'timestamptimezone', ['null' => true])
            ->addColumn('modified_by', 'uuid', ['null' => true])
            ->addIndex(['task_id', 'user_id'], ['name' => 'task_collaborators_task_id_user_id', 'unique' => true])
            ->addIndex(['user_id'], ['name' => 'task_collaborators_user_id'])
            ->create();

        $this->table('task_states', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', ['default' => Literal::from('uuid_generate_v4()'), 'null' => false])
            ->addColumn('name', 'string', ['null' => true])
            ->addColumn('color', 'string', ['default' => '#ffffff', 'limit' => 7, 'null' => false])
            ->addColumn('priority', 'integer', ['default' => 0, 'null' => false])
            ->addColumn('completed', 'boolean', ['default' => false, 'null' => false])
            ->addColumn('created', 'timestamptimezone', ['null' => false])
            ->addColumn('created_by', 'uuid', ['null' => true])
            ->addColumn('modified', 'timestamptimezone', ['null' => false])
            ->addColumn('modified_by', 'uuid', ['null' => true])
            ->create();

        $this->table('task_types', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', ['default' => Literal::from('uuid_generate_v4()'), 'null' => false])
            ->addColumn('name', 'string', ['null' => true])
            ->addColumn('access_point_required', 'boolean', ['default' => false, 'null' => false])
            ->addColumn('created', 'timestamptimezone', ['null' => false])
            ->addColumn('created_by', 'uuid', ['null' => true])
            ->addColumn('modified', 'timestamptimezone', ['null' => false])
            ->addColumn('modified_by', 'uuid', ['null' => true])
            ->addColumn('report_on_completion', 'boolean', ['default' => false, 'null' => false])
            ->create();

        $this->table('tasks', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', ['default' => Literal::from('uuid_generate_v4()'), 'null' => false])
            ->addColumn('nid', 'biginteger', ['identity' => true, 'null' => false])
            ->addColumn('task_state_id', 'uuid', ['null' => false])
            ->addColumn('task_type_id', 'uuid', ['null' => false])
            ->addColumn('user_id', 'uuid', ['null' => true])
            ->addColumn('subject', 'string', ['null' => true])
            ->addColumn('text', 'text', ['null' => true])
            ->addColumn('priority', 'integer', ['default' => 0, 'null' => false])
            ->addColumn('email', 'string', ['null' => true])
            ->addColumn('phone', 'string', ['null' => true])
            ->addColumn('start_date', 'date', ['null' => true])
            ->addColumn('finish_date', 'date', ['null' => true])
            ->addColumn('estimated_date', 'date', ['null' => true])
            ->addColumn('critical_date', 'date', ['null' => true])
            ->addColumn('access_point_id', 'uuid', ['null' => true])
            ->addColumn('created', 'timestamptimezone', ['null' => false])
            ->addColumn('created_by', 'uuid', ['null' => true])
            ->addColumn('modified', 'timestamptimezone', ['null' => false])
            ->addColumn('modified_by', 'uuid', ['null' => true])
            ->addIndex(['nid'], ['name' => 'tasks_nid', 'unique' => true])
            ->addIndex(['task_state_id'], ['name' => 'tasks_task_state_id'])
            ->addIndex(['task_type_id'], ['name' => 'tasks_task_type_id'])
            ->addIndex(['access_point_id'], ['name' => 'tasks_access_point_id'])
            ->create();

        $this->table('users', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', ['null' => false])
            ->addColumn('username', 'string', ['null' => false])
            ->addColumn('email', 'string', ['null' => true])
            ->addColumn('password', 'string', ['null' => false])
            ->addColumn('first_name', 'string', ['limit' => 50, 'null' => true])
            ->addColumn('last_name', 'string', ['limit' => 50, 'null' => true])
            ->addColumn('token', 'string', ['null' => true])
            ->addColumn('token_expires', 'timestamptimezone', ['null' => true])
            ->addColumn('api_token', 'string', ['null' => true])
            ->addColumn('activation_date', 'timestamptimezone', ['null' => true])
            ->addColumn('tos_date', 'timestamptimezone', ['null' => true])
            ->addColumn('active', 'boolean', ['default' => false, 'null' => false])
            ->addColumn('is_superuser', 'boolean', ['default' => false, 'null' => false])
            ->addColumn('role', 'string', ['default' => 'user', 'null' => true])
            ->addColumn('created', 'timestamptimezone', ['null' => false])
            ->addColumn('modified', 'timestamptimezone', ['null' => false])
            ->addColumn('secret', 'string', ['limit' => 32, 'null' => true])
            ->addColumn('secret_verified', 'boolean', ['null' => true])
            ->addColumn('additional_data', 'text', ['null' => true])
            ->addColumn('last_login', 'timestamptimezone', ['null' => true])
            ->addColumn('lockout_time', 'timestamptimezone', ['null' => true])
            ->addColumn('login_token', 'string', ['limit' => 32, 'null' => true])
            ->addColumn('login_token_date', 'timestamptimezone', ['null' => true])
            ->addColumn('token_send_requested', 'boolean', ['default' => false, 'null' => false])
            ->addColumn('user_settings', 'json', ['null' => true])
            ->addColumn('holds_tasks', 'boolean', ['default' => true, 'null' => false])
            ->addIndex(['login_token'], ['name' => 'users_login_token'])
            ->create();

        $this->table('access_point_contacts')
            ->addForeignKey('access_point_id', 'access_points', 'id', [
                'constraint' => 'access_point_contacts_access_point_id_fkey',
            ])
            ->addForeignKey('created_by', 'users', 'id', ['constraint' => 'access_point_contacts_created_by_fkey'])
            ->addForeignKey('modified_by', 'users', 'id', ['constraint' => 'access_point_contacts_modified_by_fkey'])
            ->update();

        $this->table('access_point_power_outages')
            ->addForeignKey('access_point_id', 'access_points', 'id', [
                'delete' => 'CASCADE',
                'constraint' => 'access_point_power_outages_access_point_id_fkey',
            ])
            ->addForeignKey('access_point_supply_address_id', 'access_point_supply_addresses', 'id', [
                'delete' => 'SET_NULL',
                'constraint' => 'access_point_power_outages_access_point_supply_address_id_fk',
            ])
            ->addForeignKey('power_outage_id', 'power_outages', 'id', [
                'delete' => 'CASCADE',
                'constraint' => 'access_point_power_outages_power_outage_id_fkey',
            ])
            ->update();

        $this->table('access_point_supply_addresses')
            ->addForeignKey('access_point_id', 'access_points', 'id', [
                'delete' => 'CASCADE',
                'constraint' => 'access_point_supply_addresses_access_point_id_fkey',
            ])
            ->update();

        $this->table('access_point_types')
            ->addForeignKey('created_by', 'users', 'id', ['constraint' => 'access_point_types_created_by_fkey'])
            ->addForeignKey('modified_by', 'users', 'id', ['constraint' => 'access_point_types_modified_by_fkey'])
            ->update();

        $this->table('access_points')
            ->addForeignKey('access_point_type_id', 'access_point_types', 'id', [
                'constraint' => 'access_points_access_point_type_id_fkey',
            ])
            ->addForeignKey('archived_by', 'users', 'id', ['constraint' => 'access_points_archived_by_fkey'])
            ->addForeignKey('created_by', 'users', 'id', ['constraint' => 'access_points_created_by_fkey'])
            ->addForeignKey('modified_by', 'users', 'id', ['constraint' => 'access_points_modified_by_fkey'])
            ->addForeignKey('parent_access_point_id', 'access_points', 'id', [
                'constraint' => 'access_points_parent_access_point_id_fkey',
            ])
            ->update();

        $this->table('antenna_types')
            ->addForeignKey('created_by', 'users', 'id', ['constraint' => 'antenna_types_created_by_fkey'])
            ->addForeignKey('manufacturer_id', 'manufacturers', 'id', [
                'constraint' => 'antenna_types_manufacturer_id_fkey',
            ])
            ->addForeignKey('modified_by', 'users', 'id', ['constraint' => 'antenna_types_modified_by_fkey'])
            ->addForeignKey('radio_unit_band_id', 'radio_unit_bands', 'id', [
                'constraint' => 'antenna_types_radio_unit_band_id_fkey',
            ])
            ->update();

        $this->table('customer_connection_ips')
            ->addForeignKey('created_by', 'users', 'id', ['constraint' => 'customer_connection_ips_created_by_fkey'])
            ->addForeignKey('customer_connection_id', 'customer_connections', 'id', [
                'constraint' => 'customer_connection_ips_customer_connection_id_fkey',
            ])
            ->addForeignKey('modified_by', 'users', 'id', ['constraint' => 'customer_connection_ips_modified_by_fkey'])
            ->update();

        $this->table('customer_connections')
            ->addForeignKey('access_point_id', 'access_points', 'id', [
                'constraint' => 'customer_connections_access_point_id_fkey',
            ])
            ->addForeignKey('archived_by', 'users', 'id', ['constraint' => 'customer_connections_archived_by_fkey'])
            ->addForeignKey('created_by', 'users', 'id', ['constraint' => 'customer_connections_created_by_fkey'])
            ->addForeignKey('customer_point_id', 'customer_points', 'id', [
                'constraint' => 'customer_connections_customer_point_id_fkey',
            ])
            ->addForeignKey('modified_by', 'users', 'id', ['constraint' => 'customer_connections_modified_by_fkey'])
            ->update();

        $this->table('customer_points')
            ->addForeignKey('created_by', 'users', 'id', ['constraint' => 'customer_points_created_by_fkey'])
            ->addForeignKey('modified_by', 'users', 'id', ['constraint' => 'customer_points_modified_by_fkey'])
            ->update();

        $this->table('device_types')
            ->addForeignKey('created_by', 'users', 'id', ['constraint' => 'device_types_created_by_fkey'])
            ->addForeignKey('modified_by', 'users', 'id', ['constraint' => 'device_types_modified_by_fkey'])
            ->update();

        $this->table('documentation_types')
            ->addForeignKey('created_by', 'users', 'id', ['constraint' => 'documentation_types_created_by_fkey'])
            ->addForeignKey('modified_by', 'users', 'id', ['constraint' => 'documentation_types_modified_by_fkey'])
            ->update();

        $this->table('documentations')
            ->addForeignKey('access_point_id', 'access_points', 'id', [
                'constraint' => 'documentations_access_point_id_fkey',
            ])
            ->addForeignKey('created_by', 'users', 'id', ['constraint' => 'documentations_created_by_fkey'])
            ->addForeignKey('documentation_type_id', 'documentation_types', 'id', [
                'constraint' => 'documentations_documentation_type_id_fkey',
            ])
            ->addForeignKey('modified_by', 'users', 'id', ['constraint' => 'documentations_modified_by_fkey'])
            ->update();

        $this->table('electricity_meter_readings')
            ->addForeignKey('access_point_id', 'access_points', 'id', [
                'constraint' => 'electricity_meter_readings_access_point_id_fkey',
            ])
            ->addForeignKey('created_by', 'users', 'id', ['constraint' => 'electricity_meter_readings_created_by_fkey'])
            ->addForeignKey('modified_by', 'users', 'id', [
                'constraint' => 'electricity_meter_readings_modified_by_fkey',
            ])
            ->update();

        $this->table('failed_password_attempts')
            ->addForeignKey('user_id', 'users', 'id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
                'constraint' => 'failed_password_attempts_user_id_fkey',
            ])
            ->update();

        $this->table('ip_address_ranges')
            ->addForeignKey('access_point_id', 'access_points', 'id', [
                'constraint' => 'ip_address_ranges_access_point_id_fkey',
            ])
            ->addForeignKey('created_by', 'users', 'id', ['constraint' => 'ip_address_ranges_created_by_fkey'])
            ->addForeignKey('modified_by', 'users', 'id', ['constraint' => 'ip_address_ranges_modified_by_fkey'])
            ->addForeignKey('parent_ip_address_range_id', 'ip_address_ranges', 'id', [
                'constraint' => 'ip_address_ranges_parent_ip_address_range_id_fkey',
            ])
            ->update();

        $this->table('landlord_payments')
            ->addForeignKey('access_point_id', 'access_points', 'id', [
                'constraint' => 'landlord_payments_access_point_id_fkey',
            ])
            ->addForeignKey('created_by', 'users', 'id', ['constraint' => 'landlord_payments_created_by_fkey'])
            ->addForeignKey('modified_by', 'users', 'id', ['constraint' => 'landlord_payments_modified_by_fkey'])
            ->addForeignKey('payment_purpose_id', 'payment_purposes', 'id', [
                'constraint' => 'landlord_payments_payment_purpose_id_fkey',
            ])
            ->update();

        $this->table('landlord_payments_electricity_details')
            ->addForeignKey('created_by', 'users', 'id', [
                'constraint' => 'landlord_payments_electricity_details_created_by_fkey',
            ])
            ->addForeignKey('landlord_payment_id', 'landlord_payments', 'id', [
                'constraint' => 'landlord_payments_electricity_details_landlord_payment_id_fkey',
            ])
            ->addForeignKey('modified_by', 'users', 'id', [
                'constraint' => 'landlord_payments_electricity_details_modified_by_fkey',
            ])
            ->update();

        $this->table('manufacturers')
            ->addForeignKey('created_by', 'users', 'id', ['constraint' => 'manufacturers_created_by_fkey'])
            ->addForeignKey('modified_by', 'users', 'id', ['constraint' => 'manufacturers_modified_by_fkey'])
            ->update();

        $this->table('payment_purposes')
            ->addForeignKey('created_by', 'users', 'id', ['constraint' => 'payment_purposes_created_by_fkey'])
            ->addForeignKey('modified_by', 'users', 'id', ['constraint' => 'payment_purposes_modified_by_fkey'])
            ->update();

        $this->table('power_outage_scopes')
            ->addForeignKey('power_outage_id', 'power_outages', 'id', [
                'delete' => 'CASCADE',
                'constraint' => 'power_outage_scopes_power_outage_id_fkey',
            ])
            ->update();

        $this->table('power_supplies')
            ->addForeignKey('access_point_id', 'access_points', 'id', [
                'constraint' => 'power_supplies_access_point_id_fkey',
            ])
            ->addForeignKey('created_by', 'users', 'id', ['constraint' => 'power_supplies_created_by_fkey'])
            ->addForeignKey('modified_by', 'users', 'id', ['constraint' => 'power_supplies_modified_by_fkey'])
            ->addForeignKey('power_supply_type_id', 'power_supply_types', 'id', [
                'constraint' => 'power_supplies_power_supply_type_id_fkey',
            ])
            ->update();

        $this->table('power_supply_types')
            ->addForeignKey('created_by', 'users', 'id', ['constraint' => 'power_supply_types_created_by_fkey'])
            ->addForeignKey('manufacturer_id', 'manufacturers', 'id', [
                'constraint' => 'power_supply_types_manufacturer_id_fkey',
            ])
            ->addForeignKey('modified_by', 'users', 'id', ['constraint' => 'power_supply_types_modified_by_fkey'])
            ->update();

        $this->table('radar_interferences')
            ->addForeignKey('created_by', 'users', 'id', ['constraint' => 'radar_interferences_created_by_fkey'])
            ->addForeignKey('modified_by', 'users', 'id', ['constraint' => 'radar_interferences_modified_by_fkey'])
            ->update();

        $this->table('radio_links')
            ->addForeignKey('created_by', 'users', 'id', ['constraint' => 'radio_links_created_by_fkey'])
            ->addForeignKey('modified_by', 'users', 'id', ['constraint' => 'radio_links_modified_by_fkey'])
            ->update();

        $this->table('radio_unit_bands')
            ->addForeignKey('created_by', 'users', 'id', ['constraint' => 'radio_unit_bands_created_by_fkey'])
            ->addForeignKey('modified_by', 'users', 'id', ['constraint' => 'radio_unit_bands_modified_by_fkey'])
            ->update();

        $this->table('radio_unit_types')
            ->addForeignKey('created_by', 'users', 'id', ['constraint' => 'radio_unit_types_created_by_fkey'])
            ->addForeignKey('manufacturer_id', 'manufacturers', 'id', [
                'constraint' => 'radio_unit_types_manufacturer_id_fkey',
            ])
            ->addForeignKey('modified_by', 'users', 'id', ['constraint' => 'radio_unit_types_modified_by_fkey'])
            ->addForeignKey('radio_unit_band_id', 'radio_unit_bands', 'id', [
                'constraint' => 'radio_unit_types_radio_unit_band_id_fkey',
            ])
            ->update();

        $this->table('radio_units')
            ->addForeignKey('access_point_id', 'access_points', 'id', [
                'constraint' => 'radio_units_access_point_id_fkey',
            ])
            ->addForeignKey('antenna_type_id', 'antenna_types', 'id', [
                'constraint' => 'radio_units_antenna_type_id_fkey',
            ])
            ->addForeignKey('created_by', 'users', 'id', ['constraint' => 'radio_units_created_by_fkey'])
            ->addForeignKey('customer_connection_id', 'customer_connections', 'id', [
                'constraint' => 'radio_units_customer_connection_id_fkey',
            ])
            ->addForeignKey('modified_by', 'users', 'id', ['constraint' => 'radio_units_modified_by_fkey'])
            ->addForeignKey('radio_link_id', 'radio_links', 'id', ['constraint' => 'radio_units_radio_link_id_fkey'])
            ->addForeignKey('radio_unit_type_id', 'radio_unit_types', 'id', [
                'constraint' => 'radio_units_radio_unit_type_id_fkey',
            ])
            ->update();

        $this->table('routeros_device_interfaces')
            ->addForeignKey('created_by', 'users', 'id', ['constraint' => 'routeros_device_interfaces_created_by_fkey'])
            ->addForeignKey('modified_by', 'users', 'id', [
                'constraint' => 'routeros_device_interfaces_modified_by_fkey',
            ])
            ->addForeignKey('routeros_device_id', 'routeros_devices', 'id', [
                'constraint' => 'routeros_device_interfaces_routeros_device_id_fkey',
            ])
            ->update();

        $this->table('routeros_device_ips')
            ->addForeignKey('created_by', 'users', 'id', ['constraint' => 'routeros_device_ips_created_by_fkey'])
            ->addForeignKey('modified_by', 'users', 'id', ['constraint' => 'routeros_device_ips_modified_by_fkey'])
            ->addForeignKey('routeros_device_id', 'routeros_devices', 'id', [
                'constraint' => 'routeros_device_ips_routeros_device_id_fkey',
            ])
            ->update();

        $this->table('routeros_devices')
            ->addForeignKey('access_point_id', 'access_points', 'id', [
                'constraint' => 'routeros_devices_access_point_id_fkey',
            ])
            ->addForeignKey('created_by', 'users', 'id', ['constraint' => 'routeros_devices_created_by_fkey'])
            ->addForeignKey('customer_connection_id', 'customer_connections', 'id', [
                'constraint' => 'routeros_devices_customer_connection_id_fkey',
            ])
            ->addForeignKey('device_type_id', 'device_types', 'id', [
                'constraint' => 'routeros_devices_device_type_id_fkey',
            ])
            ->addForeignKey('modified_by', 'users', 'id', ['constraint' => 'routeros_devices_modified_by_fkey'])
            ->update();

        $this->table('social_accounts')
            ->addForeignKey('user_id', 'users', 'id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
                'constraint' => 'social_accounts_user_id_fkey',
            ])
            ->update();

        $this->table('task_collaborators')
            ->addForeignKey('created_by', 'users', 'id', ['constraint' => 'task_collaborators_created_by_fkey'])
            ->addForeignKey('modified_by', 'users', 'id', ['constraint' => 'task_collaborators_modified_by_fkey'])
            ->addForeignKey('task_id', 'tasks', 'id', [
                'delete' => 'CASCADE',
                'constraint' => 'task_collaborators_task_id_fkey',
            ])
            ->addForeignKey('user_id', 'users', 'id', ['constraint' => 'task_collaborators_user_id_fkey'])
            ->update();

        $this->table('task_states')
            ->addForeignKey('created_by', 'users', 'id', ['constraint' => 'task_states_created_by_fkey'])
            ->addForeignKey('modified_by', 'users', 'id', ['constraint' => 'task_states_modified_by_fkey'])
            ->update();

        $this->table('task_types')
            ->addForeignKey('created_by', 'users', 'id', ['constraint' => 'task_types_created_by_fkey'])
            ->addForeignKey('modified_by', 'users', 'id', ['constraint' => 'task_types_modified_by_fkey'])
            ->update();

        $this->table('tasks')
            ->addForeignKey('access_point_id', 'access_points', 'id', ['constraint' => 'tasks_access_point_id_fkey'])
            ->addForeignKey('created_by', 'users', 'id', ['constraint' => 'tasks_created_by_fkey'])
            ->addForeignKey('modified_by', 'users', 'id', ['constraint' => 'tasks_modified_by_fkey'])
            ->addForeignKey('task_state_id', 'task_states', 'id', ['constraint' => 'tasks_task_state_id_fkey'])
            ->addForeignKey('task_type_id', 'task_types', 'id', ['constraint' => 'tasks_task_type_id_fkey'])
            ->addForeignKey('user_id', 'users', 'id', ['constraint' => 'tasks_user_id_fkey'])
            ->update();
    }
}
