<?php
$emp_id=trim($_SESSION["emp_id"]);

$today=date("Y-m-d");

$level_id=mysqli_fetch_array(mysqli_query($link, " SELECT `levelid` FROM `employee` WHERE `emp_id`='$emp_id' "));

if($level_id['levelid']=='1' || $level_id['levelid']=='23')
{
	$dept_sel_dis="";
}else
{
	$dept_sel_dis="disabled";
	//$dept_sel_dis="";
}

$not_accountant = array();
array_push($not_accountant, 5, 6, 11, 12, 13, 20, 21);
$not_accountant = join(',',$not_accountant);

?>
<!--header-->
<div id="content-header">
    <div class="header_div"> <span class="header"> <?php echo $menu_info["par_name"]; ?></span></div>
</div>
<!--End-header-->
<div class="container-fluid">
	<table class="table table-bordered text-center">
		<tr>
			<td>
				<center>
					<b>From</b>
					<input class="form-control datepicker" type="text" name="from" id="from" value="<?php echo date("Y-m-d"); ?>" style="width:8%;" >
					<b>To</b>
					<input class="form-control datepicker" type="text" name="to" id="to" value="<?php echo date("Y-m-d"); ?>" style="width:8%;" >
					<select id="encounter" class="span2">
					<?php //if($level_id['levelid']=='1'){ ?>
						<option value="0">All Department</option>
					<?php //} ?>
					<?php
						$qq_qry=mysqli_query($link, " SELECT * FROM `patient_type_master` WHERE `p_type_id`<6 ORDER BY `p_type_id` ");
						while($qq=mysqli_fetch_array($qq_qry))
						{
							$access="";
							
							//~ if($qq["p_type_id"]==1)
							//~ {
								//~ $access=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `cashier_access` WHERE `emp_id`='$emp_id' AND `opd_cashier`=1 "));
								//~ if($access)
								//~ {
									//~ echo "<option value='$qq[p_type_id]'>$qq[p_type]</option>";
								//~ }
							//~ }
							//~ if($qq["p_type_id"]==2)
							//~ {
								//~ $access=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `cashier_access` WHERE `emp_id`='$emp_id' AND `lab_cashier`=1 "));
								//~ if($access)
								//~ {
									//~ echo "<option value='$qq[p_type_id]'>$qq[p_type]</option>";
								//~ }
							//~ }
							//~ if($qq["p_type_id"]==3)
							//~ {
								//~ $access=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `cashier_access` WHERE `emp_id`='$emp_id' AND `ipd_cashier`=1 "));
								//~ if($access)
								//~ {
									//~ echo "<option value='$qq[p_type_id]'>$qq[p_type]</option>";
								//~ }
							//~ }
							//~ if($qq["p_type_id"]==4)
							//~ {
								//~ $access=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `cashier_access` WHERE `emp_id`='$emp_id' AND `casuality_cashier`=1 "));
								//~ if($access)
								//~ {
									//~ echo "<option value='$qq[p_type_id]'>$qq[p_type]</option>";
								//~ }
							//~ }
							
							echo "<option value='$qq[p_type_id]'>$qq[p_type]</option>";
						}
					?>
					</select>
					<select id="user_entry" class="span2" <?php echo $dept_sel_dis; ?>>
						<option value="0">All User</option>
					<?php
						
						$user_qry=mysqli_query($link, " SELECT `emp_id`,`name` FROM `employee` WHERE levelid NOT IN ($not_accountant) ORDER BY `name` ");
						while($user=mysqli_fetch_array($user_qry))
						{
							if($emp_id==$user["emp_id"]){ $sel_this="selected"; }else{ $sel_this=""; }
							echo "<option value='$user[emp_id]' $sel_this>$user[name]</option>";
						}
					?>
					</select>
<!--
					onChange="view_all('all_account')"
