<?php

require_once './AbstractDBService.php';
require_once './User.php';

class UserService extends AbstractDBService {

    public function __construct(\mysqli $db) {
        parent::__construct($db, 'user', new User());
    }

    public function getUser(){
    	return $this->reference;
    }

    public function clear(){
      $this->reference = new User();
    }

}
