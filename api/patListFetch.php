<?php
include'../includes/connection.php';
$date=date("Y-m-d");

$dt=$_GET['dt'];
if($dt=="")
{
	$dt=$date;
}
$val=array();

$arr=array();
$q=mysqli_query($link,"SELECT * FROM `uhid_and_opdid` WHERE `date`='$dt' AND `type`='2'");
while($r=mysqli_fetch_assoc($q))
{
	$temp			=array();
	$temp['uhid']	=$r['patient_id'];
	$temp['opd']	=$r['opd_id'];
	
	$pat=mysqli_fetch_assoc(mysqli_query($link,"SELECT `name`,`sex`,`age`,`age_type`,`phone` FROM `patient_info` WHERE `patient_id`='$r[patient_id]'"));
	$temp['name']	=$pat['name'];
	$temp['phone']	=$pat['phone'];
	$temp['sex']	=$pat['sex'];
	$temp['age']	=$pat['age']." ".$pat['age_type'];
	
	$ref=mysqli_fetch_assoc(mysqli_query($link,"SELECT `ref_name` FROM `refbydoctor_master` WHERE `refbydoctorid`='$r[refbydoctorid]'"));
	$temp['ref']	=$ref['ref_name'];
	$cen=mysqli_fetch_assoc(mysqli_query($link,"SELECT `centrename` FROM `centremaster` WHERE `centreno`='$r[center_no]'"));
	$temp['center']	=$cen['centrename'];
	
	$test_count		=mysqli_num_rows(mysqli_query($link,"SELECT a.`testid`, a.`testname` FROM `testmaster` a, `patient_test_details` b WHERE a.`testid`=b.`testid` AND a.`category_id`='1' AND b.`patient_id`='$r[patient_id]' AND b.`opd_id`='$r[opd_id]'"));
	
	$reports1		=mysqli_num_rows(mysqli_query($link,"SELECT DISTINCT `testid` FROM `testresults` WHERE `patient_id`='$r[patient_id]' AND `opd_id`='$r[opd_id]'"));
	$reports2		=mysqli_num_rows(mysqli_query($link,"SELECT DISTINCT `batch_no` FROM `widalresult` WHERE `patient_id`='$r[patient_id]' AND `opd_id`='$r[opd_id]'"));
	$results		=$reports1+$reports2;
	if($results>$test_count)
	{
		$results=$test_count;
	}
	$temp['report']	=$results."/".$test_count;
	$approve		=mysqli_num_rows(mysqli_query($link,"SELECT DISTINCT `testid` FROM `testresults` WHERE `patient_id`='$r[patient_id]' AND `opd_id`='$r[opd_id]' AND `doc`>'0'"));
	$temp['approve']=$approve;
	/*
	$tst=array();
	$qq=mysqli_query($link,"SELECT a.`testid`, a.`testname` FROM `testmaster` a, `patient_test_details` b WHERE a.`testid`=b.`testid` AND a.`category_id`='1' AND b.`patient_id`='$r[patient_id]' AND b.`opd_id`='$r[opd_id]'");
	while($rr=mysqli_fetch_assoc($qq))
	{
		$vl				=array();
		$vl['testid']	=$rr['testid'];
		$vl['testname']	=$rr['testname'];
		array_push($tst, $vl);
	}
	$temp['all_tests']	=$tst;
	//*/
	array_push($arr, $temp);
}
/*
if($v)
{
	$temp['response']=1;
	$temp['uId']=$v['emp_id'];
	$temp['uName']=$v['name'];
}
else
{
	$temp['uName']="Error";
}
//*/
$val['result']=$arr;

echo json_encode($val);
?>