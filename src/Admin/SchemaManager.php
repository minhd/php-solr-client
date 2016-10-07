<?php


namespace MinhD\SolrClient\Admin;

use MinhD\SolrClient\SolrManager;

class SchemaManager extends SolrManager
{
    public function get($type = null)
    {
        $result = $this->solr()->request('GET', $this->solr()->getCore().'/schema', []);

        if ($type == null) {
            return $result['schema'];
        }

        return $result['schema'][$type];
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
        $existingSchema = $this->get();

        $request = [];
        foreach ($fields as $field) {
            $mode = $this->hasField($field['name'], $existingSchema) ? 'replace-field' : 'add-field';
            $request[$mode][] = $field;
        }

        return $this->request($request);
    }

    public function setFieldTypes($fieldTypes)
    {
        $schema = $this->get();

        $request = [];
        foreach ($fieldTypes as $fieldType) {
            $mode = $this->hasFieldType($fieldType['name'], $schema) ? 'replace-field-type' : 'add-field-type';
            $request[$mode][] = $fieldType;
        }

        return $this->request($request);
    }

    public function setDynamicFields($dynamicFields)
    {
        $schema = $this->get();

        $request = [];
        foreach ($dynamicFields as $field) {
            $mode = $this->hasDynamicField($field['name'], $schema) ? 'replace-dynamic-field' : 'add-dynamic-field';
            $request[$mode][] = $field;
        }

        return $this->request($request);
    }

    public function setCopyFields($fields)
    {
        $existingCopyFields = $this->get('copyFields');
        $request = [];
        foreach ($fields as $field) {
            if ($this->hasCopyField($field['source'], $field['dest'], $existingCopyFields) === false) {
                $request['add-copy-field'][] = $field;
            }
        }
        if (array_key_exists('add-copy-field', $request)) {
            return $this->request($request);
        }
    }

    public function deleteFields($fields)
    {
        $existingSchema = $this->get();

        $request = [];
        foreach ($fields as $field) {
            if ($this->hasField($field, $existingSchema)) {
                $request['delete-field'][] = ['name' => $field];
            }
        }

        return $this->request($request);
    }

    /**
     * @param array $fieldTypes
     *
     * @return mixed
     */
    public function deleteFieldTypes($fieldTypes)
    {
        $schema = $this->get();
        $request = [];
        foreach ($fieldTypes as $fieldType) {
            if ($this->hasFieldType($fieldType, $schema)) {
                $request['delete-field-type'][] = ['name' => $fieldType];
            }
        }

        return $this->request($request);
    }

    public function deleteDynamicFields($fields)
    {
        $schema = $this->get();
        $request = [];
        foreach ($fields as $field) {
            if ($this->hasDynamicField($field, $schema)) {
                $request['delete-dynamic-field'][] = ['name' => $field];
            }
        }

        return $this->request($request);
    }

    public function deleteCopyField($source, $dest, $existingCopyFields = null)
    {
        if ($existingCopyFields == null) {
            $existingCopyFields = $this->get('copyFields');
        }
        if ($this->hasCopyField($source, $dest, $existingCopyFields)) {
            return $this->request([
                'delete-copy-field' => [
                    'source' => $source,
                    'dest' => $dest
                ]
            ]);
        }
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

    public function hasFieldType($name, $schema = null)
    {
        $fieldType = $this->getFieldType($name, $schema);

        return $fieldType === null ? false : true;
    }

    public function hasDynamicField($name, $schema = null)
    {
        $dynamicField = $this->getDynamicField($name, $schema);

        return $dynamicField === null ? false : true;
    }

    public function hasCopyField($source, $dest, $existingCopyFields = null)
    {
        $copyField = $this->getCopyField($source, $dest, $existingCopyFields);

        return $copyField === null ? false : true;
    }

    /**
     * Get a single field by name
     *
     * @param string $name
     * @param null   $existingSchema
     *
     * @return mixed
     */
    public function getField($name, $existingSchema = null)
    {
        return $this->getFieldByname($name, 'fields', $existingSchema);
    }

    public function getFieldType($name, $existingSchema = null)
    {
        return $this->getFieldByName($name, 'fieldTypes', $existingSchema);
    }

    public function getDynamicField($name, $existingSchema = null)
    {
        return $this->getFieldByName($name, 'dynamicFields', $existingSchema);
    }

    public function getCopyField($source, $dest, $existing = null)
    {
        if ($existing === null) {
            $existing = $this->get('copyFields');
        }

        foreach ($existing as $field) {
            if ($field['source'] == $source && $field['dest'] == $dest) {
                return $field;
            }
        }

        return null;
    }

    public function getFieldByName($name, $type, $existing)
    {
        if ($existing === null) {
            $existing = $this->get();
        }

        foreach ($existing[$type] as $field) {
            if ($field['name'] == $name) {
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
