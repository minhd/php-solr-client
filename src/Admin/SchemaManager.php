<?php


namespace MinhD\SolrClient\Admin;

use MinhD\SolrClient\SolrManager;

class SchemaManager extends SolrManager
{
    public function get()
    {
        return $this->solr()->request('GET', $this->solr()->getCore().'/schema', []);
    }

    /**
     * Update field definition if field exists, otherwise, add
     *
     * @param array $fields
     *
     * @return mixed
     */
    public function setFields($fields = [])
    {
        $schemaResult = $this->get();
        $existingSchema = $schemaResult['schema'];

        $request = [];
        foreach ($fields as $field) {
            $mode = $this->hasField($field['name'], $existingSchema) ? 'replace-field' : 'add-field';
            $request[$mode][] = $field;
        }

        return $this->request($request);
    }

    public function deleteFields($fields)
    {
        $schemaResult = $this->get();
        $existingSchema = $schemaResult['schema'];

        $request = [];
        foreach ($fields as $field) {
            if ($this->hasField($field, $existingSchema)) {
                $request['delete-field'][] = ['name' => $field];
            }
        }

        return $this->request($request);
    }

    /**
     * Check if a field already exists in the schema
     *
     * @param string $fieldName
     * @param null   $existingSchema
     *
     * @return bool
     */
    public function hasField($fieldName, $existingSchema = null)
    {
        $field = $this->getField($fieldName, $existingSchema);

        return $field === null ? false : true;
    }

    /**
     * Get a single field by name
     *
     * @param string $fieldName
     * @param null   $existingSchema
     *
     * @return mixed
     */
    public function getField($fieldName, $existingSchema = null)
    {
        if ($existingSchema === null) {
            $schemaResult = $this->get();
            $existingSchema = $schemaResult['schema'];
        }

        foreach ($existingSchema['fields'] as $field) {
            if ($field['name'] == $fieldName) {
                return $field;
            }
        }

        return null;
    }

    /**
     * Useful request for schema
     *
     * @param array $request
     *
     * @return mixed
     */
    public function request($request = [])
    {
        return $this->solr()->request('POST', $this->solr()->getCore().'/schema', [], $request);
    }
}
