<?php
session_start();

include("../../includes/connection.php");
include("../../includes/global.function.php");
include("../../includes/idgeneration.function.php");

function convert_date_only_sm_year($date)
{
	$timestamp = strtotime($date); 
	$new_date = date('y', $timestamp);
	return $new_date;
}

$c_user=$_SESSION["emp_id"];
$ip_addr=$_SERVER["REMOTE_ADDR"];

$emp_access=mysqli_fetch_array(mysqli_query($link, " SELECT `edit_info`,`edit_payment`,`cancel_pat`,`discount_permission` FROM `employee` WHERE `emp_id`='$c_user' "));

$date=date("Y-m-d");
$time=date("H:i:s");

$type=$_POST['type'];
$p_type_id=$_POST['p_type_id'];

$pat_typ_text=mysqli_fetch_array(mysqli_query($link,"SELECT * FROM `patient_type_master` WHERE `p_type_id`='$p_type_id'"));
$bill_name=$pat_typ_text["bill_name"];

$prefix_name=$pat_typ_text["prefix"];

if($type=="load_center")
{
	$branch_id=mysqli_real_escape_string($link, $_POST["branch_id"]);
	$patient_id=mysqli_real_escape_string($link, $_POST["patient_id"]);
	$opd_id=mysqli_real_escape_string($link, $_POST["opd_id"]);
	
	$pat_reg=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `uhid_and_opdid` WHERE `patient_id`='$patient_id' and `opd_id`='$opd_id' "));
	
	$qry=mysqli_query($link, "SELECT `centreno`,`centrename` FROM `centremaster` WHERE `branch_id`='$branch_id' ORDER BY `centrename` ASC");
	while($data=mysqli_fetch_array($qry))
	{
		if(!$pat_reg){ if($branch_id==1){ $pat_reg["center_no"]="C100"; }else{ $pat_reg["center_no"]="C102"; } }
		if($data["centreno"]==$pat_reg["center_no"]){ $sel="selected"; }else{ $sel=""; }
		echo "<option value='$data[centreno]' $sel>$data[centrename]</option>";
	}
}

if($type=="get_access_detail")
{
	$user=mysqli_real_escape_string($link, $_POST["user"]);
	
	$emp_access=mysqli_fetch_array(mysqli_query($link, " SELECT `edit_info`,`edit_payment`,`cancel_pat`,`discount_permission` FROM `employee` WHERE `emp_id`='$user' "));
	$emp_access_str="#".$emp_access["edit_info"]."#".$emp_access["edit_payment"]."#".$emp_access["cancel_pat"]."#".$emp_access["discount_permission"]."#";
	
	echo $emp_access_str;
}
if($type=="search_patients")
{
	$val=$_POST['val'];
	$typ=$_POST['typ'];
	if($typ=="name")
	{
		if(strlen($val)>2)
		{
			$q="SELECT * FROM `patient_info` WHERE `name` like '%$val%' order by `slno` DESC";
		}
	}
	if($typ=="phone")
	{
		if(strlen($val)>2)
		{
			$q="SELECT * FROM `patient_info` WHERE `phone` like '%$val%' order by `slno` DESC";
		}
	}
	if($typ=="uhid")
	{
		if(strlen($val)>2)
		{
			$q="SELECT * FROM `patient_info` WHERE `patient_id` like '$val%' order by `slno` DESC ";
		}
	}
	if($typ=="opd_serial")
	{
		if(strlen($val)>2)
		{
			$q="SELECT * FROM `patient_info` WHERE `patient_id` IN ( SELECT `patient_id` FROM `uhid_and_opdid` WHERE `uhid` like '$val%' ) order by `slno` DESC ";
		}
		
	}
	if($typ=="pin")
	{
		if(strlen($val)>2)
		{
			$q="SELECT * FROM `patient_info` WHERE `patient_id` IN ( SELECT DISTINCT `patient_id` FROM `uhid_and_opdid` WHERE `opd_id` like '$val%' ) order by `slno` DESC ";
		}
	}
	
	//echo $q;

	$qry=mysqli_query($link, $q);
?>
	<table class="table table-condensed table-bordered">
		<tr>
			<th>#</th>
			<th>Name</th>
			<th>UHID</th>
			<th>Phone</th>
		</tr>
<?php
	$i=1;
	while($q=mysqli_fetch_array($qry))
	{
		$pat_info=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `patient_info` WHERE `patient_id`='$q[patient_id]' "));
		
		$pat_type=mysqli_fetch_array(mysqli_query($link, " SELECT `type` FROM `uhid_and_opdid` WHERE `patient_id`='$q[patient_id]' AND `opd_id`='$q[opd_id]' "));
		
	?>
		<tr id="row_id<?php echo $i;?>" class="all_pat_row" onClick="load_emp_details('<?php echo $q['patient_id'];?>','<?php echo $typ;?>')" style="cursor:pointer;">
			<td><?php echo $i;?></td>
			<td><?php echo $pat_info['name'];?></td>
			<td><?php echo $pat_info['patient_id'];?><input type="hidden" id="e_id<?php echo $i;?>" value="<?php echo $q['patient_id']."@@".$typ;?>"/></td>
			<td><?php echo $pat_info['phone'];?></td>
		</tr>
<?php
		$i++;
	}
?>
	</table>
<?php
}
if($type=="load_patient_info")
{
	$patient_id=mysqli_real_escape_string($link, $_POST["patient_id"]);
	
	$pat_info=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `patient_info` WHERE `patient_id`='$patient_id' "));
	
	if($pat_info["dob"]!=""){ $age=age_calculator_date($pat_info["dob"],$date); }else{ $age=$pat_info["age"]." ".$pat_info["age_type"]; }
?>
	<table id="patient_info_tbl_load" class="table table-condensed" style="background-color:#FFF">
		<tr>
			<th colspan="4" style="text-align:center;">
				<h4>Patient Information</h4>
			</th>
		</tr>
		<tr>
			<th>UHID</th>
			<th>Name</th>
			<th>Age</th>
			<th>Sex</th>
		</tr>
		<tr>
			<td><?php echo $pat_info["patient_id"]; ?></td>
			<td><?php echo $pat_info["name"]; ?></td>
			<td><?php echo $age; ?></td>
			<td><?php echo $pat_info["sex"]; ?></td>
		</tr>
	</table>
<?php
}
if($type=="load_district")
{
	$state=$_POST['state'];
	
	$qry=mysqli_query($link,"SELECT * FROM `district` WHERE `state_id`='$state' ORDER BY `name`");
?>
	<option value="0">Select</option>
<?php
	while($district=mysqli_fetch_array($qry))
	{
		$company_detaill=mysqli_fetch_array(mysqli_query($link, " SELECT `city` FROM `company_name` "));
		//$company_detaill["city"]="Sivasagar";
		if($company_detaill["city"]==$district['name']){ $sel_district="selected"; }else{ $sel_district=""; }
?>
		<option value="<?php echo $district['district_id']; ?>" <?php echo $sel_district; ?>><?php echo $district['name']; ?></option>
<?php
	}
}
if($type=="load_centres")
{
	$source_id=$_POST["val"];
	
	$val=mysqli_fetch_array(mysqli_query($link, "SELECT `centreno` FROM `patient_source_master` WHERE `source_id`='$source_id'"));
	
	echo $val["centreno"];
}

if($type=="load_con_doctor")
{
	$branch_id=mysqli_real_escape_string($link, $_POST["branch_id"]);
	$center_no=mysqli_real_escape_string($link, $_POST["center_no"]);
	$patient_id=$_POST["uhid"];
	$opd_id=$_POST["opd_id"];
	$dept_id=$_POST["dept_id"];
	$dname=$_POST["val"];
	
	$day_number=convert_date_to_day_num($date);
	
	$edit_consultantdoctorid=0;
	$edit_visit_fee=0;
	$edit_regd_fee=0;
	if($opd_id!="0")
	{
		$edit_appointment=mysqli_fetch_array(mysqli_query($link, " SELECT `consultantdoctorid`,`visit_fee` FROM `appointment_book` WHERE `patient_id`='$patient_id' and `opd_id`='$opd_id' "));
		$edit_consultantdoctorid=$edit_appointment["consultantdoctorid"];
		
		$con_pat_pay_detail=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `consult_patient_payment_details` WHERE `patient_id`='$patient_id' and `opd_id`='$opd_id' "));
		$edit_visit_fee=$con_pat_pay_detail["visit_fee"];
		$edit_regd_fee=$con_pat_pay_detail["regd_fee"];
	}
?>
	<table style="background-color:#FFF" border="1" class="sec_table table table-bordered" id="center_table" width="600px">
		<th>ID</th><th>Doctor Name</th>
<?php
		if($dname)
		{
			$d=mysqli_query($link, " SELECT * FROM `consultant_doctor_master` WHERE `branch_id`='$branch_id' AND `Name` like '%$dname%' and `dept_id`='$dept_id' AND `status`='0' order by `Name` ");
		}
		else
		{
			$d=mysqli_query($link, " SELECT * FROM `consultant_doctor_master` WHERE `branch_id`='$branch_id' AND `dept_id`='$dept_id' AND `status`='0' order by `Name` ");
		}
		$d1_num=mysqli_num_rows($d);
		$i=1;
		while($d1=mysqli_fetch_array($d))
		{
			if($edit_consultantdoctorid==$d1['consultantdoctorid'])
			{
				$visitt_fee=$edit_visit_fee;
				$regdd_fee=$edit_regd_fee;
				
				$visit_type_info=mysqli_fetch_array(mysqli_query($link, " SELECT `visit_type_id` FROM `patient_visit_type_details` WHERE `patient_id`='$patient_id' and `opd_id`='$opd_id' "));
				
				$visit_type_id=$visit_type_info["visit_type_id"];
			}else
			{
				// Visit Fee
				$check_last_visit_fee=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `appointment_book` WHERE `patient_id`='$patient_id' AND `consultantdoctorid`='$d1[consultantdoctorid]' AND `visit_fee`>0 order by `slno` DESC "));
				
				$check_last_visit_fee_date=$check_last_visit_fee["date"];
				if(!$check_last_visit_fee_date)
				{
					$visitt_fee=$d1["opd_visit_fee"];
					$visit_type_id=1; // NEW PATIENT
				}else
				{
					$dates_array = getDatesFromRange($check_last_visit_fee_date, $date);
					$visit_fee_day_diff=sizeof($dates_array);
					if($visit_fee_day_diff<=$d1["opd_visit_validity"])
					{
						$visitt_fee=0;
						$visit_type_id=3; // FREE FOLLOW UP
					}else
					{
						$visitt_fee=$d1["opd_visit_fee"];
						$visit_type_id=6; // PAID FOLLOW UP
					}
				}
				
				if($opd_id==0) // if regd fee not doctor wise
				{
					// Regd Fee
					$check_last_regd_fee_qry=mysqli_query($link, " SELECT * FROM `pat_regd_fee` WHERE `patient_id`='$patient_id' AND `regd_fee`>0 order by `slno` DESC limit 0,1 "); // AND `dept_id`='$d1[dept_id]'  AND `consultantdoctorid`='$d1[consultantdoctorid]'
					$check_last_regd_fee_num=mysqli_num_rows($check_last_regd_fee_qry);
					$check_regd_fee=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `opd_registration_fees` "));
					if($check_last_regd_fee_num==0)
					{
						$regdd_fee=$d1["opd_reg_fee"];
					}else
					{
						$check_last_regd_fee=mysqli_fetch_array($check_last_regd_fee_qry);
						$dates_array = getDatesFromRange($check_last_regd_fee["date"], $date);
						$day_diff=sizeof($dates_array);
						if($day_diff<=$d1["opd_reg_validity"])
						{
							$regdd_fee=0;
						}else
						{
							$regdd_fee=$d1["opd_reg_fee"];
						}
					}
				}
				else
				{
					$regdd_fee=$edit_regd_fee;
				}
				
				// Centre Wise Fee
				$centre_rate=mysqli_fetch_array(mysqli_query($link, " SELECT a.`consultantdoctorid`,a.`Name`,a.`opd_visit_fee` AS `m_visit`,a.`opd_reg_fee` AS `m_regd`,b.`visit_fee` AS `c_visit`,b.`reg_fee` AS `c_regd` FROM `consultant_doctor_master` a,`opd_doc_rate` b WHERE a.`consultantdoctorid`=b.`consultantdoctorid` AND a.`consultantdoctorid`='$d1[consultantdoctorid]' AND b.`centreno`='$center_no' "));
				if($centre_rate)
				{
					if($visitt_fee>0)
					{
						$visitt_fee=$centre_rate["c_visit"];
					}
					if($regdd_fee>0)
					{
						$regdd_fee=$centre_rate["c_regd"];
					}
				}
				
				if($visitt_fee>0)
				{
					if($day_number==1) // Sunday=1, Monday=2,.....
					{
						$check_extra=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `consultant_doctor_sunday_fee` WHERE `consultantdoctorid`='$d1[consultantdoctorid]'"));
						if($check_extra)
						{
							$visitt_fee=$check_extra["opd_visit_fee"];
							$regdd_fee=$check_extra["opd_reg_fee"];
						}
					}
				}
				
				// Check Extra Visit Fee
				$not_session = array();
				$time_now=date("H:i:s");
				$check_extra=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `consultant_doctor_extra_fee` WHERE `consultantdoctorid`='$d1[consultantdoctorid]' AND '$time_now'>=`timer_one` AND '$time_now'<`timer_two` "));
				if($check_extra)
				{
					$visitt_fee=$check_extra["opd_visit_fee"];
				}else
				{
					array_push($not_session, 1); // Add Session One
					$not_session = join(',',$not_session);
					
					$check_extra=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `consultant_doctor_extra_fee` WHERE `consultantdoctorid`='$d1[consultantdoctorid]' AND '$time_now'>=`timer_one` AND `session` NOT IN($not_session) "));
					if($check_extra)
					{
						$visitt_fee=$check_extra["opd_visit_fee"];
					}else
					{
						$check_extra=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `consultant_doctor_extra_fee` WHERE `consultantdoctorid`='$d1[consultantdoctorid]' AND '$time_now'<=`timer_two` AND `session` NOT IN($not_session) "));
						if($check_extra)
						{
							$visitt_fee=$check_extra["opd_visit_fee"];
						}
					}
				}
			}
		?>
			<tr onClick="addoc_load('<?php echo $d1['consultantdoctorid'];?>','<?php echo $d1['Name'];?>','<?php echo $visitt_fee;?>','<?php echo $regdd_fee;?>','<?php echo $visit_type_id;?>')" style="cursor:pointer" <?php echo "id=addoc".$i;?>>
				<td>
					<?php echo $d1['consultantdoctorid'];?>
				</td>
				<td>
					<?php echo $d1['Name'];?>
					<div <?php echo "id=addvdoc".$i;?> style="display:none;">
						<?php echo "#".$d1['consultantdoctorid']."#".$d1['Name']."#".$visitt_fee."#".$regdd_fee."#".$visit_type_id;?>
					</div>
				</td>
			</tr>
		<?php
			$i++;
		}
		if($d1_num==0)
		{
			echo "<tr><td colspan='2'>No Doctor Available</td></tr>";
		}
?>
	</table>
<?php
}

