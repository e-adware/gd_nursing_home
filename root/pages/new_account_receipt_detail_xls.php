<?php
session_start();

include("../../includes/connection.php");
include("../../includes/global.function.php");
include("../../includes/idgeneration.function.php");

$c_user=trim($_SESSION['emp_id']);

$emp_info=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `employee` WHERE `emp_id`='$c_user' "));
$u_level=$emp_info["levelid"];


$date1=$_GET['date1'];
$date2=$_GET['date2'];

// important
$date11=$_GET['date1'];
$date22=$_GET['date2'];

$encounter=$_GET['encounter'];
$encounter_val=$_GET['encounter'];
$pay_mode=$_GET['pay_mode'];
$user_entry=$_GET['user_entry'];
$user_val=$_GET['EpMl'];
$account_break=$_GET['account_break'];
$branch_id=$_GET['branch_id'];

if(!$branch_id)
{
	$branch_id=$emp_info["branch_id"];
}

$branch_str=" AND `opd_id` IN(SELECT `opd_id` FROM `uhid_and_opdid` WHERE `branch_id`='$branch_id')";
$branch_str_a=" AND a.`opd_id` IN(SELECT `opd_id` FROM `uhid_and_opdid` WHERE `branch_id`='$branch_id')";
$branch_str_b=" AND b.`opd_id` IN(SELECT `opd_id` FROM `uhid_and_opdid` WHERE `branch_id`='$branch_id')";

$filename ="account_receipt_detail_report_".$date1."_to_".$date2."_".$account_break.".xls";

header('Content-type: application/ms-excel');
header('Content-Disposition: attachment; filename='.$filename);

$close_info=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `daily_account_close_new` WHERE `slno`='$account_break' "));
if($close_info)
{
	$break_number=$close_info["counter"]. " on ".date("d-M-Y", strtotime($close_info["date"]))." ".date("h:i A", strtotime($close_info["time"]));
}
else
{
	$break_number=0;
}

$pay_max_slno_str_less="";
$exp_max_slno_str_less="";
$ref_max_slno_str_less="";
$fre_max_slno_str_less="";

$pay_max_slno_str_grtr="";
$exp_max_slno_str_grtr="";
$ref_max_slno_str_grtr="";
$fre_max_slno_str_grtr="";

$date_str="";
$date_str_exp="";
$date_str_a="";
$date_str_b="";
$user="";
$cashier_str="All user";
if($user_entry>0)
{
	$user=" AND `user`='$user_entry'";
	$user_a=" AND a.`user`='$user_entry'";
	
	$user_name=mysqli_fetch_array(mysqli_query($link, " SELECT `name` FROM `employee` WHERE `emp_id`='$user_entry' "));
	$cashier_str=$user_name["name"];
}

$payment_mode_str="";
if($pay_mode!="0")
{
	$payment_mode_str=" AND `payment_mode`='$pay_mode'";
}

