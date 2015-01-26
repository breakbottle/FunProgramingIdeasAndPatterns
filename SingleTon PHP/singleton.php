<?php
/**
 * Created by PhpStorm.
 * User: Clint Small Cain
 * Date: 1/25/14
 * Time: 12:01 PM
 */

namespace mvcSystem;


abstract class singleton
{
    /**
     * @return null|object - A new instance of the extender
     */
    public static function Instance()
    {
        $args = func_get_args();
        $class = get_called_class();
        $instance = null;
        if (is_array($args)) {

            try {
                $r = new \ReflectionClass($class);
                $instance = $r->newInstanceArgs($args);

            } catch (\Exception $e) {
                //no construct define, swallow error..log it i mean lol
                $error = $e;
            }
        }
        else {
            $instance = new $class();
        }
        return $instance;
    }

    /**
     * @param $addPropertiesToClass - properties of extender class to be returned
     * @return mixed
     */
    public static function ChainInstance($addPropertiesToClass){
        $class = get_called_class();
        $instance = new $class();
        if(is_object($addPropertiesToClass)){
            foreach($addPropertiesToClass as $prop => $value){
                $instance->$prop = $value;
            }
        }
        return $instance;
    }
} 