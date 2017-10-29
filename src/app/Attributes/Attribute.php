<?php

namespace Marble\Admin\App\Attributes;

class Attribute
{
    protected $attribute;
    protected $classAttribute;

    public function __construct($attribute, $classAttribute = null)
    {
        $this->attribute = $attribute;
        $this->classAttribute = $classAttribute;
    }

    public function renderEdit($locale)
    {
        $data = array();
        $data['attribute'] = $this->attribute;
        $data['classAttribute'] = $this->classAttribute;
        $data['locale'] = $locale;

        return $this->view('attributes/'.$this->classAttribute->type->namedIdentifier.'_edit', $data)->render();
    }

    public function renderConfiguration()
    {
        if (!isset($this->configuration)) {
            return;
        }

        $data = array();
        $data['classAttribute'] = $this->classAttribute;

        return $this->view('attributes/'.$this->classAttribute->type->namedIdentifier.'_config', $data);
    }

    private function view($view, $data = array())
    {
        if ($this->viewPrefix) {
            $view = $this->viewPrefix.'::'.$view;
        }

        return view($view, $data);
    }

    public function getJavascripts()
    {
        $files = array();

        if (isset($this->javascripts)) {
            foreach ($this->javascripts as $file) {
                $files[] = '/vendor/'.$this->viewPrefix.'/js/attributes/'.$file;
            }
        }

        return $files;
    }
}
