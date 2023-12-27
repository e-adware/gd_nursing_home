<?php
include('../includes/connection.php');

$emp_id		=$_POST['emp_id'];
$uPhone		=$_POST['uPhone'];
$changPass	=$_POST['passwordChangeRequest'];
$password	=md5($_POST['password']);

$val=array();
$arr=array();

$temp=array();
$temp['response']=0;
$temp['updPhone']=0;
$msg="";
$v=mysqli_fetch_assoc(mysqli_query($link,"SELECT * FROM `employee` WHERE `emp_id`='$emp_id'"));
if($v['phone']!=$uPhone)
{
	mysqli_query($link,"UPDATE `employee` SET `phone`='$uPhone' WHERE `emp_id`='$emp_id'");
	$temp['updPhone']=1;
	$msg="Phone No.";
}
if($changPass=="1")
{
	mysqli_query($link,"UPDATE `employee` SET `password`='$password' WHERE `emp_id`='$emp_id'");
	if($msg)
	{
		$msg.=" and Password";
	}
	else
	{
		$msg="Password";
	}
}
if($msg)
{
	$msg.=" updated";
	$temp['response']=1;
}
else
{
	$temp['response']=2;
}
$temp['msg']=$msg;

array_push($arr, $temp);
$val['result']=$arr;

echo json_encode($val);
?>
