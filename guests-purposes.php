<?php

require_once 'db.php';
$con = new pdo_db('guests_purposes');
$purposes = $con->getData("SELECT id, description FROM guests_purposes ORDER BY id");

$response = '<option value="-">- use arrow down/up to select -</option>';
					
foreach ($purposes as $purpose) {
	$response .= '<option value="'.$purpose['description'].'">'.$purpose['description'].'</option>';
}
					
$response .= '<option value="other">Other</option>';

echo $response;

?>