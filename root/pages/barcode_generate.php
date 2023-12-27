<html>
<head>

</head>
<body>

<h2>Generating Barcode </h2>

<?php
include("../../includes/connection.php");
include("../../includes/global.function.php");

$pid=$_GET['pid'];
$opd=$_GET['opd_id'];
$ipd=$_GET['ipd_id'];
$vac=$_GET['vac'];
$tst_vac=$_GET['tst_vac'];
$batch_no=$_GET['batch_no'];
$user=$_GET['user'];

$date=date('Y-m-d');
$time=date("H:i:s");

if($opd)
{
	$pin=$opd;
}
if($ipd)
{
	$pin=$ipd;
}

//------------Removing Old Data-------------//
$ndate=date('Y-m-d', strtotime('-1 months'));

mysqli_query($link,"delete from test_sample_result where date<'$ndate'");
//-----------------------------------------//

$sample="";
$pat_info=mysqli_fetch_array(mysqli_query($link," SELECT * FROM `patient_info` WHERE `patient_id`='$pid' "));

$pat_test=mysqli_fetch_array(mysqli_query($link," SELECT * FROM `patient_test_details` WHERE `patient_id`='$pid' AND `opd_id`='$opd' AND `ipd_id`='$ipd' AND `batch_no`='$batch_no' AND `testid` IN ($tst_vac)"));
$test_date=$pat_test["date"];

if($pat_info["dob"]!=""){ $age=age_calculator_date($pat_info["dob"],$test_date); }else{ $age=$pat_info["age"]." ".$pat_info["age_type"]; }

$age=substr($age,0,8);
//echo $age;

$ages=explode(" ",$age);

$pat_info['age']=$ages[0];
$pat_info['age_type']=$ages[1];

$vac=explode("@@",$vac);
foreach($vac as $vc)
{
	if($vc)
	{
		$nreg=str_replace("/","",$pin);
		$nreg=$nreg.$batch_no;
		$barcode_id=$nreg;
		$vac_det=mysqli_fetch_array(mysqli_query($link,"select * from vaccu_master where id='$vc'"));
		$barcode_id=$barcode_id.$vac_det[barcode_suffix];
		
		if(!$_GET['sing'])
		{
			//----Check Barcode---//
			$chk_bar=mysqli_fetch_array(mysqli_query($link,"select count(*) as tot from test_sample_result where patient_id='$pid' and opd_id='$opd' and ipd_id='$ipd' and batch_no='$batch_no' and barcode_id='$barcode_id'"));
			if($chk_bar[tot]==0)
			{
				$vname=mysqli_fetch_array(mysqli_query($link,"select * from vaccu_master where id='$vc'"));
			
				$sample.="/".$barcode_id."==".$vname[type];	
			}
			
			//--------------------//
			
			$test=mysqli_query($link,"select b.* from patient_test_details a,Testparameter b where a.testid=b.TestId and b.vaccu='$vc' and a.patient_id='$pid' and a.opd_id='$opd' and a.ipd_id='$ipd' and a.batch_no='$batch_no'");	
			while($tst=mysqli_fetch_array($test))
			{
				$chk=mysqli_fetch_array(mysqli_query($link,"select count(*) as tot from test_sample_result where patient_id='$pid' and opd_id='$opd' and ipd_id='$ipd' and batch_no='$batch_no' and testid='$tst[TestId]' and paramid='$tst[ParamaterId]'"));
				
				if($chk["tot"]==0)
				{
					mysqli_query($link,"insert into test_sample_result( `patient_id`, `opd_id`,`ipd_id`, `batch_no`, `barcode_id`, `vaccus`, `sample_id`, `equip_id`, `testid`, `paramid`, `iso_no`, `result`, `time`, `date`, `user`) VALUES ('$pid','$opd','$ipd','$batch_no','$barcode_id','$vc','$tst[sample]','0','$tst[TestId]','$tst[ParamaterId]','0','','$time','$date','$user')");
				}
				else
				{
					$chk_v=mysqli_fetch_array(mysqli_query($link,"select * from test_sample_result where patient_id='$pid' and opd_id='$opd' and ipd_id='$ipd' and batch_no='$batch_no' and testid='$tst[TestId]' and paramid='$tst[ParamaterId]' and vaccus!='$vc'"));
					
					if($chk_v["testid"] && $chk_v["result"]=='')
					{
						mysqli_query($link,"update test_sample_result set barcode_id='$barcode_id',vaccus='$vc' where patient_id='$pid' and opd_id='$opd' and ipd_id='$ipd' and batch_no='$batch_no' and testid='$tst[TestId]' and paramid='$tst[ParamaterId]'");
					}
				}
				
				//---Remove Para which is not in TestPara---//
			
				$chk_para=mysqli_query($link,"select * from test_sample_result where patient_id='$pid' and opd_id='$opd' and ipd_id='$ipd' and batch_no='$batch_no' and testid='$tst[TestId]'");
				while($cp=mysqli_fetch_array($chk_para))
				{
					if($cp[result]=='')
					{
						$chk_tp=mysqli_fetch_array(mysqli_query($link,"select count(*) as tot from Testparameter where TestId='$tst[TestId]' and ParamaterId='$cp[paramid]'"));
						
						if($chk_tp[tot]==0)
						{
							//mysqli_query($link,"delete from test_sample_result where patient_id='$pid' and opd_id='$opd' and ipd_id='$ipd' and batch_no='$batch_no' and testid='$tst[TestId]'");
						}
					}
				}
				//------------------------------------------//
				
			}
		}
		else
		{
			$vname=mysqli_fetch_array(mysqli_query($link,"select * from vaccu_master where id='$vc'"));
			
			$sample.="/".$barcode_id."==".$vname[type];
		}
	}
}

if($sample!='')
{
	//$IP = $_SERVER['REMOTE_ADDR'];   
	//$computerName = gethostbyaddr($IP); 

	//$target_file="http://".$computerName."/barcodeprinter/barcode_generate.php?PoiU=".$sample."&name=".$pat_info['name']."&age=".$pat_info['age']."&age_type=".$pat_info['age_type']."&sex=".$pat_info['sex']."&pin=".$pin;
	
	$br=mysqli_fetch_array(mysqli_query($link,"select branch_id from uhid_and_opdid where opd_id='$pin'"));

    $target_file="../../js_print/index.php?PoiU=".$sample."&name=".$pat_info['name']."&age=".$pat_info['age']."&age_type=".$pat_info['age_type']."&sex=".$pat_info['sex']."&pin=".$pin."&test_time=".date("d-M",strtotime($pat_test["date"]))." ".date("h:i A",strtotime($pat_test["time"]));
	
?>
	<script>
		window.location="<?php echo $target_file;?>";
	</script>
<?php
	die();
}
else
{
	?> <script>window.close();</script> <?php
}

//~ if($sample!='')
//~ {
	//~ $IP = $_SERVER['REMOTE_ADDR'];   
	//~ $computerName = gethostbyaddr($IP); 

	//~ $target_file="http://".$computerName."/barcodeprinter/barcode_generate.php?PoiU=".$sample."&name=".$pat_info['name']."&age=".$pat_info['age']."&age_type=".$pat_info['age_type']."&sex=".$pat_info['sex']."&pin=".$pin;

	//~ echo $target_file;

	//~ //header("Location: $target_file");
	//~ die();
//~ }
//~ else
//~ {
	//~ ?> <script>window.close();</script> <?php
//~ }



?>
</body>
</html>
