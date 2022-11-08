<?php

require_once 'db.php';

$con = new pdo_db("guests_infos");
$con->insertData($_POST);

?>