// Less
if($user_entry!=$c_user)
{
	// admin
	$date_str=" AND `date` BETWEEN '$date1' AND '$date2'";
	$date_str_exp=" AND `expense_date` BETWEEN '$date1' AND '$date2'";
	$date_str_a=" AND a.`date` BETWEEN '$date1' AND '$date2'";
	$date_str_b=" AND b.`date` BETWEEN '$date1' AND '$date2'";
}else
{
	if($account_break==0)
	{
		// Greater
		$check_close_account_last=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `daily_account_close_new` WHERE `user`='$user_entry' ORDER BY `slno` DESC limit 1 "));
		
		if($check_close_account_last)
		{
			//$date1=date('Y-m-d', strtotime($date1. ' - 1 days'));
			
			$first_date=$check_close_account_last['date'];
			
			$date_str=" AND date>='$first_date'";
			$date_str_a=" AND a.date>='$first_date'";
			
			$pay_max_slno_grtr=$check_close_account_last['pay_id'];
			$pay_max_slno_str_grtr=" AND `pay_id`>$pay_max_slno_grtr ";
			
			$exp_max_slno_grtr=$check_close_account_last['exp_slno'];
			$exp_max_slno_str_grtr=" AND `slno`>$exp_max_slno_grtr ";
			
			// Object a
			$ref_max_slno_grtr=$check_close_account_last['refund_slno'];
			$ref_max_slno_str_grtr=" AND a.`slno`>$ref_max_slno_grtr ";
			
			$fre_max_slno_grtr=$check_close_account_last['free_slno'];
			$fre_max_slno_str_grtr=" AND a.`slno`>$fre_max_slno_grtr ";
			
		}
		else
		{
			$first_date="";
			$pay_max_slno_str_grtr=" AND `pay_id`>0 ";
			$exp_max_slno_str_grtr=" AND `slno`>0 ";
			$ref_max_slno_str_grtr=" AND a.`slno`>0 ";
			$fre_max_slno_str_grtr=" AND a.`slno`>0 ";
		}
		
	}else
	{
		$check_close_account_today=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `daily_account_close_new` WHERE `slno`='$account_break' "));
		
		$last_date=$check_close_account_today["close_date"];
		
		$date_str=" AND date<='$last_date'";
		$date_str_a=" AND a.date<='$last_date'";
		
		$pay_max_slno_less=$check_close_account_today['pay_id'];
		$pay_max_slno_str_less=" AND `pay_id`<=$pay_max_slno_less ";
		
		$exp_max_slno_less=$check_close_account_today['exp_slno'];
		$exp_max_slno_str_less=" AND `slno`<=$exp_max_slno_less ";
		
		// Object a
		$ref_max_slno_less=$check_close_account_today['refund_slno'];
		$ref_max_slno_str_less=" AND a.`slno`<=$ref_max_slno_less ";
		
		$fre_max_slno_less=$check_close_account_today['free_slno'];
		$fre_max_slno_str_less=" AND a.`slno`<=$fre_max_slno_less ";
		
		// Greater
		$check_close_account_last=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `daily_account_close_new` WHERE `slno`<'$account_break' AND `user`='$c_user' ORDER BY `slno` DESC limit 1 "));
		
		if($check_close_account_last)
		{
			$first_date=$check_close_account_last["close_date"];
			
			$date_str.=" AND date>='$first_date'";
			$date_str_a.=" AND a.date>='$first_date'";
			
			$pay_max_slno_grtr=$check_close_account_last['pay_id'];
			$pay_max_slno_str_grtr=" AND `pay_id`>$pay_max_slno_grtr ";
			
			$exp_max_slno_grtr=$check_close_account_last['exp_slno'];
			$exp_max_slno_str_grtr=" AND `slno`>$exp_max_slno_grtr ";
			
			// Object a
			$ref_max_slno_grtr=$check_close_account_last['refund_slno'];
			$ref_max_slno_str_grtr=" AND a.`slno`>$ref_max_slno_grtr ";
			
			$fre_max_slno_grtr=$check_close_account_last['free_slno'];
			$fre_max_slno_str_grtr=" AND a.`slno`>$fre_max_slno_grtr ";
			
		}
		else
		{
			$first_date="";
		}
	}
}

$encounter_str="";
$encounter_pay_type=0;
if($encounter>0)
{
	$pat_typ_text=mysqli_fetch_array(mysqli_query($link, " SELECT `type` FROM `patient_type_master` WHERE `p_type_id`='$encounter' "));
	$encounter_pay_type=$pat_typ_text["type"];
	
	$encounter_str=" AND `encounter`='$encounter'";
}

$report_name="Receipt Detail ";
if($user_entry!=$c_user)
{
	$report_name.=" from ".convert_date($date1)." to ".convert_date($date2);
}
else
{
	$report_name.=" of Break no. ".$break_number;
}
?>
	<table class="table table-hover table-condensed">
		<tr>
			<th colspan="8">Cashier: <?php echo $cashier_str; ?></th>
		</tr>
		<tr>
			<th colspan="8"><?php echo $report_name; ?></th>
		</tr>
		<thead class="table_header_fix">
			<tr>
				<th>#</th>
				<th>Date</th>
				<th>Bill No</th>
				<th>Transaction No</th>
				<th>Patient Name</th>
				<th>Amount</th>
				<th>User</th>
				<th>Encounter</th>
			</tr>
		</thead>
