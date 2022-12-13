<?php

require_once 'db.php';
$con = new pdo_db();

$rfid = $_POST['rfid'];

$now = date('Y-m-d');

$sql = "SELECT attendances.id, attendances.rfid, CONCAT(profiles.first_name, ' ', SUBSTRING(profiles.middle_name,1,1), '. ', profiles.last_name) fullname, attendances.time_log, profiles.cp, DATE_FORMAT(attendances.time_log, '%a, %b %e, %Y') log_date, DATE_FORMAT(attendances.time_log, '%h:%i %p') log_time, DATE_FORMAT(attendances.time_log, '%Y-%m-%d') logQ_date, profiles.chat_id FROM attendances LEFT JOIN profiles ON attendances.rfid = profiles.rfid WHERE profiles.profile_type = 'Student' AND SUBSTRING(time_log,1,10) = '{$now}' AND attendances.rfid = '{$rfid}' ORDER BY log_time DESC";

$log = $con->getData($sql);

if (($con->rows)>0) {

  $message = "";

  $studentIn = "FROM: Lord of Zion Divine School. Good day. Dear parent/guardian, Your child ".$log[0]['fullname']." has entered the school premises on ".$log[0]['log_date']." at ".$log[0]['log_time'].". Wishing you a great day ahead. Thank you.";
  $studentOut = "FROM: Lord of Zion Divine School. Great day. Dear parent/guardian, Your child ".$log[0]['fullname']." has left the campus on ".$log[0]['log_date']." at ".$log[0]['log_time'].". Enjoy the rest of the day. God bless.";

  if (date("H") < 12) {
    $message = $studentIn;
  } else {
    $message = $studentOut;
  }

  $log[0]['message'] = $message;

  echo json_encode($log[0]);
  exit();
}

echo "Error fetching log";

?>