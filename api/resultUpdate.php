<?php
include'../includes/connection.php';

$date			=date("Y-m-d");
$time			=date("H:i:s");

$pid			=$_POST['pid'];
$opd			=$_POST['opd'];
$tst			=$_POST['tst'];
$parVal			=$_POST['parVal'];
$user			=$_POST['user'];

$res=array();

$arr=array();

$temp			=array();
$temp['pid']	=$pid;
$temp['opd']	=$opd;
$temp['tst']	=$tst;
//$temp['par']	=$parVal;


$parVal			=json_decode($parVal, true);

$vv="";
foreach($parVal as $key => $value)
{
	$result		=mysqli_fetch_assoc(mysqli_query($link,"SELECT * FROM `testresults` WHERE `patient_id`='$pid' AND `opd_id`='$opd' AND `testid`='$tst' AND `paramid`='$key'"));
	if($result['result'] && $result['result']!=$value)
	{
		//$vv.=$key.": ".$value.", ";
		mysqli_query($link,"UPDATE `testresults` SET `result`='$value' WHERE `patient_id`='$pid' AND `opd_id`='$opd' AND `testid`='$tst' AND `paramid`='$key'");
		
		mysqli_query($link,"INSERT INTO `testresults_update`(`patient_id`, `opd_id`, `ipd_id`, `batch_no`, `testid`, `paramid`, `sequence`, `result`, `time`, `date`, `doc`, `tech`, `main_tech`, `for_doc`, `update_by`, `update_user_type`) VALUES ('$pid','$opd','','$result[batch_no]','$tst','$key','$result[sequence]','$result[result]','$time','$date','$result[doc]','$result[tech]','$result[main_tech]','$result[for_doc]','$user','2')");
		$vv.=" INSERT INTO `testresults_update`(`patient_id`, `opd_id`, `ipd_id`, `batch_no`, `testid`, `paramid`, `sequence`, `result`, `time`, `date`, `doc`, `tech`, `main_tech`, `for_doc`, `update_by`, `update_user_type`) VALUES ('$pid','$opd','','$result[batch_no]','$tst','$key','$result[sequence]','$result[result]','$time','$date','$result[doc]','$result[tech]','$result[main_tech]','$result[for_doc]','$user','2') ";
	}
	mysqli_query($link,"UPDATE `testresults` SET `doc`='$user' WHERE `patient_id`='$pid' AND `opd_id`='$opd' AND `testid`='$tst' AND `paramid`='$key'");
}
$temp['allVals']	=$vv;

array_push($arr, $temp);



$res['result']	=$arr;

echo json_encode($res);
?>