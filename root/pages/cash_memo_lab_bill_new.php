<?php
include("../../includes/connection.php");
require("../../includes/global.function.php");

$uhid=mysqli_real_escape_string($link, base64_decode($_GET["uhid"]));
$opd_id=mysqli_real_escape_string($link, base64_decode($_GET["opdid"]));
$user=mysqli_real_escape_string($link, base64_decode($_GET["user"]));
$v=mysqli_real_escape_string($link, $_GET["v"]);

if($val==0)
{
	$page_head_name="Money Receipt";
}
if($val==1)
{
	$page_head_name="Bill";
}

$page_head_name="Bill";

$emp=mysqli_fetch_array(mysqli_query($link, " SELECT `name` FROM `employee` WHERE `emp_id`='$user' "));

$company_info=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `company_master` limit 0,1 "));

$pat_reg=$dt_tm=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `uhid_and_opdid` WHERE `patient_id`='$uhid' and `opd_id`='$opd_id' "));

$pat_info=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `patient_info` WHERE `patient_id`='$uhid' "));

$ref_doc=mysqli_fetch_array(mysqli_query($link, " SELECT `ref_name` FROM `refbydoctor_master` WHERE `refbydoctorid`='$dt_tm[refbydoctorid]' "));
if(!$ref_doc)
{
	$ref_doc=mysqli_fetch_array(mysqli_query($link, " SELECT `ref_name` FROM `refbydoctor_master` WHERE `refbydoctorid`='$pat_info[refbydoctorid]' "));
}

$pat_pay_detail=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `invest_patient_payment_details` WHERE `patient_id`='$uhid' and `opd_id`='$opd_id' "));

$inv_pat_test_detail_qry=mysqli_query($link, " SELECT a.`test_rate`,b.`testname` FROM `patient_test_details` a,`testmaster` b  WHERE a.`testid`=b.`testid` and a.`patient_id`='$uhid' and a.`opd_id`='$opd_id' ");

$inv_pat_vaccu_qry=mysqli_query($link, " SELECT * FROM `vaccu_master` WHERE `id` in ( SELECT `vaccu_id` FROM `patient_vaccu_details` WHERE `patient_id`='$uhid' and `opd_id`='$opd_id' ) ");

// USG Serial
$w=1;
$usg_num=0;
$usg_test_detail_qry=mysqli_query($link, " SELECT a.* FROM `patient_test_details` a,`testmaster` b  WHERE a.`testid`=b.`testid` and b.`type_id`='128' and a.`date`='$dt_tm[date]' ");
while($usg_test_detail=mysqli_fetch_array($usg_test_detail_qry))
{
	if($usg_test_detail["opd_id"]==$opd_id)
	{
		$usg_num=$w;
		break;
	}else
	{
		$w++;
	}
}

$tot="";
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
		<hr>
		<center><b style="font-size: 18px;"><?php echo $page_head_name; ?></b></center>
		<hr>
		<?php include('patient_header.php'); ?>
		<hr>
		
		<table class="table table-condensed">
			<tr>
				<th>Sl No</th>
				<th>Test Name</th>
				<th><span class="text-right">Amount</span></th>
			</tr>
		<?php
			
			echo "<tr><td colspan='3'><hr></td></tr>";
			
			$j=1;
			while($inv_pat_test_detail=mysqli_fetch_array($inv_pat_test_detail_qry))
			{
				$tot=$tot+$inv_pat_test_detail["test_rate"];
				
				$j = str_pad($j,2,"0",STR_PAD_LEFT);
		?>
			<tr>
				<td style="width:5%;"><?php echo $j; ?></td>
				<td><?php echo $inv_pat_test_detail["testname"]; ?></td>
				<td><span class="text-right"><?php echo $inv_pat_test_detail["test_rate"]; ?></span></td>
			</tr>
		<?php 
				$j++;
			}
		?>
		<?php
			while($inv_pat_vaccu=mysqli_fetch_array($inv_pat_vaccu_qry))
			{
				$tot=$tot+$inv_pat_vaccu["rate"];
		?>
			<tr>
				<td style="width:2%;"><?php echo $j; ?></td>
				<td><?php echo $inv_pat_vaccu["type"]; ?></td>
				<td><span class="text-right"><?php echo $inv_pat_vaccu["rate"]; ?></span></td>
			</tr>
		<?php
				$j++;
			}
			
			while($j<32)
			{
				echo "<tr><td colspan='3'> &nbsp;</td></tr>";
				$j++;
			}
			echo "<tr><td colspan='3'><hr></td></tr>";
		?>
			<tr>
				<td></td>
				<td></td>
				<th>Total Amount <span class="text-right"><?php echo number_format($pat_pay_detail["tot_amount"],2); ?></span></th>
			</tr>
		<?php
			if($pat_pay_detail["dis_amt"]>0)
			{
				$dis_per=round(($pat_pay_detail["dis_amt"]/$pat_pay_detail["tot_amount"])*100,2);
		?>
			<tr>
				<td></td>
				<td></td>
				<th>Discount Amount  (<?php echo $dis_per."%"; ?>): <span class="text-right"><?php echo number_format($pat_pay_detail["dis_amt"],2); ?></span></th>
			</tr>
		<?php } ?>
			<tr>
				<td></td>
				<td></td>
				<th>Grand Total <span class="text-right"><?php echo number_format(($tot-$pat_pay_detail["dis_amt"]),2); ?></span></th>
			</tr>
		</table>
		<hr>
		<p>Indian Rupees <?php echo convert_number($pat_pay_detail["advance"]); ?> Only</p>
		<br>
		<br>
		<div class="span8"></div>
		<div class="span4 text-right" style="margin-top: 1%;line-height: 12px;">
			<b>FOR <?php echo $company_info["name"]; ?></b>
			<br>
			(<?php echo $emp["name"]; ?>)
		</div>
	</div>
	<!--<br>
	<center>
		<p>------------------------------------------------------------ This is a computer generated receipt --------------------------------------------------------</p>
	</center>-->
	
	<!--<div style="position:absolute;top:1%;right:4%;">
		<svg id="barcode3"></svg>
		<script>
			var val="<?php echo $pat_info["patient_id"]; ?>";
			JsBarcode("#barcode3", val, {
				format:"CODE128",
				displayValue:false,
				fontSize:10,
				width:1,
				height:50,
			});
		</script>
	</div>-->
	
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
	function refreshParent()
	{
		var uhid=$("#uhid").text().trim();
		var opd_id=$("#opd_id").text().trim();
		var url_redirect=$("#url_redirect").text().trim();
		if(url_redirect==1)
		{
			//window.opener.location.reload(true);
			window.opener.location.href="../processing.php?param=3&uhid="+uhid+"&lab=1&opd="+opd_id;
		}
	}
</script>
<style>
.table > thead > tr > th, .table > tbody > tr > th, .table > tfoot > tr > th, .table > thead > tr > td, .table > tbody > tr > td, .table > tfoot > tr > td
{
	padding: 0 0 0 0;
	border-top: 1px solid #fff;
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

.page_break {page-break-before: always; padding-top: 5px;}
.req_slip{ min-height:520px;}
.f_req_slip{ min-height:670px;}
.rad_req_slip{ min-height:300px;}

*
{
	font-size:11px;
}
@page
{
	margin:0.2cm;
}
</style>