if($type=="load_ref_doctor")
{
	$dname=$_POST['val'];

?>
	<table style="background-color:#FFF" border="1" class="sec_table table table-bordered" id="center_table" width="600px">
	<th>ID</th><th>Doctor Name</th>
<?php
	
	if($dname)
	{

		$d=mysqli_query($link, "select * from refbydoctor_master where ref_name like '%$dname%'  order by ref_name");
	}
	else
	{
		$d=mysqli_query($link, "select * from refbydoctor_master where ref_name='Self' order by ref_name");
	}
	$i=1;
	while($d1=mysqli_fetch_array($d))
	{
?>
		<tr onclick="doc_load('<?php echo $d1['refbydoctorid'];?>','<?php echo $d1['ref_name'];?>','<?php echo $spec['Name'];?>')" style="cursor:pointer" <?php echo "id=doc".$i;?>><td>
		<?php echo $d1['refbydoctorid'];?></td><td><?php echo $d1['ref_name'].' / '.$d1['qualification'];?>
		<div <?php echo "id=dvdoc".$i;?> style="display:none;">
		<?php echo "#".$d1['refbydoctorid']."#".$d1['ref_name']."#".$d1['Name'];?>
		</div>
		</td></tr>
<?php
		$i++;
	}
?>
	</table>
<?php
}

if($type=="load_payment_info")
{
	$patient_id=$_POST["patient_id"];
	$opd_id=$_POST["opd_id"];
	
	$discount_attribute="";
	if($emp_access["discount_permission"]==0)
	{
		$discount_attribute="readonly";
	}
	
	$save_element_style="";
	if($patient_id==0)
	{
		
	}
	
	if($opd_id=="0" || $opd_id=="")
	{
		$save_type_str="Save";
		$transaction_table_style="display:none;";
		$save_element_style="display:none;";
		$operation_str="";
	}
	else
	{
		$save_type_str="Update";
		$transaction_table_style="";
		
		$operation_str=" AND `operation`=1";
	}
	
	$pat_pay_det=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `consult_patient_payment_details` WHERE `patient_id`='$patient_id' and `opd_id`='$opd_id' "));
	
	$bill_amount=$pat_pay_det["tot_amount"];
	
	$discount_amount_master=$pat_pay_det["dis_amt"];
	$paid_amount_master=$pat_pay_det["advance"];
	$due_amount_master=$pat_pay_det["balance"];
	
	$pay_det=mysqli_fetch_array(mysqli_query($link, " SELECT ifnull(SUM(`amount`),0) AS `paid_amount`,ifnull(SUM(`discount_amount`),0) AS `dis_amount`,ifnull(SUM(`refund_amount`),0) AS `ref_amount`,ifnull(SUM(`tax_amount`),0) AS `tax_amount` FROM `payment_detail_all` WHERE `patient_id`='$patient_id' and `opd_id`='$opd_id' "));
	
	$paid_amount=$pay_det["paid_amount"];
	$discount_amount=$pay_det["dis_amount"];
	$tax_amount=$pay_det["tax_amount"];
	$refund_amount=$pay_det["ref_amount"];
	
	$due_amount=$bill_amount-$paid_amount-$discount_amount-$tax_amount+$refund_amount;
	
?>
	<table class="table table-condensed" style="<?php echo $transaction_table_style; ?>">
		<tr>
			<th>#</th>
			<th>UHID</th>
			<th><?php echo $prefix_name; ?></th>
			<th>Transaction No</th>
			<th>Amount</th>
			<th>Discount</th>
			<th>Refund</th>
			<th>Payment Type</th>
			<th>Payment Mode</th>
			<th>Date-Time</th>
			<th>User</th>
		</tr>
	<?php
		$zz=1;
		$payment_det_qry=mysqli_query($link, "SELECT * FROM `payment_detail_all` WHERE `patient_id`='$patient_id' AND `opd_id`='$opd_id' AND `payment_mode`!='Credit' ORDER BY `pay_id` ASC");
		$payment_det_num=mysqli_num_rows($payment_det_qry);
		while($payment_det=mysqli_fetch_array($payment_det_qry))
		{
			$pay_mode_type=mysqli_fetch_array(mysqli_query($link, "SELECT `operation` FROM `payment_mode_master` WHERE `p_mode_name`='$payment_det[payment_mode]'"));
			
			$user_info=mysqli_fetch_array(mysqli_query($link, "SELECT `name` FROM `employee` WHERE `emp_id`='$payment_det[user]'"));
	?>
			<tr id="opd_trans<?php echo $zz; ?>">
				<td><?php echo $zz; ?></td>
				<td><?php echo $payment_det["patient_id"]; ?></td>
				<td><?php echo $payment_det["opd_id"]; ?></td>
				<td><?php echo $payment_det["transaction_no"]; ?></td>
				<td><?php echo $payment_det["amount"]; ?></td>
				<td><?php echo $payment_det["discount_amount"]; ?></td>
				<td><?php echo $payment_det["refund_amount"]; ?></td>
				<td><?php echo $payment_det["payment_type"]; ?></td>
				<td>
					<select class="span1" id="opd_payment_mode_trans<?php echo $payment_det["pay_id"]; ?>" onchange="payment_mode_change_trans('<?php echo $payment_det["pay_id"]; ?>')">
					<?php
						$pay_mode_master_qry=mysqli_query($link, "SELECT `p_mode_name` FROM `payment_mode_master` WHERE `operation`='$pay_mode_type[operation]' ORDER BY `sequence` ASC");
						while($pay_mode_master=mysqli_fetch_array($pay_mode_master_qry))
						{
							if($pay_mode_master["p_mode_name"]==$payment_det["payment_mode"]){ $sel="selected"; }else{ $sel=""; }
							echo "<option value='$pay_mode_master[p_mode_name]' $sel>$pay_mode_master[p_mode_name]</option>";
						}
					?>
					</select>
					<br>
					<input type="hidden" class="span2" id="opd_cheque_ref_no<?php echo $payment_det["pay_id"]; ?>" value="<?php echo $payment_det["cheque_ref_no"]; ?>" placeholder="cheque_ref_no">
				</td>
				<td><?php echo date("d-M-Y", strtotime($payment_det["date"])); ?> - <?php echo date("h:i A", strtotime($payment_det["time"])); ?></td>
				<td>
					<?php echo $user_info["name"]; ?>
					<button class="btn btn-print btn-mini" style="float:right;" onclick="print_transaction('<?php echo $payment_det["pay_id"]; ?>')"><i class="icon-print"></i></button>
			<?php
				if($payment_det_num==1 && $payment_det["payment_type"]=="Advance")
				{
					if($emp_access["edit_payment"]==1){
			?>
					<button class="btn btn-edit btn-mini" style="float:right;" onclick="edit_receipt('<?php echo $payment_det["pay_id"]; ?>')"><i class="icon-edit"></i></button>
			<?php
					}
				}
			?>
			<?php
				if($payment_det_num==$zz && $payment_det_num>1 && $payment_det["payment_type"]=="Balance")
				{
					if($emp_access["edit_payment"]==1){
			?>
					<button class="btn btn-delete btn-mini" style="float:right;" onclick="delete_receipt('<?php echo $payment_det["pay_id"]; ?>')"><i class="icon-remove"></i></button>
			<?php
					}
				}
			?>
				</td>
			</tr>
	<?php
			$zz++;
		}
	?>
	</table>
	<div id="advance_paid_div" style="display:none;">
		
	</div>
	<div id="res_payment_div">
		<table class="table table-condensed">
			<tr>
				<th style="width: 25%;">Total Bill Amount</th>
				<td>
					<span id="opd_bill_amount_str"><?php echo $bill_amount; ?></span>
					<input type="hidden" id="opd_bill_amount" value="<?php echo $bill_amount; ?>">
					<input type="hidden" id="opd_bill_amount_old" value="<?php echo $bill_amount; ?>">
				</td>
			</tr>
			<tr style="<?php echo $save_element_style; ?>">
				<th>Discount Amount</th>
				<td>
					<span id="opd_disount_amount_str"><?php echo $discount_amount; ?></span>
					<input type="hidden" id="opd_disount_amount" value="<?php echo $discount_amount; ?>">
				</td>
			</tr>
			<tr style="<?php echo $save_element_style; ?>">
				<th>Paid Amount</th>
				<td>
					<span id="opd_paid_amount_str"><?php echo $paid_amount; ?></span>
					<input type="hidden" id="opd_paid_amount" value="<?php echo $paid_amount; ?>">
				</td>
			</tr>
	<?php
		if($refund_amount>0)
		{
	?>
			<tr>
				<th>Refunded Amount</th>
				<td>
					<span id="opd_refunded_amount_str"><?php echo $refund_amount; ?></span>
					<input type="hidden" id="opd_refunded_amount" value="<?php echo $refund_amount; ?>">
				</td>
			</tr>
	<?php
		}
	?>
			<tr style="<?php echo $save_element_style; ?>">
				<th>Balance Amount</th>
				<td>
					<span id="opd_balance_amount_str"><?php echo number_format($due_amount,2); ?></span>
					<input type="hidden" id="opd_balance_amount" value="<?php echo $due_amount; ?>">
					<input type="hidden" id="opd_balance_amount_old" value="<?php echo $due_amount; ?>">
				</td>
			</tr>
			<tr id="now_discount_tr">
				<th>Now Discount</th>
				<td>
					<input type="text" class="span1 numericcfloat discount_cls" id="opd_now_discount_per" value="0" onkeyup="opd_discount_per(event)" placeholder="%" onpaste="return false;" ondrop="return false;" maxlength="4" <?php echo $discount_attribute; ?>>%
					
					<input type="text" class="span1 numericc discount_cls" id="opd_now_discount_amount" value="0" onkeyup="opd_discount_amount(event)" onpaste="return false;" ondrop="return false;" <?php echo $discount_attribute; ?>>INR
					<br>
					<input type="hidden" class="span2" id="opd_now_discount_reason" onkeyup="opd_now_discount_reason(event)" value="" placeholder="Discount Reason">
				</td>
			</tr>
			<tr id="opd_now_pay_tr">
				<th>Now Pay</th>
				<td>
					<input type="text" class="span2 numericc" id="opd_now_pay" value="0" onkeyup="opd_now_pay(event)" onpaste="return false;" ondrop="return false;">
				</td>
			</tr>
			<tr id="opd_now_refund_tr" style="color:red;display:none;">
				<th>Amount To Refund</th>
				<td>
					<b id="opd_now_refund_str">0.00</b>
					<input type="hidden" class="span2" id="opd_now_refund" value="0" disabled>
				</td>
			</tr>
			<tr id="opd_now_payment_mode_tr">
				<th>Payment Mode</th>
				<td>
					<select class="span2" id="opd_now_payment_mode" onchange="opd_payment_mode_change(this.value)" onkeyup="opd_payment_mode_up(event)">
				<?php
					$pay_mode_master_qry=mysqli_query($link, "SELECT `p_mode_name` FROM `payment_mode_master` WHERE `p_mode_name`!='' $operation_str ORDER BY `sequence` ASC");
					while($pay_mode_master=mysqli_fetch_array($pay_mode_master_qry))
					{
						echo "<option value='$pay_mode_master[p_mode_name]'>$pay_mode_master[p_mode_name]</option>";
					}
				?>
					</select>
					<br>
					<!--<span id="opd_now_balance_reason_str" style="display:none;">
						<input type="text" class="span2" id="opd_now_balance_reason" value="" placeholder="Credit Reason">
					</span>-->
					<input type="hidden" class="span1" id="opd_now_ref_field">
					<input type="hidden" class="span1" id="opd_now_operation">
				</td>
			</tr>
			<tr class="opd_now_balance_tr" style="display:none;">
				<th>Now Balanace</th>
				<td>
					<span id="opd_now_balance">0</span>
				</td>
			</tr>
			<tr class="opd_now_balance_tr" style="display:none;">
				<th>Balance Reason</th>
				<td>
					<input type="text" class="span2" id="now_balance_reason" onkeyup="now_balance_reason(event)">
				</td>
			</tr>
			<tr id="opd_now_cheque_ref_no_tr" style="display:none;">
				<th>Cheque/Reference No</th>
				<td>
					<input type="text" class="span2" id="opd_now_cheque_ref_no" onkeyup="opd_now_cheque_ref_no(event)">
				</td>
			</tr>
			<tr id="save_tr">
				<td colspan="2" style="text-align:center;">
			<?php
				$doc_pay_num=mysqli_num_rows(mysqli_query($link," SELECT * FROM `payment_settlement_doc` WHERE `patient_id`='$patient_id' AND `opd_id`='$opd_id' "));
				if($doc_pay_num==0){
			?>
					<button class="btn btn-save" id="pat_save_btn" onClick="pat_save()"><i class="icon-save"></i> <?php echo $save_type_str; ?></button>
					<input type="hidden" class="span1" id="save_type" value="<?php echo $save_type_str; ?>">
			<?php } ?>
				<?php if($patient_id!="0" && $opd_id!="0"){ ?>
					<button class="btn btn-print" id="print_con_receipt_btn" onClick="print_receipt('pages/print_consulant_receipt_new.php?v=0')"><i class="icon-print"></i> Consultation Receipt</button>
					
					<button class="btn btn-print" id="print_con_bill_btn" onClick="print_receipt('pages/print_consulant_receipt_bill_new.php?v=1')"><i class="icon-print"></i> Bill</button>
					
					<button class="btn btn-print" id="opd_prescription_btn" onClick="print_receipt('pages/prescription_rpt_new.php?v=0')"><i class="icon-print"></i> Prescription</button>
					
					<!--<button class="btn btn-print print_assess lite" id="opd_assess_btn" onClick="print_receipt('pages/opd_assessment_new.php?v=0')"><i class="icon-print"></i> Assessment Form</button>-->
				<?php } ?>
					<button class="btn btn-new" id="opd_new_reg_btn" onclick="new_registration()"><i class="icon-edit"></i> New Registration</button>
				</td>
			</tr>
		</table>
	</div>
	
<?php
}

