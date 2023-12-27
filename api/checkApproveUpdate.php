<?php
include('../includes/connection.php');

$val			=array();
$arr			=array();

$chk=mysqli_fetch_assoc(mysqli_query($link,"SELECT * FROM `update_approve_app` ORDER BY `slno` DESC LIMIT 0,1"));
if($link)
{
if($chk)
{
	$val	=array("Status" => "1", "version" => $chk['version']);
}
else
{
	$val	=array("Status" => "1", "version" => 0);
}
}
else
{
	$val	=array("Status" => "0", "version" => 0);
}

echo json_encode($val);
?>