<?php
include'../includes/connection.php';

$pid=$_GET['pid'];
$opd=$_GET['opd'];

$val=array();

$arr=array();

$temp			=array();
$temp['id']		=0;
$temp['name']	="Select All";

array_push($arr, $temp);

$q=mysqli_query($link,"SELECT DISTINCT c.`id`,c.`name` FROM `patient_test_details` a, `testmaster` b, `test_department` c WHERE a.`testid`=b.`testid` AND b.`category_id`='1' AND b.`category_id`=c.`category_id` AND b.`type_id`=c.`id` AND a.`patient_id`='$pid' AND a.`opd_id`='$opd'");
while($r=mysqli_fetch_assoc($q))
{
	$temp			=array();
	$temp['id']		=$r['id'];
	$temp['name']	=$r['name'];
	
	array_push($arr, $temp);
}

$val['result']	=$arr;

echo json_encode($val);
?>