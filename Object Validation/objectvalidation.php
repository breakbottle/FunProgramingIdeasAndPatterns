<?php
/**
 * Created by PhpStorm.
 * User: Clint Small Cain
 * Date: 12/4/14
 * Time: 5:20 AM
 */

namespace mvcSystem\logic;

/**
 * Class objectValidation
 * @package mvcSystem\logic
 * Basic rules - when validating an object, since php is not a typed language. We will use property name to determine
 * type so we can validate that the property has a value that matches that type. For example
 * a name like $userId will be automatically typed as a number and validated as numeric.
 *
 * Cases - keywords:
 * (*)Id - numeric
 * (*)List, (*)Array - is_array
 * (*)Object, (*)Model, (*)DTO (*)Callback, (*)Function, (*)Closure
 * everything else will eval as valued string, has value
 *
 * Examples Uses:
 *  $validate = new objectValidation();
 *  $bool = $validate->Validate($objectToValidate);
 *
 *  Additional Rules for given object example:
 *  $validate = new objectValidation();
 *  $rules->userId = "[1-5]";//auto validate as in but additionally validate this given range
 *  $rules->userName = "/[a-zA-Z]/ig";//auto validate as in but additionally validate regex
 *  $rules->myObject = new stdClass();//same for Arrays
 *  $rules->myObject->count = 4;//auto validate as object additionally validate object size equal to 4
 *  $rules->myObject->key = "one,two";//auto validate as object additionally validate object property names exists
 *  $rules->myObject->value->one = "/[a-zA-Z]/ig";//auto validate as object additionally validate object prop value regex - same goes for int
 *  $bool = $validate->Validate($objectToValidate,$rules);
 *
 *
 */
class objectValidation {
    public function Validate($object,\stdClass $rules = null){
        if(is_object($object)){
            $check = array();
            foreach($object as $property => $value){
                if($this->IsNumeric($property,$value,$rules->{$property})){
                    $check[$property] = $value;
                }
                elseif($this->IsList($property,$value,$rules->{$property})){
                    $check[$property] = $value;
                }
                elseif($this->IsObject($property,$value,$rules->{$property})){
                    $check[$property] = $value;
                }
                elseif($this->IsBool($property,$value,$rules->{$property})){
                    $check[$property] = $value;
                }
                elseif($this->IsString($property,$value,$rules->{$property})){
                    $check[$property] = $value;
                }
            }
            $notValid = array();
            if(count($check) != count((array)$object)){
                foreach($object as $property => $value){
                    if(!array_key_exists($property,$check)){
                        $notValid[$property] = $value;
                    }
                }
                throw new ObjectValidationException("All properties were not valid.".print_r($notValid,true)." Valid properties: ".print_r($check,true));
            }
        } else {
            throw new ObjectValidationException("Cannot validate on non-object ".$object);
        }
        return true;
    }

    /**
     * @param $property
     * @param int $enum
     * @return bool|int
     */
    private function Types($property,$enum = 0){
        switch($enum){
            case 1:
                $bool = preg_match("/Object/",$property) || preg_match("/DTO/",$property) || preg_match("/Model/",$property) || preg_match("/Closure/",$property) || preg_match("/Function/",$property) || preg_match("/Callback/",$property);
                break;
            case 2:
                $bool = preg_match("/List/",$property) || preg_match("/Array/",$property);
                break;
            case 3:
                $bool = preg_match("/Id/",$property);
                break;
            case 4:
                $bool = preg_match("/^Is/i",$property) || preg_match("/Valid/",$property);//  || preg_match("/assert/",$property);
                break;
            default:
                $bool = preg_match("/String/",$property) || (!preg_match("/^Is/i",$property) && !preg_match("/Valid/",$property) && !preg_match("/List/",$property) && !preg_match("/Array/",$property) && !preg_match("/Id/",$property) && !preg_match("/Object/",$property) && !preg_match("/DTO/",$property) && !preg_match("/Model/",$property));
                break;
        }
        return $bool;

    }

