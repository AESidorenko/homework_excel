<?php

namespace App\Helper;

use App\Exception\JsonParametersMissingHttpException;

trait MissingArrayFieldsValidator
{
    public function AssertSchema(array $array, array $schemaFields): void
    {
        $missedFields = array_filter($schemaFields, function ($field) use ($array) {
            return !array_key_exists($field, $array);
        });

        if (!empty($missedFields)) {
            throw new JsonParametersMissingHttpException($missedFields);
        }

        return;
    }
}