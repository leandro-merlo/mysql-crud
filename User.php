<?php

class User {

    public $id;
    public $name;
    public $email;

    function getId() {
        return $this->id;
    }

    function getName() {
        return $this->name;
    }

    function getEmail() {
        return $this->email;
    }

    function setId($id) {
        $this->id = $id;
        return $this;
    }

    function setName($name) {
        $this->name = $name;
        return $this;
    }

    function setEmail($email) {
        $this->email = $email;
        return $this;
    }

    public function atualizar() {
        $stmt = $this->db->stmt_init();
        $stmt->prepare('UPDATE user SET name=?, email=? WHERE id=?');
        $stmt->bind_param('ssi', $this->name, $this->email, $this->id);
        return $stmt->execute();
    }

    public function deletar() {
        $stmt = $this->db->stmt_init();
        $stmt->prepare('DELETE FROM user WHERE id=?');
        $stmt->bind_param('i', $this->id);
        return $stmt->execute();        
    }
    
    public function findById($id) {
        $stmt = $this->db->stmt_init();
        $stmt->prepare("SELECT * FROM user WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->bind_result($dbId, $name, $email);
        if ($stmt->fetch()):
            $this->id = $id;
            $this->name = $name;
            $this->email = $email;
        endif;
        return $this;
    }

}
