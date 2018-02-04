<?php

namespace controllers;


use utils\validators\JsonSchemaValidator;

/**
 * Request POST data validation controller
 * Class ValidationController
 * @package utils
 */
class ValidationController
{
    /**
     * Get POST body data
     * @return bool|string
     */
    protected function getData()
    {
        return file_get_contents('php://input');
    }

    /**
     * Process POST data with schema and custom types for validation
     * @param string $schema
     * @param array $customTypesMap
     */
    public function process(string $schema = '', array $customTypesMap = [])
    {
        $rawData = $this->getData();
        try {
            $validator = new JsonSchemaValidator($customTypesMap);
            $result = $validator->validate($schema, $rawData);
            http_response_code(200);
            echo json_encode($result);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode($e->getMessage());
        }
    }

}