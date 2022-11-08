<?php

require_once '../../db.php';
require_once '../excel/PHPExcel/IOFactory.php';

$_POST = json_decode($_POST['form2excel'], true);

$date = $_POST['year']."-".$_POST['month']."-01";

$fileName = 'SF_2_Daily_Attendance.xlsx';
$school_id = "400075";
$school = "Lord of Zion Divine School";
$sy = $_POST['year'].date("-Y", strtotime("+1 Year", strtotime($date)));
$month = date("F",strtotime($date));

// Read the file
$fileType = PHPExcel_IOFactory::identify($fileName);
$objReader = PHPExcel_IOFactory::createReader($fileType);
$objPHPExcel = $objReader->load($fileName);

// Add entries
$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(2, 6, $school_id);
$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(2, 8, $school);
$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(10, 6, $sy);
$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(23, 6, $month);
$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(23, 8, $_POST['level']);
$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(28, 8, $_POST['section']);

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

$c = 3;
$markAbsent = array(
    'borders' => array(
        'diagonal' => array(
            'style' => PHPExcel_Style_Border::BORDER_THICK,
            'color' => array('argb' => 'FF000000'),
        ),
        'diagonaldirection' => PHPExcel_Style_Borders::DIAGONAL_BOTH
    )
);
foreach ($weekdays as $day) {
	foreach ($day as $key => $value) {
		if ($key == "date") continue;
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($c, 11, $value);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($c, 12, $key);

		$objPHPExcel->getActiveSheet()->getStyle('D13')->applyFromArray($markAbsent);
	}
	$c++;
}

// $boysStart = 13;
// $girlsStart = 35;

// $con = new pdo_db();
// $sql = "SELECT rfid, CONCAT(last_name, ', ', first_name, ' ', middle_name) fullname FROM profiles WHERE profile_type = 'Student' AND level = '$_POST[level]' AND section = '$_POST[section]'";
// $students = $con->getData($sql);

// foreach ($students as $key => $student) {
	
	// $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1, $boysStart, $student['fullname']);
	
	// $absent = 0;
	// $tardy = 0;
	// foreach ($weekdays as $day) {
		
		// $explicit = false;
		
		// foreach ($day as $k => $value) {

			// if ($k == "date") {
				// $sql = "SELECT * FROM attendances WHERE rfid = '$student[rfid]' AND time_log LIKE '$value%'";
				// $student_morning_in = "$value 07:25:00";
				// continue;
			// }
			
			// $present = "x";			
			// $dtr = $con->getData($sql);
			
			// /*
			// ** if there's at least one log with order not set to zero
			// */
			// foreach ($dtr as $no => $d) {	
				// if ($d['log_order'] != 0) {
					// $explicit = true;
					// break;				
				// }
			// }
			
			// if ($explicit) { // reset query
				// $sql .= " AND log_order != 0 ORDER BY log_order";
				// $dtr = $con->getData($sql);
			// }
			
			// at least 1 log to be present
			// if ($con->rows > 0) {
				// $present = "/";
				// if (strtotime($dtr[0]['time_log']) > strtotime($student_morning_in)) ++$tardy;				
			// } else {
				// ++$absent;
			// }

		// }
		
	// }
	
	// $boysStart++;
	
// }

$objPHPExcel->getActiveSheet()->getRowDimension('63')->setRowHeight(22);

// Write the file
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $fileType);
$objWriter->save(str_replace('.php', '.xlsx', __FILE__));

?>