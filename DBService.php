<?php
interface DBService {
    public function getAll();
    public function create();
    public function read($id);
    public function update($id);
    public function delete($id);
}
