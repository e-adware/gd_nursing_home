<html>
<head>
<title></title>
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" href="../../css/bootstrap.min.css" />
	<script src="../../js/jquery.min.js"></script>
	<script src="../../js/bootstrap.min.js"></script>
<style>

.line td{border-top:1px dotted}
@media print{
 .noprint{
	 display:none;
 }
}
.bline td{border-bottom:1px solid;}
.line td{border-top:1px dotted;}
</style>

</head>
<body oncopy="return false;" oncut="return false;" onpaste="return false;" oncontextmenu="return false;">
<?php
include'../../includes/connection.php';


$fdate=base64_decode($_GET['fDt']);
$tdate=base64_decode($_GET['tDt']);
$sub_id=$ph=base64_decode($_GET['ph']);
if($ph==0)
{
	$sub_id=1;
}
//$date2 = $_GET['date2'];
function convert_date($date)
{
$timestamp = strtotime($date); 
$new_date = date('d-m-Y', $timestamp);
return $new_date;
}


function val_con($val)
{
	$nval=explode(".",$val);
	if(!$nval[1])
	{
		return $val.".00";	
	}
	else
	{
		if(!$nval[1][1])
		{
			return $val."0";	
		}
		else
		{
			return $val;	
		}
	}
}
$splr=mysqli_fetch_array(mysqli_query($link,"select substore_name from inv_sub_store  where substore_id='$ph'  "));
if($splr)
{
	$vsbstrnm=$splr['substore_name'];
}
else
{
	$vsbstrnm="All";
}

$compny=mysqli_fetch_array(mysqli_query($link,"select * from company_name  "));

?>

<div class="container-fluid">
	<input type="hidden" id="fdate" value="<?php echo $fdate;?>" />
	<input type="hidden" id="tdate" value="<?php echo $tdate;?>" />
	<input type="hidden" id="ph" value="<?php echo $ph;?>" />
	<div>
		<?php include('page_header_ph.php'); ?>
	</div>
	<center><h5><u>Store Transfer Report</u></h5></center>
<table>
 <!--<tr><td style="font-weight:bold;font-size:13px">From </td><td style="font-weight:bold;font-size:13px"> : <?php echo $fdate;?></td></tr>
 <tr><td style="font-weight:bold;font-size:13px">To  <td style="font-weight:bold;font-size:13px"> : <?php echo $tdate;?></td></tr>-->
 <!--<tr><td style="font-weight:bold;font-size:13px">Pharmacy </td><td style="font-weight:bold;font-size:13px"> : <?php echo $vsbstrnm;?></td></tr>-->
</table>

      <table width="100%">
      <tr>
		  <td>Date: <?php echo date("d-m-Y");?></td>
        <td colspan="6" style="text-align:right"><div class="noprint"><input type="button" name="button" class="btn btn-info" id="button" value="Print" onClick="window.print()" />&nbsp;<input type="button" name="button" class="btn btn-danger" id="button" value="Exit" onClick="window.close()" /></div></td>
      </tr>
      </table> 
      <div id="res"></div>
 </div>
 <script>
	$(document).ready(function()
	{
		load_data();
	})
	
	function load_data()
	{
		$.post("inv_stock_report_ajax.php",
		{
			fdate:$("#fdate").val(),
			tdate:$("#tdate").val(),
			type:3,
		},
		function(data,status)
		{
			$("#res").html(data);
			$(".btn_act").hide();
		})
	}
 </script>
 <style>
	.table
	{
		font-size:13px;
	}
	.table tr th, .table tr td
	{
		padding: 0px;
		padding-left: 3px;
		padding-right: 3px;
	}
 </style>
</body>
</html>

