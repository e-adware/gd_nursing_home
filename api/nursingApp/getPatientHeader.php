<?php
include "../../includes/connection.php";

$ipd_id = $_POST['ipdid'];
$patient_id = $_POST['unit'];


$arr = array();

$bed_det = mysqli_fetch_array(mysqli_query($link, "SELECT `ward_id`, `bed_id` FROM `ipd_bed_alloc_details` WHERE `patient_id` = '$patient_id' AND `ipd_id` = '$ipd_id'"));

$pat_det = mysqli_fetch_array(mysqli_query($link, "SELECT `name` FROM `patient_info` WHERE `patient_id`='$patient_id'"));

$ward_name = mysqli_fetch_array(mysqli_query($link, "SELECT `name` FROM `ward_master` WHERE `ward_id` = '$bed_det[ward_id]'"));

// echo "SELECT `ward_id`, `bed_id` FROM ` ipd_bed_alloc_details` WHERE `patient_id` = '$patient_id' AND `ipd_id` = '$ipd_id'";

$bed_no = mysqli_fetch_array(mysqli_query($link, "SELECT `bed_no` FROM  `bed_master` WHERE `bed_id` = '$bed_det[bed_id]'"));

// $temp['name'] = $pat_det['name'];
// $temp['ward'] = $ward_name['name'];
// $temp['bed'] = $bed_no['bed_no'];

$arr = array("name"=>"$pat_det[name]", "ward" => "$ward_name[name]", "bed" => "$bed_no[bed_no]");

echo json_encode($arr);