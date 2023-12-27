<?php
$date=date("Y-m-d");
if(isset($_GET['param']))
{
	$para=base64_decode($_GET['param']);
	include("../inc/header.php");
	require('../includes/global.inc.php');
	require('../includes/global.function.php');
	$t_page=$page[$para];
	
	if(file_exists($t_page))
	{
		if($menu_access_info)
		{
			include $t_page;
			
		}
		else
		{
			include $t_page;
			//include "pages/error404.php";
			//echo "<script>window.location='index.php';</script>";
		}
	}
	else
	{
		echo "<center><img src='../emoji/404.jpeg'></center>";
	}
	
}else
{
	$para=0;
	include("../inc/header.php");
	
	$gret=mysqli_fetch_assoc(mysqli_query($link,"SELECT * FROM `greetings` WHERE `date`='$date'"));
    if($gret)
    {
		$userReview=mysqli_fetch_assoc(mysqli_query($link,"SELECT * FROM `greetingsReview` WHERE `msg_id`='$gret[msg_id]' AND `emp_id`='$_SESSION[emp_id]'"));
		if(!$userReview)
		{
			include("welcome.php");
		}
	}
	
	if($p_info["levelid"]==5)
	{
		require('../includes/global.inc.php');
		require('../includes/global.function.php');
		
		$t_page=$page[15];
		
		if(file_exists($t_page))
		{
			include $t_page;
		}
	}
	else
	{
?>

<!--header-->
<div id="content-header">
    <div class="header_div">
		<span class="header">Dashboard</span>
		<!--<button class="btn btn-default" onClick="show_tel_no(1)" style="margin-left: 84%;">Tel No</button><br>-->
	</div>
</div>
<!--End-header-->
<div class="container-fluid">
	<?php
	if($p_info['levelid']==1 || $p_info['levelid']==5)
	{
	?>
	<table class="table table-condensed table-bordered table-report">
		<tr>
			<th colspan="9" style="text-align:center;">Overall</th>
		</tr>
		<tr>
			<th>Bill Amount</th>
			<th>Discount</th>
			<th>Amount Received</th>
			<th>Balance Received</th>
			<th>Refund</th>
			<th>Net Amount</th>
			<th style="display:none;">Free</th>
			<th>Balance</th>
		</tr>
		<tr>
			<td id="overall_bill"></td>
			<td id="overall_disc"></td>
			<td id="overall_amt_rcv"></td>
			<td id="overall_bal_rcv"></td>
			<td id="overall_ref"></td>
			<td id="overall_net"></td>
			<td style="display:none;" id="overall_free"></td>
			<td id="overall_bal"></td>
		</tr>
	</table>
	<?php
	}
	$overall_bill=0;
	$overall_disc=0;
	$overall_amt_rcv=0;
	$overall_bal_rcv=0;
	$overall_ref=0;
	$overall_net=0;
	$overall_free=0;
	$overall_bal=0;
	?>
	<div class="parent">
	<?php
	//~ if($p_info['levelid']==1)
	//~ {
		$snp=mysqli_fetch_array(mysqli_query($link,"SELECT `snippets` FROM `level_master` WHERE `levelid`='$p_info[levelid]'"));
		$sn=explode("@",$snp['snippets']);
		$len=sizeof(array_filter($sn));
		for($s=0; $s<=$len; $s++)
		{
			if($sn[$s])
			{
				$snip="";
				if($sn[$s]==1)
				{
					$snip="opd_snippets.php";
				}
				if($sn[$s]==2)
				{
					$snip="invest_snippets.php";
				}
				if($sn[$s]==3 && $p_info["branch_id"]==1)
				{
					$snip="ipd_snippets.php";
				}
				if($sn[$s]==4)
				{
					$snip="casuality_snippets.php";
				}
				if($sn[$s]==5)
				{
					$snip="daycare_snippets.php";
				}
				if($sn[$s]==6)
				{
					$snip="dental_snippets.php";
				}
				if($sn[$s]==7)
				{
					$snip="dialysis_snippets.php";
				}
				if($sn[$s]==9)
				{
					$snip="service_snippets.php";
				}
				if($sn[$s]==15)
				{
					$snip="procedure_snippets.php";
				}
				if($sn[$s]==14)
				{
					$snip="ambulance_snippets.php";
				}
				
				if($sn[$s]==11 && $p_info["branch_id"]==1)
				{
					$snip="bed_snippets.php";
				}
			}
			if($sn[$s])
			include($snip);
		}
    //}
	//~ if($p_info['levelid']==3 || $p_info['levelid']==19)
	//~ {
		//~ include "opd_snippets_receipt.php";
		//~ include "ipd_snippets_receipt.php";
		//~ include "casuality_snippets_receipt.php";
		//~ include "bed_snippets.php";
	//~ }
	if($p_info['levelid']==1 || $p_info['levelid']==5)
	{
		echo "<script>$('#overall_bill').text('".number_format($overall_bill,2)."');</script>";
		echo "<script>$('#overall_disc').text('".number_format($overall_disc,2)."');</script>";
		echo "<script>$('#overall_amt_rcv').text('".number_format($overall_amt_rcv,2)."');</script>";
		echo "<script>$('#overall_bal_rcv').text('".number_format($overall_bal_rcv,2)."');</script>";
		echo "<script>$('#overall_ref').text('".number_format($overall_ref,2)."');</script>";
		echo "<script>$('#overall_net').text('".number_format($overall_net,2)."');</script>";
		echo "<script>$('#overall_free').text('".number_format($overall_free,2)."');</script>";
		echo "<script>$('#overall_bal').text('".number_format($overall_bal,2)."');</script>";
	}
?>
</div>

<!--<div class="quick-actions_homepage">
<ul class="quick-actions">
<li class="bg_lb my_url"> <a href="#/dash"> <i class="icon-dashboard"></i> <span class="label label-important">20</span> My Dashboard </a> </li>
<li class="bg_lg my_url"> <a href="#/charts/index.php?param=ODE="> <i class="icon-signal"></i> Charts</a> </li>
<li class="bg_ly my_url"> <a href="widgets.html"> <i class="icon-inbox"></i><span class="label label-success">101</span> Widgets </a> </li>
<li class="bg_lo my_url"> <a href="tables.html"> <i class="icon-th"></i> Tables</a> </li>
<li class="bg_ls my_url"> <a href="grid.html"> <i class="icon-fullscreen"></i> Full width</a> </li>
<li class="bg_lo my_url"> <a href="form-common.html"> <i class="icon-th-list"></i> Forms</a> </li>
</ul>
</div>-->

</div>
<style>
.parent
{
	font-size: 12px;  /* parent value */
}

.snip
{
	margin:0;
	padding:2px;
	display: inline-block;
	width: 48%;
}

.child_snip
{
	margin:0;
	padding:2px;
	display: inline-block;
	width: 100%;
	font-size: 12px; /* some value */
	height:250px;
	max-height:250px;overflow-y:scroll;
}

.table-report tr td, .table-report tr th
{
	padding:3px;
}
.table-report
{
	background:#FFFFFF;
}
.normal
{
	color:#111111;
}
.green
{
	color:#146C16;
}
.red
{
	color:#EB1B16;
}

@media only screen and (max-width: 600px)
{
	.snip
	{
		display: block;
		width: 100%;
	}
}

@media only screen and (min-width: 600px)
{
	.snip
	{
		display: block;
		width: 100%;
	}
}

@media only screen and (min-width: 768px)
{
	.snip
	{
		display: block;
		width: 100%;
	}
}

@media only screen and (min-width: 990px)
{
	.snip
	{
		display: inline-block;
		width: 48%;
	}
}

//====================================

</style>

<script>
	$(document).ready(function()
	{
		//today_stats();
		$(".my_url").click(function()
		{
			alert();
		});
	});
	
	function call_me()
	{
		setTimeout(function(){today_stats();},20000);
	}
	
	function cash_rep_print(id)
	{
		var user=btoa(id);
		url="print_cash_report.php?user="+user;
		wind=window.open(url,'Window','scrollbars=1,menubar=1,toolbar=0,height=670,width=1000');
	}
	
	function today_stats()
	{
		$.post("page_ajax.php",
		{
			dt:$("#dte").val(),
			usr:$("#user").text().trim(),
			type:"statistics",
		},
		function(data,status)
		{
			$("#stat").html(data);
			call_me();
		})
	}
	function show_tel_no()
	{
		url="pages/telephone_no_list.php";
		wind=window.open(url,'Window','scrollbars=1,menubar=1,toolbar=0,height=670,width=1000');
	}
</script>
<?php
	}
}

echo "<input type='hidden' id='edit_info_cu' value='$p_info[edit_info]' class='span1'>";
echo "<input type='hidden' id='edit_payment_cu' value='$p_info[edit_payment]' class='span1'>";
echo "<input type='hidden' id='cancel_pat_cu' value='$p_info[cancel_pat]' class='span1'>";
echo "<input type='hidden' id='discount_permission_cu' value='$p_info[discount_permission]' class='span1'>";

include("../inc/footer.php");
?>
