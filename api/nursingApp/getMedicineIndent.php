<?php
include "../../includes/connection.php";
include("../../includes/global.function.php");

$type = $_POST['type'];
$uhid = $_POST['uhid'];
$ipd = $_POST['ipd'];
$arr = array();
$user = $_POST['user'];
$date = date("Y-m-d");
$time = date("H:i:s");


if ($type == 'getIndentList') {

    $qq = mysqli_query($link, "SELECT * FROM `patient_medicine_detail` WHERE `patient_id`='$uhid' AND `pin`='$ipd' AND `type`='2' GROUP BY `indent_num`");


    while ($rr = mysqli_fetch_array($qq)) {

        $temp = array();
        $temp['indentNo'] = $rr['indent_num'];
        $temp['dateTime'] = $rr['date'] . " " . $rr['time'];
        $temp['ipdId'] = $rr['pin'];
        $temp['uhid'] = $rr['patient_id'];



        array_push($arr, $temp);

    }
}
if ($type == 'getIndentDetails') {

    $indent_num = $_POST['indentNo'];
    $indent_qry = mysqli_query($link, "SELECT * FROM `patient_medicine_detail` WHERE `pin` = '$ipd' AND `patient_id` = '$uhid' AND `indent_num` = '$indent_num'");

    while ($r = mysqli_fetch_array($indent_qry)) {
        $m = mysqli_fetch_array(mysqli_query($link, "SELECT `item_name` FROM `item_master` WHERE `item_id`='$r[item_code]'"));

        $isu = mysqli_fetch_array(mysqli_query($link, "SELECT `sale_qnt` FROM `ph_sell_details` WHERE `bill_no`='$r[bill_no]' AND `item_code`='$r[item_code]'"));

        if (!$isu['sale_qnt']) {
            $isu['sale_qnt'] = 0;
        }

        $item_return_quantity = 0;

        $item_return = mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `ph_item_return_master` WHERE `bill_no`='$r[bill_no]' AND `indent_num`='$r[indent_num]' AND `item_code`='$r[item_code]' "));

        if ($item_return) {
            $item_return_quantity = $item_return["return_qnt"];
        }

        $temp = array();
        $temp['drugName'] = $m['item_name'];
        $temp['claimed'] = $r['quantity'];
        $temp['received'] = $isu['sale_qnt'];
        $temp['rejected'] = $item_return_quantity;

        array_push($arr, $temp);
    }

}
if ($type == 'drugList') {
    $qry = mysqli_query($link, "SELECT a.`item_id`,a.`item_name`,a.`generic_name` FROM `item_master` a, `ph_stock_master` b where a.`item_id`=b.`item_code` AND a.category_id='1' AND b.`substore_id`='1' AND b.`quantity`>'0' and a.`item_name`!='' order by a.`item_name`");

    while ($item = mysqli_fetch_array($qry)) {
        $temp = array();
        $temp['itemId'] = $item['item_id'];
        $temp['itemName'] = $item['item_name'];
        array_push($arr, $temp);
    }

}
if ($type == 'saveIndent') {

    $temp = array();
    $drugId = $_POST['indent'];
    $dId = explode("##", $drugId);

    $ind = mysqli_fetch_array(mysqli_query($link, "SELECT MAX(indent_num) as max FROM `patient_medicine_detail` WHERE `patient_id`='$uhid' AND `pin`='$ipd'"));
    $in = $ind['max'] + 1;

    foreach ($dId as $i => $v) {
        if ($v) {
            $vl = explode("@@", $v);
            $item_id = $vl[0];
            $qty = $vl[1];
            if (mysqli_query($link, "INSERT INTO `patient_medicine_detail`(`patient_id`, `pin`, `indent_num`, `item_code`, `dosage`, `instruction`, `quantity`, `status`, `date`, `time`, `user`, `type`, `bill_no`) VALUES ('$uhid','$ipd','$in','$item_id','','0','$qty','0','$date','$time','$user','2','')")) {
                $arr = array("status" => "success");

            } else {
                $arr = array("status" => "failed");
            }
        }
    }


}

echo json_encode($arr);