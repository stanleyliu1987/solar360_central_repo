<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

abstract class Bean {
    public function __call($name, $arguments) {
        if (strpos($name, 'get') === 0) {
            $property = strtolower(substr($name,3,1)) . substr($name,4);
            return $this->$property;
        }
        elseif (strpos($name, 'set') === 0) {
            $property = strtolower(substr($name,3,1)) . substr($name,4);
            return $this->$property = $arguments[0];
        }
        else {
            throw new Exception("Method $name does not exist");
        }
    }

    public function __get($name) {
        $getter = 'get' . ucfirst($name);
        if (method_exists($this, $getter)) {
            return call_user_func(array($this, $getter));
        }
        return $this->$name;
    }

    public function __set($name, $value) {
        $setter = 'set' . ucfirst($name);
        if (method_exists($this, $setter)) {
            call_user_func(array($this, $setter), $value);
        }
        else {
            $this->$name = $value;
        }
    }
}
?>

