<?php

require_once '../../db.php';

$_POST = json_decode(file_get_contents('php://input'), true);

$dir = "../../profile-pics/";

switch ($_GET['r']) {

case "profiles":
	
	$filters = [];
	$filter = "";
	if ($_POST['profile_type']['name'] != "All") $filters[] = "profile_type = '".$_POST['profile_type']['name']."'";
	if (isset($_POST['sy'])) {
		if ($_POST['sy']['id'] > 0) $filters[] = "school_year = ".$_POST['sy']['id'];
	}
	
	foreach ($filters as $key => $value) {
		if ($key > 0) $filter .= " AND $value";
		else $filter .= " WHERE $value";
	}
	
	$con = new pdo_db();
	$results = $con->getData("SELECT *, IF(profile_type = 'Guest',first_name,CONCAT(first_name, ' ', SUBSTRING(middle_name, 1, 1), '. ', last_name)) full_name FROM profiles$filter");
	foreach ($results as $key => $result) {
		$school_year = $con->getData("SELECT id, school_year FROM school_years WHERE id = $result[school_year]");
		$results[$key]['school_year'] = (isset($school_year[0]))?$school_year[0]:array("id"=>"","school_year"=>"");
	}

	echo json_encode($results);	

break;

case "new_profile":

	$con = new pdo_db('profiles');
	$profile = $con->insertData(array("date_registered"=>"CURRENT_TIMESTAMP","date_last_modified"=>"CURRENT_TIMESTAMP","privilege"=>2));
	echo $con->insertId;

break;

case "get_profile_picture":

	$con = new pdo_db();
	$picture = $con->getData("SELECT picture FROM profiles WHERE id = ".$_GET['id']);
	echo "../profile-pics/".$picture[0]['picture'];

break;

case "save_profile":
	
	$_POST['school_id'] = (isset($_POST['school_id']['enrollee_fid']))?$_POST['school_id']['enrollee_fid']:$_POST['school_id'];
	$_POST['school_year'] = $_POST['school_year']['id'];
	$_POST['date_last_modified'] = "CURRENT_TIMESTAMP";
	$_POST['profile_saved'] = 1;
	
	$con = new pdo_db('profiles');
	$profile = $con->updateData($_POST,'id');

break;

case "save_account":	
	
	$con = new pdo_db('accounts');

	if ($_POST['id'] == 0) {
		unset($_POST['id']);
		$account = $con->insertData($_POST);
	} else {
		$account = $con->updateData($_POST,'id');
	}

break;

case "edit_profile":

	$con = new pdo_db();
	$profile = $con->getData("SELECT * FROM profiles WHERE id = ".$_GET['id']);

	$sy = $con->getData("SELECT id, school_year FROM school_years WHERE id = ".$profile[0]['school_year']);
	
	$profile[0]['school_year'] = (count($sy))?$sy[0]:array("id"=>0,"school_year"=>"All");
	
	$avatar = "../profile-pics/avatar.png";
	$picture = "../profile-pics/".$profile[0]['picture'];

	$profile[0]['picture'] = (file_exists("../".$picture))?$picture:$avatar;

	$account = $con->getData("SELECT * FROM accounts WHERE profile_id = ".$_GET['id']);

	if (!count($account)) $account[0] = array("id"=>0,"profile_id"=>$profile[0]['id'],"username"=>"","password"=>"");

	echo json_encode(array("profile"=>$profile[0],"account"=>$account[0]));

break;

case "cancel_profile":

	delProfilePics($_POST['id']);

	$con = new pdo_db('profiles');
	$delete = $con->deleteData($_POST);

break;

case "upload_profile_picture":
	
	$fn = $_GET['id'].date("-Y-m-d-H-i-s").$_GET['en'];
	move_uploaded_file($_FILES['file']['tmp_name'],$dir.$fn);
	
	$con = new pdo_db('profiles');
	$profile = $con->updateData(array("picture"=>$fn,"id"=>$_GET['id']),'id');

break;

case "check_unsaved_profile":

/*
** Only delete unsaved profiles added by current logged in user
*/

$con = new pdo_db('profiles');
$unsaveds = $con->getData("SELECT id FROM profiles WHERE profile_saved = 0"); // don't account_id to be added later

foreach($unsaveds as $unsaved) {
	
	delProfilePics($unsaved['id']);

	$delete = $con->deleteData($unsaved);
	
}

break;

}

function delProfilePics($ids) {
	
	global $dir;
	
	$ids = explode(",",$ids);
	
	$con = new pdo_db();
	foreach($ids as $id) {
		
		$picture = $con->getData("SELECT picture FROM profiles WHERE id = $id");
		if ($picture[0]['picture'] != null) {
			if (file_exists($dir.$picture[0]['picture'])) {
				unlink($dir.$picture[0]['picture']);
			}
		}
		
	}
	
}

?>