<?php
		$payment_mode_array=array();
		$payment_mode_array_refund=array();
		$total_amount=0;
		$pmode=1;
		$trans=1;
		$payment_mode_name="";
		
		$payment_mode_qry=mysqli_query($link, "SELECT `p_mode_name`,`operation` FROM `payment_mode_master` WHERE `p_mode_name` IN(SELECT DISTINCT `payment_mode` FROM `payment_detail_all` WHERE `pay_id`>0 AND `payment_type`!='Refund' $payment_mode_str $encounter_str $date_str $pay_max_slno_str_less $pay_max_slno_str_grtr $user $branch_str ) ORDER BY `sequence` ASC");
		while($payment_mode=mysqli_fetch_array($payment_mode_qry))
		{
			$pay_str=" SELECT * FROM `payment_detail_all` WHERE `pay_id`>0 AND (`amount`>0 OR `balance_amount`>0) AND `payment_type`!='Refund' AND `payment_mode`='$payment_mode[p_mode_name]' $encounter_str $date_str $pay_max_slno_str_less $pay_max_slno_str_grtr $user $branch_str ORDER BY `pay_id` ASC ";
			
			//echo $pay_str."<br>";
			
			$pay_mode_amount=0;
			$pay=1;
			$pay_det_qry=mysqli_query($link, $pay_str);
			$pay_det_num=mysqli_num_rows($pay_det_qry);
			if($pay_det_num>0)
			{
				if($payment_mode_name!=$payment_mode["p_mode_name"])
				{
					echo "<tr><th colspan='8'>$payment_mode[p_mode_name]</th></tr>";
				}
				else
				{
					$payment_mode_name=$payment_mode["p_mode_name"];
				}
				
				while($pay_det=mysqli_fetch_array($pay_det_qry))
				{
					$patient_id =$pay_det["patient_id"];
					$opd_id     =$pay_det["opd_id"];
					
					$tr_show=1;
					
					$amount=$pay_det["amount"];
					if($payment_mode["operation"]==2) // Credit
					{
						$amount=$pay_det["balance_amount"];
						
						$check_paid=mysqli_fetch_array(mysqli_query($link, " SELECT ifnull(SUM(`amount`),0) AS `paid`, ifnull(SUM(`discount_amount`),0) AS `discount`, ifnull(SUM(`refund_amount`),0) AS `refund`, ifnull(SUM(`tax_amount`),0) AS `tax` FROM `payment_detail_all` WHERE `patient_id`='$patient_id' AND `opd_id`='$opd_id' AND `pay_id`>$pay_det[pay_id] $date_str $pay_max_slno_str_less $pay_max_slno_str_grtr $user "));
						
						$already_paid      =$check_paid["paid"];
						$already_discount  =$check_paid["discount"];
						$already_refund    =$check_paid["refund"];
						$already_tax       =$check_paid["tax"];
						
						$amount-=$already_paid+$already_discount+$already_tax+$already_refund;
					}
					if($amount<0){ $amount=0; }
					
					$pat_reg=mysqli_fetch_array(mysqli_query($link, "SELECT * FROM `uhid_and_opdid` WHERE `patient_id`='$patient_id' AND `opd_id`='$opd_id' "));
					
					$pat_typ_text=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `patient_type_master` WHERE `p_type_id`='$pat_reg[type]' "));
					$encounter_name=$pat_typ_text['p_type'];
					
					$pat_info=mysqli_fetch_array(mysqli_query($link, " SELECT `uhid`,`name` FROM `patient_info` WHERE `patient_id`='$patient_id' "));
					
					$user_info=mysqli_fetch_array(mysqli_query($link, " SELECT `name` FROM `employee` WHERE `emp_id`='$pay_det[user]' "));
					
					$cheque_ref_no="";
					if($pay_det["cheque_ref_no"]!="")
					{
						$cheque_ref_no="<br><small style='font-size: 9px;'>(Ref : $pay_det[cheque_ref_no])</small>";
					}
				if($amount>0)
				{
	?>
					<tr>
						<td><?php echo $trans; ?></td>
						<td><?php echo $pay_det["date"]; ?></td>
						<td><?php echo $pay_det["opd_id"]; ?></td>
						<td><?php echo $pay_det["transaction_no"].$cheque_ref_no; ?></td>
						<td><?php echo $pat_info["name"]; ?></td>
						<td><?php echo indian_currency_format($amount); ?></td>
						<td><?php echo $user_info["name"]; ?></td>
						<td><?php echo $encounter_name; ?></td>
					</tr>
	<?php
						$pay_mode_amount+=$amount;
						$total_amount+=$amount;
						$trans++;
						$pay++;
					}
				}
				
				if($payment_mode_name!=$payment_mode["p_mode_name"])
				{
					$payment_mode_array[$payment_mode["p_mode_name"]]=$pay_mode_amount;
	?>
				<tr>
					<th colspan="4"></th>
					<th style="text-align:right">Total <?php echo $payment_mode["p_mode_name"]; ?></th>
					<th><?php echo indian_currency_format($pay_mode_amount); ?></th>
					<th></th>
					<th></th>
				</tr>
	<?php
				}
				$pmode++;
			}
		}
		
		// Refund
		$total_amount_refunded=0;
		$payment_mode_qry=mysqli_query($link, "SELECT `p_mode_name`,`operation` FROM `payment_mode_master` WHERE `p_mode_name` IN(SELECT DISTINCT `payment_mode` FROM `payment_detail_all` WHERE `pay_id`>0 AND `payment_type`='Refund' $payment_mode_str $encounter_str $date_str $pay_max_slno_str_less $pay_max_slno_str_grtr $user $branch_str) ORDER BY `sequence` ASC");
		$payment_mode_num=mysqli_num_rows($payment_mode_qry);
		if($payment_mode_num>0)
		{
			while($payment_mode=mysqli_fetch_array($payment_mode_qry))
			{
				if($payment_mode_name!=$payment_mode["p_mode_name"])
				{
					echo "<tr><th colspan='8' style='display:;'>$payment_mode[p_mode_name] Refund</th></tr>";
				}
				else
				{
					$payment_mode_name=$payment_mode["p_mode_name"];
				}
				
				$pay_str=" SELECT * FROM `payment_detail_all` WHERE `pay_id`>0 AND `refund_amount`>0 AND `payment_type`='Refund' AND `payment_mode`='$payment_mode[p_mode_name]' $encounter_str $date_str $pay_max_slno_str_less $pay_max_slno_str_grtr $user $branch_str ORDER BY `pay_id` ASC ";
				
				//echo $pay_str."<br>";
				
				$pay_mode_amount=0;
				$pay=1;
				$pay_det_qry=mysqli_query($link, $pay_str);
				$pay_det_num=mysqli_num_rows($pay_det_qry);
				
				if($pay_det_num>0)
				{
					while($pay_det=mysqli_fetch_array($pay_det_qry))
					{
						$patient_id =$pay_det["patient_id"];
						$opd_id     =$pay_det["opd_id"];
						
						$amount=$pay_det["refund_amount"];
						
						$pat_reg=mysqli_fetch_array(mysqli_query($link, "SELECT * FROM `uhid_and_opdid` WHERE `patient_id`='$patient_id' AND `opd_id`='$opd_id' "));
						
						$pat_typ_text=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `patient_type_master` WHERE `p_type_id`='$pat_reg[type]' "));
						$encounter_name=$pat_typ_text['p_type'];
						
						$pat_info=mysqli_fetch_array(mysqli_query($link, " SELECT `uhid`,`name` FROM `patient_info` WHERE `patient_id`='$patient_id' "));
						
						$user_info=mysqli_fetch_array(mysqli_query($link, " SELECT `name` FROM `employee` WHERE `emp_id`='$pay_det[user]' "));
						
						$cheque_ref_no="";
						if($pay_det["cheque_ref_no"]!="")
						{
							$cheque_ref_no="<br><small style='font-size: 9px;'>(Ref : $pay_det[cheque_ref_no])</small>";
						}
						if($amount<0){ $amount=0; }
		?>
					<tr style="display:;">
						<td><?php echo $trans; ?></td>
						<td><?php echo $pay_det["date"]; ?></td>
						<td><?php echo $pay_det["opd_id"]; ?></td>
						<td><?php echo $pay_det["transaction_no"].$cheque_ref_no; ?></td>
						<td><?php echo $pat_info["name"]; ?></td>
						<td><?php echo indian_currency_format($amount); ?></td>
						<td><?php echo $user_info["name"]; ?></td>
						<td><?php echo $encounter_name; ?></td>
					</tr>
		<?php
						$total_amount_refunded+=$amount;
						$pay_mode_amount+=$amount;
						$total_amount+=$amount;
						$trans++;
						$pay++;
					}
					
					if($payment_mode_name!=$payment_mode["p_mode_name"])
					{
						$payment_mode_array_refund[$payment_mode["p_mode_name"]]=$pay_mode_amount;
		?>
					<tr style="display:;">
						<th colspan="4"></th>
						<th style="text-align:right">Total <?php echo $payment_mode["p_mode_name"]; ?> Refund</th>
						<th><?php echo indian_currency_format($pay_mode_amount); ?></th>
						<th></th>
						<th></th>
					</tr>
		<?php
					}
					$pmode++;
				}
			}
		}
		$net_amount=$total_amount-$payment_mode_array["Credit"];