-->
					<select id="pay_mode" class=""style="width:8%;">
						<option value="0">All</option>
						<option value="Cash">Cash</option>
						<option value="Card">Card</option>
					</select>
					<select id="account_break" class=""style="width:8%;">
						<option value="0">Current</option>
					<?php
						$c=1;
						$close_qry=mysqli_query($link, " SELECT * FROM `daily_account_close` WHERE `user`='$_SESSION[emp_id]' AND `close_date`='$today' ");
						while($account_close=mysqli_fetch_array($close_qry))
						{
							echo "<option value='$account_close[slno]'>Break $c</option>";
							$c++;
						}
					?>
					</select>
					<br>
					<button class="btn btn-success" onClick="view_all('all_account')">Receipt Detail </button>
					<button class="btn btn-success" onClick="view_all('account_summary')">Detail Account</button>
					<button class="btn btn-success" onClick="view_all('discount_report')">Discount Report</button>
					<!--<button class="btn btn-success" onClick="view_exp()">View Expenses</button>-->
					<button class="btn btn-success" onClick="view_all('expense')">View Expenses</button>
					<button class="btn btn-success" onClick="view_all('cancel_pat')">Patient Cancel Report</button>
					<button class="btn btn-success" onClick="view_all('payment_cancel')">Payment Cancel Report</button>
					<button class="btn btn-success" onClick="payment_refund()">Payment Refund</button>
					<br>
					<button class="btn btn-success" onClick="view_all('freepatient')">Free Patient</button>
				</center>
			</td>
		</tr>
		<tr style="display:none;">
			<td>
				<center>
					<button class="btn btn-success" onClick="view_all('daily_detail')">Daily Details</button>
					<button class="btn btn-success" onClick="view_all('daily_details')">Daily Summary</button>
					<button class="btn btn-success" onClick="view_all('all_patient')">All Patient</button>
					<button class="btn btn-success" onClick="view_all('view_summry')">User Summary</button>
					<button class="btn btn-success" onClick="view_all('freepatient')">Free Patient</button>
				</center>
			</td>
		</tr>
	</table>
	<div id="load_all" class="ScrollStyle" style="display:none;">
		
	</div>
	<!--<input type="button" value="Print" onClick="print_doc_pat('load_all')" class="btn btn-success">-->
