<?php
session_start();
$c_user=trim($_SESSION['emp_id']);

include'../../includes/connection.php';
require('../../includes/global.function.php');

$test_dept_exclude="129"; // SRL=129

function calculate_commission_bill_wise($refbydoctorid,$patient_id,$opd_id,$batch_no,$pat_encounter,$centreno)
{
	global $link;
	global $test_dept_exclude;
	
	//$centreno=0;
	
	$commision_amount=0;
	
	$pat_typ_text=mysqli_fetch_array(mysqli_query($link, " SELECT `type` FROM `patient_type_master` WHERE `p_type_id`='$pat_encounter' "));
	$encounter_type=$pat_typ_text["type"];
	
	if($encounter_type==2) // Investigation
	{
		$pat_pay_det=mysqli_fetch_array(mysqli_query($link, "SELECT * FROM `invest_patient_payment_details` WHERE `patient_id`='$patient_id' AND `opd_id`='$opd_id'"));
		$bill_amount=$pat_pay_det["tot_amount"];
		
		if($bill_amount>0)
		{
			$discount_amount=$pat_pay_det["dis_amt"];
			//$discount_amount=0;
			$discount_per=round(($discount_amount/$bill_amount)*100,2);
			
			$test_qry=mysqli_query($link, "SELECT `testid`,`test_rate` FROM `patient_test_details` WHERE `patient_id`='$patient_id' AND `opd_id`='$opd_id' AND `batch_no`='$batch_no'");
			while($test_data=mysqli_fetch_array($test_qry))
			{
				$testid=$test_data["testid"];
				$test_amount=$test_data["test_rate"];
				
				$test_disacount=round(($test_amount*$discount_per)/100);
				$test_amount_after_disacount=$test_amount-$test_disacount;
				
				$test_info=mysqli_fetch_array(mysqli_query($link, "SELECT `category_id`,`type_id` FROM `testmaster` WHERE `testid`='$testid' AND `type_id` NOT IN('$test_dept_exclude')"));
				if($test_info)
				{
					$category_id=$test_info["category_id"];
					$type_id=$test_info["type_id"];
					
					$test_comm=mysqli_fetch_array(mysqli_query($link, "SELECT `com_per`,`com_amount` FROM `dal_com_setup` WHERE `centreno`='$centreno' AND  `refbydoctorid`='$refbydoctorid' AND `testid`='$testid'")); // AND `category_id`='$category_id' AND `type_id`='$type_id'
					if($test_comm)
					{
						if($test_comm["com_amount"]>0)
						{
							$commision_amount+=$test_comm["com_amount"];
						}
						else
						{
							$commision_amount+=round(($test_amount_after_disacount*$test_comm["com_per"])/100);
						}
					}
					else
					{
						$dept_comm=mysqli_fetch_array(mysqli_query($link, "SELECT `com_per`,`com_amount` FROM `dal_com_setup` WHERE `centreno`='$centreno' AND  `refbydoctorid`='$refbydoctorid' AND `type_id`='$type_id' AND `testid`='0'"));// AND `category_id`='$category_id'
						if($dept_comm)
						{
							if($dept_comm["com_amount"]>0)
							{
								$commision_amount+=$dept_comm["com_amount"];
							}
							else
							{
								$commision_amount+=round(($test_amount_after_disacount*$dept_comm["com_per"])/100);
							}
						}
						else
						{
							$cat_comm=mysqli_fetch_array(mysqli_query($link, "SELECT `com_per`,`com_amount` FROM `dal_com_setup` WHERE `centreno`='$centreno' AND  `refbydoctorid`='$refbydoctorid' AND `category_id`='$category_id' AND `type_id`='0' AND `testid`='0'"));
							if($cat_comm)
							{
								if($cat_comm["com_amount"]>0)
								{
									$commision_amount+=$cat_comm["com_amount"];
								}
								else
								{
									$commision_amount+=round(($test_amount_after_disacount*$cat_comm["com_per"])/100);
								}
							}
							else
							{
								$whole_comm=mysqli_fetch_array(mysqli_query($link, "SELECT `com_per`,`com_amount` FROM `dal_com_setup` WHERE `centreno`='$centreno' AND  `refbydoctorid`='$refbydoctorid' AND `category_id`='0' AND `type_id`='0' AND `testid`='0'"));
								if($whole_comm)
								{
									if($whole_comm["com_amount"]>0)
									{
										$commision_amount+=$whole_comm["com_amount"];
									}
									else
									{
										$commision_amount+=round(($test_amount_after_disacount*$whole_comm["com_per"])/100);
									}
								}
							}
						}
					}
				}
				else
				{
					$commision_amount+=0;
				}
			}
			
			return $commision_amount;
		}
		else
		{
			return 0;
		}
	}
}

