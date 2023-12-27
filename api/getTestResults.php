<?php
include'../includes/connection.php';

$pid=$_GET['pid'];
$opd=$_GET['opd'];
$tst=$_GET['tst'];

$val=array();

$arr=array();

if($tst=="1227")
{
	$tname			=mysqli_fetch_assoc(mysqli_query($link,"SELECT `testname` FROM `testmaster` WHERE `testid`='$tst'"));
	$temp			=array();
	//$temp['id']		=$tst;
	//$temp['name']	=$tname['testname'];
	
	$r1				=mysqli_fetch_assoc(mysqli_query($link,"SELECT * FROM `widalresult` WHERE `patient_id`='$pid' AND `opd_id`='$opd' AND `slno`='1' "));
	$res1			=array();
	$res1['F1']		=$r1['F1'];
	$res1['F2']		=$r1['F2'];
	$res1['F3']		=$r1['F3'];
	$res1['F4']		=$r1['F4'];
	$res1['F5']		=$r1['F5'];
	$res1['F6']		=$r1['F6'];
	//$temp['row1']	=$res1;
	
	$r2				=mysqli_fetch_assoc(mysqli_query($link,"SELECT * FROM `widalresult` WHERE `patient_id`='$pid' AND `opd_id`='$opd' AND `slno`='2' "));
	$res2			=array();
	$res2['F1']		=$r2['F1'];
	$res2['F2']		=$r2['F2'];
	$res2['F3']		=$r2['F3'];
	$res2['F4']		=$r2['F4'];
	$res2['F5']		=$r2['F5'];
	$res2['F6']		=$r2['F6'];
	//$temp['row2']	=$res2;
	
	$r3				=mysqli_fetch_assoc(mysqli_query($link,"SELECT * FROM `widalresult` WHERE `patient_id`='$pid' AND `opd_id`='$opd' AND `slno`='3' "));
	$res3			=array();
	$res3['F1']		=$r3['F1'];
	$res3['F2']		=$r3['F2'];
	$res3['F3']		=$r3['F3'];
	$res3['F4']		=$r3['F4'];
	$res3['F5']		=$r3['F5'];
	$res3['F6']		=$r3['F6'];
	//$temp['row3']	=$res3;
	
	$r4				=mysqli_fetch_assoc(mysqli_query($link,"SELECT * FROM `widalresult` WHERE `patient_id`='$pid' AND `opd_id`='$opd' AND `slno`='4' "));
	$res4			=array();
	$res4['F1']		=$r4['F1'];
	$res4['F2']		=$r4['F2'];
	$res4['F3']		=$r4['F3'];
	$res4['F4']		=$r4['F4'];
	$res4['F5']		=$r4['F5'];
	$res4['F6']		=$r4['F6'];
	//$temp['row4']	=$res4;
	
	//array_push($arr, $temp);
	array_push($temp, $res1);
	array_push($temp, $res2);
	array_push($temp, $res3);
	array_push($temp, $res4);
	$val['result']	=$temp;
	$val['doctor']	=$r4['doc'];
}
else
{
$qry="SELECT DISTINCT b.`testid`,b.`testname` FROM `patient_test_details` a, `testmaster` b, `test_department` c WHERE a.`testid`=b.`testid` AND b.`category_id`='1' AND b.`category_id`=c.`category_id` AND b.`type_id`=c.`id` AND a.`patient_id`='$pid' AND a.`opd_id`='$opd' AND a.`testid`='$tst'";
$doc="0";
$q=mysqli_query($link,$qry);
while($r=mysqli_fetch_assoc($q))
{
	$temp			=array();
	//$temp['id']		=$r['testid'];
	//$temp['name']	=$r['testname'];
	
	//$testresult		=array();
	//$results		=array();
	$qq=mysqli_query($link,"SELECT `paramid`,`result`,`range_status`,`range_id`,`tech`,`main_tech`,`doc`,`for_doc` FROM `testresults` WHERE `patient_id`='$pid' AND `opd_id`='$opd' AND `testid`='$r[testid]' ORDER BY `sequence`");
	while($rr=mysqli_fetch_assoc($qq))
	{
		$par				=mysqli_fetch_assoc(mysqli_query($link,"SELECT `Name`,`UnitsID`,`method` FROM `Parameter_old` WHERE `ID`='$rr[paramid]'"));
		$results			=array();
		$results['parId']	=$rr['paramid'];
		$results['parName']	=$par['Name'];
		$doc				=$rr['doc'];
		
		$unit_info			=mysqli_fetch_array(mysqli_query($link,"SELECT `unit_name` FROM `Units` WHERE `ID`='$par[UnitsID]'"));
		if($unit_info)
		{
			$results['unit']=$unit_info['unit_name'];
		}
		else
		{
			$results['unit']="";
		}
		
		$method				=mysqli_fetch_array(mysqli_query($link,"SELECT `name` FROM `test_methods` WHERE `id`='$par[method]'"));
		if($method)
		{
			$results['method']=$method['name'];
		}
		else
		{
			$results['method']="";
		}
		
		$results['result']	=stripcslashes($rr['result']);
		
		$normal_range		=mysqli_fetch_array(mysqli_query($link,"SELECT `normal_range` FROM `parameter_normal_check` WHERE `slno`='$rr[range_id]'"));
		if($normal_range)
		{
			$results['normal']=str_replace("<br />","",nl2br($normal_range["normal_range"]));
		}
		else
		{
			$results['normal']="";
		}
		
		array_push($temp, $results);
	}
	//$temp['testresult']	=$temp;
	
	//array_push($arr, $temp);
}
$val['doctor']	=$doc;
}

$summry			=mysqli_fetch_assoc(mysqli_query($link,"SELECT `summary` FROM `patient_test_summary` WHERE `patient_id`='$pid' AND `opd_id`='$opd' AND `batch_no`='1' AND `testid`='$tst'"));
if($summry)
{
$val['testSummary']	=$summry['summary'];
}
else
{
$allSumm	=mysqli_fetch_assoc(mysqli_query($link,"SELECT `summary` FROM `test_summary` WHERE `testid`='$tst'"));
if($allSumm)
{
$val['testSummary']	=$allSumm['summary'];
}
else
{
$val['testSummary']	="";
}
}

$val['result']	=$temp;

echo json_encode($val);
?>