if($type=="pat_save")
{
	//print_r($_POST);
	
	$user=mysqli_real_escape_string($link, $_POST["user"]);
	
	$branch_id=mysqli_real_escape_string($link, $_POST["branch_id"]);
	$center_no=mysqli_real_escape_string($link, $_POST["center_no"]);
	
	$save_type=mysqli_real_escape_string($link, $_POST["save_type"]);
	$p_type_id=mysqli_real_escape_string($link, $_POST["p_type_id"]);
	
	$patient_id=mysqli_real_escape_string($link, $_POST["patient_id"]);
	$opd_id=mysqli_real_escape_string($link, $_POST["opd_id"]);
	
	$pat_reg_type=mysqli_real_escape_string($link, $_POST["pat_reg_type"]);
	
	$source_id=mysqli_real_escape_string($link, $_POST["patient_type"]);
	$name_title=mysqli_real_escape_string($link, $_POST["name_title"]);
	$pat_name=mysqli_real_escape_string($link, $_POST["pat_name"]);
	
	$pat_name_full=trim($name_title." ".$pat_name);
	
	$sex=mysqli_real_escape_string($link, $_POST["sex"]);
	$dob=mysqli_real_escape_string($link, $_POST["dob"]);
	$phone=mysqli_real_escape_string($link, $_POST["phone"]);
	$marital_status=mysqli_real_escape_string($link, $_POST["marital_status"]);
	$email=mysqli_real_escape_string($link, $_POST["email"]);
	$father_name=mysqli_real_escape_string($link, $_POST["father_name"]);
	$mother_name=mysqli_real_escape_string($link, $_POST["mother_name"]);
	$gd_name=mysqli_real_escape_string($link, $_POST["gd_name"]);
	$g_relation=mysqli_real_escape_string($link, $_POST["g_relation"]);
	$gd_phone=mysqli_real_escape_string($link, $_POST["gd_phone"]);
	$income_id=mysqli_real_escape_string($link, $_POST["income_id"]);
	$state=mysqli_real_escape_string($link, $_POST["state"]);
	$district=mysqli_real_escape_string($link, $_POST["district"]);
	$city=mysqli_real_escape_string($link, $_POST["city"]);
	$police=mysqli_real_escape_string($link, $_POST["police"]);
	$post_office=mysqli_real_escape_string($link, $_POST["post_office"]);
	$pin=mysqli_real_escape_string($link, $_POST["pin"]);
	$address=mysqli_real_escape_string($link, $_POST["address"]);
	
	$refbydoctorid=mysqli_real_escape_string($link, $_POST["refbydoctorid"]);
	$dept_id=mysqli_real_escape_string($link, $_POST["dept_id"]);
	$hguide_id=mysqli_real_escape_string($link, $_POST["hguide_id"]);
	//$sel_center=mysqli_real_escape_string($link, $_POST["sel_center"]);
	$appoint_date=mysqli_real_escape_string($link, $_POST["appoint_date"]);
	$consultantdoctorid=mysqli_real_escape_string($link, $_POST["consultantdoctorid"]);
	$doctor_session=mysqli_real_escape_string($link, $_POST["doctor_session"]);
	$visit_type_id=mysqli_real_escape_string($link, $_POST["visit_type_id"]);
	
	$visit_fee=mysqli_real_escape_string($link, $_POST["visit_fee"]);
	$regd_fee=mysqli_real_escape_string($link, $_POST["regd_fee"]);
	$total=mysqli_real_escape_string($link, $_POST["total"]);
	
	$discount_amount=mysqli_real_escape_string($link, $_POST["now_discount"]);
	$discount_reason=mysqli_real_escape_string($link, $_POST["opd_now_discount_reason"]);
	$now_pay=mysqli_real_escape_string($link, $_POST["opd_now_pay"]);
	$payment_mode=mysqli_real_escape_string($link, $_POST["opd_now_payment_mode"]);
	$balance_reason=mysqli_real_escape_string($link, $_POST["now_balance_reason"]);
	$cheque_ref_no=mysqli_real_escape_string($link, $_POST["opd_now_cheque_ref_no"]);
	
	if(!$urgent){ $urgent=0; }
	
	$sel_center=$center_no;
	
	$pat_source=mysqli_fetch_array(mysqli_query($link, " SELECT `source_id` FROM `patient_source_master` WHERE `centreno`='$center_no' "));
	if($pat_source)
	{
		$source_id=$pat_source["source_id"];
	}
	else
	{
		$source_id=1;
	}
	
	// Insurance card no( if any)
	$card_id=0;
	
	$emergency_fee=0;
	$pat_emergency=0;
	$cross_consult=0;
	
	if(!$hguide_id)
	{
		$hguide_id=101; // Self
	}
	
	$blood_group="";
	$credit="";
	$fileno="";
	$esi_ip_no="";
	
	if(!$ptype){ $ptype=0; }
	if(!$refbydoctorid){ $refbydoctorid=0; }
	if(!$crno){ $crno=0; }
	if(!$state){ $state=0; }
	if(!$district){ $district=0; }
	if(!$income_id){ $income_id=0; }
	if(!$dept_id){ $dept_id=0; }
	if(!$consultantdoctorid){ $consultantdoctorid=0; }
	if(!$doctor_session){ $doctor_session=0; }
	if(!$visit_type_id){ $visit_type_id=0; }
	if(!$visit_fee){ $visit_fee=0; }
	if(!$regd_fee){ $regd_fee=0; }
	if(!$total){ $total=0; }
	if(!$discount_amount){ $discount_amount=0; }
	if(!$now_pay){ $now_pay=0; }
	
	$appoint_day =convert_date_to_day_num($appoint_date);
	
	$dis_per=round(($discount_amount*100)/$total,2);

	$balance=$total-$now_pay-$discount_amount;
	
	$refund_amount=0;
	$refund_reason="";
	$tax_amount=0;
	$tax_reason="";
	
	if($total==0)
	{
		$payment_mode="Cash";
		
		$balance=0;
		$now_pay=0;
		$discount_amount=0;
		$dis_per=0;
		$refund_amount=0;
		$tax_amount=0;
	}
	
	$emp_access=mysqli_fetch_array(mysqli_query($link, " SELECT `edit_info`,`edit_payment`,`cancel_pat`,`discount_permission` FROM `employee` WHERE `emp_id`='$user' "));
	$emp_access_str="#".$emp_access["edit_info"]."#".$emp_access["edit_payment"]."#".$emp_access["cancel_pat"]."#".$emp_access["discount_permission"]."#";
	
	include("patient_info_save.php");
	
	if($patient_id=="0")
	{
		echo $patient_id."@".$opd_id."@".$emp_access_str."@Failed, try again later.";
	}
	else
	{
		if($opd_id=="0")
		{
			// Save
			
			$alrdy_appoint_num=mysqli_num_rows(mysqli_query($link, " SELECT `appointment_no` FROM `appointment_book` WHERE `patient_id`='$patient_id' and `consultantdoctorid`='$consultantdoctorid' and `appointment_date`='$appoint_date' "));
			$alrdy_appoint_num=0;
			if($alrdy_appoint_num==0)
			{
				if(mysqli_query($link, " INSERT INTO `uhid_and_opdid`(`patient_id`, `opd_id`, `date`, `time`, `user`, `type`, `ipd_serial`, `refbydoctorid`, `center_no`, `hguide_id`, `branch_id`) VALUES ('$patient_id',NULL,'$date','$time','$user','$p_type_id','','$refbydoctorid','$sel_center','$hguide_id','$branch_id') "))
				{
					$last_row=mysqli_fetch_array(mysqli_query($link, " SELECT `slno` FROM `uhid_and_opdid` WHERE `patient_id`='$patient_id' AND `date`='$date' AND `time`='$time' AND `user`='$user' AND `type`='$p_type_id' AND `refbydoctorid`='$refbydoctorid' AND `hguide_id`='$hguide_id' ORDER BY `slno` DESC LIMIT 0,1 "));
	
					$last_row_num=$last_row["slno"];
					
					$patient_reg_type=$p_type_id;
					include("opd_id_generator.php");
					
					if(mysqli_query($link," UPDATE `uhid_and_opdid` SET `opd_id`='$opd_id',`ipd_serial`='$opd_serial' WHERE `slno`='$last_row_num' "))
					{
						$appnt_qry=mysqli_fetch_array(mysqli_query($link, " SELECT max(`appointment_no`) as mx FROM `appointment_book` WHERE `consultantdoctorid`='$consultantdoctorid' AND `appointment_date`='$appoint_date' AND doctor_session='$doctor_session' "));
						$appnt_num=$appnt_qry["mx"];
						if($appnt_num==0)
						{
							$appoint_no=1;
						}else
						{
							$appoint_no=$appnt_num+1;
						}
						
						mysqli_query($link, " INSERT INTO `appointment_book`(`patient_id`, `opd_id`, `dept_id`, `consultantdoctorid`, `appointment_date`, `appointment_day`, `appointment_no`, `user`, `date`, `time`, `emergency`, `visit_fee`, `doctor_session`) VALUES ('$patient_id','$opd_id','$dept_id','$consultantdoctorid','$appoint_date','$appoint_day','$appoint_no','$user','$date','$time','$pat_emergency','$visit_fee','$doctor_session') ");
						
						// Cross Consultation
						if($cross_consult>0)
						{
							$check_double_entry_cross_consult=mysqli_num_rows(mysqli_query($link, " SELECT * FROM `cross_consultation` WHERE `patient_id`='$patient_id' and `opd_id`='$opd_id' "));
							if($check_double_entry_cross_consult==0)
							{
								mysqli_query($link, " INSERT INTO `cross_consultation`(`patient_id`, `opd_id`, `amount`, `user`, `date`, `time`) VALUES ('$patient_id','$opd_id','$cross_consult_fee','$user','$date','$time') ");
							}
						}
						
						// Regd Fee Record
						if($regd_fee>0)
						{
							//mysqli_query($link, " INSERT INTO `pat_regd_fee`(`patient_id`,`opd_id`, `regd_fee`, `date`, `time`) VALUES ('$patient_id','$opd_id','$regd_fee','$date','$time') ");
							mysqli_query($link, " INSERT INTO `pat_regd_fee`(`patient_id`,`opd_id`, `consultantdoctorid`, `regd_fee`, `date`, `time`) VALUES ('$patient_id','$opd_id','$consultantdoctorid','$regd_fee','$date','$time') ");
						}
						if($emergency_fee>0)
						{
							//mysqli_query($link, " INSERT INTO `pat_regd_fee`(`patient_id`,`opd_id`, `regd_fee`, `date`, `time`) VALUES ('$patient_id','$opd_id','$emergency_fee','$date','$time') ");
							mysqli_query($link, " INSERT INTO `pat_regd_fee`(`patient_id`,`opd_id`, `consultantdoctorid`, `regd_fee`, `date`, `time`) VALUES ('$patient_id','$opd_id','$consultantdoctorid','$emergency_fee','$date','$time') ");
						}
						
						mysqli_query($link, " INSERT INTO `consult_patient_payment_details`(`patient_id`, `opd_id`, `visit_fee`, `regd_fee`, `emergency_fee`, `tot_amount`, `dis_per`, `dis_amt`, `dis_reason`, `advance`, `refund_amount`, `tax_amount`, `balance`, `bal_reason`, `date`, `time`, `user`) VALUES ('$patient_id','$opd_id','$visit_fee','$regd_fee','$emergency_fee','$total','$dis_per','$discount_amount','$discount_reason','$now_pay','$refund_amount','$tax_amount','$balance','$balance_reason','$date','$time','$user') ");
						
						// payment_detail_all
				
						$check_double_entry_pay_detail=mysqli_num_rows(mysqli_query($link, " SELECT * FROM `payment_detail_all` WHERE `patient_id`='$patient_id' and `opd_id`='$opd_id' "));
						if($check_double_entry_pay_detail==0)
						{
							if($payment_mode=="Credit")
							{
								$now_pay=0;
								$balance=$total-$now_pay-$discount_amount;
							}
							if($now_pay==0)
							{
								$payment_mode="Credit";
								if($total==0)
								{
									$payment_mode="Cash";
									
									$balance=0;
									$now_pay=0;
									$discount_amount=0;
									$dis_per=0;
									$refund_amount=0;
									$tax_amount=0;
								}
								else if($discount_amount==0)
								{
									$pay_mode="Credit";
								}
								else
								{
									$pay_mode="Cash";
								}
							}
							
							if($now_pay>0 && $balance>0)
							{
								if($now_pay>0)
								{
									$check_paid=mysqli_fetch_array(mysqli_query($link, " SELECT ifnull(SUM(`amount`),0) AS `paid` FROM `payment_detail_all` WHERE `patient_id`='$patient_id' and `opd_id`='$opd_id' "));
									$already_paid=$check_paid["paid"];
									
									$bill_no=generate_bill_no_new($bill_name,$p_type_id);
									$balance_now=0;
									$balance_reason_now="";
									
									mysqli_query($link, " INSERT INTO `payment_detail_all`(`patient_id`, `opd_id`, `transaction_no`, `bill_amount`, `already_paid`, `amount`, `discount_amount`, `discount_reason`, `refund_amount`, `refund_reason`, `tax_amount`, `tax_reason`, `balance_amount`, `balance_reason`, `payment_type`, `payment_mode`, `cheque_ref_no`, `user`, `date`, `time`, `encounter`) VALUES ('$patient_id','$opd_id','$bill_no','$total','$already_paid','$now_pay','$discount_amount','$discount_reason','$refund_amount','$refund_reason','$tax_amount','$tax_reason','$balance','$balance_reason','Advance','$payment_mode','$cheque_ref_no','$user','$date','$time','$p_type_id') ");
									
									$discount_amount=0;
									$discount_reason="";
									$cheque_ref_no="";
									$tax_amount=0;
									$tax_reason="";
								}
								if($balance>0)
								{
									$check_paid=mysqli_fetch_array(mysqli_query($link, " SELECT ifnull(SUM(`amount`),0) AS `paid` FROM `payment_detail_all` WHERE `patient_id`='$patient_id' and `opd_id`='$opd_id' "));
									$already_paid=$check_paid["paid"];
									
									$bill_no=generate_bill_no_new($bill_name,$p_type_id);
									$now_pay_now=0;
									$payment_mode="Credit";
									
									mysqli_query($link, " INSERT INTO `payment_detail_all`(`patient_id`, `opd_id`, `transaction_no`, `bill_amount`, `already_paid`, `amount`, `discount_amount`, `discount_reason`, `refund_amount`, `refund_reason`, `tax_amount`, `tax_reason`, `balance_amount`, `balance_reason`, `payment_type`, `payment_mode`, `cheque_ref_no`, `user`, `date`, `time`, `encounter`) VALUES ('$patient_id','$opd_id','$bill_no','$total','$already_paid','$now_pay_now','$discount_amount','$discount_reason','$refund_amount','$refund_reason','$tax_amount','$tax_reason','$balance','$balance_reason','Advance','$payment_mode','$cheque_ref_no','$user','$date','$time','$p_type_id') ");
								}
							}
							else
							{
								$check_paid=mysqli_fetch_array(mysqli_query($link, " SELECT ifnull(SUM(`amount`),0) AS `paid` FROM `payment_detail_all` WHERE `patient_id`='$patient_id' and `opd_id`='$opd_id' "));
								$already_paid=$check_paid["paid"];
								
								$bill_no=generate_bill_no_new($bill_name,$p_type_id);
								
								mysqli_query($link, " INSERT INTO `payment_detail_all`(`patient_id`, `opd_id`, `transaction_no`, `bill_amount`, `already_paid`, `amount`, `discount_amount`, `discount_reason`, `refund_amount`, `refund_reason`, `tax_amount`, `tax_reason`, `balance_amount`, `balance_reason`, `payment_type`, `payment_mode`, `cheque_ref_no`, `user`, `date`, `time`, `encounter`) VALUES ('$patient_id','$opd_id','$bill_no','$total','$already_paid','$now_pay','$discount_amount','$discount_reason','$refund_amount','$refund_reason','$tax_amount','$tax_reason','$balance','$balance_reason','Advance','$payment_mode','$cheque_ref_no','$user','$date','$time','$p_type_id') ");
							}
						}
						
						// Check double entry
						$pay_mode_qry=mysqli_query($link," SELECT `p_mode_name` FROM `payment_mode_master` ORDER BY `p_mode_name` ASC ");
						while($pay_mode=mysqli_fetch_array($pay_mode_qry))
						{
							$cash_adv_pay_qry=mysqli_query($link," SELECT * FROM `payment_detail_all` WHERE `patient_id`='$patient_id' AND `opd_id`='$opd_id' AND `payment_type`='Advance' AND `payment_mode`='$pay_mode[p_mode_name]' ");
							$cash_adv_pay_num=mysqli_num_rows($cash_adv_pay_qry);

							if($cash_adv_pay_num>1)
							{
								$h=1;
								while($cash_adv_pay_val=mysqli_fetch_array($cash_adv_pay_qry))
								{
									if($h>1)
									{
										$check_pay_mode_change=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `payment_mode_change` WHERE `patient_id`='$patient_id' and `ipd_id`='$opd_id' "));
										if(!$check_pay_mode_change)
										{
											mysqli_query($link," DELETE FROM `payment_detail_all` WHERE `pay_id`='$cash_adv_pay_val[pay_id]' ");
										}
									}
									$h++;
								}
							}
						}
						
						if($discount_amount>0)
						{
							// Discount Approve
							mysqli_query($link, " INSERT INTO `discount_approve`(`patient_id`, `pin`, `bill_amount`, `dis_amount`, `reason`, `user`, `approve_by`, `date`, `time`) VALUES ('$patient_id','$opd_id','$total','$discount_amount','$discount_reason','$user','0','$date','$time') ");
						}
						
						if($visit_type_id>0)
						{
							mysqli_query($link, " INSERT INTO `patient_visit_type_details`(`patient_id`, `opd_id`, `p_type_id`, `visit_type_id`,`date`,`time`) VALUES ('$patient_id','$opd_id','$p_type_id','$visit_type_id','$date','$time') ");
						}
						
						
						// Insurance card no( if any)
						if($card_id>0)
						{
							mysqli_query($link, " INSERT INTO `patient_card_details`(`patient_id`, `opd_id`, `card_id`, `card_no`, `card_details`) VALUES ('$patient_id','$opd_id','$card_id','$card_no','$card_details') ");
						}
						
						echo $patient_id."@".$opd_id."@".$emp_access_str."@Saved";
						
					}
					else
					{
						mysqli_query($link," DELETE FROM `uhid_and_opdid` WHERE `slno`='$last_row_num' ");
						
						$opd_id=0;
						echo $patient_id."@".$opd_id."@".$emp_access_str."@Failed, try again later.";
					}
				}
				else
				{
					echo $patient_id."@".$opd_id."@".$emp_access_str."@Failed, try again later..";
				}
			}
			else
			{
				echo $patient_id."@".$opd_id."@".$emp_access_str."@Already got appointment with the doctor";
			}
		}
		else
		{
			// Edit Counter
			$edit_counter=mysqli_fetch_array(mysqli_query($link, " SELECT count(`counter`) as cntr FROM `edit_counter` WHERE `patient_id`='$patient_id' AND `opd_id`='$opd_id' AND `type`='$p_type_id' "));
			$edit_counter_num=$edit_counter["cntr"];
			$counter_num=$edit_counter_num+1;
			
			// edit counter record
			mysqli_query($link, " INSERT INTO `edit_counter`(`patient_id`, `opd_id`, `date`, `time`, `user`, `type`, `counter`) VALUES ('$patient_id','$opd_id','$date','$time','$user','$p_type_id','$counter_num') ");
			
			// Consulting info
			$pat_reg=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `uhid_and_opdid` WHERE `patient_id`='$patient_id' and `opd_id`='$opd_id' "));
			
			mysqli_query($link, " INSERT INTO `uhid_and_opdid_edit`(`patient_id`, `opd_id`, `date`, `time`, `user`, `type`, `ipd_serial`, `refbydoctorid`, `center_no`, `hguide_id`, `branch_id`, `counter`) VALUES ('$pat_reg[patient_id]','$pat_reg[opd_id]','$pat_reg[date]','$pat_reg[time]','$pat_reg[user]','$pat_reg[type]','$pat_reg[ipd_serial]','$pat_reg[refbydoctorid]','$pat_reg[center_no]','$pat_reg[hguide_id]','$pat_reg[branch_id]','$counter_num') ");
			
			mysqli_query($link, " UPDATE `uhid_and_opdid` SET `refbydoctorid`='$refbydoctorid', `center_no`='$sel_center', `hguide_id`='$hguide_id', `branch_id`='$branch_id', `urgent`='$urgent' WHERE `patient_id`='$patient_id' and `opd_id`='$opd_id' ");
			
			if($emp_access["edit_payment"]==1)
			{
				// appointment_book_edit
				$check_double_entry_appointment=mysqli_num_rows(mysqli_query($link, " SELECT * FROM `appointment_book_edit` WHERE `patient_id`='$patient_id' and `opd_id`='$opd_id' and `counter`='$counter_num' "));
				if($check_double_entry_appointment==0)
				{
					$doc_consult=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `appointment_book` WHERE `patient_id`='$patient_id' and `opd_id`='$opd_id' "));
					
					mysqli_query($link, "  INSERT INTO `appointment_book_edit`(`patient_id`, `opd_id`, `dept_id`, `consultantdoctorid`, `appointment_date`, `appointment_day`, `appointment_no`, `user`, `date`, `time`, `emergency`, `visit_fee`, `doctor_session`, `counter`) VALUES ('$doc_consult[patient_id]','$doc_consult[opd_id]','$doc_consult[dept_id]','$doc_consult[consultantdoctorid]','$doc_consult[appointment_date]','$doc_consult[appointment_day]','$doc_consult[appointment_no]','$doc_consult[user]','$doc_consult[date]','$doc_consult[time]','$doc_consult[emergency]','$doc_consult[visit_fee]','$doc_consult[doctor_session]','$counter_num') ");
				}
				
				// Regd Fee Record
				$check_regd_fees=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `pat_regd_fee` WHERE `patient_id`='$patient_id' and `opd_id`='$opd_id' "));
				if($check_regd_fees)
				{
					//mysqli_query($link, " UPDATE `pat_regd_fee` SET `regd_fee`='$regd_fee' WHERE `patient_id`='$patient_id' and `opd_id`='$opd_id' ");
					mysqli_query($link, " UPDATE `pat_regd_fee` SET `consultantdoctorid`='$consultantdoctorid',`regd_fee`='$regd_fee' WHERE `patient_id`='$patient_id' and `opd_id`='$opd_id' ");
				}else
				{
					if($regd_fee>0)
					{
						//mysqli_query($link, " INSERT INTO `pat_regd_fee`(`patient_id`,`opd_id`, `regd_fee`, `date`, `time`) VALUES ('$patient_id','$opd_id','$regd_fee','$date','$time') ");
						mysqli_query($link, " INSERT INTO `pat_regd_fee`(`patient_id`,`opd_id`, `consultantdoctorid`, `regd_fee`, `date`, `time`) VALUES ('$patient_id','$opd_id','$consultantdoctorid','$regd_fee','$date','$time') ");
					}
					if($emergency_fee>0)
					{
						// Regd Fee Record
						//mysqli_query($link, " INSERT INTO `pat_regd_fee`(`patient_id`,`opd_id`, `regd_fee`, `date`, `time`) VALUES ('$patient_id','$opd_id','$emergency_fee','$date','$time') ");
						mysqli_query($link, " INSERT INTO `pat_regd_fee`(`patient_id`,`opd_id`, `consultantdoctorid`, `regd_fee`, `date`, `time`) VALUES ('$patient_id','$opd_id','$consultantdoctorid','$emergency_fee','$date','$time') ");
					}
				}
				
				//Cross Consultation
				if($cross_consult>0)
				{
					$check_double_entry_cross_consult=mysqli_num_rows(mysqli_query($link, " SELECT * FROM `cross_consultation` WHERE `patient_id`='$patient_id' and `opd_id`='$opd_id' "));
					if($check_double_entry_cross_consult==0)
					{
						$date=$appoint_date;
						mysqli_query($link, " INSERT INTO `cross_consultation`(`patient_id`, `opd_id`, `amount`, `user`, `date`, `time`) VALUES ('$patient_id','$opd_id','$cross_consult_fee','$user','$date','$time') ");
					}else
					{
						mysqli_query($link, " UPDATE `cross_consultation` SET `amount`='$cross_consult_fee' WHERE `patient_id`='$patient_id' and `opd_id`='$opd_id' ");
					}
				}else
				{
					mysqli_query($link, " DELETE FROM `cross_consultation` WHERE `patient_id`='$patient_id' and `opd_id`='$opd_id' ");
				}
				
				
				// Payment
				$con_pat_pay_detail=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `consult_patient_payment_details` WHERE `patient_id`='$patient_id' and `opd_id`='$opd_id' "));
				$earlier_bill_amount=$con_pat_pay_detail["tot_amount"];
				
				$bill_diff_amount=$total-$earlier_bill_amount;
				
				if($earlier_bill_amount!=$total)
				{
					// consult_patient_payment_details_edit
					$check_double_entry_pat_pay_detail=mysqli_num_rows(mysqli_query($link, " SELECT * FROM `consult_patient_payment_details_edit` WHERE `patient_id`='$patient_id' and `opd_id`='$opd_id' and `counter`='$counter_num' "));
					if($check_double_entry_pat_pay_detail==0)
					{
						$con_pat_pay_detail=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `consult_patient_payment_details` WHERE `patient_id`='$patient_id' and `opd_id`='$opd_id' "));
						$earlier_bill_amount=$con_pat_pay_detail["tot_amount"];
						
						mysqli_query($link, " INSERT INTO `consult_patient_payment_details_edit`(`patient_id`, `opd_id`, `visit_fee`, `regd_fee`, `emergency_fee`, `tot_amount`, `dis_per`, `dis_amt`, `dis_reason`, `advance`, `refund_amount`, `tax_amount`, `balance`, `bal_reason`, `date`, `time`, `user`, `counter`) VALUES ('$con_pat_pay_detail[patient_id]','$con_pat_pay_detail[opd_id]','$con_pat_pay_detail[visit_fee]','$con_pat_pay_detail[regd_fee]','$con_pat_pay_detail[emergency_fee]','$con_pat_pay_detail[tot_amount]','$con_pat_pay_detail[dis_per]','$con_pat_pay_detail[dis_amt]','$con_pat_pay_detail[dis_reason]','$con_pat_pay_detail[advance]','$con_pat_pay_detail[refund_amount]','$con_pat_pay_detail[tax_amount]','$con_pat_pay_detail[balance]','$con_pat_pay_detail[bal_reason]','$con_pat_pay_detail[date]','$con_pat_pay_detail[time]','$con_pat_pay_detail[user]','$counter_num') ");
					}
					
					// payment_detail_all_edit
					$check_double_entry_pay_detail=mysqli_num_rows(mysqli_query($link, " SELECT * FROM `payment_detail_all_edit` WHERE `patient_id`='$patient_id' AND `opd_id`='$opd_id' and `counter`='$counter_num' "));
					if($check_double_entry_pay_detail==0)
					{
						$payment_detail_all_qry=mysqli_query($link, " SELECT * FROM `payment_detail_all` WHERE `patient_id`='$patient_id' AND `opd_id`='$opd_id' ");
						while($payment_detail_all=mysqli_fetch_array($payment_detail_all_qry))
						{
							mysqli_query($link, " INSERT INTO `payment_detail_all_edit`(`pay_id`, `patient_id`, `opd_id`, `transaction_no`, `bill_amount`, `already_paid`, `amount`, `discount_amount`, `discount_reason`, `refund_amount`, `refund_reason`, `tax_amount`, `tax_reason`, `balance_amount`, `balance_reason`, `payment_type`, `payment_mode`, `cheque_ref_no`, `user`, `date`, `time`, `encounter`, `counter`) VALUES ('$payment_detail_all[pay_id]','$payment_detail_all[patient_id]','$payment_detail_all[opd_id]','$payment_detail_all[transaction_no]','$payment_detail_all[bill_amount]','$payment_detail_all[already_paid]','$payment_detail_all[amount]','$payment_detail_all[discount_amount]','$payment_detail_all[discount_reason]','$payment_detail_all[refund_amount]','$payment_detail_all[refund_reason]','$payment_detail_all[tax_amount]','$payment_detail_all[tax_reason]','$payment_detail_all[balance_amount]','$payment_detail_all[balance_reason]','$payment_detail_all[payment_type]','$payment_detail_all[payment_mode]','$payment_detail_all[cheque_ref_no]','$payment_detail_all[user]','$payment_detail_all[date]','$payment_detail_all[time]','$payment_detail_all[encounter]','$counter_num') ");
						}
					}
					
					$check_paid=mysqli_fetch_array(mysqli_query($link, " SELECT ifnull(SUM(`amount`),0) AS `paid`, ifnull(SUM(`discount_amount`),0) AS `discount`, ifnull(SUM(`refund_amount`),0) AS `refund`, ifnull(SUM(`tax_amount`),0) AS `tax` FROM `payment_detail_all` WHERE `patient_id`='$patient_id' and `opd_id`='$opd_id' "));
					
					$already_paid      =$check_paid["paid"];
					$already_discount  =$check_paid["discount"];
					$already_refund    =$check_paid["refund"];
					$already_tax       =$check_paid["tax"];
					
					$settle_amount=$already_paid+$already_discount+$already_tax-$already_refund;
					
					$net_paid=$already_paid-$already_refund;
					
					$payment_type="Balance";
					
					if($bill_diff_amount<0)
					{
						$bill_diff_amount_abs=abs($bill_diff_amount);
						
						if($settle_amount>$total)
						{
							$refund_amount  =$settle_amount-$total;
							$refund_reason  ="Bill amount has been reduced";
							
							$payment_type="Refund";
							
							$now_pay=0;
							$balance=0;
							$discount_amount=0;
						}
						else
						{
							$refund_amount=0;
							$refund_reason="";
							
							$amount_to_pay=$total-$settle_amount;
							
							$balance=$amount_to_pay-$now_pay-$discount_amount;
							if($balance<0)
							{
								echo $patient_id."@".$opd_id."@".$emp_access_str."@Wrong input1";
								exit();
							}
						}
					}
					else
					{
						$refund_amount=0;
						$refund_reason="";
						
						$amount_to_pay=$total-$settle_amount;
						
						$balance=$amount_to_pay-$now_pay-$discount_amount;
						if($balance<0)
						{
							echo $patient_id."@".$opd_id."@".$emp_access_str."@Wrong input2";
							exit();
						}
					}
					
					$total_paid=$already_paid+$now_pay-$refund_amount;;
					$total_discount=$already_discount+$discount_amount;
					$total_refund=$already_refund+$refund_amount;
					$total_tax=$already_tax+$tax_amount;
					
					$discount_percetage=round(($total_discount/$total)*100,2);
					
					mysqli_query($link, " UPDATE `consult_patient_payment_details` SET `visit_fee`='$visit_fee',`regd_fee`='$regd_fee',`emergency_fee`='$emergency_fee',`tot_amount`='$total',`dis_per`='$discount_percetage',`dis_amt`='$total_discount',`advance`='$total_paid',`refund_amount`='$total_refund',`tax_amount`='$total_tax',`balance`='$balance',`bal_reason`='$bal_reason' WHERE `patient_id`='$patient_id' and `opd_id`='$opd_id' ");
					
					$bill_no=generate_bill_no_new($bill_name,$p_type_id);
					
					mysqli_query($link, " INSERT INTO `payment_detail_all`(`patient_id`, `opd_id`, `transaction_no`, `bill_amount`, `already_paid`, `amount`, `discount_amount`, `discount_reason`, `refund_amount`, `refund_reason`, `tax_amount`, `tax_reason`, `balance_amount`, `balance_reason`, `payment_type`, `payment_mode`, `cheque_ref_no`, `user`, `date`, `time`, `encounter`) VALUES ('$patient_id','$opd_id','$bill_no','$total','$already_paid','$now_pay','$discount_amount','$discount_reason','$refund_amount','$refund_reason','$tax_amount','$tax_reason','$balance','$balance_reason','$payment_type','$payment_mode','$cheque_ref_no','$user','$date','$time','$p_type_id') ");
					
					if($balance>0)
					{
						$pay_det_credit=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `payment_detail_all` WHERE `patient_id`='$patient_id' and `opd_id`='$opd_id' AND `payment_mode`='Credit' "));
						if($pay_det_credit)
						{
							mysqli_query($link, " UPDATE `payment_detail_all` SET `balance_amount`='$balance',`balance_reason`='$balance_reason' WHERE `patient_id`='$patient_id' AND `opd_id`='$opd_id' AND `payment_mode`='Credit' ");
						}
						else
						{
							$bill_no=generate_bill_no_new($bill_name,$p_type_id);
							
							$payment_mode="Credit";
							
							mysqli_query($link, " INSERT INTO `payment_detail_all`(`patient_id`, `opd_id`, `transaction_no`, `bill_amount`, `already_paid`, `amount`, `discount_amount`, `discount_reason`, `refund_amount`, `refund_reason`, `tax_amount`, `tax_reason`, `balance_amount`, `balance_reason`, `payment_type`, `payment_mode`, `cheque_ref_no`, `user`, `date`, `time`, `encounter`) VALUES ('$patient_id','$opd_id','$bill_no','$total','$already_paid','0','0','','0','','0','','$balance','$balance_reason','Advance','$payment_mode','','$user','$date','$time','$p_type_id') ");
						}
					}
				}
				else
				{
					$con_pat_pay_detail=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `consult_patient_payment_details` WHERE `patient_id`='$patient_id' and `opd_id`='$opd_id' "));
					
					$bill_amount=$con_pat_pay_detail["tot_amount"];
					$balance_amount=$con_pat_pay_detail["balance"];
					
					
					$check_paid=mysqli_fetch_array(mysqli_query($link, " SELECT ifnull(SUM(`amount`),0) AS `paid`, ifnull(SUM(`discount_amount`),0) AS `discount`, ifnull(SUM(`refund_amount`),0) AS `refund`, ifnull(SUM(`tax_amount`),0) AS `tax` FROM `payment_detail_all` WHERE `patient_id`='$patient_id' and `opd_id`='$opd_id' "));
					
					$already_paid      =$check_paid["paid"];
					$already_discount  =$check_paid["discount"];
					$already_refund    =$check_paid["refund"];
					$already_tax       =$check_paid["tax"];
					
					$total_discount    =$con_pat_pay_detail["dis_amt"]+$discount_amount;
					$total_paid        =$already_paid+$now_pay-$already_refund;
					
					$refund_amount=0;
					$refund_reason="";
					$tax_amount=0;
					$tax_reason="";
					
					$payment_type="Balance";
					
					$balance=$balance_amount-$now_pay-$discount_amount;
					
					if($balance<0)
					{
						echo $patient_id."@".$opd_id."@".$emp_access_str."@Wrong input1";
						exit();
					}
					
					if($now_pay>0 || $discount_amount>0)
					{
						$discount_percetage=round(($total_discount/$bill_amount)*100,2);
						
						mysqli_query($link, " UPDATE `consult_patient_payment_details` SET `dis_per`='$discount_percetage',`dis_amt`='$total_discount',`advance`='$total_paid',`balance`='$balance',`bal_reason`='$bal_reason' WHERE `patient_id`='$patient_id' and `opd_id`='$opd_id' ");
						
						$bill_no=generate_bill_no_new($bill_name,$p_type_id);
						
						mysqli_query($link, " INSERT INTO `payment_detail_all`(`patient_id`, `opd_id`, `transaction_no`, `bill_amount`, `already_paid`, `amount`, `discount_amount`, `discount_reason`, `refund_amount`, `refund_reason`, `tax_amount`, `tax_reason`, `balance_amount`, `balance_reason`, `payment_type`, `payment_mode`, `cheque_ref_no`, `user`, `date`, `time`, `encounter`) VALUES ('$patient_id','$opd_id','$bill_no','$bill_amount','$already_paid','$now_pay','$discount_amount','$discount_reason','$refund_amount','$refund_reason','$tax_amount','$tax_reason','$balance','$balance_reason','$payment_type','$payment_mode','$cheque_ref_no','$user','$date','$time','$p_type_id') ");
					}
				}
				
				$check_appmnt=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `appointment_book` WHERE `patient_id`='$patient_id' and `opd_id`='$opd_id' "));
				
				$appnt_qry=mysqli_fetch_array(mysqli_query($link, " SELECT max(`appointment_no`) as mx FROM `appointment_book` WHERE `consultantdoctorid`='$consultantdoctorid' and `appointment_date`='$appoint_date' and doctor_session='$doctor_session' "));
				$appnt_num=$appnt_qry["mx"];
				if($appnt_num==0)
				{
					$appoint_no=1;
				}else
				{
					$appoint_no=$appnt_num+1;
				}
				
				if($consultantdoctorid==$check_appmnt["consultantdoctorid"] && $appoint_date==$check_appmnt["appointment_date"])
				{
					if($doctor_session!=$check_appmnt["doctor_session"])
					{
						mysqli_query($link, " UPDATE `appointment_book` SET `appointment_no`='$appoint_no',`emergency`='$pat_emergency',`visit_fee`='$visit_fee',`doctor_session`='$doctor_session' WHERE `patient_id`='$patient_id' and `opd_id`='$opd_id' ");
					}else
					{
						mysqli_query($link, " UPDATE `appointment_book` SET `emergency`='$pat_emergency',`visit_fee`='$visit_fee' WHERE `patient_id`='$patient_id' and `opd_id`='$opd_id' ");
					}
				}else
				{
					mysqli_query($link, " UPDATE `appointment_book` SET `dept_id`='$dept_id',`consultantdoctorid`='$consultantdoctorid',`appointment_date`='$appoint_date',`appointment_day`='$appoint_day',`appointment_no`='$appoint_no',`emergency`='$pat_emergency',`visit_fee`='$visit_fee',`doctor_session`='$doctor_session' WHERE `patient_id`='$patient_id' and `opd_id`='$opd_id' ");
				}
				
				if($discount_amount>0)
				{
					// Discount Approve
					mysqli_query($link, " INSERT INTO `discount_approve`(`patient_id`, `pin`, `bill_amount`, `dis_amount`, `reason`, `user`, `approve_by`, `date`, `time`) VALUES ('$patient_id','$opd_id','$total','$discount_amount','$dis_reason','$user','0','$date','$time') ");
				}
				
				if($visit_type_id>0)
				{
					$check_visit_type_entry=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `patient_visit_type_details` WHERE `patient_id`='$patient_id' and `opd_id`='$opd_id' "));
					
					if($check_visit_type_entry)
					{
						mysqli_query($link, " UPDATE `patient_visit_type_details` SET `visit_type_id`='$visit_type_id' WHERE `patient_id`='$patient_id' and `opd_id`='$opd_id' ");
					}
					else
					{
						mysqli_query($link, " INSERT INTO `patient_visit_type_details`(`patient_id`, `opd_id`, `p_type_id`, `visit_type_id`,`date`,`time`) VALUES ('$patient_id','$opd_id','$p_type_id','$visit_type_id','$date','$time') ");
					}
				}
				
				
				$check_card_entry=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `patient_card_details` WHERE `patient_id`='$patient_id' and `opd_id`='$opd_id' "));
				if($check_card_entry)
				{
					if($card_id>0)
					{
						mysqli_query($link, " UPDATE `patient_card_details` SET `card_id`='$card_id',`card_no`='$card_no',`card_details`='$card_details' WHERE `patient_id`='$patient_id' and `opd_id`='$opd_id' ");
					}else
					{
						mysqli_query($link, " DELETE FROM `patient_card_details` WHERE `patient_id`='$patient_id' and `opd_id`='$opd_id' ");
					}
				}else
				{
					if($card_id>0)
					{
						mysqli_query($link, " INSERT INTO `patient_card_details`(`patient_id`, `opd_id`, `card_id`, `card_no`, `card_details`) VALUES ('$patient_id','$opd_id','$card_id','$card_no','$card_details') ");
					}
				}
			}
			echo $patient_id."@".$opd_id."@".$emp_access_str."@Updated";
		}
	}
}