?>
		<tr><td colspan="8" style="border-top:2px solid #000;"></td></tr>
<?php
	$total_amount_received=0;
	foreach($payment_mode_array as $key => $value)
	{
		if($key!="Credit")
		{
			$total_amount_received+=$value;
?>
		<tr>
			<th colspan="4"></th>
			<th style="text-align:right">Total <?php echo $key; ?> Received</th>
			<th><?php echo indian_currency_format($value); ?></th>
			<th></th>
			<th></th>
		</tr>
<?php
		}
	}
	$net_amount=$total_amount_received-$total_amount_refunded;
?>
		<tr>
			<th colspan="4"></th>
			<th style="text-align:right">Total Amount Received</th>
			<th><?php echo indian_currency_format($total_amount_received); ?></th>
			<th></th>
			<th></th>
		</tr>
		<tr>
			<th colspan="4"></th>
			<th style="text-align:right">Total Amount Refunded</th>
			<th><?php echo indian_currency_format($total_amount_refunded); ?></th>
			<th></th>
			<th></th>
		</tr>
		<tr>
			<th colspan="4"></th>
			<th style="text-align:right">Net Amount(<?php $z=1; foreach($payment_mode_array as $key => $value){ if($key!="Credit"){ if($z!=1){ echo "+"; } echo"$key"; $z++; } } ?>)</th>
			<th><?php echo indian_currency_format($net_amount); ?></th>
			<th></th>
			<th></th>
		</tr>
