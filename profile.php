<?php
require_once 'db.php';
$con = new pdo_db('attendances');

$mode = "In";
$logsCount = 1;

/*
** reverification
*/
$diff_from_last_log = 10;
$rev = 1;
$get_today_log = $con->getData("SELECT id, time_log FROM attendances WHERE rfid = '$_GET[rfid]' AND DATE_FORMAT(time_log,'%Y-%m-%d') = '".date("Y-m-d")."' ORDER BY id DESC");
if (($con->rows)>0) {
	$id = $get_today_log[0]['id'];
	$last_log = strtotime($get_today_log[0]['time_log']);
	$min = strtotime(date("Y-m-d H:i:s"));
	$diff_from_last_log = round(abs($min - $last_log) / 60,2);
}

/*
** determine if IN or OUT
*/
$logsCount = $con->rows;

$profileType = $_GET['profile_type'];

/*
**	log date/time
*/
if (($profileType == 'Staff') || ($profileType == 'Student')) { /* staff or student */
	if ($diff_from_last_log > $rev) {
		$insert_log = $con->insertData(array("rfid"=>$_GET['rfid'],"time_log"=>"CURRENT_TIMESTAMP","log_order"=>0));
		$id = $con->insertId;
		$logsCount++;
	}
} else { /* guest or no record */

/*
** reverification is not applicable for guest so insert guest log
*/

$insert_log = $con->insertData(array("rfid"=>$_GET['rfid'],"time_log"=>"CURRENT_TIMESTAMP","log_order"=>0));
$id = $con->insertId;
$cardPunches = $con->getData("SELECT id, time_log FROM attendances WHERE rfid = '$_GET[rfid]' AND DATE_FORMAT(time_log,'%Y-%m-%d') = '".date("Y-m-d")."' ORDER BY id DESC");
$logsCount = $con->rows;

}

if (($logsCount)%2 == 0) $mode = "Out";

$result = $con->getData("SELECT *, CONCAT(first_name, ' ', SUBSTRING(middle_name,1,1), '. ', last_name) fullname FROM attendances INNER JOIN profiles ON attendances.rfid = profiles.rfid WHERE attendances.id = $id");
if (($con->rows) > 0) {
if (($result[0]['profile_type'] == "Staff") || ($result[0]['profile_type'] == "Student")) { // staff or teacher
?>
			<div class="col-lg-6">
				<img id="profile-pic" class="img-responsive center-block" src="profile-pics/<?php echo $result[0]['picture']; ?>">				
			</div>
			<div class="col-lg-6">
				<div id="profile-info">
					<div class="panel panel-primary">
					  <div class="panel-heading profile-info-header"><?php echo $result[0]['profile_type']." "; ?>Name</div>				
					  <div class="panel-body profile-info-body">			
						<p><?php echo $result[0]['fullname']; ?></p>
					  </div>
					</div>
					<div class="panel panel-primary">
					  <div class="panel-heading profile-info-header">ID No</div>				
					  <div class="panel-body profile-info-body">			
						<p><?php echo $result[0]['school_id']; ?></p>
					  </div>
					</div>
					<?php if ($result[0]['profile_type'] == 'Student') { ?>
					<div class="panel panel-primary">
					  <div class="panel-heading profile-info-header">Level / Section</div>				
					  <div class="panel-body profile-info-body">			
						<p><?php echo $result[0]['level']; ?> / <?php echo $result[0]['section']; ?></p>
					  </div>
					</div>
					<?php } ?>
					<div class="panel panel-primary">
					  <div class="panel-heading profile-info-header">Time <?php echo $mode; ?></div>				
					  <div class="panel-body profile-info-body">
						<p id="time-in-out"><?php echo date("h:i A",strtotime($result[0]['time_log'])); ?></p>
					  </div>
					</div>					
				</div>
			</div>			
<?php
} else { /* guest */

$displayGuestLogForm = true;

if ($mode == "Out") $displayGuestLogForm = false;

?>			
			<div class="col-lg-4 col-lg-offset-2 col-md-6">
				<div class="guest-log">
					<div style="margin-top: 80px;"></div>
					<div class="panel panel-primary">
					  <div class="panel-heading"><p style="font-size: 3em !important;">Guest Log</p></div>
					  <div class="panel-body">			
						<p style="font-size: 5em !important;" id="cacheGuestInfo" data-guest-log-id="<?php echo $id; ?>" data-guest-rfid="<?php echo $_GET['rfid']; ?>" ><?php echo $result[0]['first_name']; ?></p>
					  </div>
					</div>
<?php
if ($displayGuestLogForm) {
?>				
					<div class="panel panel-primary">
						<div class="panel-heading"><p style="font-size: 3em !important;">Enter Name</p></div>
						<div class="panel-body profile-info-body">
						  <div class="form-group">
							<input type="text" class="form-control" style="height: 50px!important" id="guest_name" placeholder="Guest Name" autofocus>
						  </div>
						</div>
					</div>
<?php
}
?>
				</div>
			</div>
			<div class="col-lg-4 col-md-6">
				<div class="guest-log">
					<div style="margin-top: 80px;"></div>				
					<div class="panel panel-primary">
					  <div class="panel-heading"><p style="font-size: 3em !important;">Time <?php echo $mode; ?></p></div>				
					  <div class="panel-body">
						<p style="font-size: 5em !important;"><?php echo date("h:i A",strtotime($result[0]['time_log'])); ?></p>
					  </div>
					</div>
<?php
if ($displayGuestLogForm) {
?>					
					<div class="panel panel-primary">
						<div class="panel-heading"><p style="font-size: 3em !important;">Enter Purpose</p></div>
						<div class="panel-body profile-info-body">
						  <div class="form-group">
							<input type="text" class="form-control" style="height: 50px!important" id="guest_purpose" placeholder="Guest Purpose">
						  </div>							
						</div>
					</div>
<?php
}
?>					
				</div>
			</div>
<?php
if ($displayGuestLogForm) {
?>				
			<div class="col-lg-6 col-md-offset-3" style="border: solid #337AB7;">
			<div style="font-size: 18px;"><div style="font-style: italic;"><p>Data Privacy Act</p></div>
			<p><center><strong>NOTICE:</strong></center><p>
			<p>We are collecting your personal information pursuant to the security of the school.
			No data will be divulged to any person or entity without your prior approval. Thank you.</p>
			<p style="direction: rtl;">~LZDS Admin</p></div>
			</div>
			<div class="col-lg-2 col-lg-offset-5 col-md-2" style="margin-bottom: 50px;">
				<button type="button" id="logGuest" class="btn btn-primary btn-lg btn-block"><span style="font-size: 3em;">Ok</span></button>			
			</div>
<?php
		}
	}
} else { /* no record */
?>
			<div class="col-lg-6 col-lg-offset-3">
				<div class="notification">
					<div style="margin-top: 50px;"></div>
					<div class="panel panel-primary">
					  <div class="panel-heading"><p style="font-size: 3em !important;">Notification</p></div>
					  <div class="panel-body">			
						<p style="font-size: 5em !important;">No Record Found</p>
					  </div>
					</div>										
				</div>
			</div>			
<?php } ?>