    /**
     * @param $property
     * @param $value
     * @param null $rule - full regex
     * @return bool
     * @throws ObjectValidationException
     */
    private function IsString($property,$value,$rule = null){
        if($this->Types($property)){
           if(!is_string($value)){
               if(is_numeric($property) && !is_numeric($value)){//if index is numeric we'll allow values to be strings or numbers by default
                   throw new ObjectValidationException("property/key ".$property." is not string assumed integer, failed!. Value given: ".gettype($value));
               } elseif(!is_numeric($property)){
                   throw new ObjectValidationException("property/key ".$property." is not a string. Value given: ".gettype($value));
               }

           } elseif(strlen($value) <= 0 && $rule == null) {
               throw new ObjectValidationException("property/key ".$property." does not have a value. ");
           } elseif($rule != null) {
               if(!preg_match($rule,$value)){
                   throw new ObjectValidationException("property/key ".$property." value is not valid. given: ".$value.", expected match ".$rule);
               }
           }
           return true;
        }
        return false;
    }

    /**
     * @param $property
     * @param $value
     * @param null $rule - number regex
     * @return bool
     * @throws ObjectValidationException
     */
    private function IsNumeric($property,$value,$rule = null){
        if($this->Types($property,3)){
           if(!is_numeric($value)){
               throw new ObjectValidationException("property/key ".$property." is not numeric. Value given: ".gettype($value));
           } elseif($rule != null) {
               if(!preg_match("/".$rule."/",$value)){
                   throw new ObjectValidationException("property/key ".$property." value is not valid. given: ".$value.", expected match ".$rule);
               }
           }
           return true;
        }
        return false;
    }

    /**
     * @param $property
     * @param $value
     * @param null $rule -
     * @return bool
     * @throws ObjectValidationException
     */
    private function IsBool($property,$value,$rule = null){
        if($this->Types($property,4)){
           if(!is_bool($value)){
               throw new ObjectValidationException("property/key ".$property." is not a boolean. Value given: ".gettype($value));
           } elseif(is_bool($rule)) {
               if($rule != $value){
                   throw new ObjectValidationException("property/key ".$property." value is not valid. given: ".(($value)?"true":"false").", expected match ".(($rule)?"true":"false"));
               }
           }
           return true;
        }
        return false;
    }

    /**
     * @param $property
     * @param $value
     * @param null $rule - object count or prop exists or value exists
     * @return bool
     * @throws ObjectValidationException
     */
    private function IsObject($property,$value,$rule = null){
        if($this->Types($property,1)){
           if(!is_object($value)){
               throw new ObjectValidationException("property/key ".$property." is not an object. Value given: ".gettype($value));
           } elseif($rule != null) {
               if(property_exists($rule,"count")){
                   if(count((array)$value) != $rule->count)
                       throw new ObjectValidationException("property/key ".$property." size is not valid. given: ".count((array)$value).", expected match ".$rule->count);
               }
               if(count((array)$value) > 0 ){
                   if(property_exists($rule,"key")){
                       $keys = explode(",",$rule->key);
                       foreach($keys as $key){
                           if(!property_exists($value,$key))
                               throw new ObjectValidationException("property/key ".$property." object property is not found. given: ".print_r($value,true).", expected keys ".$rule->key);
                       }
                   }
                   if(property_exists($rule,"value")){
                       return $this->Validate((object)$value,$rule->value);

                   }
               }
           }
           return true;
        }
        return false;
    }

    /**
     * @param $property
     * @param $value
     * @param null $rule - array count or key exists or value exists
     * @return bool
     * @throws ObjectValidationException
     */
    private function IsList($property,$value,$rule = null){
        if($this->Types($property,2)){
           if(!is_array($value)){
               throw new ObjectValidationException("property/key ".$property." is not an array. Value given: ".gettype($value));
           } elseif($rule != null) {
               if(property_exists($rule,"count")){
                   if(count($value) != $rule->count)
                        throw new ObjectValidationException("property/key ".$property." size is not valid. given: ".count($value).", expected match ".$rule->count);
               }
               if(count($value) > 0 ){
                   if(property_exists($rule,"key")){
                       $keys = explode(",",$rule->key);
                       foreach($keys as $key){
                           if(!array_key_exists($key,$value))
                               throw new ObjectValidationException("property/key ".$property." array key is not found. given: ".print_r($value,true).", expected keys ".$rule->key);
                       }
                   }
                   if(property_exists($rule,"value")){
                       return $this->Validate((object)$value,$rule->value);
                   }
               }
           }
           return true;
        }
        return false;
    }
}
