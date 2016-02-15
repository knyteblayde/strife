<?php namespace Kernel;

use Kernel\Database\Database;

/**
 * Interface RequestInterface
 *
 * @package Kernel
 */
interface RequestInterface
{
    public function __construct($request);

    public function get($field);

    public function values();

    public function sanitize($string);

    public function validate();
}

/**
 * Request class, this will handle validations and rules for requests.
 * validation includes: email,number,file,text maximum and minimum values,
 * and can determine whether a database unique value is taken.
 *
 * Class Request
 *
 * @package Kernel
 */
class Request implements RequestInterface
{
    /**
     * Storage for request method
     */
    public $request = null;

    /**
     * Storage for field errors
     */
    public $errors = null;


    /**
     * Catches request method, and filter
     * each values
     *
     * @param $request
     */
    public function __construct($request = null)
    {
        $request = is_null($request) ? $_POST : $request;

        if (is_null($this->request)) {
            if (array_key_exists('__FORM_TOKEN__', $request)) {
                unset($request['__FORM_TOKEN__']);
            }
            $this->request = filter_var_array($request, FILTER_SANITIZE_STRIPPED);
        }
        if (array_key_exists('__FORM_TOKEN__', $_POST)) {
            $token = $_POST['__FORM_TOKEN__'];
            unset($_POST['__FORM_TOKEN__']);

            if (!Token::verify($token)) {
                $auth = new \Auth();
                return $auth->restartSession();
            }
        }

        return true;
    }


    /**
     * Returns a field that is present in
     * $request property that is set beforehand.
     *
     * @param $field
     * @return string
     */
    public function get($field)
    {
        return $this->sanitize($this->request[$field]);
    }


    /**
     * returns request array ready for insertion
     *
     * @return string
     */
    public function values()
    {
        return ($this->request);
    }


    /**
     * Store current field values
     * to fields session var and can be accessed thru
     * fields() method
     *
     * @return string
     */
    public function retain()
    {
        $_SESSION['__FIELDS__'] = $this->request;
    }


    /**
     * Returns a sanitized string
     *
     * @param $string
     * @return string
     */
    public function sanitize($string)
    {
        return filter_var($string, FILTER_SANITIZE_STRING);
    }


    /**
     * Actual validation of request with rules implied
     * from its child classes.
     *
     * @param null $route
     * @return bool|void
     */
    public function validate($route = null)
    {
        for ($i = 0; $i < count($this->request); $i++) {
            $field = array_keys($this->request);
            if (array_key_exists($field[$i], $this->rules)) {
                $rule = explode('|', $this->rules[$field[$i]]);
                for ($z = 0; $z < count($rule); $z++) {
                    if ($rule[$z] == 'required') {
                        if (strlen($this->request[$field[$i]]) == 0) {
                            $this->errors[$field[$i]] = "{$field[$i]} is required.";
                            break;
                        }
                    }
                    if (preg_match('/unique/i', $rule[$z])) {
                        $db = new Database;
                        $value = $this->request[$field[$i]];
                        foreach ($rule as $item) {
                            if ($item == 'password') {
                                $value = Hash::encode($value);
                                break;
                            }
                        }
                        if ($db->table(explode(':', $rule[$z])[1])->where($field[$i], $value)->exists()) {
                            $this->errors[$field[$i]] = "{$field[$i]} not available.";
                            break;
                        }
                    }
                    if ($rule[$z] == 'email') {
                        if (!preg_match('/@/', $this->request[$field[$i]])) {
                            $this->errors[$field[$i]] = "Enter a valid e-mail.";
                            break;
                        }
                    }
                    if ($rule[$z] == 'alphanumeric') {
                        if (!preg_match('/[^A-Za-z0-9]/i', $this->request[$field[$i]])) {
                            $this->errors[$field[$i]] = "Only alphanumeric characters are allowed.";
                            break;
                        }
                    }
                    if ($rule[$z] == 'letters') {
                        if (!preg_match('/^[A-Za-z]/i', $this->request[$field[$i]])) {
                            $this->errors[$field[$i]] = "$field[$i] accepts letters only.";
                            break;
                        }
                    }
                    if ($rule[$z] == 'number' || $rule[$z] == 'numeric') {
                        if (!preg_match('/[0-9]/', $this->request[$field[$i]])) {
                            $this->errors[$field[$i]] = "{$field[$i]} should be numeric.";
                            break;
                        }
                    }
                    if (preg_match('/match/i', $rule[$z])) {
                        $compare = explode(':', $rule[$z])[1];
                        if ($this->request[$field[$i]] !== $this->request[$compare]) {
                            $this->errors[$field[$i]] = "Field did not match to {$compare}.";
                            $this->errors[$compare] = "Field did not match to {$field[$i]}.";
                            break;
                        }
                    }
                    if (preg_match('/min/i', $rule[$z])) {
                        $min = explode(':', $rule[$z])[1];
                        if (strlen($this->request[$field[$i]]) < $min) {
                            $this->errors[$field[$i]] = "{$field[$i]} requires a minimum of {$min} characters.";
                            break;
                        }
                    }
                    if (preg_match('/max/i', $rule[$z])) {
                        $max = explode(':', $rule[$z])[1];
                        if (strlen($this->request[$field[$i]]) > $max) {
                            $this->errors[$field[$i]] = "{$field[$i]} requires a maximum of {$max} characters.";
                            break;
                        }
                    }
                }
            }
        }
        $fileRules = array_keys($this->rules);
        for ($f = 0; $f < count($fileRules); $f++) {
            if (array_key_exists($fileRules[$f], $_FILES)) {
                if (empty($_FILES[$fileRules[$f]]['name'])) {
                    $this->errors[$fileRules[$f]] = "{$fileRules[$f]} is required.";
                }
            }
        }
        $redirectRoute = (!is_null($route)) ? $route : $this->route;
        if (is_null($this->errors)) {
            return true;
        } else {
            $_SESSION['__ERRORS__'] = $this->errors;
            $_SESSION['__FIELDS__'] = $this->request;
            return header("location: {$redirectRoute}");
        }
    }
}