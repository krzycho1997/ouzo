<?php

namespace Ouzo\Restriction;

class NotEqualToRestriction extends Restriction {

    private $value;

    function __construct($value)
    {
        $this->value = $value;
    }

    public function toSql($fieldName)
    {
        return $fieldName . ' <> ?';
    }

    public function getValues()
    {
        return $this->value;
    }
}