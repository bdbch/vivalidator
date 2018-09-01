<?php

namespace Vivalidator;

class Validator
{
    private $regex = [
        'url' => '/https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&\/\/=]*)/'
    ];

    public function __construct($data = [], $options = [], $extra = [])
    {
        $this->options = $options;
        $this->data = $data;
        $this->extra = $extra;
        $this->errors = [];

        foreach ($this->options as $key => $rules) {
            $this->validate($key, $rules);
        }

        if (isset($this->extra['recaptcha']) && isset($this->extra['recaptcha']['secret'])) {
            $this->checkRecaptcha();
        }
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function addCustomError($error)
    {
        array_push($this->errors, $error);
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

        switch ($rule['rule']) {
            case 'required':
                $error = $this->checkEmptyRule($key, $rule);
                break;

            case 'minlength':
                $error = $this->checkMinLengthRule($key, $rule);
                break;

            case 'maxlength':
                $error = $this->checkMaxLengthRule($key, $rule);
                break;

            case 'email':
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

            case 'between':
                $error = $this->checkRangeRule($key, $rule);
                break;

            case 'regex':
                $error = $this->checkRegexRule($key, $rule);
                break;
        }

        return $error;
    }

    private function checkEmptyRule($key, $rule)
    {
        return (!isset($this->data[$key]) || strlen($this->data[$key]) === 0) ? $rule['message'] : false;
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

    private function checkRangeRule($key, $rule)
    {
        return ($this->data[$key] < $rule['values']['from'] || $this->data[$key] > $rule['values']['to']) ? $rule['message'] : false;
    }

    private function checkNumberRule($key, $rule)
    {
        return (!is_numeric($this->data[$key])) ? $rule['message'] : false;
    }

    private function checkRegexRule($key, $rule)
    {
        return (!preg_match($rule['value'], $this->data[$key])) ? $rule['message'] : false;
    }

    private function checkRecaptcha()
    {
        if (!class_exists('\ReCaptcha\ReCaptcha')) {
            return false;
        }

        $reCaptcha = new \ReCaptcha\ReCaptcha($this->extra['recaptcha']['secret']);
        $res = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : false;

        if (!$res) {
            $this->errors[] = (isset($this->extra['recaptcha']['error'])) ? $this->extra['recaptcha']['error'] : 'Captcha was not verfied.';
            return false;
        }

        $captchaValidator = $reCaptcha->verify($res);
        if (!$captchaValidator->isSuccess()) {
            $this->errors[] = (isset($this->extra['recaptcha']['error'])) ? $this->extra['recaptcha']['error'] : 'Captcha was not verfied.';
        }

        return false;
    }
}