function calculate_commission_test_wise($refbydoctorid,$testid,$test_amount,$pat_encounter,$centreno)
{
	global $link;
	global $test_dept_exclude;
	
	//$centreno=0;
	
	$commision_amount=0;
	
	$pat_typ_text=mysqli_fetch_array(mysqli_query($link, " SELECT `type` FROM `patient_type_master` WHERE `p_type_id`='$pat_encounter' "));
	$encounter_type=$pat_typ_text["type"];
	
	if($encounter_type==2) // Investigation
	{
		if($test_amount>0)
		{
			$test_info=mysqli_fetch_array(mysqli_query($link, "SELECT `category_id`,`type_id` FROM `testmaster` WHERE `testid`='$testid' AND `type_id` NOT IN('$test_dept_exclude')"));
			if($test_info)
			{
				$category_id=$test_info["category_id"];
				$type_id=$test_info["type_id"];
				
				$test_comm=mysqli_fetch_array(mysqli_query($link, "SELECT `com_per`,`com_amount` FROM `dal_com_setup` WHERE `centreno`='$centreno' AND  `refbydoctorid`='$refbydoctorid' AND `testid`='$testid'"));// AND `category_id`='$category_id' AND `type_id`='$type_id'
				if($test_comm)
				{
					if($test_comm["com_amount"]>0)
					{
						$commision_amount+=$test_comm["com_amount"];
					}
					else
					{
						$commision_amount+=round(($test_amount*$test_comm["com_per"])/100);
					}
				}
				else
				{
					$dept_comm=mysqli_fetch_array(mysqli_query($link, "SELECT `com_per`,`com_amount` FROM `dal_com_setup` WHERE `centreno`='$centreno' AND  `refbydoctorid`='$refbydoctorid' AND `type_id`='$type_id' AND `testid`='0'"));// AND `category_id`='$category_id'
					if($dept_comm)
					{
						if($dept_comm["com_amount"]>0)
						{
							$commision_amount+=$dept_comm["com_amount"];
						}
						else
						{
							$commision_amount+=round(($test_amount*$dept_comm["com_per"])/100);
						}
					}
					else
					{
						$cat_comm=mysqli_fetch_array(mysqli_query($link, "SELECT `com_per`,`com_amount` FROM `dal_com_setup` WHERE `centreno`='$centreno' AND  `refbydoctorid`='$refbydoctorid' AND `category_id`='$category_id' AND `type_id`='0' AND `testid`='0'"));
						if($cat_comm)
						{
							if($cat_comm["com_amount"]>0)
							{
								$commision_amount+=$cat_comm["com_amount"];
							}
							else
							{
								$commision_amount+=round(($test_amount*$cat_comm["com_per"])/100);
							}
						}
						else
						{
							$whole_comm=mysqli_fetch_array(mysqli_query($link, "SELECT `com_per`,`com_amount` FROM `dal_com_setup` WHERE `centreno`='$centreno' AND  `refbydoctorid`='$refbydoctorid' AND `category_id`='0' AND `type_id`='0' AND `testid`='0'"));
							if($whole_comm)
							{
								if($whole_comm["com_amount"]>0)
								{
									$commision_amount+=$whole_comm["com_amount"];
								}
								else
								{
									$commision_amount+=round(($test_amount*$whole_comm["com_per"])/100);
								}
							}
						}
					}
				}
			}
			else
			{
				$commision_amount+=0;
			}
			return $commision_amount;
		}
		else
		{
			return 0;
		}
	}
}