if($type=="load_paid_info")
{
	$patient_id=mysqli_real_escape_string($link, $_POST["patient_id"]);
	$opd_id=mysqli_real_escape_string($link, $_POST["opd_id"]);
	$pay_id=mysqli_real_escape_string($link, $_POST["pay_id"]);
	
	$discount_attribute="";
	if($emp_access["discount_permission"]==0)
	{
		$discount_attribute="readonly";
	}
	
	$pay_det=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `payment_detail_all` WHERE `pay_id`='$pay_id' AND `patient_id`='$patient_id' AND `opd_id`='$opd_id' "));
	
	if($pay_det)
	{
		$pat_pay_det=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `consult_patient_payment_details` WHERE `patient_id`='$patient_id' and `opd_id`='$opd_id' "));
		
		$bill_amount=$pat_pay_det["tot_amount"];
		//~ $disount_amount=$pat_pay_det["dis_amt"];
		//~ $paid_amount=$pat_pay_det["advance"];
		$due_amount_str=$pat_pay_det["balance"];
		
		$paid_amount=$pay_det["amount"];
		$discount_amount=$pay_det["discount_amount"];
		$refund_amount=$pay_det["ref_amount"];
		$tax_amount=$pay_det["tax_amount"];
		$balance_amount=$pay_det["balance_amount"];
		
		$discount_per=round(($discount_amount/$bill_amount)*100,2);
		
		$discount_reason_style="hidden";
		if($discount_amount>0)
		{
			$discount_reason_style="text";
		}
		
		
		$balance_style="display:none;";
		if($balance_amount>0)
		{
			$balance_style="";
		}
		
		$refund_style="display:none;";
		if($refund_amount>0)
		{
			$refund_style="";
		}
		
		$cheque_ref_no_style="display:none;";
		if($pat_det["cheque_ref_no"]!="")
		{
			$cheque_ref_no_style="";
		}
?>
		<table class="table table-condensed">
			<tr>
				<th style="width: 25%;">Total Bill Amount</th>
				<td>
					<span id="opd_bill_amount_str"><?php echo $bill_amount; ?></span>
					<input type="hidden" id="opd_bill_amount" value="<?php echo $bill_amount; ?>">
				</td>
			</tr>
			<tr id="now_discount_tr">
				<th>Now Discount</th>
				<td>
					<input type="text" class="span1 numericcfloat discount_cls" id="opd_now_discount_per" value="<?php echo $discount_per; ?>" onkeyup="opd_discount_per(event)" placeholder="%" onpaste="return false;" ondrop="return false;" maxlength="4" <?php echo $discount_attribute; ?>>%
					
					<input type="text" class="span1 numericc discount_cls" id="opd_now_discount_amount" value="<?php echo $discount_amount; ?>" onkeyup="opd_discount_amount(event)" onpaste="return false;" ondrop="return false;" <?php echo $discount_attribute; ?>>INR
					<br>
					<input type="<?php echo $discount_reason_style; ?>" class="span2" id="opd_now_discount_reason" onkeyup="opd_now_discount_reason(event)" value="<?php echo $pay_det["discount_reason"]; ?>" placeholder="Discount Reason">
				</td>
			</tr>
			<tr id="opd_now_pay_tr">
				<th>Now Pay</th>
				<td>
					<input type="text" class="span2 numericc" id="opd_now_pay" value="<?php echo $paid_amount; ?>" onkeyup="opd_now_pay(event)" onpaste="return false;" ondrop="return false;">
				</td>
			</tr>
			<tr id="opd_now_refund_tr" style="color:red;<?php echo $refund_style; ?>">
				<th>Amount To Refund</th>
				<td>
					<b id="opd_now_refund_str"><?php echo $refund_amount; ?></b>
					<input type="hidden" class="span2" id="opd_now_refund" value="<?php echo $refund_amount; ?>" disabled>
				</td>
			</tr>
			<tr id="opd_now_payment_mode_tr">
				<th>Payment Mode</th>
				<td>
					<select class="span2" id="opd_now_payment_mode" onchange="opd_payment_mode_change(this.value)" onkeyup="opd_payment_mode_up(event)">
				<?php
					$pay_mode_master_qry=mysqli_query($link, "SELECT `p_mode_name` FROM `payment_mode_master` WHERE `p_mode_name`!='' $operation_str ORDER BY `sequence` ASC");
					while($pay_mode_master=mysqli_fetch_array($pay_mode_master_qry))
					{
						if($pay_det["payment_mode"]==$pay_mode_master["p_mode_name"]){ $p_mode_sel="selected"; }else{ $p_mode_sel=""; }
						echo "<option value='$pay_mode_master[p_mode_name]' $p_mode_sel>$pay_mode_master[p_mode_name]</option>";
					}
				?>
					</select>
					<br>
					<input type="hidden" class="span1" id="opd_now_ref_field">
					<input type="hidden" class="span1" id="opd_now_operation">
				</td>
			</tr>
			<tr class="opd_now_balance_tr" style="<?php echo $balance_style; ?>">
				<th>Now Balanace</th>
				<td>
					<span id="opd_now_balance"><?php echo $balance_amount; ?></span>
				</td>
			</tr>
			<tr class="opd_now_balance_tr" style="<?php echo $balance_style; ?>">
				<th>Balance Reason</th>
				<td>
					<input type="text" class="span2" id="now_balance_reason" onkeyup="now_balance_reason(event)" value="<?php echo $pay_det["balance_reason"]; ?>">
				</td>
			</tr>
			<tr id="opd_now_cheque_ref_no_tr" style="<?php echo $cheque_ref_no_style; ?>">
				<th>Cheque/Reference No</th>
				<td>
					<input type="text" class="span2" id="opd_now_cheque_ref_no" onkeyup="opd_now_cheque_ref_no(event)" value="<?php echo $pay_det["cheque_ref_no"]; ?>">
				</td>
			</tr>
			<tr id="save_tr">
				<td colspan="2" style="text-align:center;">
					<button class="btn btn-save" id="pat_save_btn" onClick="save_payment_edit('<?php echo $pay_id; ?>')"><i class="icon-save"></i> Update</button>
					<button class="btn btn-back" onclick="load_payment_info()"><i class="icon-backward"></i> Back</button>
				</td>
			</tr>
		</table>
		
<?php
	}
	else
	{
		echo "<h4>Payment no found.</h4>";
	}
}

