<?php

/**
 * Created by Muhammad Muflih Kholidin
 * https://github.com/mmuflih
 * muflic.24@gmail.com
 **/

namespace MMuflih\Traits;

trait HasImage
{
    protected $valid;

    public function setPicture($field, $required = false)
    {
        $this->valid[$field] = '';
        if ($required && !(isset($this->valid[$field]) && isset($this->valid[$field]['uri']))) {
            throw new \Exception('Invalid image data', 422);
        } else if (isset($this->valid[$field]) && isset($this->valid[$field]['uri'])) {
            $this->valid[$field] = $this->valid[$field]['uri'];
        }
    }

    public function getPicture($field)
    {
        if ($this->$field == '') {
            return null;
        }
        return [
            "mime_type" => '',
            "uri" => $this->$field,
            "url" => env('APP_URL') . $this->$field
        ];
    }

    public function fillImageDB($valid, $field)
    {
        if (isset($valid[$field]) && isset($valid[$field]['url'])) {
            $this->$field = $valid[$field]['uri'];
        }
    }
}
