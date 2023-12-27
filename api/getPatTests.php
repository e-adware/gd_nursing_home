<?php
include'../includes/connection.php';
include("../includes/global.function.php");

$pid=$_GET['pid'];
$opd=$_GET['opd'];
$did=$_GET['did'];

$val=array();

$arr=array();

if($did)
{
	$q=mysqli_query($link,"SELECT DISTINCT b.`testid`,b.`testname` FROM `patient_test_details` a, `testmaster` b, `test_department` c WHERE a.`testid`=b.`testid` AND b.`category_id`='1' AND b.`category_id`=c.`category_id` AND b.`type_id`=c.`id` AND c.`id`='$did' AND a.`patient_id`='$pid' AND a.`opd_id`='$opd'");
}
else
{
	$q=mysqli_query($link,"SELECT DISTINCT b.`testid`,b.`testname` FROM `patient_test_details` a, `testmaster` b, `test_department` c WHERE a.`testid`=b.`testid` AND b.`category_id`='1' AND b.`category_id`=c.`category_id` AND b.`type_id`=c.`id` AND a.`patient_id`='$pid' AND a.`opd_id`='$opd'");
}
while($r=mysqli_fetch_assoc($q))
{
	$temp			=array();
	$temp['id']		=$r['testid'];
	$temp['name']	=$r['testname'];
	
	$testresult		=array();
	$doc			="0";
	$qq=mysqli_query($link,"SELECT `paramid`,`result`,`range_status`,`range_id`,`tech`,`main_tech`,`doc`,`for_doc` FROM `testresults` WHERE `patient_id`='$pid' AND `opd_id`='$opd' AND `testid`='$r[testid]' ORDER BY `sequence`");
	while($rr=mysqli_fetch_assoc($qq))
	{
		$par				=mysqli_fetch_assoc(mysqli_query($link,"SELECT `Name`,`UnitsID`,`method` FROM `Parameter_old` WHERE `ID`='$rr[paramid]'"));
		$results			=array();
		$results['parId']	=$rr['paramid'];
		$results['parName']	=$par['Name'];
		
		$unit_info			=mysqli_fetch_array(mysqli_query($link,"SELECT `unit_name` FROM `Units` WHERE `ID`='$par[UnitsID]'"));
		$results['unit']	=$unit_info['unit_name'];
		
		$method				=mysqli_fetch_array(mysqli_query($link,"SELECT `name` FROM `test_methods` WHERE `id`='$par[method]'"));
		$results['method']	=$method['name'];
		
		$results['result']	=$rr['result'];
		
		$normal_range		=mysqli_fetch_array(mysqli_query($link,"SELECT `normal_range` FROM `parameter_normal_check` WHERE `slno`='$rr[range_id]'"));
		$results['normal']	=str_replace("<br />","",nl2br($normal_range["normal_range"]));
		
		array_push($testresult, $results);
		if($rr['doc']>0)
		{
			$doc=$rr['doc'];
		}
	}
	$temp['doctor']		=$doc;
	$temp['testresult']	=$testresult;
	
	array_push($arr, $temp);
}

$val['result']	=$arr;

echo json_encode($val);
?>