if($type=="save_payment_edit")
{
	//print_r($_POST);
	
	$patient_id=mysqli_real_escape_string($link, $_POST["patient_id"]);
	$opd_id=mysqli_real_escape_string($link, $_POST["opd_id"]);
	$pay_id=mysqli_real_escape_string($link, $_POST["pay_id"]);
	$total=mysqli_real_escape_string($link, $_POST["total"]);
	$discount_amount=mysqli_real_escape_string($link, $_POST["now_discount"]);
	$discount_reason=mysqli_real_escape_string($link, $_POST["opd_now_discount_reason"]);
	$now_pay=mysqli_real_escape_string($link, $_POST["opd_now_pay"]);
	$payment_mode=mysqli_real_escape_string($link, $_POST["opd_now_payment_mode"]);
	$balance_reason=mysqli_real_escape_string($link, $_POST["now_balance_reason"]);
	$cheque_ref_no=mysqli_real_escape_string($link, $_POST["opd_now_cheque_ref_no"]);
	$user=mysqli_real_escape_string($link, $_POST["user"]);
	
	$pat_reg=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `uhid_and_opdid` WHERE `patient_id`='$patient_id' and `opd_id`='$opd_id' "));
	
	$pay_num=mysqli_num_rows(mysqli_query($link, " SELECT * FROM `payment_detail_all` WHERE `patient_id`='$patient_id' AND `opd_id`='$opd_id' AND `payment_mode`!='Credit' "));
	if($pay_num==1)
	{
		$pat_pay_det=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `consult_patient_payment_details` WHERE `patient_id`='$patient_id' and `opd_id`='$opd_id' "));
		
		$bill_amount=$pat_pay_det["tot_amount"];
		$balance=$bill_amount-$discount_amount-$now_pay;
		
		if($payment_mode=="Credit")
		{
			$now_pay=0;
			$balance=$bill_amount-$discount_amount-$now_pay;
		}
		if($now_pay==0)
		{
			$payment_mode="Credit";
			if($bill_amount==0)
			{
				$payment_mode="Cash";
				
				$balance=0;
				$now_pay=0;
				$discount_amount=0;
			}
			else if($discount_amount==0)
			{
				$payment_mode="Credit";
			}
			else
			{
				$payment_mode="Cash";
			}
		}
		
		if($balance<0)
		{
			echo "Failed.@405";
		}
		
		if($now_pay>0 || $discount_amount>0 || $balance>0)
		{
			// Edit Counter
			$edit_counter=mysqli_fetch_array(mysqli_query($link, " SELECT count(`counter`) as cntr FROM `edit_counter` WHERE `patient_id`='$uhid' AND `opd_id`='$opd_id' AND `type`='$p_type_id' "));
			$edit_counter_num=$edit_counter["cntr"];
			$counter_num=$edit_counter_num+1;
			
			// edit counter record
			mysqli_query($link, " INSERT INTO `edit_counter`(`patient_id`, `opd_id`, `date`, `time`, `user`, `type`, `counter`) VALUES ('$patient_id','$opd_id','$date','$time','$user','$p_type_id','$counter_num') ");
			
			$payment_detail_all_qry=mysqli_query($link, " SELECT * FROM `payment_detail_all` WHERE `patient_id`='$patient_id' AND `opd_id`='$opd_id' ");
			while($payment_detail_all=mysqli_fetch_array($payment_detail_all_qry))
			{
				mysqli_query($link, " INSERT INTO `payment_detail_all_edit`(`pay_id`, `patient_id`, `opd_id`, `transaction_no`, `bill_amount`, `already_paid`, `amount`, `discount_amount`, `discount_reason`, `refund_amount`, `refund_reason`, `tax_amount`, `tax_reason`, `balance_amount`, `balance_reason`, `payment_type`, `payment_mode`, `cheque_ref_no`, `user`, `date`, `time`, `encounter`, `counter`) VALUES ('$payment_detail_all[pay_id]','$payment_detail_all[patient_id]','$payment_detail_all[opd_id]','$payment_detail_all[transaction_no]','$payment_detail_all[bill_amount]','$payment_detail_all[already_paid]','$payment_detail_all[amount]','$payment_detail_all[discount_amount]','$payment_detail_all[discount_reason]','$payment_detail_all[refund_amount]','$payment_detail_all[refund_reason]','$payment_detail_all[tax_amount]','$payment_detail_all[tax_reason]','$payment_detail_all[balance_amount]','$payment_detail_all[balance_reason]','$payment_detail_all[payment_type]','$payment_detail_all[payment_mode]','$payment_detail_all[cheque_ref_no]','$payment_detail_all[user]','$payment_detail_all[date]','$payment_detail_all[time]','$payment_detail_all[encounter]','$counter_num') ");
			}
			
			$discount_percetage=round(($discount_amount/$bill_amount)*100,2);
			
			// Update
			mysqli_query($link, " UPDATE `consult_patient_payment_details` SET `dis_per`='$discount_percetage',`dis_amt`='$discount_amount',`dis_reason`='$discount_reason',`advance`='$now_pay',`balance`='$balance',`bal_reason`='$balance_reason' WHERE `patient_id`='$patient_id' AND `opd_id`='$opd_id' ");
			
			mysqli_query($link, " UPDATE `payment_detail_all` SET `amount`='$now_pay',`discount_amount`='$discount_amount',`discount_reason`='$discount_reason',`balance_amount`='$balance',`balance_reason`='$balance_reason',`payment_mode`='$payment_mode',`cheque_ref_no`='$cheque_ref_no' WHERE `pay_id`='$pay_id' AND `patient_id`='$patient_id' AND `opd_id`='$opd_id' ");
			
			if($balance==0)
			{
				mysqli_query($link, " DELETE FROM `payment_detail_all` WHERE `patient_id`='$patient_id' AND `opd_id`='$opd_id' AND `payment_mode`='Credit' ");
			}
			else
			{
				$payment_mode="Credit";
				
				$pay_det_credit=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `payment_detail_all` WHERE `patient_id`='$patient_id' AND `opd_id`='$opd_id' AND `payment_mode`='Credit' "));
				if($pay_det_credit)
				{
					// Update
					mysqli_query($link, " UPDATE `payment_detail_all` SET `balance_amount`='$balance',`balance_reason`='$balance_reason' WHERE `patient_id`='$patient_id' AND `opd_id`='$opd_id' AND `payment_mode`='Credit' ");
				}
				else
				{
					// Insert
					$bill_no=generate_bill_no_new($bill_name,$p_type_id);
					
					mysqli_query($link, " INSERT INTO `payment_detail_all`(`patient_id`, `opd_id`, `transaction_no`, `bill_amount`, `already_paid`, `amount`, `discount_amount`, `discount_reason`, `refund_amount`, `refund_reason`, `tax_amount`, `tax_reason`, `balance_amount`, `balance_reason`, `payment_type`, `payment_mode`, `cheque_ref_no`, `user`, `date`, `time`, `encounter`) VALUES ('$patient_id','$opd_id','$bill_no','$bill_amount','$now_pay','0','0','','0','','0','','$balance','$balance_reason','Advance','$payment_mode','','$pat_reg[user]','$pat_reg[date]','$pat_reg[time]','$p_type_id') ");
				}
			}
			echo "Updated@101";
		}
		else
		{
			echo "Wrong input.@405";
		}
	}
	else
	{
		echo "Failed.@405";
	}
}