<?php
	if($payment_mode_array_refund["Cash"])
	{
		$net_cash_amount=$payment_mode_array["Cash"]-$payment_mode_array_refund["Cash"];
?>
		<tr>
			<th colspan="4"></th>
			<th style="text-align:right">Net Cash Amount</th>
			<th><?php echo indian_currency_format($net_cash_amount); ?></th>
			<th></th>
			<th></th>
		</tr>
<?php
	}
	if($user_entry==0)
	{
		$expense=mysqli_fetch_array(mysqli_query($link, " SELECT ifnull(SUM(`Amount`),0) AS `amount` FROM `expensedetail` WHERE `expense_date` BETWEEN '$date11' AND '$date22' "));
		$expense_amount=$expense["amount"];
		if($expense_amount>0)
		{
?>
		<tr>
			<th colspan="4"></th>
			<th style="text-align:right">Expense Amount</th>
			<th><?php echo indian_currency_format($expense_amount); ?></th>
			<th></th>
			<th></th>
		</tr>
		<tr>
			<th colspan="4"></th>
			<th style="text-align:right">Net Amount</th>
			<th><?php echo indian_currency_format($net_amount-$expense_amount); ?></th>
			<th></th>
			<th></th>
		</tr>
<?php
		}
	}
?>
	</table>