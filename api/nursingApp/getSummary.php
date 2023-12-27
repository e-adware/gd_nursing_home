<?php 
include "../includes/connection.php";

$arr = array();

$dateFrom = $_GET["dateFrom"];
$dateTo = $_GET["dateTo"];
$date1 = date("Y-m-d", strtotime($dateFrom));
$date2 = date("Y-m-d", strtotime($dateTo));
$encounter = $_GET["encounterId"];
$branch_id = $_GET["branchId"];
if (!$encounter) {
$enc_qry = "";
$encounter = 0;
} else {
$enc_qry = "AND `type`='$encounter'";
}
if (!$branchId) {
$branchId = 1;
}

// SELECT `bill_amount`, `balance_amount` FROM `payment_detail_all` WHERE `patient_id`='' AND `opd_id`='' ORDER BY `pay_id` DESC LIMIT 1;

$uhid_opd_qry = mysqli_query($link, "SELECT DISTINCT `patient_id`, `opd_id`, `type`, `date` FROM `uhid_and_opdid` WHERE `date` BETWEEN '$date1' AND '$date2'  $enc_qry ");

// echo "SELECT DISTINCT `patient_id`, `opd_id` FROM `uhid_and_opdid` WHERE `date` BETWEEN '$date1' AND '$date2'  $enc_qry ";

while($uhid_opd = mysqli_fetch_array($uhid_opd_qry)) {
    $pat_name = mysqli_fetch_array(mysqli_query($link, "SELECT * FROM `patient_info` WHERE `patient_id` = '$uhid_opd[patient_id]'"));
    

    $amtqry = mysqli_fetch_array(mysqli_query($link,"SELECT * FROM `payment_detail_all` WHERE `patient_id`='$uhid_opd[patient_id]' AND `opd_id`='$uhid_opd[opd_id]' ORDER BY `pay_id` DESC LIMIT 1"));

    $encounter = mysqli_fetch_array(mysqli_query($link, "SELECT `p_type` FROM `patient_type_master` WHERE `p_type_id` = '$uhid_opd[type]'"));

    // echo "SELECT * FROM `payment_detail_all` WHERE `patient_id`='$uhid_opd[patient_id]' AND `opd_id`='$uhid_opd[opd_id]' ORDER BY `pay_id` DESC LIMIT 1;";
    
// echo "SELECT * FROM `payment_detail_all` WHERE `patient_id`='$uhid_opd[patient_id]' AND `opd_id`='$uhid_opd[opd_id]' ORDER BY `pay_id` DESC LIMIT 1;";

    $temp = array();
    $temp['uhid'] = $uhid_opd['patient_id'];
    $temp['billno'] = $uhid_opd['opd_id'];
    $temp['name'] = $pat_name['name'];
    $temp['amount'] = $amtqry['bill_amount'];
    if(!$temp['amount']) {
        $temp['amount'] = 0;
    }
    $temp['date'] = date('d-M-Y', strtotime($uhid_opd['date']));
    $temp['encounter'] = $encounter['p_type'];
    array_push($arr, $temp);
    
}

echo json_encode($arr);