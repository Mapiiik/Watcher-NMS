<?php
declare(strict_types=1);

use Migrations\BaseMigration;
use Migrations\Db\Literal;

class CreateRlanStations extends BaseMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/5/en/migrations.html#the-change-method
     *
     * @return void
     */
    public function change(): void
    {
        $this->table('rlan_stations', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', [
                'default' => Literal::from('uuid_generate_v4()'),
                'limit' => null,
                'null' => false,
            ])
            // What the register keeps the station under. The natural key of the mirror, and the
            // only identifier of a station that is certain to be its own.
            ->addColumn('station_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'comment' => 'The number the register keeps the station under.',
            ])
            // Accounts may share their stations with one another, and what is read is everything
            // shared with ours. This is what says which of them are ours.
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'comment' => 'The account the station is registered to.',
            ])
            ->addColumn('station_pair_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'comment' => 'The other end of the link, where the station is one end of a pair.',
            ])
            ->addColumn('master_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('pair_position', 'string', [
                'default' => null,
                'limit' => 16,
                'null' => true,
                'comment' => 'Which end of the pair this is.',
            ])
            // Kept as text rather than as a list of the kinds the register documents: it answers
            // with kinds it does not document, and a station of an unknown kind is still a station.
            ->addColumn('type', 'string', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'comment' => 'The kind of station, as the register names it.',
            ])
            ->addColumn('type_name', 'string', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'comment' => 'The kind of station written out, as the register shows it.',
            ])
            // What a radio unit records the registration under. Not unique - both ends of a
            // point-to-point link are commonly filed under one name - so it names a station only
            // together with something that tells the two ends apart.
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'comment' => 'What the station is called in the register.',
            ])
            // Float rather than decimal, so that the distance from an access point is arithmetic
            // between two columns of one type - the coordinates of an access point are float too.
            ->addColumn('latitude', 'float', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('longitude', 'float', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            // Text rather than `macaddr`, unlike the addresses read off devices: this one was
            // typed into a form by whoever filed the registration, and one address written badly
            // would otherwise refuse the row and with it the whole reading. It is put into the
            // shape a MAC address is written in as it is read, where it is one at all.
            ->addColumn('mac_address', 'string', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            // As the register words it rather than as a state of its own: what is read carries the
            // label a reader of the portal sees, and inventing a vocabulary for it here would only
            // be something else to keep in step.
            ->addColumn('status', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => true,
            ])
            ->addColumn('is_ap', 'boolean', [
                'default' => null,
                'null' => true,
            ])
            // The rest is asked only of the stations in the 60 GHz bands. On the bands registered
            // by address alone the register holds none of it, so an empty column here usually says
            // the station is not of a kind that is asked rather than that somebody left it out.
            ->addColumn('direction', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'comment' => 'Main direction of radiation, in degrees.',
            ])
            ->addColumn('antenna_gain', 'decimal', [
                'default' => null,
                'precision' => 6,
                'scale' => 2,
                'null' => true,
                'comment' => 'Gain of the antenna the station was registered with, in dBi.',
            ])
            ->addColumn('channel_width', 'decimal', [
                'default' => null,
                'precision' => 9,
                'scale' => 3,
                'null' => true,
                'comment' => 'Occupied bandwidth, in MHz.',
            ])
            ->addColumn('power', 'decimal', [
                'default' => null,
                'precision' => 6,
                'scale' => 2,
                'null' => true,
                'comment' => 'Mean power delivered to the antenna, in dBm.',
            ])
            ->addColumn('eirp', 'decimal', [
                'default' => null,
                'precision' => 6,
                'scale' => 2,
                'null' => true,
            ])
            ->addColumn('frequency', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'comment' => 'Transmit frequency in MHz, which only the fixed point-to-point stations carry.',
            ])
            ->addColumn('ratio_signal_interference', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            // The technical data is read separately from the list of stations, and not every
            // station has any to read. Without this there would be no telling a station whose
            // parameters the register does not keep from one whose parameters were never fetched.
            ->addColumn('parameters_read', 'timestamp', [
                'timezone' => true,
                'default' => null,
                'null' => true,
                'comment' => 'When the technical parameters of the station were last read.',
            ])
            // The station as it arrived. Nothing the register hands over is promised, and the
            // first question asked of a surprising row is what it actually said.
            ->addColumn('raw', 'jsonb', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'timezone' => true,
                'default' => null,
                'null' => true,
            ])
            // Also when the station was last read from the register, which is what the reading is
            // swept by and what the listing reports its own age from.
            ->addColumn('modified', 'timestamp', [
                'timezone' => true,
                'default' => null,
                'null' => true,
            ])
            ->addIndex(['station_id'], ['unique' => true])
            // The two a radio unit is matched to a station by.
            ->addIndex(['mac_address'])
            ->addIndex(['name'])
            ->addIndex(['user_id'])
            ->create();
    }
}
