<?php

namespace Marble\Admin\Database\Seeders;

use Illuminate\Database\Seeder;
use Marble\Admin\Models\FieldType;

class FieldTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Text Field', 'identifier' => 'textfield', 'class' => \Marble\Admin\FieldTypes\Textfield::class],
            ['name' => 'Text Block', 'identifier' => 'textblock', 'class' => \Marble\Admin\FieldTypes\Textblock::class],
            ['name' => 'Select Box', 'identifier' => 'selectbox', 'class' => \Marble\Admin\FieldTypes\Selectbox::class],
            ['name' => 'HTML Block', 'identifier' => 'htmlblock', 'class' => \Marble\Admin\FieldTypes\Htmlblock::class],
            ['name' => 'Date', 'identifier' => 'date', 'class' => \Marble\Admin\FieldTypes\Date::class],
            ['name' => 'Date & Time', 'identifier' => 'datetime', 'class' => \Marble\Admin\FieldTypes\Datetime::class],
            ['name' => 'Time', 'identifier' => 'time', 'class' => \Marble\Admin\FieldTypes\Time::class],
            ['name' => 'Object Relation', 'identifier' => 'object_relation', 'class' => \Marble\Admin\FieldTypes\ObjectRelation::class],
            ['name' => 'Object Relation List', 'identifier' => 'object_relation_list', 'class' => \Marble\Admin\FieldTypes\ObjectRelationList::class],
            ['name' => 'Image', 'identifier' => 'image', 'class' => \Marble\Admin\FieldTypes\Image::class],
            ['name' => 'Image Gallery', 'identifier' => 'images', 'class' => \Marble\Admin\FieldTypes\Images::class],
            ['name' => 'Key/Value Store', 'identifier' => 'keyvalue_store', 'class' => \Marble\Admin\FieldTypes\KeyValueStore::class],
            ['name' => 'Checkbox', 'identifier' => 'checkbox', 'class' => \Marble\Admin\FieldTypes\Checkbox::class],
            ['name' => 'Repeater', 'identifier' => 'repeater', 'class' => \Marble\Admin\FieldTypes\Repeater::class],
            ['name' => 'File', 'identifier' => 'file', 'class' => \Marble\Admin\FieldTypes\File::class],
            ['name' => 'Files', 'identifier' => 'files', 'class' => \Marble\Admin\FieldTypes\Files::class],
        ];

        foreach ($types as $type) {
            FieldType::updateOrCreate(
                ['identifier' => $type['identifier']],
                $type
            );
        }
    }
}
