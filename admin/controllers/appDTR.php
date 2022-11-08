<?php

require_once '../../db.php';

$_POST = json_decode(file_get_contents('php://input'), true);

$con = new pdo_db('attendances');

switch ($_GET['r']) {

case "fullname_fids":

$sql = "SELECT IF(profile_type = 'Guest',first_name,CONCAT(first_name, ' ', middle_name, ' ', last_name)) fullname, rfid FROM profiles WHERE profile_type = '".substr($_GET['profile_type'],0,strlen($_GET['profile_type'])-1)."'";
$results = $con->getData($sql);

echo json_encode($results);

break;

case "Staffs":

$filter = "";

if (isset($_POST['period'])) {
	
	if ($_POST['period'] == "first_period") {
		$begin = date("Y-m-d",strtotime($_POST['year']."-".$_POST['month']."-01"));
		$end = date("Y-m-d",strtotime($_POST['year']."-".$_POST['month']."-15"));
	} else { // second period
		$begin = date("Y-m-d",strtotime($_POST['year']."-".$_POST['month']."-16"));
		$end = date("Y-m-t",strtotime($_POST['year']."-".$_POST['month']."-01"));	
	}
	
}

if (isset($_POST['dateSpecific'])) {
	$begin = date("Y-m-d",strtotime($_POST['dateSpecific']));
	$end = date("Y-m-d",strtotime($_POST['dateSpecific']));
}

if ((isset($_POST['month'])) && (!isset($_POST['period']))) {
	$begin = date("Y-m-d",strtotime($_POST['year']."-".$_POST['month']."-01"));
	$end = date("Y-m-t",strtotime($_POST['year']."-".$_POST['month']."-01"));	
}

$logs = [];
$mode = array("morning_in","morning_out","afternoon_in","afternoon_out");
while (strtotime($begin) <= strtotime($end)) {

$explicit = false;

$filter = " AND SUBSTRING(time_log, 1, 10) = '$begin'";

$sql = "SELECT attendances.id, attendances.log_order, attendances.rfid, CONCAT(first_name, ' ', middle_name, ' ', last_name) fullname, attendances.time_log FROM attendances LEFT JOIN profiles ON attendances.rfid = profiles.rfid WHERE profiles.profile_type = 'Staff' AND profiles.rfid = '".$_POST['rfid']."'$filter";
$results = $con->getData($sql);

$logs[$begin]['date'] = date("M j",strtotime($begin));
$logs[$begin]['day'] = date("D",strtotime($begin));
$logs[$begin]['morning_in'] = "";
$logs[$begin]['morning_out'] = "";
$logs[$begin]['afternoon_in'] = "";
$logs[$begin]['afternoon_out'] = "";
$logs[$begin]['fdate'] = $begin;
$logs[$begin]['tardiness'] = "";
$logs[$begin]['undertime'] = "";

foreach ($results as $i => $result) {
	
	if ($i > 3) continue;
	$logs[$begin][$mode[$i]] = date("h:i:s A",strtotime($result['time_log']));

}

/*
** if there's at least one log with order not set to zero
*/
foreach ($results as $i => $result) {
	
	if ($result['log_order'] != 0) {

		$explicit = true;
		break;
	
	}

}

if ($explicit) {
	
	// reset
	$logs[$begin]['morning_in'] = "";
	$logs[$begin]['morning_out'] = "";
	$logs[$begin]['afternoon_in'] = "";
	$logs[$begin]['afternoon_out'] = "";

	foreach ($results as $i => $result) {
		
		if ($result['log_order'] == 0) continue;
		$logs[$begin][$mode[$result['log_order']-1]] = date("h:i:s A",strtotime($result['time_log']));

	}
	
}

$mi_mark = "$begin 07:45:00";
$mo_mark = "$begin 12:00:00";
$ai_mark = "$begin 13:00:00";
$ao_mark = "$begin 16:00:00";

$mor_aft_cutoff = "$begin 12:00:00";

$begin = date("Y-m-d", strtotime("+1 day", strtotime($begin)));
	
}

echo json_encode($logs);

break;

case "Students":

$filter = "";

if (isset($_POST['dateSpecific'])) {
	$begin = date("Y-m-d",strtotime($_POST['dateSpecific']));
	$end = date("Y-m-d",strtotime($_POST['dateSpecific']));
}

if ((isset($_POST['month'])) && (!isset($_POST['period']))) {
	$begin = date("Y-m-d",strtotime($_POST['year']."-".$_POST['month']."-01"));
	$end = date("Y-m-t",strtotime($_POST['year']."-".$_POST['month']."-01"));	
}

$logs = [];
$mode = array("log1","log2","log3","log4");
while (strtotime($begin) <= strtotime($end)) {

$explicit = false;

$filter = " AND SUBSTRING(time_log, 1, 10) = '$begin'";

$sql = "SELECT attendances.id, attendances.log_order, attendances.rfid, CONCAT(first_name, ' ', middle_name, ' ', last_name) fullname, attendances.time_log FROM attendances LEFT JOIN profiles ON attendances.rfid = profiles.rfid WHERE profiles.profile_type = 'Student' AND profiles.rfid = '".$_POST['rfid']."'$filter";
$results = $con->getData($sql);

$logs[$begin]['date'] = date("M j",strtotime($begin));
$logs[$begin]['day'] = date("D",strtotime($begin));
$logs[$begin]['log1'] = "";
$logs[$begin]['log2'] = "";
$logs[$begin]['log3'] = "";
$logs[$begin]['log4'] = "";
$logs[$begin]['fdate'] = $begin;

foreach ($results as $i => $result) {
	
	if ($i > 3) continue;
	$logs[$begin][$mode[$i]] = date("h:i:s A",strtotime($result['time_log']));

}

/*
** if there's at least one log with order not set to zero
*/
foreach ($results as $i => $result) {
	
	if ($result['log_order'] != 0) {

		$explicit = true;
		break;
	
	}

}

if ($explicit) {

	$logs[$begin]['log1'] = "";
	$logs[$begin]['log2'] = "";
	$logs[$begin]['log3'] = "";
	$logs[$begin]['log4'] = "";

	foreach ($results as $i => $result) {
		
		if ($result['log_order'] == 0) continue;
		$logs[$begin][$mode[$result['log_order']-1]] = date("h:i:s A",strtotime($result['time_log']));

	}

}

	$begin = date("Y-m-d", strtotime("+1 day", strtotime($begin)));

}

echo json_encode($logs);

break;

case "Guests":

$filter = "";

if (isset($_POST['dateSpecific'])) {
	$begin = date("Y-m-d 00:00:00",strtotime($_POST['dateSpecific']));
	$end = date("Y-m-d 23:59:59",strtotime($_POST['dateSpecific']));
}

if ((isset($_POST['month'])) && (!isset($_POST['period']))) {
	$begin = date("Y-m-d 00:00:00",strtotime($_POST['year']."-".$_POST['month']."-01"));
	$end = date("Y-m-t 23:59:59",strtotime($_POST['year']."-".$_POST['month']."-01"));	
}

$logs = [];

$filter = " AND time_log >= '$begin' AND time_log <= '$end'";
if ($_POST['rfid'] != "") $filter .= " AND profiles.rfid = '".$_POST['rfid']."'";

$sql = "SELECT attendances.id, attendances.rfid, profiles.first_name, attendances.time_log, guests_infos.guest_name, guests_infos.guest_purpose FROM attendances LEFT JOIN profiles ON attendances.rfid = profiles.rfid LEFT JOIN guests_infos ON attendances.id = guests_infos.guest_log_id WHERE profiles.profile_type = 'Guest'$filter ORDER BY guests_infos.guest_name DESC, attendances.time_log ASC";
$results = $con->getData($sql);

$c = 0;
foreach ($results as $i => $result) {

	$logs[$c]['date'] = date("F j",strtotime($result['time_log']));
	$logs[$c]['day'] = date("l",strtotime($result['time_log']));
	$logs[$c]['guest'] = $result['first_name'];
	$logs[$c]['logtime'] = date("h:i:s A",strtotime($result['time_log']));
	$logs[$c]['guest_name'] = $result['guest_name'];
	$logs[$c]['guest_purpose'] = $result['guest_purpose'];
	
	$c++;

}

echo json_encode($logs);

break;

case "student_attendance_report":

$con = new pdo_db();

$date = $_POST['year']."-".$_POST['month']."-01";

$start = date("Y-m-d",strtotime($date));
$end = date("Y-m-t",strtotime($date));

$weekdays = [];
while (strtotime($start) <= strtotime($end)) {

if ( (date("D",strtotime($start)) != "Sat") && (date("D",strtotime($start)) != "Sun") ) {
	if (date("D",strtotime($start)) == "Thu") $weekdays[][substr(date("D",strtotime($start)),0,2)] = date("j",strtotime($start));
	else $weekdays[][substr(date("D",strtotime($start)),0,1)] = date("j",strtotime($start));
}
	
$start = date("Y-m-d", strtotime("+1 day", strtotime($start)));	
	
}
 
$table = [];
$table["headerRows"] = 2;
$table["widths"][] = 200;
$table["body"][0][] = array("text"=>"", "style"=>"tabHeader");
$table["body"][1][] = array("text"=>"LEARNER'S NAME", "style"=>"tabHeader", "bold"=>"true");
foreach ($weekdays as $day) {
	foreach ($day as $key => $value) {
		$table["widths"][] = "*";
		$table["body"][0][] = array("text"=>$value, "style"=>"tabHeader");
		$table["body"][1][] = array("text"=>$key, "style"=>"tabHeader");		
	}
}
$table["widths"][] = 60;
$table["body"][0][] = array("text"=>"Total", "style"=>"tabHeader");
$table["body"][1][] = array("text"=>"Absent|Tardy", "style"=>"tabHeader");

$sql = "SELECT CONCAT(last_name, ', ', first_name, ' ', middle_name) fullname FROM profiles WHERE profile_type = 'Student' AND level = '$_POST[level]' AND section = '$_POST[section]'";
$students = $con->getData($sql);

foreach ($students as $key => $student) {
	
	$table["body"][$key+2][] = array("text"=>$student['fullname'], "style"=>"col");
	
	foreach ($weekdays as $day) {
		
		$table["body"][$key+2][] = array("text"=>"", "style"=>"col");
		
	}
	
	$table["body"][$key+2][] = array("text"=>"", "style"=>"col");
	
}

echo json_encode($table);
			
break;

case "student_attendance_report_jspdf":

$con = new pdo_db();

$date = $_POST['year']."-".$_POST['month']."-01";

$start = date("Y-m-d",strtotime($date));
$end = date("Y-m-t",strtotime($date));

$weekdays = [];
while (strtotime($start) <= strtotime($end)) {

if ( (date("D",strtotime($start)) != "Sat") && (date("D",strtotime($start)) != "Sun") ) {
	if (date("D",strtotime($start)) == "Thu") {
		$weekdays[] = array("date"=>$start,substr(date("D",strtotime($start)),0,2)=>date("j",strtotime($start)));
	} else {
		$weekdays[] = array("date"=>$start,substr(date("D",strtotime($start)),0,1)=>date("j",strtotime($start)));
	}
}

$start = date("Y-m-d", strtotime("+1 day", strtotime($start)));	
	
}
 
$table = [];
$table["columns"][] = array("title"=>"","dataKey"=>"student");
$table["rows"][] = array("student"=>"LEARNER'S NAME");

foreach ($weekdays as $day) {
	foreach ($day as $key => $value) {
		if ($key == "date") continue;
		$table["columns"][] = array("title"=>$value,"dataKey"=>$value);
		$table["rows"][0][$value] = $key;
	}
}

$table["columns"][] = array("title"=>"Total for the month","dataKey"=>"lastCell");
$table["rows"][0]["lastCell"] = "Total";

$sql = "SELECT rfid, CONCAT(last_name, ', ', first_name, ' ', middle_name) fullname FROM profiles WHERE profile_type = 'Student' AND level = '$_POST[level]' AND section = '$_POST[section]'";
$students = $con->getData($sql);

foreach ($students as $key => $student) {
	
	$table["rows"][$key+1]["student"] = $student['fullname'];
	
	$absent = 0;
	$tardy = 0;
	foreach ($weekdays as $day) {		
		
		$explicit = false;
		
		foreach ($day as $k => $value) {

			if ($k == "date") {
				$sql = "SELECT * FROM attendances WHERE rfid = '$student[rfid]' AND time_log LIKE '$value%'";
				$student_morning_in = "$value 07:25:00";
				continue;
			}
			
			$present = "x";			
			$dtr = $con->getData($sql);
			
			/*
			** if there's at least one log with order not set to zero
			*/
			foreach ($dtr as $no => $d) {	
				if ($d['log_order'] != 0) {
					$explicit = true;
					break;				
				}
			}
			
			if ($explicit) { // reset query
				$sql .= " AND log_order != 0 ORDER BY log_order";
				$dtr = $con->getData($sql);
			}
			
			// at least 1 log to be present
			if ($con->rows > 0) {
				$present = "/";
				if (strtotime($dtr[0]['time_log']) > strtotime($student_morning_in)) ++$tardy;				
			} else {
				++$absent;
			}

			$table["rows"][$key+1][$value] = $present;

		}
		
	}
	
	$table["rows"][$key+1]["lastCell"] = "";
	
	$table["rows"][$key+1]["absent"] = "$absent";
	$table["rows"][$key+1]["tardy"] = "$tardy";
	
}

echo json_encode($table);
			
break;

case "guests_dtr_jspdf":

$con = new pdo_db();

$date = $_POST['year']."-".$_POST['month']."-01";

$table = [];
$table["columns"][] = array("title"=>"Date","dataKey"=>"date");
$table["columns"][] = array("title"=>"Day","dataKey"=>"day");
$table["columns"][] = array("title"=>"Guest","dataKey"=>"guest");
$table["columns"][] = array("title"=>"Log Time","dataKey"=>"log");
$table["columns"][] = array("title"=>"Name","dataKey"=>"name");
$table["columns"][] = array("title"=>"Purpose","dataKey"=>"purpose");

$begin = date("Y-m-d 00:00:00",strtotime($date));
$end = date("Y-m-t 23:59:59",strtotime($date));	

$filter = " AND time_log >= '$begin' AND time_log <= '$end'";

$sql = "SELECT attendances.id, attendances.rfid, profiles.first_name, attendances.time_log, IFNULL(guests_infos.guest_name,'') guest_name, IFNULL(guests_infos.guest_purpose,'') guest_purpose FROM attendances LEFT JOIN profiles ON attendances.rfid = profiles.rfid LEFT JOIN guests_infos ON attendances.id = guests_infos.guest_log_id WHERE profiles.profile_type = 'Guest'$filter ORDER BY guests_infos.guest_name DESC, attendances.time_log ASC";
$guests = $con->getData($sql);

foreach ($guests as $key => $guest) {

	$table["rows"][$key]["date"] = date("F j",strtotime($guest['time_log']));
	$table["rows"][$key]["day"] = date("l",strtotime($guest['time_log']));
	$table["rows"][$key]["guest"] = $guest['first_name'];
	$table["rows"][$key]["log"] = date("h:i:s A",strtotime($guest['time_log']));
	$table["rows"][$key]["name"] = $guest['guest_name'];
	$table["rows"][$key]["purpose"] = $guest['guest_purpose'];
	
}

echo json_encode($table);

break;

case "select_staffs":

$con = new pdo_db();

$sql = "SELECT rfid, CONCAT(first_name, ' ', last_name) staff FROM profiles WHERE profile_type = 'Staff'";
$staffs = $con->getData($sql);

$selects = [];
foreach ($staffs as $staff) {

	$selects[$staff['staff']] = $staff['rfid'];
	
}

echo json_encode($selects);

break;

case "staffs_dtr_jspdf":

$con = new pdo_db();

$date = $_POST['year']."-".$_POST['month']."-01";

$table = [];
$table["columns"][] = array("title"=>"Date","dataKey"=>"date");
$table["columns"][] = array("title"=>"Day","dataKey"=>"day");
$table["columns"][] = array("title"=>"Morning In","dataKey"=>"morning_in");
$table["columns"][] = array("title"=>"Morning Out","dataKey"=>"morning_out");
$table["columns"][] = array("title"=>"Afternoon In","dataKey"=>"afternoon_in");
$table["columns"][] = array("title"=>"Afternoon Out","dataKey"=>"afternoon_out");
$table["columns"][] = array("title"=>"Tardiness","dataKey"=>"tardiness");
$table["columns"][] = array("title"=>"Undertime","dataKey"=>"undertime");

$start = date("Y-m-d",strtotime($date));
$end = date("Y-m-t",strtotime($date));

$key = 0;
$mode = array("morning_in","morning_out","afternoon_in","afternoon_out");
while (strtotime($start) <= strtotime($end)) {

$explicit = false;

$table["rows"][$key]["date"] = date("M j",strtotime($start));
$table["rows"][$key]["day"] = date("D",strtotime($start));

$sql = "SELECT time_log, log_order FROM attendances WHERE rfid = '$_POST[staff]' AND time_log LIKE '$start%'";
$dtrs = $con->getData($sql);

$table["rows"][$key]["morning_in"] = "";
$table["rows"][$key]["morning_out"] = "";
$table["rows"][$key]["afternoon_in"] = "";
$table["rows"][$key]["afternoon_out"] = "";
$table["rows"][$key]["tardiness"] = "";
$table["rows"][$key]["undertime"] = "";
$table["rows"][$key]["tardiness"] = "";
$table["rows"][$key]["undertime"] = "";

foreach ($dtrs as $no => $dtr) {
	
	if ($no > 3) continue;
	$table["rows"][$key][$mode[$no]] = date("h:i:s A",strtotime($dtr['time_log']));
	
}

/*
** if there's at least one log with order not set to zero
*/
foreach ($dtrs as $no => $dtr) {
	
	if ($dtr['log_order'] != 0) {

		$explicit = true;
		break;
	
	}

}

if ($explicit) {
	
	// reset
	$table["rows"][$key]["morning_in"] = "";
	$table["rows"][$key]["morning_out"] = "";
	$table["rows"][$key]["afternoon_in"] = "";
	$table["rows"][$key]["afternoon_out"] = "";

	foreach ($dtrs as $no => $dtr) {
		
		if ($dtr['log_order'] == 0) continue;
		$table["rows"][$key][$mode[$dtr['log_order']-1]] = date("h:i:s A",strtotime($dtr['time_log']));

	}
	
}

$mi_mark = "$start 07:45:00";
$mo_mark = "$start 12:00:00";
$ai_mark = "$start 13:00:00";
$ao_mark = "$start 16:00:00";

$mor_aft_cutoff = "$start 12:00:00";

$table["rows"][$key]["tardiness"] = "";
$table["rows"][$key]["undertime"] = "";
	
$start = date("Y-m-d", strtotime("+1 day", strtotime($start)));	
$key++;
	
}

echo json_encode($table);

break;

case "time_logs":

$con = new pdo_db();

$sql = "SELECT * FROM attendances WHERE time_log LIKE '$_POST[date]%' AND rfid = '$_POST[rfid]'";
$time_logs = $con->getData($sql);

$explicit = false;

foreach ($time_logs as $i => $log) {
	$time_logs[$i]['log_order'] = $i+1;
	$time_logs[$i]['time_log'] = date("H:i:s",strtotime($log['time_log']));
	$time_logs[$i]['disabled'] = true;
	$time_logs[$i]['invalid'] = false;
}

foreach ($time_logs as $i => $log) {
	if ($log['log_order'] != 0) {
		$explicit = true;
		break;
	}
}

if ($explicit) {
	
	$time_logs = $con->getData($sql." ORDER BY log_order ASC");	
	$ordered_time_logs = [];

	foreach ($time_logs as $i => $log) {
		$time_logs[$i]['time_log'] = date("H:i:s",strtotime($log['time_log']));
		$time_logs[$i]['disabled'] = true;
		$time_logs[$i]['invalid'] = false;
	}
	
	// order accordingly
	foreach ($time_logs as $i => $log) {
		if ($log['log_order'] > 0) {
			$ordered_time_logs[] = $log;
		}
	}
	
	// add log with order equals zero
	foreach ($time_logs as $i => $log) {
		if ($log['log_order'] == 0) {
			$ordered_time_logs[] = $log;
		}
	}
	
	echo json_encode($ordered_time_logs);
	exit();
	
}

echo json_encode($time_logs);

break;

case "delete_log":

$con = new pdo_db("attendances");

$delete = $con->deleteData(array("id"=>implode(",",$_POST['id'])));

break;

case "save_log":

$con = new pdo_db("attendances");

$update = $con->updateData(array("id"=>$_POST['log']['id'],"time_log"=>$_POST['date']." ".$_POST['log']['time_log'],"log_order"=>$_POST['log']['log_order']),'id');

break;

case "save_multi_logs":

$con = new pdo_db("attendances");

$logs = [];
foreach ($_POST['logs'] as $i => $log) {

$logs[] = array("id"=>$log['id'],"time_log"=>$_POST['date']." ".$log['time_log'],"log_order"=>$log['log_order']);
if ($log['log_order'] > 4) $logs[$i]['log_order'] = 0;
	
}

$update = $con->updateDataMulti($logs,'id');

break;

case "add_log":

$con = new pdo_db("attendances");

$add = $con->insertData($_POST);

break;

case "change_log_order":

$con = new pdo_db("attendances");

$update = $con->updateData(array("id"=>$_POST['log']['id'],"log_order"=>$_POST['log']['log_order']),'id');

break;

}

?>