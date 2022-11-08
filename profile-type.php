<?php

require_once 'db.php';

$con = new pdo_db("profile_type");

$profileType = "no_record";
$profileTypes = $con->getData("SELECT profile_type FROM profiles WHERE rfid = '".trim($_POST['rfid'])."'");
if (($con->rows)>0) {
	$profileType = $profileTypes[0]['profile_type'];
}

echo $profileType;

?>