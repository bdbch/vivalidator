<?php

namespace Vivalidator;

class Validator
{
    private $regex = [
        'url' => '/https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&\/\/=]*)/'
    ];

    public function __construct($data = [], $options = [])
    {
        $this->options = $options;
        $this->data = $data;
        $this->errors = [];

        foreach ($this->options as $key => $rules) {
            $this->validate($key, $rules);
        }
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function validate($key, $rules)
    {
        foreach ($rules as $rule) {
            $error = $this->validateRule($key, $rule);
            if ($error) {
                array_push($this->errors, $error);
                return false;
            }
        }
    }

    private function validateRule($key, $rule)
    {
        $error = false;

        if (!isset($this->data[$key])) {
            return false;
        }

        switch ($rule['rule']) {
            case 'empty':
                $error = $this->checkEmptyRule($key, $rule);
                break;

            case 'minlength':
                $error = $this->checkMinLengthRule($key, $rule);
                break;

            case 'maxlength':
                $error = $this->checkMaxLengthRule($key, $rule);
                break;

            case 'mail':
                $error = $this->checkEmailRule($key, $rule);
                break;

            case 'url':
                $error = $this->checkURLRule($key, $rule);
                break;

            case 'min':
                $error = $this->checkMinRule($key, $rule);
                break;

            case 'max':
                $error = $this->checkMaxRule($key, $rule);
                break;

            case 'number':
                $error = $this->checkNumberRule($key, $rule);
                break;

            case 'regex':
                $error = $this->checkRegexRule($key, $rule);
                break;
        }

        return $error;
    }

    private function checkEmptyRule($key, $rule)
    {
        return (strlen($this->data[$key]) === 0) ? $rule['message'] : false;
    }

    private function checkMinLengthRule($key, $rule)
    {
        return (strlen($this->data[$key]) < $rule['value']) ? $rule['message'] : false;
    }

    private function checkMaxLengthRule($key, $rule)
    {
        return (strlen($this->data[$key]) > $rule['value']) ? $rule['message'] : false;
    }

    private function checkEmailRule($key, $rule)
    {
        return (!filter_var($this->data[$key], FILTER_VALIDATE_EMAIL)) ? $rule['message'] : false;
    }

    private function checkURLRule($key, $rule)
    {
        return (!preg_match($this->regex['url'], $this->data[$key])) ? $rule['message'] : false;
    }

    private function checkMinRule($key, $rule)
    {
        return ($this->data[$key] < $rule['value']) ? $rule['message'] : false;
    }

    private function checkMaxRule($key, $rule)
    {
        return ($this->data[$key] > $rule['value']) ? $rule['message'] : false;
    }

    private function checkNumberRule($key, $rule)
    {
        return (!is_numeric($this->data[$key])) ? $rule['message'] : false;
    }

    private function checkRegexRule($key, $rule)
    {
        return (!preg_match($rule['value'], $this->data[$key])) ? $rule['message'] : false;
    }
}
