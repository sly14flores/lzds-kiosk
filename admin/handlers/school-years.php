<?php

$_POST = json_decode(file_get_contents('php://input'), true);

require_once '../../db.php';

session_start();

$con = new pdo_db();

$sql = "SELECT id, school_year FROM school_years";
$_school_years = $con->getData($sql);

$school_years = [];
$school_years[] = array("id"=>0,"school_year"=>"All");

foreach ($_school_years as $sy) {
	$school_years[] = $sy;
}

$current_sy = date("Y-").date("y",strtotime("+1 Year",strtotime(date("Y"))));
$school_year = $con->getData("SELECT id, school_year FROM school_years WHERE school_year = '$current_sy'");

if (!count($school_year)) $school_year = 0;
else $school_year = $school_year[0];

echo json_encode(array("_school_years"=>$_school_years,"school_years"=>$school_years,"school_year"=>$school_year));

?>