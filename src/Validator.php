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

    // Start Validation Process
    // Key will be the current data key, rules are the rules defined in the options of this key
    // If there are errors found in the currently validated value, it will push the error into $this->errors
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

    // This function actually validates a value with a rule
    // Depends on what kind of rule is given it will run different version
    // If a rule returns an error, it will also be returned up to the parent function
    // This stops validation so the first error is always the only one shown
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
        }

        return $error;
    }

    // Rule to check if the value is empty or not
    private function checkEmptyRule($key, $rule)
    {
        return (strlen($this->data[$key]) === 0) ? $rule['message'] : false;
    }

    // Rule to check if the value is longer than the min rule
    private function checkMinLengthRule($key, $rule)
    {
        return (strlen($this->data[$key]) < $rule['value']) ? $rule['message'] : false;
    }

    // Rule to check if the value is shorter than the max rule
    private function checkMaxLengthRule($key, $rule)
    {
        return (strlen($this->data[$key]) > $rule['value']) ? $rule['message'] : false;
    }

    // Checks if the value is a valid mail address
    private function checkEmailRule($key, $rule)
    {
        return (!filter_var($this->data[$key], FILTER_VALIDATE_EMAIL)) ? $rule['message'] : false;
    }

    // Checks if the value is a valid URL (http + https format)
    private function checkURLRule($key, $rule)
    {
        return (!preg_match($this->regex['url'], $this->data[$key])) ? $rule['message'] : false;
    }

    // Checks if the value is actually higher than the min rule
    private function checkMinRule($key, $rule)
    {
        return ($this->data[$key] < $rule['value']) ? $rule['message'] : false;
    }

    // Checks if the value is actually lower than the max rule
    private function checkMaxRule($key, $rule)
    {
        return ($this->data[$key] > $rule['value']) ? $rule['message'] : false;
    }
}
