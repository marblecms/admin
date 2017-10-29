<?php

namespace Marble\Admin\App\Attributes;

use Request;
use Storage;
use File;
use Marble\Admin\App\Models\NodeTranslation;

class Images extends Attribute
{
    protected $viewPrefix = 'admin';
    protected $javascripts = array('images-edit.js');

    public function processValue($oldValue, $newValue, $nodeClassAttribute, $languageId)
    {
        if ($newValue !== 'noop') {
            $keys = explode(',', $newValue);
            foreach ($keys as $key) {
                Storage::delete($oldValue[$key]->filename);
                unset($oldValue[$key]);
            }
        }

        $fileKey = 'file_'.$nodeClassAttribute->id.'_'.$languageId;

        if (!$_FILES[$fileKey]['size']) {
            return $oldValue;
        }

        $value = (object) array();

        $file = Request::file($fileKey);
        $extension = $file->getClientOriginalExtension();
        $filename = $file->getFilename().'.'.$extension;
        Storage::put($filename,  File::get($file));

        $value->originalFilename = $file->getClientOriginalName();
        $value->filename = $filename;
        $value->size = $file->getSize();
        $value->transformations = (object) array();

        $oldValue[] = $value;

        return $oldValue;
    }

    public function ajaxEndpoint($request, $languageId)
    {
        $translation = NodeTranslation::where(
            array(
                'nodeClassAttributeId' => $this->attribute->id,
                'languageId' => $languageId,
            )
        )->get()->first();

        $images = unserialize($translation->value);

        if ($request->input('method') === 'saveTransformations') {
            $index = $request->input('index');

            $images[$index]->transformations = (object) $request->input('transformations');

            foreach ($images[$index]->transformations as &$transformation) {
                $transformation = (int) $transformation;
            }
        }

        if ($request->input('method') === 'sort') {
            $sortOrder = $request->input('sortOrder');
            $sortedImages = array();

            foreach ($sortOrder as $index) {
                $sortedImages[] = $images[$index];
            }

            $images = $sortedImages;
        }

        $translation->value = serialize($images);
        $translation->save();

        die;
    }
}