</div>
<div id="loader" style="margin-top:-10%;"></div>
<link rel="stylesheet" href="include/css/jquery-ui.css" />
<script src="include/js/jquery-ui.js"></script>
<!-- Time -->
<link rel="stylesheet" href="include/ui-1.10.0/ui-lightness/jquery-ui-1.10.0.custom.min.css" type="text/css" />
<!-- Loader -->
<link rel="stylesheet" href="../css/loader.css" />
<script>
	$(document).ready(function(){
		$("#loader").hide();
		$(".datepicker").datepicker({
			dateFormat: 'yy-mm-dd',
			maxDate: '0',
		});
	});
	function payment_refund()
	{
		$("#loader").show();
		$.post("pages/payment_refund_ajax.php",
		{
			type:4,
			date1:$("#from").val(),
			date2:$("#to").val(),
			encounter:$("#encounter").val(),
			user_entry:$("#user_entry").val(),
			pay_mode:$("#pay_mode").val(),
		},
		function(data,status)
		{
			$("#loader").hide();
			$("#load_all").show().html(data);
		})
	}
	function view_all(typ)
	{
		$("#loader").show();
		$.post("pages/new_daily_account_details_all.php",
		{
			type:typ,
			date1:$("#from").val(),
			date2:$("#to").val(),
			encounter:$("#encounter").val(),
			user_entry:$("#user_entry").val(),
			pay_mode:$("#pay_mode").val(),
			account_break:$("#account_break").val(),
		},
		function(data,status)
		{
			$("#loader").hide();
			$("#load_all").show().html(data);
		})
	}
	function view_exp()
	{
		var fdate=$("#from").val();
		var tdate=$("#to").val();
		url="pages/daily_expense_rep.php?fdate="+fdate+"&tdate="+tdate;
		wind=window.open(url,'Window','scrollbars=1,menubar=1,toolbar=0,height=670,width=1000');
	}
	function print_doc_pat(el)
	{
		$("#excel_btn_hide").hide();
		var restorepage = $('body').html();
		var printcontent = $('#' + el).clone();
		$('body').empty().html(printcontent);
		window.print();
		$('body').html(restorepage);
		
		/*var disp_setting="toolbar=yes,location=no,";
		disp_setting+="directories=yes,menubar=yes,";
		disp_setting+="scrollbars=yes,width=650, height=600, left=100, top=25";
		var content_vlue = document.getElementById(el).innerHTML;
		var docprint=window.open("","",disp_setting);
		docprint.document.open();
		docprint.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"');
		docprint.document.write('"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">');
		docprint.document.write('<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">');
		docprint.document.write('<head><title>My Title</title>');
		docprint.document.write('<style type="text/css">body{ margin:0px;');
		docprint.document.write('font-family:verdana,Arial;color:#000;');
		docprint.document.write('font-family:Verdana, Geneva, sans-serif; font-size:12px;}');
		docprint.document.write('a{color:#000;text-decoration:none;} </style>');
		docprint.document.write('</head><body onLoad="self.print()"><center>');
		docprint.document.write(content_vlue);
		docprint.document.write('</center></body></html>');
		docprint.document.close();
		docprint.focus();
		*/
	}
	function print_page(val,date1,date2,encounter,user_entry,pay_mode,account_break)
	{
		var user=$("#user").text().trim();
		if(val=="detail")
		{
			url="pages/new_detail_account_print.php?date1="+date1+"&date2="+date2+"&encounter="+encounter+"&user_entry="+user_entry+"&pay_mode="+pay_mode+"&EpMl="+user+"&account_break="+account_break;
		}
		if(val=="all_account_all")
		{
			url="pages/new_detail_account_print_all.php?date1="+date1+"&date2="+date2+"&encounter="+encounter+"&user_entry="+user_entry+"&pay_mode="+pay_mode+"&EpMl="+user+"&account_break="+account_break;
		}
		if(val=="summary")
		{
			url="pages/summary_account_print.php?date1="+date1+"&date2="+date2+"&encounter="+encounter+"&user_entry="+user_entry+"&EpMl="+user;
		}
		if(val=="discount")
		{
			url="pages/discount_account_print.php?date1="+date1+"&date2="+date2+"&encounter="+encounter+"&user_entry="+user_entry+"&EpMl="+user;
		}
		if(val=="expense")
		{
			url="pages/expense_account_print.php?date1="+date1+"&date2="+date2+"&encounter="+encounter+"&user_entry="+user_entry+"&EpMl="+user;
		}
		
		if(val=="free_pat")
		{
			url="pages/lab_free_patient_rpt.php?date1="+date1+"&date2="+date2+"&encounter="+encounter+"&user_entry="+user_entry+"&EpMl="+user;
		}
		
		if(val=="cancel_pat")
		{
			url="pages/cancel_pat_account_print.php?date1="+date1+"&date2="+date2+"&encounter="+encounter+"&user_entry="+user_entry+"&EpMl="+user;
		}
		if(val=="payment_cancel")
		{
			url="pages/payment_cancel_account_print.php?date1="+date1+"&date2="+date2+"&encounter="+encounter+"&user_entry="+user_entry+"&EpMl="+user;
		}
		if(val=="pay_refund")
		{
			url="pages/pay_refund_rpt.php?date1="+date1+"&date2="+date2+"&encounter="+encounter+"&user_entry="+user_entry+"&EpMl="+user;
		}
		if(val=="all_patient")
		{
			var from=$("#sl_frm").val();
			var to=$("#sl_to").val();
			url="pages/all_patient_rpt.php?date1="+date1+"&date2="+date2+"&encounter="+encounter+"&user_entry="+user_entry+"&from="+from+"&to="+to+"&EpMl="+user;
		}
		if(val=="view_summry")
		{
			url="pages/summry_rpt.php?date1="+date1+"&date2="+date2+"&encounter="+encounter+"&user_entry="+user_entry+"&EpMl="+user;
		}
		wind=window.open(url,'Window','scrollbars=1,menubar=1,toolbar=0,height=670,width=1000');
	}
	function close_account(c_date)
	{
		bootbox.dialog({
			message: "<h5>Are you sure want to close ?</h5>",
			buttons: {
				cancel: {
					label: '<i class="icon-remove"></i> Cancel',
					className: "btn btn-inverse",
					callback: function() {
					  bootbox.hideAll();
					}
				},
				confirm: {
					label: '<i class="icon-ok"></i> Close',
					className: "btn btn-danger",
					callback: function() {
						$.post("pages/close_account_data.php",
						{
							type:"close_account",
							c_date:c_date,
							user:$("#user").text().trim(),
						},
						function(data,status)
						{
							bootbox.dialog({ message: "<h5>"+data+"'s account is closed</h5>"});
							setTimeout(function(){
								bootbox.hideAll();
								window.location.reload(true);
							},3000);
						})
					}
				}
			}
		});
	}
</script>
<style>
.ScrollStyle
{
    max-height: 400px;
    overflow-y: scroll;
}
@media print {
  body * {
    visibility: hidden;
  }
  #load_all, #load_all * {
    visibility: visible;
  }
  #load_all {
	  overflow:visible;
    position: absolute;
    left: 0;
    top: 0;
  }
}
.ipd_serial
{
	display:none;
}
</style>
