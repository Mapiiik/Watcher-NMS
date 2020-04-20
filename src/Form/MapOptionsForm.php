<?php
declare(strict_types=1);

namespace App\Form;

use Cake\Form\Form;
use Cake\Form\Schema;
use Cake\Validation\Validator;

/**
 * MapOptions Form.
 */
class MapOptionsForm extends Form
{
    /**
     * Builds the schema for the modelless form
     *
     * @param \Cake\Form\Schema $schema From schema
     * @return \Cake\Form\Schema
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        return $schema->addField('routeros_ip_links', 'boolean')
            ->addField('routeros_wireless_links', 'boolean')
            ->addField('radio_links', 'boolean')
            ->addField('linked_customers', 'boolean')
            ->addField('access_point_id', 'uuid')
            ->addField('routeros_device_id', 'uuid');
}

    /**
     * Form validation builder
     *
     * @param \Cake\Validation\Validator $validator to use against the form
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        return $validator;
    }

    /**
     * Defines what to execute once the Form is processed
     *
     * @param array $data Form data.
     * @return bool
     */
    protected function _execute(array $data): bool
    {
        $this->setData($data);
        return true;
    }
}
