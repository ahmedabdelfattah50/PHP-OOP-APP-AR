<?php

  namespace App\classes;

  class Session {
    public static function add($key , $value){
      return $_SESSION[$key] = $value;
    } 

    public static function get($key){
      return $_SESSION[$key];
    }

    public static function exits($key){
      return (isset($_SESSION[$key])) ? true : false;
    }

    public static function delete($key){
      if(self::exits($key)){
        unset($_SESSION[$key]);
      }
    }

  }