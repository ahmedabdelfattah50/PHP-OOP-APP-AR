<?php

  namespace App\classes; 
  
  class CsrfMiddleware { 

    const NAME = "token";

    public function generation(){
        return Session::add(self::NAME , bin2hex(random_bytes(128)));
    }

    public function check($token){
      $token_name = self::NAME;
      if(Session::exits($token_name) && $token === Session::get($token_name)){
        Session::delete($token_name);
        return true;
      }
      return false;
    }
  }