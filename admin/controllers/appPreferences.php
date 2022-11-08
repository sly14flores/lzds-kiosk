<?php

require_once '../../db.php';

$_POST = json_decode(file_get_contents('php://input'), true);

$dir = "../profile-pics/";

$con = new pdo_db('guests_purposes');

switch ($_GET['r']) {

case "load":

$results = $con->getData("SELECT id, description FROM guests_purposes ORDER BY id");
foreach ($results as $i => $result) {
	$results[$i]['disabled'] = true;
	$results[$i]['invalid'] = false;
}

echo json_encode($results);

break;

case "save":

if (count($_POST['purposesAdd']) > 0) {

	$con->insertDataMulti($_POST['purposesAdd']);

}

if (count($_POST['purposesUpdate']) > 0) {

	$con->updateDataMulti($_POST['purposesUpdate'],'id');

}

if (count($_POST['purposesDelete']) > 0) {

	$con->deleteData(array("id"=>implode(",",$_POST['purposesDelete'])));

}

break;

case "schedules":

$con = new pdo_db();

$schedules = $con->getData("SELECT * FROM schedules");

echo json_encode($schedules);

break;

case "saveStaffSchedule":

$con = new pdo_db("schedules");
$con1 = new pdo_db("schedule_details");

if ($_POST['id'] === 0) {

$schedule = $con->insertData(array("description"=>$_POST['description']));
$schedule_id = $con->insertId;

foreach ($_POST['details'] as $key => $value) {
	
	$_POST['details'][$key]['schedule_id'] = $schedule_id;
	$_POST['details'][$key]['morning_in'] = date("H:i:s",strtotime($value['morning_in']));
	$_POST['details'][$key]['morning_out'] = date("H:i:s",strtotime($value['morning_out']));
	$_POST['details'][$key]['afternoon_in'] = date("H:i:s",strtotime($value['afternoon_in']));
	$_POST['details'][$key]['afternoon_out'] = date("H:i:s",strtotime($value['afternoon_out']));
	
}

$schedule_details = $con1->insertDataMulti($_POST['details']);

} else {
	
$schedule = $con->updateData(array("id"=>$_POST['id'],"description"=>$_POST['description']),"id");

foreach ($_POST['details'] as $key => $value) {
	
	unset($_POST['details'][$key]['schedule_id']);
	unset($_POST['details'][$key]['day']);
	$_POST['details'][$key]['morning_in'] = date("H:i:s",strtotime($value['morning_in']));
	$_POST['details'][$key]['morning_out'] = date("H:i:s",strtotime($value['morning_out']));
	$_POST['details'][$key]['afternoon_in'] = date("H:i:s",strtotime($value['afternoon_in']));
	$_POST['details'][$key]['afternoon_out'] = date("H:i:s",strtotime($value['afternoon_out']));
	
}

$schedule_details = $con1->updateDataMulti($_POST['details'],"id");
	
}

break;

case "editStaffSchedule":

	$con = new pdo_db();
	$schedule = $con->getData("SELECT * FROM schedules WHERE id = $_POST[id]");
	$schedule_details = $con->getData("SELECT * FROM schedule_details WHERE schedule_id = $_POST[id]");
	
	$schedule[0]['details'] = $schedule_details;
	
	echo json_encode($schedule[0]);

break;

case "deleteSchedule":

	$con = new pdo_db("schedules");		
	$delete = $con->deleteData(array("id"=>implode(",",$_POST['id'])));	

break;

}

?>