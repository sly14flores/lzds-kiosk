<?php

$_POST = json_decode(file_get_contents('php://input'), true);

require_once '../db.php';

switch ($_GET['r']) {
	case "login":
	$con = new pdo_db();
	$sql = "SELECT profiles.id, CONCAT(first_name, ' ', last_name) full_name, profiles.profile_type, privileges.description privilege FROM profiles LEFT JOIN accounts ON profiles.id = accounts.profile_id LEFT JOIN privileges ON profiles.privilege = privileges.id WHERE accounts.username = '".$_POST['username']."' AND accounts.password = '".$_POST['password']."'";
	$account = $con->getData($sql);
	if (($con->rows) > 0) {
		session_start();
		$_SESSION['id'] = $account[0]['id'];
		echo json_encode($account[0]);
	} else {
		echo json_encode(array("id"=>0));
	}
	break;
}

?>