if($type=="delete_payment")
{
	//print_r($_POST);
	
	$patient_id=mysqli_real_escape_string($link, $_POST["patient_id"]);
	$opd_id=mysqli_real_escape_string($link, $_POST["opd_id"]);
	$pay_id=mysqli_real_escape_string($link, $_POST["pay_id"]);
	$del_reason=mysqli_real_escape_string($link, $_POST["del_reason"]);
	$user=mysqli_real_escape_string($link, $_POST["user"]);
	
	$payment_detail_all=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `payment_detail_all` WHERE `pay_id`='$pay_id' AND `patient_id`='$patient_id' AND `opd_id`='$opd_id' "));
	
	
	$pay_id_amount=$payment_detail_all["amount"];
	$pay_id_discount=$payment_detail_all["discount_amount"];
	$pay_id_tax_amount=$payment_detail_all["tax_amount"];
	
	$pat_pay_det=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `consult_patient_payment_details` WHERE `patient_id`='$patient_id' and `opd_id`='$opd_id' "));
	
	$bill_amount=$pat_pay_det["tot_amount"];
	
	$paid_amount=$pat_pay_det["advance"]-$pay_id_amount;
	$discount_amount=$pat_pay_det["dis_amt"]-$pay_id_discount;
	$balance_amount=$pat_pay_det["balance"]+$pay_id_amount+$pay_id_discount+$pay_id_tax_amount;
	
	$discount_percetage=round(($discount_amount/$bill_amount)*100,2);
	
	if(mysqli_query($link, " UPDATE `consult_patient_payment_details` SET `dis_per`='$discount_percetage',`dis_amt`='$discount_amount',`advance`='$paid_amount',`balance`='$balance_amount' WHERE `patient_id`='$patient_id' AND `opd_id`='$opd_id' "))
	{
		if($payment_detail_all)
		{
			mysqli_query($link, " INSERT INTO `payment_detail_all_delete`(`pay_id`, `patient_id`, `opd_id`, `transaction_no`, `bill_amount`, `already_paid`, `amount`, `discount_amount`, `discount_reason`, `refund_amount`, `refund_reason`, `tax_amount`, `tax_reason`, `balance_amount`, `balance_reason`, `payment_type`, `payment_mode`, `cheque_ref_no`, `user`, `date`, `time`, `encounter`, `del_reason`, `del_user`, `del_date`, `del_time`) VALUES ('$payment_detail_all[pay_id]','$payment_detail_all[patient_id]','$payment_detail_all[opd_id]','$payment_detail_all[transaction_no]','$payment_detail_all[bill_amount]','$payment_detail_all[already_paid]','$payment_detail_all[amount]','$payment_detail_all[discount_amount]','$payment_detail_all[discount_reason]','$payment_detail_all[refund_amount]','$payment_detail_all[refund_reason]','$payment_detail_all[tax_amount]','$payment_detail_all[tax_reason]','$payment_detail_all[balance_amount]','$payment_detail_all[balance_reason]','$payment_detail_all[payment_type]','$payment_detail_all[payment_mode]','$payment_detail_all[cheque_ref_no]','$payment_detail_all[user]','$payment_detail_all[date]','$payment_detail_all[time]','$payment_detail_all[encounter]','$del_reason','$user','$date','$time') ");
		}
		
		mysqli_query($link, " DELETE FROM `payment_detail_all` WHERE `pay_id`='$pay_id' AND `patient_id`='$patient_id' AND `opd_id`='$opd_id' ");
		
		echo "Deleted";
	}
	else
	{
		echo "Failed, try gain later.";
	}
}

