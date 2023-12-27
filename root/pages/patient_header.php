<?php
if($dt_tm)
{
	$reg_date=$dt_tm["date"];
	$prefix_det=mysqli_fetch_array(mysqli_query($link, " SELECT `prefix` FROM `patient_type_master` WHERE `p_type_id`='$dt_tm[type]' "));
}
if($reg)
{
	$reg_date=$reg["date"];
	$prefix_det=mysqli_fetch_array(mysqli_query($link, " SELECT `prefix` FROM `patient_type_master` WHERE `p_type_id`='$reg[type]' "));
}
if($pat_reg)
{
	$reg_date=$pat_reg["date"];
	$prefix_det=mysqli_fetch_array(mysqli_query($link, " SELECT `prefix` FROM `patient_type_master` WHERE `p_type_id`='$pat_reg[type]' "));
}


//$add_info=mysqli_fetch_array(mysqli_query($link," SELECT * FROM `patient_info_rel` WHERE `patient_id`='$uhid' "));
$add_info=$pat_info;

$st_info=mysqli_fetch_array(mysqli_query($link," SELECT `name` FROM `state` WHERE `state_id`='$add_info[state]' "));

$dist_info=mysqli_fetch_array(mysqli_query($link," SELECT `name` FROM `district` WHERE `district_id`='$add_info[district]' "));

$address="";
//~ if($pat_info["gd_name"])
//~ {
	//~ $address="C/O: ".$pat_info["gd_name"].", ";
//~ }
if($add_info["city"])
{
	$address.="Town/Vill- ".$add_info["city"].", ";
}
if($pat_info["address"])
{
	$address.=$pat_info["address"].", ";
}
if($add_info["police"])
{
	$address.="P.S- ".$add_info["police"].", ";
}
if($dist_info["name"])
{
	//$address.=" &nbsp; District- ".$dist_info["name"].", ";
}
if($st_info["name"])
{
	//$address.=" &nbsp; State- ".$st_info["name"].", ";
}
//~ if($add_info["pin"])
//~ {
	//~ $address.="PIN-".$add_info["pin"];
//~ }

//if($pat_info["dob"]!=""){ $age=age_calculator($pat_info["dob"]); }else{ $age=$pat_info["age"]." ".$pat_info["age_type"]; }

if($pat_info["dob"]!=""){ $age=age_calculator_date($pat_info["dob"],$reg_date); }else{ $age=$pat_info["age"]." ".$pat_info["age_type"]; }

?>
<!--<br/>-->
<table class="table table-no-top-border">
	<tr>
		<th>UHID</th>
		<!--<td>: <?php echo $pat_info["uhid"]; ?></td>-->
		<th style="font-size: 15px;">: <?php echo $uhid; ?></th>
		<th><?php echo $prefix_det["prefix"]; ?></th>
		<th style="font-size: 15px;">: <?php echo $opd_id; ?></th>
		<!--<th>Bill No</th>
		<td>: <?php echo $adv_paid["bill_no"]; ?></td>-->
		<th>Reg Date</th>
		<td>: <?php echo convert_date($dt_tm["date"]); ?> <?php echo convert_time($dt_tm["time"]); ?></td>
	</tr>
	<tr>
		<th>Name</th>
		<td>: <?php echo $pat_info["name"]; ?></td>
		<th>Age / Sex</th>
		<td>: <?php echo $age; ?> / <?php echo $pat_info["sex"]; ?></td>
		<th>Phone</th>
		<td>: <?php echo $pat_info["phone"]; ?></td>
	</tr>
	<tr>
		<th>Ref By</th>
		<td>: <?php echo $ref_doc["ref_name"]; ?></td>
		<th>Address</th>
		<td colspan="3">: <?php echo $address; ?></td>
	</tr>
<?php
	if($appointment_info)
	{
		if($page_head_name!="Bill")
		{
			$appointment_no = str_pad($appointment_info["appointment_no"],2,"0",STR_PAD_LEFT);
			echo "<tr><th>Appointment No</th><td>: $appointment_no</td></tr>";
		}
	}
?>
<?php
	if($bill_no)
	{
		$payment_time=convert_date($bill_det["date"])." ".convert_time($bill_det["time"]);
		echo "<tr><th>Payment Time</th><td>: $payment_time</td></tr>";
	}
	else if($pay_id)
	{
		$payment_time=convert_date($pay_det["date"])." ".convert_time($pay_det["time"]);
		echo "<tr><th>Payment Time</th><td>: $payment_time</td></tr>";
	}
?>
</table>
