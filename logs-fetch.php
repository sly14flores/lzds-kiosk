<?php

header("Access-Control-Allow-Origin: *", false);
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

$_POST = json_decode(file_get_contents('php://input'), true);

$by = $_POST['by'];

// require_once '../db2.php';
// $con = new pdo_db("monitoring","attendances");

require_once 'db.php';
$con = new pdo_db();

if ($by == "Month") {
	$filter = " AND time_log LIKE '".$_POST['year']."-".$_POST['month']['month']."%'";
} else {
	$filter = " AND time_log >= '".date("Y-m-d 00:00:00",strtotime($_POST['dateFrom']))."' AND time_log <= '".date("Y-m-d 23:59:59",strtotime($_POST['dateTo']))."'";
}

$sql = "SELECT attendances.id, attendances.rfid, attendances.time_log FROM attendances LEFT JOIN profiles ON attendances.rfid = profiles.rfid WHERE profile_type = 'Staff'$filter";
$logs = $con->getData($sql);

// header("Content-type: application/json");
echo json_encode($logs);

?>