if($type=="payment_mode_change")
{
	//print_r($_POST);
	
	$patient_id=mysqli_real_escape_string($link, $_POST["patient_id"]);
	$opd_id=mysqli_real_escape_string($link, $_POST["opd_id"]);
	$pay_id=mysqli_real_escape_string($link, $_POST["pay_id"]);
	$payment_mode=mysqli_real_escape_string($link, $_POST["payment_mode"]);
	$cheque_ref_no=mysqli_real_escape_string($link, $_POST["cheque_ref_no"]);
	$user=mysqli_real_escape_string($link, $_POST["user"]);
	
	$payment_detail_all=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `payment_detail_all` WHERE `pay_id`='$pay_id' AND `patient_id`='$patient_id' AND `opd_id`='$opd_id' "));
	
	if(mysqli_query($link, " UPDATE `payment_detail_all` SET `payment_mode`='$payment_mode', `cheque_ref_no`='$cheque_ref_no' WHERE `pay_id`='$pay_id' AND `patient_id`='$patient_id' AND `opd_id`='$opd_id' "))
	{
		mysqli_query($link, " INSERT INTO `payment_mode_change`(`patient_id`, `ipd_id`, `bill_no`, `pay_mode`, `cheque_ref_no`, `user`, `date`, `time`) VALUES ('$patient_id','$opd_id','$payment_detail_all[transaction_no]','$payment_detail_all[payment_mode]','$payment_detail_all[cheque_ref_no]','$user','$date','$time') ");
		
		echo "Payment mode changed";
	}
	else
	{
		echo "Failed, try again later.";
	}
}
if($type=="load_doc_fees")
{
	//print_r($_POST);
	
	$branch_id=mysqli_real_escape_string($link, $_POST["branch_id"]);
	$patient_id=mysqli_real_escape_string($link, $_POST["patient_id"]);
	$consultantdoctorid=mysqli_real_escape_string($link, $_POST["consultantdoctorid"]);
	
	$doc_info=mysqli_fetch_array(mysqli_query($link, " SELECT `Name`,`opd_visit_fee`,`opd_visit_validity`,`opd_reg_fee`,`opd_reg_validity` FROM `consultant_doctor_master` WHERE `consultantdoctorid`='$consultantdoctorid'"));
	
	//$check_last_visit_fee=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `appointment_book` WHERE `patient_id`='$patient_id' AND `consultantdoctorid`='$consultantdoctorid' AND `visit_fee`>0 order by `slno` DESC "));
	
	$check_last_visit_fee=mysqli_fetch_array(mysqli_query($link, " SELECT a.`date` FROM `consult_patient_payment_details` a, `appointment_book` b WHERE a.`patient_id`=b.`patient_id` AND a.`opd_id`=b.`opd_id` AND a.`patient_id`='$patient_id' AND b.`consultantdoctorid`='$consultantdoctorid' AND a.`visit_fee`>0 ORDER BY a.`slno` DESC LIMIT 1 "));
	
	$check_last_visit_fee_date=$check_last_visit_fee["date"];
	if(!$check_last_visit_fee_date)
	{
		$visitt_fee=$doc_info["opd_visit_fee"];
		$visit_type_id=1; // NEW PATIENT
	}else
	{
		$dates_array = getDatesFromRange($check_last_visit_fee_date, $date);
		$visit_fee_day_diff=sizeof($dates_array);
		if($visit_fee_day_diff<=$doc_info["opd_visit_validity"])
		{
			$visitt_fee=0;
			$visit_type_id=3; // FREE FOLLOW UP
		}else
		{
			$visitt_fee=$doc_info["opd_visit_fee"];
			$visit_type_id=6; // PAID FOLLOW UP
		}
	}
	
	//$check_last_regd_fee_qry=mysqli_query($link, " SELECT * FROM `pat_regd_fee` WHERE `patient_id`='$patient_id' AND `regd_fee`>0 AND `opd_id` IN(SELECT `opd_id` FROM `uhid_and_opdid` WHERE `patient_id`='$patient_id' AND `branch_id`='$branch_id') ORDER BY `slno` DESC LIMIT 1 "); // AND `dept_id`='$d1[dept_id]'
	
	$check_last_regd_fee_qry=mysqli_query($link, "SELECT a.`date` FROM `consult_patient_payment_details` a, `appointment_book` b WHERE a.`patient_id`=b.`patient_id` AND a.`opd_id`=b.`opd_id` AND a.`patient_id`='$patient_id' AND a.`regd_fee`>0 ORDER BY a.`slno` DESC LIMIT 1"); //AND b.`consultantdoctorid`='$consultantdoctorid' 
	$check_last_regd_fee_num=mysqli_num_rows($check_last_regd_fee_qry);
	
	if($check_last_regd_fee_num==0)
	{
		$regdd_fee=$doc_info["opd_reg_fee"];
	}else
	{
		$check_last_regd_fee=mysqli_fetch_array($check_last_regd_fee_qry);
		$dates_array = getDatesFromRange($check_last_regd_fee["date"], $date);
		$day_diff=sizeof($dates_array);
		//~ if($day_diff<=$d1["opd_reg_validity"])
		if($day_diff<=180)
		{
			$regdd_fee=0;
		}else
		{
			$regdd_fee=$d1["opd_reg_fee"];
		}
	}
	
	echo $consultantdoctorid."@$@".$doc_info["Name"]."@$@".$visitt_fee."@$@".$regdd_fee."@$@".$visit_type_id;
}
?>
