<?php
/**
 * Created by PhpStorm.
 * User: sirmention
 * Date: 1/25/2015
 * Time: 11:12 AM
 */

namespace mvcSystem\logic;


class ObjectValidationException extends \Exception{
    public function __construct($message,$code = 0, \Exception $previous = null){
        parent::__construct($message, $code, $previous);
    }
}