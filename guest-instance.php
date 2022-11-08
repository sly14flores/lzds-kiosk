<?php

require_once 'db.php';

$con = new pdo_db("attendances");

$mode = "In";
$cardPunches = $con->getData("SELECT id, time_log FROM attendances WHERE rfid = '".trim($_POST['rfid'])."' AND DATE_FORMAT(time_log,'%Y-%m-%d') = '".date("Y-m-d")."' ORDER BY id DESC");
$logsCount = $con->rows;
if (($con->rows)>0) {
	if (($logsCount)%2 == 0) $mode = "Out";
}

echo $mode;

?>