$type=mysqli_real_escape_string($link, base64_decode($_GET['typ']));
$date1=mysqli_real_escape_string($link, base64_decode($_GET['dt1']));
$date2=mysqli_real_escape_string($link, base64_decode($_GET['dt2']));
$refbydoctorid=mysqli_real_escape_string($link, base64_decode($_GET['rdoc']));
$encounter=mysqli_real_escape_string($link, base64_decode($_GET['tp']));

$filename ="comm_report_from_".$date1."_to_".$date2.".xls";
header('Content-type: application/ms-excel');
header('Content-Disposition: attachment; filename='.$filename);

$encounter_type=0;
if($encounter>0)
{
	$pat_typ_text=mysqli_fetch_array(mysqli_query($link, " SELECT `type` FROM `patient_type_master` WHERE `p_type_id`='$encounter' "));
	$encounter_type=$pat_typ_text["type"];
}


?>
<html>
<head>
<title>Reports</title>

<style>
input[type="text"]
{
	border:none;
}
body {
	padding: 10px;
	font-size:13px; font-family:Arial, Helvetica, sans-serif; line-height: 18px;
}
.line td{border-top:1px dotted}
@media print{
 .noprint{
	 display:none;
 }
}
.bline td{border-bottom:1px solid;}
*{font-size:13px;}
</style>
</head>
<body>
	<div class="container">
