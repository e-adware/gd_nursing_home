<?php
include("../../includes/connection.php");
$id=$_POST[id];
$pids=$_POST[pids];
$sq=$_POST[sq];

mysqli_query($GLOBALS["___mysqli_ston"], "delete from  Testparameter where TestId='$id'");

$p=explode("#",$pids);
$s=explode("#",$sq);

$tot=sizeof($p);

for($i=0;$i<$tot;$i++)
{
	if($p[$i])
	{
		$det=explode("%",$p[$i]);
		$par=$det[0];
		$samp=$det[1];
		$vaccu=$det[2];
		
		mysqli_query($GLOBALS["___mysqli_ston"], "insert into Testparameter(TestId,ParamaterId,sequence,sample,vaccu) values('$id','$par','$s[$i]','$samp','$vaccu')");
	}
}

//----------Mand------------//
mysqli_query($link,"delete from test_param_mandatory where testid='$id' and paramid not in(select ParamaterId from Testparameter where TestId='$id')");
//--------------------------//

?>
