<?php

require_once 'db.php';
$con = new pdo_db();

$rfid = substr($_POST['rfid'],0,10);

$now = date('Y-m-d');

$sql = "SELECT attendances.id, attendances.rfid, CONCAT(profiles.first_name, ' ', SUBSTRING(profiles.middle_name,1,1), '. ', profiles.last_name) fullname, attendances.time_log, profiles.cp, DATE_FORMAT(attendances.time_log, '%a, %b %e, %Y') log_date, DATE_FORMAT(attendances.time_log, '%h:%i %p') log_time, DATE_FORMAT(attendances.time_log, '%Y-%m-%d') logQ_date, profiles.chat_id FROM attendances LEFT JOIN profiles ON attendances.rfid = profiles.rfid WHERE profiles.profile_type = 'Student' AND SUBSTRING(time_log,1,10) = '{$now}' AND attendances.rfid = '{$rfid}' ORDER BY time_log DESC";

$log = $con->getData($sql);

if (($con->rows)>0) {

  $_log = [];
  
  $i = count($log);
  if ( ($i%2) == 1) { // In
    $message = "FROM: Lord of Zion Divine School. Good day. Dear parent/guardian, Your child ".$log[0]['fullname']." has entered the school premises on ".$log[0]['log_date']." at ".$log[0]['log_time'].". Wishing you a great day ahead. Thank you.";
    $log[0]['message'] = $message;
    $_log = $log[0];
  } else { // Out
    $message = "FROM: Lord of Zion Divine School. Great day. Dear parent/guardian, Your child ".$log[$i-1]['fullname']." has left the campus on ".$log[$i-1]['log_date']." at ".$log[$i-1]['log_time'].". Enjoy the rest of the day. God bless.";
    $log[0]['message'] = $message;
    $_log = $log[0];
  }

  echo json_encode($_log);
  
  exit();
}

echo "Error fetching log";

?>
