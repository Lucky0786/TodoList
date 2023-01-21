<?php

namespace App\Services;

use DB;
use File;
use DateTime;
use App\Models\User;

Class ExistingClass {

	public function isEmailExist($email='',$code=''){

    	$existEmail = User::where('email', $email)->where('email_verified_code', $code)->count();
    	if($existEmail>0){

        	return true;
    	}else{

    		return false;
    	}
	}
	public function isEmailCheck($email=''){

    	$existEmail = User::where('email', $email)->count();
    	if($existEmail>0){

        	return true;
    	}else{

    		return false;
    	}
	}

	public function ArrayCleaner($input) { 
	  foreach ($input as &$value) { 
		if (is_array($value)) { 
		  $value = $this->ArrayCleaner($value); 
		}
	  }
	  

	  return array_map(function($v){
					return (is_null($v)) ? "" : $v;
					},$input);
	}
}