<?php
	if($encounter==0 || $encounter_type==2)
	{
		$doc_str="SELECT b.`refbydoctorid`, b.`ref_name` FROM `uhid_and_opdid` a, `refbydoctor_master` b, `dal_com_setup` c WHERE a.`refbydoctorid`=b.`refbydoctorid` AND a.`refbydoctorid`=c.`refbydoctorid`";
		
		if($encounter==0)
		{
			$doc_str.=" AND a.`type` IN(SELECT `p_type_id` FROM `patient_type_master` WHERE `type`=2)";
		}
		else
		{
			$doc_str.=" AND a.`type`='$encounter'";
		}
		
		if($date1 && $date2)
		{
			$doc_str.=" AND a.`date` BETWEEN '$date1' AND '$date2'";
		}
		
		if($refbydoctorid)
		{
			//~ $doc_str.=" AND a.`refbydoctorid`='$refbydoctorid'";
			$doc_str.=" AND a.`refbydoctorid` IN($refbydoctorid)";
		}
		
		$doc_str.=" GROUP BY b.`refbydoctorid` ORDER BY b.`ref_name` ASC";
?>
		<table class="table table-condensed table-bordered table-hover">
			<thead class="table_header_fix">
				<tr>
					<th>#</th>
					<th>Date</th>
					<th>UHID</th>
					<th>Bill No</th>
					<th>Name</th>
					<th style="text-align:center;">Bill Amount</th>
					<th style="text-align:center;">Discount Amount</th>
					<th style="text-align:center;">After Discount</th>
					<th style="text-align:center;">Comm Amount</th>
				</tr>
			</thead>
<?php
		$n=1;
		$doc_qry=mysqli_query($link, $doc_str);
		while($doc_data=mysqli_fetch_array($doc_qry))
		{
			$refbydoctorid=$doc_data["refbydoctorid"];
			echo "<tr style='display:none;' id='dr$refbydoctorid'><th colspan='9'>$doc_data[ref_name]</th></tr>";
			
			
			$bill_amount_each=0;
			$discount_amount_each=0;
			$bill_amount_after_discount_each=0;
			$comm_amount_each=0;
			
			$pat_str="SELECT * FROM `uhid_and_opdid` WHERE `refbydoctorid`='$refbydoctorid'";
			
			if($encounter==0)
			{
				$pat_str.=" AND `type` IN(SELECT `p_type_id` FROM `patient_type_master` WHERE `type`=2)";
			}
			else
			{
				$pat_str.=" AND `type`='$encounter'";
			}
			
			if($date1 && $date2)
			{
				$pat_str.=" AND `date` BETWEEN '$date1' AND '$date2'";
			}
			$pat_str.=" ORDER BY `slno` ASC";
			
			$pat_qry=mysqli_query($link, $pat_str);
			while($pat_reg=mysqli_fetch_array($pat_qry))
			{
				$patient_id=$pat_reg["patient_id"];
				$opd_id=$pat_reg["opd_id"];
				$pat_encounter=$pat_reg["type"];
				$centreno=$pat_reg["center_no"];
				
				$pat_info=mysqli_fetch_array(mysqli_query($link, "SELECT `name` FROM `patient_info` WHERE `patient_id`='$patient_id'"));
				
				$pat_pay_det=mysqli_fetch_array(mysqli_query($link, "SELECT * FROM `invest_patient_payment_details` WHERE `patient_id`='$patient_id' AND `opd_id`='$opd_id'"));
				
				$bill_amount=$pat_pay_det["tot_amount"];
				$discount_amount=$pat_pay_det["dis_amt"];
				
				$bill_amount_after_discount=$bill_amount-$discount_amount;
				
				$batch_no=1;
				$comm_amount=calculate_commission_bill_wise("$refbydoctorid","$patient_id","$opd_id","$batch_no","$pat_encounter","$centreno");
				if($comm_amount>0)
				{
?>
				<tr>
					<td><?php echo $n; ?></td>
					<td><?php echo date("d-M-Y",strtotime($pat_reg["date"])); ?></td>
					<td><?php echo $pat_reg["patient_id"]; ?></td>
					<td><?php echo $pat_reg["opd_id"]; ?></td>
					<td><?php echo $pat_info["name"]; ?></td>
					<td style="text-align:right;"><?php echo $bill_amount; ?></td>
					<td style="text-align:right;"><?php echo $discount_amount; ?></td>
					<td style="text-align:right;"><?php echo number_format($bill_amount_after_discount,2); ?></td>
					<td style="text-align:right;"><?php echo number_format($comm_amount,2); ?></td>
				</tr>
<?php
				$bill_amount_each+=$bill_amount;
				$discount_amount_each+=$discount_amount;
				$bill_amount_after_discount_each+=$bill_amount_after_discount;
				$comm_amount_each+=$comm_amount;
				$n++;
				}
			}
			if($comm_amount_each>0)
			{
				echo "<script>$('#dr$refbydoctorid').show();</script>";
?>
			<tr>
				<th colspan="4"></th>
				<th style="text-align:right;">Total</th>
				<th style="text-align:right;"><?php echo number_format($bill_amount_each,2); ?></th>
				<th style="text-align:right;"><?php echo number_format($discount_amount_each,2); ?></th>
				<th style="text-align:right;"><?php echo number_format($bill_amount_after_discount_each,2); ?></th>
				<th style="text-align:right;"><?php echo number_format($comm_amount_each,2); ?></th>
			</tr>
<?php
			}
		}
?>
		</table>
<?php
		$refbydoctorid_search=0;
		if($refbydoctorid_search>0)
		{
			$refbydoctorid=$refbydoctorid_search;
			$dis_centre_str="SELECT DISTINCT `centreno`  FROM `dal_com_setup` ";
			
			$as_a_whole_num=mysqli_num_rows(mysqli_query($link, " SELECT `com_per`,`com_amount` FROM `dal_com_setup` WHERE `refbydoctorid`='$refbydoctorid' AND `category_id`=0 AND `type_id`=0 AND `testid`=0"));
			
			$category_num=mysqli_num_rows(mysqli_query($link, " SELECT `com_per`,`com_amount` FROM `dal_com_setup` WHERE `refbydoctorid`='$refbydoctorid' AND `category_id`>0 AND `type_id`=0 AND `testid`=0"));
			
			$dept_num=mysqli_num_rows(mysqli_query($link, " SELECT `com_per`,`com_amount` FROM `dal_com_setup` WHERE `refbydoctorid`='$refbydoctorid' AND `category_id`>0 AND `type_id`>0 AND `testid`=0"));
			
			$test_num=mysqli_num_rows(mysqli_query($link, " SELECT `com_per`,`com_amount` FROM `dal_com_setup` WHERE `refbydoctorid`='$refbydoctorid' AND `category_id`>0 AND `type_id`>0 AND `testid`>0"));
?>
			<table class="table table-condensed table-bordered table-hover">
				<thead class="table_header_fix">
					<tr>
						<!--<th>#</th>-->
						<th>Doctor Name</th>
					<?php if($as_a_whole_num>0){ ?>
						<th>As A Whole</th>
					<?php } ?>
					<?php if($category_num>0){ ?>
						<th>Category Wise</th>
					<?php } ?>
					<?php if($dept_num>0){ ?>
						<th>Department Wise</th>
					<?php } ?>
					<?php if($test_num>0){ ?>
						<th>Test Wise</th>
					<?php } ?>
					</tr>
				</thead>
		<?php
				$same_centre="";
				$n=1;
				$dis_centre_qry=mysqli_query($link, $dis_centre_str);
				while($dis_centre=mysqli_fetch_array($dis_centre_qry))
				{
					if($dis_centre["centreno"]!=$same_centre)
					{
						$same_centre=$dis_centre["centreno"];
						
						if($same_centre=="0")
						{
							//~ $centre_name="All Centre";
							$centre_name="";
						}
						else
						{
							$centre_info=mysqli_fetch_array(mysqli_query($link, " SELECT `centrename` FROM `centremaster` WHERE `centreno`='$same_centre' "));
							$centre_name=$centre_info["centrename"];
						}
						
						echo "<tr><th colspan='3'>$centre_name</th></tr>";
					}
					
					$str="SELECT a.*,b.`ref_name`  FROM `dal_com_setup` a, `refbydoctor_master` b WHERE a.`refbydoctorid`=b.`refbydoctorid`";
					
					$str.=" AND a.`centreno`='$dis_centre[centreno]'";
					
					if($refbydoctorid>0)
					{
						$str.=" AND a.`refbydoctorid`='$refbydoctorid'";
					}
					
					$str.=" GROUP BY `refbydoctorid` ORDER BY b.`ref_name` ASC";
					
					//echo $str;
					
					$qry=mysqli_query($link, $str);
					while($data=mysqli_fetch_array($qry))
					{
			?>
						<tr>
							<!--<td><?php echo $n; ?></td>-->
							<td><?php echo $data["ref_name"]; ?></td>
						<?php
							$com_info=mysqli_fetch_array(mysqli_query($link, " SELECT `com_per`,`com_amount` FROM `dal_com_setup` WHERE `refbydoctorid`='$data[refbydoctorid]' AND `category_id`=0 AND `type_id`=0 AND `testid`=0  AND `centreno`='$dis_centre[centreno]'"));
							
							if($com_info)
							{
						?>
								<td>
									<table class="table table-condensed table-bordered">
										<tr>
											<th>Percentage</th>
											<th>Amount</th>
										</tr>
										<tr>
											<th><?php echo $com_info["com_per"]; ?>%</th>
											<th><?php echo $com_info["com_amount"]; ?></th>
										</tr>
									</table>
								</td>
						<?php
								//echo "<th>".$com_info["com_per"]."% &nbsp;&nbsp;".$com_info["com_amount"]."</th>";
							}
						?>
						<?php if($category_num>0){ ?>
							<td>
								<table class="table table-condensed table-bordered">
									<tr>
										<tr>
											<th>Category Name</th>
											<th>Percentage</th>
											<th>Amount</th>
										</tr>
									</tr>
						<?php
							$cat_str="SELECT `category_id` FROM `dal_com_setup` WHERE `category_id`>0 AND `type_id`=0 AND `testid`=0";
							
							$cat_str.=" AND `centreno`='$dis_centre[centreno]'";
							
							if($data["refbydoctorid"]>0)
							{
								$cat_str.=" AND `refbydoctorid`='$data[refbydoctorid]'";
							}
							
							//~ if($category_id>0)
							//~ {
								//~ $cat_str.=" AND `category_id`='$category_id'";
							//~ }
							$cat_dept_str="SELECT `category_id`, `name` FROM `test_category_master` WHERE `category_id` IN($cat_str)";
							$cat_dept_qry=mysqli_query($link, $cat_dept_str);
							while($cat_dept=mysqli_fetch_array($cat_dept_qry))
							{
								$com_info=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `dal_com_setup` WHERE `refbydoctorid`='$data[refbydoctorid]' AND `category_id`='$cat_dept[category_id]' AND `type_id`=0 AND `testid`=0 AND `centreno`='$dis_centre[centreno]' "));
						?>
								<tr>
									<td><?php echo $cat_dept["name"]; ?></td>
									<th>
										<?php echo $com_info["com_per"]; ?>%
									</th>
									<th>
										<?php echo $com_info["com_amount"]; ?>
									</th>
								</tr>
						<?php
							}
						?>
								</table>
							</td>
						<?php } ?>
						<?php if($dept_num>0){ ?>
							<td>
								<table class="table table-condensed table-bordered">
									<tr>
										<tr>
											<th>Dept Name</th>
											<th>Percentage</th>
											<th>Amount</th>
										</tr>
									</tr>
							<?php
								$cat_str="SELECT `type_id` FROM `dal_com_setup` WHERE `category_id`>0 AND `type_id`>0 AND `testid`=0";
								
								$cat_str.=" AND `centreno`='$dis_centre[centreno]'";
								
								if($data["refbydoctorid"]>0)
								{
									$cat_str.=" AND `refbydoctorid`='$data[refbydoctorid]'";
								}
								
								//~ if($category_id>0)
								//~ {
									//~ $cat_str.=" AND `category_id`='$category_id'";
								//~ }
								
								//~ if($type_id>0)
								//~ {
									//~ $cat_str.=" AND `type_id`='$type_id'";
								//~ }
								
								$cat_dept_str="SELECT `id`, `name` FROM `test_department` WHERE `id` IN($cat_str)";
								$cat_dept_qry=mysqli_query($link, $cat_dept_str);
								while($cat_dept=mysqli_fetch_array($cat_dept_qry))
								{
									$com_info=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `dal_com_setup` WHERE `refbydoctorid`='$data[refbydoctorid]' AND `category_id`>0 AND `type_id`='$cat_dept[id]' AND `testid`=0 AND `centreno`='$dis_centre[centreno]' "));
									if($com_info["com_per"]>0)
									{
							?>
									<tr>
										<td style="width: 60%;"><?php echo $cat_dept["name"]; ?></td>
										<th>
											<?php echo $com_info["com_per"]; ?>%
										</th>
										<th>
											<?php echo $com_info["com_amount"]; ?>
										</th>
									</tr>
							<?php
									}
								}
							?>
								</table>
							</td>
						<?php } ?>
						<?php if($test_num>0){ ?>
							<td>
								<table class="table table-condensed table-bordered">
									<tr>
										<tr>
											<th>Test Name</th>
											<th>Percentage</th>
											<th>Amount</th>
										</tr>
									</tr>
							<?php
								$cat_str="SELECT `testid` FROM `dal_com_setup` WHERE `category_id`>0 AND `type_id`>0 AND `testid`>0";
								
								$cat_str.=" AND `centreno`='$dis_centre[centreno]'";
								
								if($data["refbydoctorid"]>0)
								{
									$cat_str.=" AND `refbydoctorid`='$data[refbydoctorid]'";
								}
								
								//~ if($category_id>0)
								//~ {
									//~ $cat_str.=" AND `category_id`='$category_id'";
								//~ }
								
								//~ if($type_id>0)
								//~ {
									//~ $cat_str.=" AND `type_id`='$type_id'";
								//~ }
								
								//~ if($testid>0)
								//~ {
									//~ $cat_str.=" AND `testid`='$testid'";
								//~ }
								
								$cat_dept_str="SELECT `testid`,`testname` FROM `testmaster` WHERE `testname`!='' AND `testid` IN($cat_str)";
								$cat_dept_qry=mysqli_query($link, $cat_dept_str);
								while($cat_dept=mysqli_fetch_array($cat_dept_qry))
								{
									$com_info=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `dal_com_setup` WHERE `refbydoctorid`='$data[refbydoctorid]' AND `category_id`>0 AND `type_id`>0 AND `testid`='$cat_dept[testid]' AND `centreno`='$dis_centre[centreno]' "));
									if($com_info["com_per"]>0)
									{
							?>
									<tr>
										<td style="width: 60%;"><?php echo $cat_dept["testname"]; ?></td>
										<th>
											<?php echo $com_info["com_per"]; ?>%
										</th>
										<th>
											<?php echo $com_info["com_amount"]; ?>
										</th>
									</tr>
							<?php
									}
								}
							?>
								</table>
							</td>
						<?php } ?>
						</tr>
			<?php
						$n++;
					}
				}
		?>
			</table>
<?php
		}
	}
?>
	</div>
</body>
</html>
