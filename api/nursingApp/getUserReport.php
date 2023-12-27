<?php
include "../includes/connection.php";

$arr = [];

$dateFrom = $_GET["dateFrom"];
$dateTo = $_GET["dateTo"];
$date1 = date("Y-m-d", strtotime($dateFrom));
$date2 = date("Y-m-d", strtotime($dateTo));
$encounter = $_GET["encounterId"];
$branch_id = $_GET["branchId"];
if (!$encounter) {
    // $enc_qry = "";
    $encounter = 0;
    $encounter_str_b = "";
} else {
    // $enc_qry = "AND `encounter`='$encounter'";
    $str .= " AND b.`type`='$encounter'";

    $encounter_str_b = " AND b.`type`='$encounter'";
}
if (!$branchId) {
    $branchId = 1;
    $branch_id_str_b = "";
} else {
    $str .= " AND b.`branch_id`='$branch_id'";

    $branch_id_str_b = " AND b.`branch_id`='$branch_id'";
}

$str =
    " SELECT DISTINCT a.`user`, a.`date` FROM `payment_detail_all` a, `uhid_and_opdid` b WHERE a.`patient_id`=b.`patient_id` AND a.`opd_id`=b.`opd_id`";

if ($date1 && $date2) {
    $str .= " AND a.`date` BETWEEN '$date1' AND '$date2'";

    $date_str_a = " AND a.`date` BETWEEN '$date1' AND '$date2'";
}

$str .= " ORDER BY a.`user` ASC";

$qry = mysqli_query($link, $str);
$total_received_amount = $total_refund_amount = $total_net_amount = 0;

// echo $str;

while ($data = mysqli_fetch_array($qry)) {
    $user_name = mysqli_fetch_array(
        mysqli_query(
            $link,
            " SELECT `name` FROM `employee` WHERE `emp_id`='$data[user]' "
        )
    );

    $pay_det = mysqli_fetch_array(
        mysqli_query(
            $link,
            " SELECT ifnull(SUM(a.`amount`),0) AS `tot_rcv`, ifnull(SUM(a.`refund_amount`),0) AS `tot_refund` FROM `payment_detail_all` a, `uhid_and_opdid` b WHERE a.`patient_id`=b.`patient_id` AND a.`opd_id`=b.`opd_id` AND a.`user`='$data[user]' $date_str_a $branch_id_str_b $encounter_str_b "
        )
    );

    $received_amount = $pay_det["tot_rcv"];
    $total_received_amount += $pay_det["tot_rcv"];

    $refund_amount = $pay_det["tot_refund"];
    $total_refund_amount += $pay_det["tot_refund"];

    $net_amount = $received_amount - $refund_amount;
    $total_net_amount += $received_amount - $refund_amount;

    $temp = [];
    // $temp['uhid'] = $summary['patient_id'];
    $temp["billno"] = "user_report";
    $temp["name"] = $user_name["name"];
    $temp["amount"] = $received_amount;
    $temp["date"] = date("d-M-Y", strtotime($data["date"]));
    $temp["encounter"] = $refund_amount;
    array_push($arr, $temp);
}
echo json_encode($arr);
?>