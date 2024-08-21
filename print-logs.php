<?php
include 'includes/connection.php'; 
include 'includes/functions.php'; 
include 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as writerxlsx;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as readerxlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing as drawing; // Instead PHPExcel_Worksheet_Drawing
use PhpOffice\PhpSpreadsheet\Style\Alignment as alignment; // Instead alignment
use PhpOffice\PhpSpreadsheet\Style\Border as border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat as number_format;
use PhpOffice\PhpSpreadsheet\Style\Fill as fill; // Instead fill
use PhpOffice\PhpSpreadsheet\Style\Color as color; //Instead PHPExcel_Style_Color
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup as pagesetup; // Instead PHPExcel_Worksheet_PageSetup
use PhpOffice\PhpSpreadsheet\IOFactory as io_factory; // Instead PHPExcel_IOFactory

$sql="SELECT * FROM log_head";

if(!empty($_GET)){
	$sql .= " WHERE"; 
	if(!empty($_GET['date_from'])){
		if(!empty($_GET['date_to'])){
			$sql .= " date_performed BETWEEN '$_GET[date_from]' AND '$_GET[date_to]' AND";
		}else{
			$sql .= " date_performed BETWEEN '$_GET[date_from]' AND '$_GET[date_from]' AND";
			
		}
	}

	if(!empty($_GET['duefrom'])){
		if(!empty($_GET['dueto'])){
			$sql .= " due_date BETWEEN '$_GET[duefrom]' AND '$_GET[dueto]' AND";
		}else{
			$sql .= " due_date BETWEEN '$_GET[duefrom]' AND '$_GET[duefrom]' AND";
			
		}
	}

	if(!empty($_GET['unit'])){
		$sql .= " unit =  '$_GET[unit]' AND";
	}
	if(!empty($_GET['system_name'])){
		$sql .= " main_system =  '$_GET[system_name]' AND";
		//$url.="system_name=".$_GET['system_name'];
	}
	if(!empty($_GET['sub_system'])){
		$sql .= " sub_system =  '$_GET[sub_system]' AND";
	
	}
	if(!empty($_GET['status'])){
		$sql .= " status =  '$_GET[status]' AND";
	
	}
}
$q = substr($sql,-3);
if($q == 'AND'){
	$sql = substr($sql,0,-3);
} else{
	if(!empty($_GET['base']) && $_GET['base']=='view_latest'){
		$current = date("Y-m-d");
		$date = strtotime($current.'-3 months');
		$final = date('Y-m', $date);	
		$final = $final.'-01';
		$sql="SELECT * FROM log_head WHERE (date_performed BETWEEN '$final' AND '$current') OR status = 'On-Progress'";
	}else if(!empty($_GET['base']) && $_GET['base']=='view_records'){
		$sql="SELECT * FROM log_head";
	}
}
// else {
// 	$sql = substr($sql,0,-5);
// }


// require_once 'js/phpexcel/Classes/PHPExcel/IOFactory.php';
$exportfilename="export/Logbook.xlsx";
// $objPHPExcel = new PHPExcel();
$objPHPExcel = new Spreadsheet();
$styleArray = array(
	'borders' => array(
		'allBorders' => array(
			'borderStyle' => border::BORDER_THIN
		)
	)
); 
foreach(range('A','L') as $columnID){
	$objPHPExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
}
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', "LOGBOOK SYSTEM");
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A2', "Unit");
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B2', "Main Category");
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('C2', "Sub System");
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('D2', "Date/Time Performed");
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('E2', "Date Done");
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('F2', "Done By");
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('G2', "Due Date");
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('H2', "Notes");
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('I2', "Performed By");
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('J2', "Logged By");
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('K2', "Logged Date");
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('L2', "Status");

$num=3;
$q = $con->query($sql);
while($fetch = $q->fetch_array()){
	$datetime = $fetch['date_performed'] . " " . $fetch['time_performed'];
	$unit=getInfo($con, 'unit_name', 'unit', 'unit_id',  $fetch['unit']);
	$main=getInfo($con, 'system_name', 'main_system', 'main_id',  $fetch['main_system']);
	$sub=getInfo($con, 'subsys_name', 'sub_system', 'sub_id',  $fetch['sub_system']);
	$loggedby=getInfo($con, 'fullname', 'users', 'user_id',  $fetch['logged_by']);
	$finishby = ($fetch['finished_by']!=0) ? getInfo($con, "fullname", "users", "user_id", $fetch['finished_by']) : '';
	// $unit='';
	// $main='';
	// $sub='';
	// $loggedby='';
	// $finishby ='';
	$status = $fetch['status'];
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$num, $unit);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B'.$num, $main);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue('C'.$num, $sub);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue('D'.$num, $datetime);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue('E'.$num, $fetch['date_finish']);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue('F'.$num, $finishby);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue('G'.$num, $fetch['due_date']);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue('H'.$num, $fetch['notes']);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue('I'.$num, $fetch['performed_by']);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue('J'.$num, $loggedby);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue('K'.$num, $fetch['logged_date']);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue('L'.$num, $fetch['status']);
	$objPHPExcel->getActiveSheet()->getStyle('A'.$num.":L".$num)->applyFromArray($styleArray);
	$updates = $con->query("SELECT * FROM update_logs WHERE log_id = '$fetch[log_id]'");
	$update_rows = $updates->num_rows;
	if($update_rows > 0){

		while($fetchup = $updates->fetch_array()){
			
			$num++;
			$datetime_up = $fetchup['date_performed'] . " " . $fetchup['time_performed'];
			// $loggedby_up='';
			$loggedby_up=getInfo($con, 'fullname', 'users', 'user_id',  $fetchup['logged_by']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$num, 'UPDATES:');
			$objPHPExcel->getActiveSheet()->mergeCells('A'.$num.':C'.$num);
			$objPHPExcel->getActiveSheet()->getStyle('A'.$num)->getFont()->setBold(true);
			
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('D'.$num, $datetime_up);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('H'.$num, $fetchup['notes']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('I'.$num, $fetchup['performed_by']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('J'.$num, $loggedby_up);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('K'.$num, $fetchup['logged_date']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('L'.$num, $fetchup['status']);
			$objPHPExcel->getActiveSheet()->getStyle('A'.$num.":L".$num)->applyFromArray($styleArray);
		}
	}
	$num++;
}
$objPHPExcel->getActiveSheet()->getStyle('A2:L2')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('A2:L2')->getAlignment()->setHorizontal(alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('A2:L2')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->mergeCells('A1:L1');


header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Logbook.xlsx"');
header('Cache-Control: max-age=0');
ob_end_clean();
$objWriter = io_factory::createWriter($objPHPExcel, 'Xlsx');
$objWriter->save('php://output');

// $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
// if (file_exists($exportfilename))
// 		unlink($exportfilename);
// $objWriter->save($exportfilename);
// unset($objPHPExcel);
// unset($objWriter);   
// ob_end_clean();
// header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
// header('Content-Disposition: attachment; filename="logbook.xlsx"');
// readfile($exportfilename);
?>