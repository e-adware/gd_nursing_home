<?php
include("../../includes/connection.php");

if($_POST["typ"]=="save")
{
	$testid=mysqli_real_escape_string($link, $_POST["testid"]);
	$testname=mysqli_real_escape_string($link, $_POST["testname"]);
	$category_id=mysqli_real_escape_string($link, $_POST["category_id"]);
	$type_id=mysqli_real_escape_string($link, $_POST["type_id"]);
	$instruction=mysqli_real_escape_string($link, $_POST["instruction"]);
	$rd_day=mysqli_real_escape_string($link, $_POST["rd_day"]);
	$rd_hour=mysqli_real_escape_string($link, $_POST["rd_hour"]);
	$rd_minute=mysqli_real_escape_string($link, $_POST["rd_minute"]);
	$report_delivery_2=mysqli_real_escape_string($link, $_POST["report_delivery_2"]);
	$samp=mysqli_real_escape_string($link, $_POST["sample_details"]);
	$out_sample=mysqli_real_escape_string($link, $_POST["out_sample"]);
	$vacc=mysqli_real_escape_string($link, $_POST["vacc"]);
	$rate=mysqli_real_escape_string($link, $_POST["rate"]);
	$sex=mysqli_real_escape_string($link, $_POST["sex"]);
	$equipment=mysqli_real_escape_string($link, $_POST["equipment"]);
	
	$turn_around_time=$rd_day."@".$rd_hour."#".$rd_minute;
	
	$notes="";
	$lineno=0;
	$vac_charge=0;
	$sequence=0;
	
	if(!$rate){ $rate=0; }
	
	$dept_info=mysqli_fetch_array(mysqli_query($link, "SELECT `name` FROM `test_department` WHERE `id`='$type_id'"));
	
	$type_name=$dept_info["name"];
	
	if($testid==0)
	{
		if(mysqli_query($link, "INSERT INTO `testmaster`(`testname`, `rate`, `instruction`, `notes`, `report_delivery`, `report_delivery_2`, `sample_details`, `category_id`, `type_id`, `type_name`, `equipment`, `sex`, `lineno`, `vac_charge`, `out_sample`) VALUES ('$testname','$rate','$instruction','$notes','$turn_around_time','$report_delivery_2','$sample_details','$category_id','$type_id','$type_name','$equipment','$sex','$lineno','$vac_charge','$out_sample')"))
		{
			mysqli_query($link, "DELETE FROM `TestSample` WHERE `TestId`='$testid'");
			if($samp!=1)
			{
				mysqli_query($link, "insert into TestSample values('$testid','$samp')");
			}
			
			$vacc=explode("@",$vacc);
			foreach($vacc as $v)
			{
				if($v)
				{
					mysqli_query($link, "insert into test_vaccu values('$testid','$v')");
				}
			}
			
			echo "Saved";
		}
		else
		{
			echo "Faild, try again later";
		}
	}
	else
	{
		if(mysqli_query($link, "UPDATE `testmaster` SET `testname`='$testname',`rate`='$rate',`instruction`='$instruction',`notes`='$notes',`report_delivery`='$turn_around_time',`report_delivery_2`='$report_delivery_2',`sample_details`='$sample_details',`category_id`='$category_id',`type_id`='$type_id',`type_name`='$type_name',`equipment`='$equipment',`sex`='$sex',`lineno`='$lineno',`vac_charge`='$vac_charge',`out_sample`='$out_sample' WHERE `testid`='$testid'"))
		{
			mysqli_query($link, "delete from TestSample where TestId='$testid'");
			
			if($samp!=1)
			{
				mysqli_query($link, "insert into TestSample values('$testid','$samp')");
				
				mysqli_query($link, "update patient_test_details set sample_id='$samp' where testid='$testid'");
				
			}else
			{
				mysqli_query($link, "update patient_test_details set sample_id='0' where testid='$testid'");
			}
			
			mysqli_query($link, "delete from test_vaccu where testid='$testid'");
			
			$vacc=explode("@",$vacc);
			foreach($vacc as $v)
			{
				if($v)
				{
					mysqli_query($link, "insert into test_vaccu values('$testid','$v')");
				}
			}
			
			echo "Updated";
		}
		else
		{
			echo "Faild, try again later";
		}
	}
}
else if($_POST[typ]=="del")
{
	$id=$_POST[tid];
	
	$check_entry=mysqli_fetch_array(mysqli_query($link, "SELECT * FROM `patient_test_details` WHERE `testid`='$id'"));
	if(!$check_entry)
	{
		mysqli_query($link, "delete from testmaster where testid='$id'");
		mysqli_query($link, "delete from Testparameter where TestId='$id'");
		mysqli_query($link, "delete from TestSample where TestId='$id'");
		mysqli_query($link, "delete from CategoryTest where id='$id'");
		
		echo "1";
	}else
	{
		echo "404";
	}
}
else if($_POST[typ]=="load_dept")
{
	$category_id=$_POST['category_id'];
	$val="";
	$q=mysqli_query($link,"SELECT * FROM `test_department` WHERE `category_id`='$category_id'");
	while($r=mysqli_fetch_assoc($q))
	{
		if($val)
		{
			$val.="#%#".$r['id']."@@".$r['name'];
		}
		else
		{
			$val=$r['id']."@@".$r['name'];
		}
	}
	echo $val;
}
?>
