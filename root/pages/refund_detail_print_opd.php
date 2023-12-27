<?php
include("../../includes/connection.php");
require('../../includes/global.function.php');

$uhid=base64_decode($_GET["uhid"]);
$opd_id=base64_decode($_GET["opd_id"]);
$user=base64_decode($_GET["EpMl"]);
$refund_request_id=base64_decode($_GET["rrid"]);

$emp=mysqli_fetch_array(mysqli_query($link, " SELECT `name` FROM `employee` WHERE `emp_id`='$user' "));

$company_info=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `company_master` limit 0,1 "));

$pat_reg=$dt_tm=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `uhid_and_opdid` WHERE `patient_id`='$uhid' and `opd_id`='$opd_id' "));

$pat_info=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `patient_info` WHERE `patient_id`='$uhid' "));

if($pat_info["dob"]!=""){ $age=age_calculator($pat_info["dob"]); }else{ $age=$pat_info["age"]." ".$pat_info["age_type"]; }


$add_info=mysqli_fetch_array(mysqli_query($link," SELECT * FROM `patient_info_rel` WHERE `patient_id`='$uhid' "));

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

$ref_doc=mysqli_fetch_array(mysqli_query($link, " SELECT `ref_name` FROM `refbydoctor_master` WHERE `refbydoctorid`='$dt_tm[refbydoctorid]' "));
if(!$ref_doc)
{
	$ref_doc=mysqli_fetch_array(mysqli_query($link, " SELECT `ref_name` FROM `refbydoctor_master` WHERE `refbydoctorid`='$pat_info[refbydoctorid]' "));
}

$refund_info=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `patient_refund` WHERE `refund_request_id`='$refund_request_id' AND `patient_id`='$uhid' AND `opd_id`='$opd_id' "));
if($refund_info)
{
	$refund_det_qry=mysqli_query($link, " SELECT * FROM `patient_refund_details` WHERE `refund_id`='$refund_info[refund_id]' ");
	$page_head_name="Refund Receipt";
}
else
{
	$refund_info=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `refund_request` WHERE `refund_request_id`='$refund_request_id' AND `patient_id`='$uhid' AND `opd_id`='$opd_id' "));

	if($refund_info)
	{
		$refund_det_qry=mysqli_query($link, " SELECT * FROM `refund_request_details` WHERE `refund_request_id`='$refund_request_id' ");
		
		$page_head_name="Refund Request Receipt";
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<title><?php echo $page_head_name; ?></title>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" href="../../css/bootstrap.min.css" />
	<link rel="stylesheet" href="../../css/custom.css" />
	<script src="../../js/jquery.min.js"></script>
	<script src="../../js/bootstrap.min.js"></script>
	
</head>

<body onafterprint="window.close();" onkeyup="close_window(event)" onunload="refreshParent()">
	<div class="container-fluid">
		<div class="">
			<?php include('page_header.php'); ?>
		</div>
		<center>
			<h4><?php echo $page_head_name; ?></h4>
			<div class="noprint ">
				<input type="button" class="btn btn-info" id="Name1" value="Print" onclick="javascript:window.print()">
				<input type="button" class="btn btn-danger" id="Name2" value="Exit" onclick="javascript:window.close()"></div>
		</center>
		<hr>
		<div class="">
			<?php include('patient_header.php'); ?>
		</div>
		<hr>
		<center><b><?php //echo $page_head_name; ?></b></center>
		<table class="table table-condensed">
		<?php
			$j=1;
			while($refund_det=mysqli_fetch_array($refund_det_qry))
			{
				$tot=$tot+$refund_det["refund_amount"];
				
				$j = str_pad($j,2,"0",STR_PAD_LEFT);
				
				if($refund_det["service_id"]==1)
				{
					$service_name="Registration fee";
				}
				if($refund_det["service_id"]==2)
				{
					$service_name="Consultation fee";
				}
		?>
			<tr>
				<td style="width:2%;"><?php echo $j; ?></td>
				<td><?php echo $service_name; ?></td>
				<td><span class="text-right"><?php echo $refund_det["refund_amount"]; ?></span></td>
			</tr>
		<?php 
				$j++;
			}
		?>
			<tr>
				<td></td>
				<td></td>
				<th>Total Amount: <span class="text-right"><?php echo number_format($tot,2); ?></span></th>
			</tr>
		</table>
		<hr>
		<p>Indian Rupees <?php echo convert_number($tot); ?> Only</p>
		<div class="span8"></div>
		<div class="span4 text-right" style="margin-top: 1%;line-height: 12px;">
			<b>FOR <?php echo $company_info["name"]; ?></b>
			<br>
			(<?php echo $emp["name"]; ?>)
		</div>
	</div>
	<span id="user" style="display:none;"><?php echo $user; ?></span>
	<span id="uhid" style="display:none;"><?php echo $uhid; ?></span>
	<span id="opd_id" style="display:none;"><?php echo $opd_id; ?></span>
	<span id="url_redirect" style="display:none;"><?php echo $url_redirect; ?></span>
</body>
</html>
<script>
	$(document).keydown(function (event) {
		if (event.keyCode == 123 && $("#user").text().trim()!='101' && $("#user").text().trim()!='102') { // Prevent F12
			return false;
		} else if (event.ctrlKey && event.shiftKey && event.keyCode == 73 && $("#user").text().trim()!='101' && $("#user").text().trim()!='102') { // Prevent Ctrl+Shift+I        
			return false;
		}
	});
	$(document).on("contextmenu",function(e){
		if($("#user").text().trim()!='101' && $("#user").text().trim()!='102')
		{
			e.preventDefault();
		}
	});
	$(document).ready(function(){
		if($("#user").text().trim()!='101' && $("#user").text().trim()!='102')
		{
			window.print();
		}
	});
	function close_window(e)
	{
		var unicode=e.keyCode? e.keyCode : e.charCode;

		if(unicode==27)
		{
			window.close();
		}
	}
</script>
<style>
.table > thead > tr > th, .table > tbody > tr > th, .table > tfoot > tr > th, .table > thead > tr > td, .table > tbody > tr > td, .table > tfoot > tr > td
{
	padding: 0 0 0 0;
}
.table
{
	margin-bottom:1px;
}
hr
{
	margin:0;
	border-bottom:0;
	border-top: 1px solid #000;
}

*
{
	font-size:11px;
}
@page
{
	margin:0.2cm;
}
@media print
{
	.noprint{
		display:none;
	 }
}
</style>
