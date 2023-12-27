<?php
include('../includes/connection.php');

$phone		=$_GET['phone'];
$pass		=mysqli_real_escape_string($link,$_GET['pass']);
$pass		=md5($pass);
$levelid	="13";

$val=array();

$temp=array();
$temp['response']=0;
$temp['uId']=0;
$temp['uName']="";
$temp['msg']="";

$arr=array();
$v=mysqli_fetch_assoc(mysqli_query($link,"SELECT * FROM `employee` WHERE `phone`='$phone' AND `password`='$pass' AND `levelid`='$levelid'"));
if($v)
{
	$temp['response']=1;
	$temp['uId']=$v['emp_id'];
	$temp['uName']=$v['name'];
	$temp['msg']="Login Success";
}
else
{
	$temp['uName']="Error";
	$temp['msg']="Wrong Phone No. / Password";
}

array_push($arr, $temp);
$val['result']=$arr;

echo json_encode($val);
?>