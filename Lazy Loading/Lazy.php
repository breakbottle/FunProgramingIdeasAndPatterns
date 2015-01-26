<?php
/**
 * Created by PhpStorm.
 * User: Clint Small Cain
 * Date: 1/24/2015
 * Time: 9:16 AM
 */

namespace mvcSystem;

class LazyProperties{
    public $className;
    public $classArgs;
    public $methodName;
    public $methodArgs;
}

class Lazy extends singleton{
    private $typeClassObjectContainer = null;//proabably should be type of empty string.
    private $Value = null;

    /**
     * @param $className - The class name to be init when Value is called.
     * @param array $constructorArgs - Parameter to be used when init is used.
     */
    public function __construct($className,$constructorArgs = array()){
        $this->typeClassObjectContainer = new LazyProperties();//load properties to be used in class.
        $this->typeClassObjectContainer->className = $className;
        $this->typeClassObjectContainer->classArgs = $constructorArgs;
    }

    /**
     * @param $method -- The method name to be called on pre-defined class object
     * @param array $args - Parameters to be used in the method
     * @return $this - return the full object, just in case a use calls for it.
     */
    public function Call($method,$args = array()){
        $this->typeClassObjectContainer->methodName = $method;
        $this->typeClassObjectContainer->methodArgs = $args;
        return $this;
    }

    /**
     * @return  - The object class or value of the called class.
     */
    public function Value(){
        $value = null;
        if($this->typeClassObjectContainer->className && $this->typeClassObjectContainer->methodName){
            $value = $this->Load($this->typeClassObjectContainer->classArgs)->Method($this->typeClassObjectContainer->methodName,$this->typeClassObjectContainer->methodArgs);
        } else if($this->typeClassObjectContainer->className){
            $value = $this->Load($this->typeClassObjectContainer->classArgs);
        }
        return $value->Value;
    }

    /**
     * @param array $arrayOfArgs - private init of the defined class.
     * @return $this
     */
    private function Load($arrayOfArgs = array()){
        if(class_exists($this->typeClassObjectContainer->className)){
            $r = new \ReflectionClass($this->typeClassObjectContainer->className);
            $this->Value = $r->newInstanceArgs($arrayOfArgs);
        }
        return $this;
    }

    /**
     * @param $method - private call to method of the defined class.
     * @param array $args - parameters of the method.
     * @return $this
     */
    private function Method($method,$args = array()){
        if(method_exists($this->Value,$method)){
            $this->Value = call_user_func_array(array($this->Value,$method),$args);
        }
        return $this;
    }

} 