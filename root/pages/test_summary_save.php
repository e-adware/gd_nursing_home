<?php
	include("../../includes/connection.php");
	$testid=$_POST["testid"];
	$param=$_POST["param"];
	$summ=mysqli_real_escape_string($link, $_POST["summ"]);
	
	if($param)
	{
		if($summ=="<p><br></p>")
		{
			mysqli_query($GLOBALS["___mysqli_ston"], "delete from test_summary where paramid='$param'");
		}
		else
		{
			mysqli_query($GLOBALS["___mysqli_ston"], "delete from test_summary where paramid='$param'");
			//mysqli_query($GLOBALS["___mysqli_ston"], "insert into test_summary values('','$param','$summ')");
			mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `test_summary`(`testid`, `paramid`, `summary`) VALUES ('0','$param','$summ')");
		}
	}
	else
	{
		if($summ=="<p><br></p>")
		{
			mysqli_query($GLOBALS["___mysqli_ston"], "delete from test_summary where testid='$testid'");
		}
		else
		{
			mysqli_query($GLOBALS["___mysqli_ston"], "delete from test_summary where testid='$testid'");
			//mysqli_query($GLOBALS["___mysqli_ston"], "insert into test_summary values('$testid','0','$summ')");
			mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `test_summary`(`testid`, `paramid`, `summary`) VALUES ('$testid','0','$summ')");
		}
	}
	
?>

