<?php

namespace Flynt\Features\Validator;

class Validator
{
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
                $error = $this->checkEmptyState($key, $rule);
                break;

            case 'minlength':
                $error = $this->checkMinLengthState($key, $rule);
                break;

            case 'maxlength':
                $error = $this->checkMaxLengthState($key, $rule);
                break;

            case 'mail':
                $error = $this->checkEmailState($key, $rule);
                break;
        }

        return $error;
    }

    private function checkEmptyState($key, $rule)
    {
        return (strlen($this->data[$key]) === 0) ? $rule['message'] : false;
    }

    private function checkMinLengthState($key, $rule)
    {
        return (strlen($this->data[$key]) < $rule['value']) ? $rule['message'] : false;
    }

    private function checkMaxLengthState($key, $rule)
    {
        return (strlen($this->data[$key]) > $rule['value']) ? $rule['message'] : false;
    }

    private function checkEmailState($key, $rule)
    {
        return (!filter_var($this->data[$key], FILTER_VALIDATE_EMAIL)) ? $rule['message'] : false;
    }
}
