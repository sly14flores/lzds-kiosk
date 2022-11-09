<?php

require_once 'db.php';

$_POST = json_decode(file_get_contents('php://input'), true);

$con = new pdo_db("attendances");

switch ($_POST['r']) {

case "collect_queues":

	$results = $con->getData("SELECT attendances.id, attendances.rfid, CONCAT(profiles.first_name, ' ', SUBSTRING(profiles.middle_name,1,1), '. ', profiles.last_name) fullname, attendances.time_log, profiles.cp, DATE_FORMAT(attendances.time_log, '%a, %b %e, %Y') log_date, DATE_FORMAT(attendances.time_log, '%h:%i %p') log_time, DATE_FORMAT(attendances.time_log, '%Y-%m-%d') logQ_date FROM attendances LEFT JOIN profiles ON attendances.rfid = profiles.rfid WHERE sms = 'queue' AND profiles.profile_type = 'Student' AND SUBSTRING(time_log,1,10) = '2022-10-28'");
	// $results = $con->getData("SELECT attendances.id, attendances.rfid, CONCAT(profiles.first_name, ' ', SUBSTRING(profiles.middle_name,1,1), '. ', profiles.last_name) fullname, attendances.time_log, profiles.cp, DATE_FORMAT(attendances.time_log, '%a, %b %e, %Y') log_date, DATE_FORMAT(attendances.time_log, '%h:%i %p') log_time, DATE_FORMAT(attendances.time_log, '%Y-%m-%d') logQ_date FROM attendances LEFT JOIN profiles ON attendances.rfid = profiles.rfid WHERE sms = 'queue' AND profiles.profile_type = 'Student' AND SUBSTRING(time_log,1,10) = '2022-10-28' AND attendances.rfid = '0008398564'");
	// $results = $con->getData("SELECT attendances.id, attendances.rfid, CONCAT(profiles.first_name, ' ', SUBSTRING(profiles.middle_name,1,1), '. ', profiles.last_name) fullname, attendances.time_log, profiles.cp, DATE_FORMAT(attendances.time_log, '%a, %b %e, %Y') log_date, DATE_FORMAT(attendances.time_log, '%h:%i %p') log_time, DATE_FORMAT(attendances.time_log, '%Y-%m-%d') logQ_date FROM attendances LEFT JOIN profiles ON attendances.rfid = profiles.rfid WHERE sms = 'queue' AND profiles.profile_type = 'Student' AND SUBSTRING(time_log,1,10) = '".date("Y-m-d")."'");
	echo json_encode($results);

break;

}

?>