<?php

namespace Marble\Admin\App\Attributes;

class KeyvalueStore extends Attribute
{
    protected $viewPrefix = 'admin';

    public function getValues($values)
    {
        $keyValue = array();

        foreach ($values as $languageId => $rows) {
            $keyValue[$languageId] = (object) array();

            if (!is_array($rows)) {
                continue;
            }

            foreach ($rows as $row) {
                $keyValue[$languageId]->{$row['key']} = $row['value'];
            }
        }

        return $keyValue;
    }
}
