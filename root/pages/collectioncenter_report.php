<?php
include("../../includes/connection.php");
require('../../includes/global.function.php');

$type=$_POST['type'];

if($type=="collectionreport")
{
	$cid=$_POST['cid'];
	$branch_id=$_POST['branch_id'];
	$fdate=$_POST['fdate'];
	$tdate=$_POST['tdate'];
	$rep=$_POST['rep'];
	
	$center_info=mysqli_fetch_array(mysqli_query($link,"SELECT `centreno`, `centrename` FROM `centremaster` WHERE `centreno`='$cid' "));
	
	if($rep==1)
	{
		?>
		<span class="text-right" id="print_div"><button type="button" class="btn btn-info" onclick="print_rep('1','<?php echo $cid;?>','<?php echo $branch_id;?>')">Print</button></span>
		<table class="table table-condensed table-bordered">
			<thead class="table_header_fix">
				<tr>
					<th>#</th>
					<th>Date</th>
					<!--<th>UHID</th>-->
					<th>Bill No.</th>
					<th>Name</th>
					<th>Age/Sex</th>
					<th>Refer Doctor</th>
					<th style="text-align:right;">Total Amount</th>
					<th style="text-align:right;">Discount</th>
					<th style="text-align:right;">Paid</th>
					<th style="text-align:right;">Balance</th>
					<th>User</th>
				</tr>
			</thead>
		<?php
		//$center_str="SELECT `centreno`, `centrename` FROM `centremaster`";
		$center_str="SELECT DISTINCT a.`centreno`, a.`centrename` FROM `centremaster` a, `uhid_and_opdid` b WHERE a.`centreno`=b.`center_no` AND b.`date` BETWEEN '$fdate' AND '$tdate'";
		if($cid)
		{
			//$center_str="SELECT `centreno`, `centrename` FROM `centremaster`";
			$center_str.=" AND a.`centreno`='$cid'";
		}
		$center_str.=" AND b.`branch_id`='$branch_id' ORDER BY a.`centrename` ASC";
		//echo $center_str;
		$tot=$bal=$dis=$paid=0;
		$center_qry=mysqli_query($link, $center_str);
		while($center_info=mysqli_fetch_array($center_qry))
		{
			echo "<tr><td colspan='10'><b>Centre Name : ".$center_info['centrename']."</b></td></tr>";
			
			$str="SELECT * FROM `uhid_and_opdid` WHERE `center_no`='$center_info[centreno]' AND `date` BETWEEN '$fdate' AND '$tdate'";
		
			$qry=mysqli_query($link, $str);
				$i=1;
				while($reg_info=mysqli_fetch_array($qry))
				{
					$pat_info=mysqli_fetch_array(mysqli_query($link,"SELECT * FROM `patient_info` WHERE `patient_id`='$reg_info[patient_id]'"));
					
					$rdoc=mysqli_fetch_array(mysqli_query($link,"SELECT `ref_name` FROM `refbydoctor_master` WHERE `refbydoctorid`='$reg_info[refbydoctorid]'"));
					
					$pat_pay_detail=mysqli_fetch_array(mysqli_query($link,"SELECT * FROM `invest_patient_payment_details` WHERE `patient_id`='$reg_info[patient_id]' AND `opd_id`='$reg_info[opd_id]'"));
					$user_info=mysqli_fetch_array(mysqli_query($link, " SELECT `name` FROM `employee` WHERE `emp_id`='$reg_info[user]' "));
			?>
					<tr>
						<td><?php echo $i;?></td>
						<td><?php echo convert_date_g($reg_info['date']);?></td>
						<!--<td><?php echo $rr['uhid'];?></td>-->
						<td><?php echo $reg_info['opd_id'];?></td>
						<td><?php echo $pat_info['name'];?></td>
						<td><?php echo $pat_info['age'];?> <?php echo $pat_info['age_type'];?>/<?php echo $pat_info['sex'];?></td>
						<td><?php echo $rdoc['ref_name'];?></td>
						<td style="text-align:right;"><?php echo number_format($pat_pay_detail['tot_amount'],2);?></td>
						<td style="text-align:right;"><?php echo number_format($pat_pay_detail['dis_amt'],2);?></td>
						<td style="text-align:right;"><?php echo number_format($pat_pay_detail['advance'],2);?></td>
						<td style="text-align:right;"><?php echo number_format($pat_pay_detail['balance'],2);?></td>
						<td><?php echo $user_info['name'];?></td>
					</tr>
			<?php
					$tot+=$pat_pay_detail['tot_amount'];
					$bal+=$pat_pay_detail['balance'];
					$dis+=$pat_pay_detail['dis_amt'];
					$paid+=$pat_pay_detail['advance'];
					$i++;
				}
			?>
<?php
		}
		?>
			<tr>
				<th colspan="5" style="text-align:right;">Total Amount</th>
				<th style="text-align:right;"><?php echo number_format($tot,2);?></th>
				<th style="text-align:right;"><?php echo number_format($dis,2);?></th>
				<th style="text-align:right;"><?php echo number_format($paid,2);?></th>
				<th style="text-align:right;"><?php echo number_format($bal,2);?></th>
				<td></td>
			</tr>
		</table>
		<?php
	}
	if($rep==2)
	{
		//~ $center_str="SELECT `centreno`, `centrename` FROM `centremaster`";
		$center_str="SELECT DISTINCT a.`centreno`, a.`centrename` FROM `centremaster` a, `uhid_and_opdid` b WHERE a.`centreno`=b.`center_no` AND b.`date` BETWEEN '$fdate' AND '$tdate'";
		if($cid)
		{
			//$center_str="SELECT `centreno`, `centrename` FROM `centremaster`";
			$center_str.=" AND a.`centreno`='$cid'";
		}
		$center_str.=" AND b.`branch_id`='$branch_id' ORDER BY a.`centrename` ASC";
		//echo $center_str;
		?>
		<span class="text-right" id="print_div"><button type="button" class="btn btn-info" onclick="print_rep('2','<?php echo $cid;?>','<?php echo $branch_id;?>')">Print</button></span>
		<table class="table table-condensed table-bordered">
			<thead class="table_header_fix">
				<tr>
					<th>#</th>
					<th>Date</th>
					<th>Bill No.</th>
					<th>Patient Name</th>
					<th>Age/sex</th>
					<th>Test Performed</th>
					<th style="text-align:right;">Test Rate</th>
					<th style="text-align:right;">Amount</th>
					<th style="text-align:right;">Discount</th>
					<th style="text-align:right;">Paid</th>
					<th style="text-align:right;">Balance</th>
					<th>User</th>
				</tr>
			</thead>
		<?php
		$tot_rate=$tot_dis=$tot_net=0;
		$center_qry=mysqli_query($link, $center_str);
		while($center_info=mysqli_fetch_array($center_qry))
		{
			echo "<tr><td colspan='11'><b>Centre Name : ".$center_info['centrename']."</b></td></tr>";
			
			$str="SELECT * FROM `uhid_and_opdid` WHERE `center_no`='$center_info[centreno]' AND `date` BETWEEN '$fdate' AND '$tdate'";
		
			$qry=mysqli_query($link, $str);
				$i=1;
				while($reg_info=mysqli_fetch_array($qry))
				{
					$pat_info=mysqli_fetch_array(mysqli_query($link,"SELECT * FROM `patient_info` WHERE `patient_id`='$reg_info[patient_id]'"));
					
					$pat_pay_detail=mysqli_fetch_array(mysqli_query($link,"SELECT * FROM `invest_patient_payment_details` WHERE `patient_id`='$reg_info[patient_id]' AND `opd_id`='$reg_info[opd_id]'"));
					$user_info=mysqli_fetch_array(mysqli_query($link, " SELECT `name` FROM `employee` WHERE `emp_id`='$reg_info[user]' "));
					
					$dis_per=round(($pat_pay_detail["dis_amt"]/$pat_pay_detail["tot_amount"])*100,2);
					
					$pat_test_qry=mysqli_query($link,"SELECT * FROM `patient_test_details` WHERE `patient_id`='$reg_info[patient_id]' AND `opd_id`='$reg_info[opd_id]'");
					
					$ts=1;
					$pat_test_num=mysqli_num_rows($pat_test_qry);
					while($pat_test=mysqli_fetch_array($pat_test_qry))
					{
						$test_info=mysqli_fetch_array(mysqli_query($link," SELECT `testname` FROM `testmaster` WHERE `testid`='$pat_test[testid]' "));
						
						$discount=round(($pat_test["test_rate"]*$dis_per)/100,2);
						
						$net_test_amount=$pat_test["test_rate"]-$discount;
			?>
						<tr>
					<?php
						if($ts==1){
					?>
							<td rowspan="<?php echo $pat_test_num; ?>"><?php echo $i;?></td>
							<td rowspan="<?php echo $pat_test_num; ?>"><?php echo convert_date_g($reg_info['date']);?></td>
							<td rowspan="<?php echo $pat_test_num; ?>"><?php echo $reg_info['opd_id'];?></td>
							<td rowspan="<?php echo $pat_test_num; ?>"><?php echo $pat_info['name'];?></td>
							<td rowspan="<?php echo $pat_test_num; ?>"><?php echo $pat_info['age'];?> <?php echo $pat_info['age_type'];?>/<?php echo $pat_info['sex'];?></td>
					<?php } ?>
							<td><?php echo $test_info["testname"]; ?></td>
							<td style="text-align:right;"><?php echo $pat_test["test_rate"]; ?></td>
						<?php
						if($ts==1){
						?>
							<td style="text-align:right;" rowspan="<?php echo $pat_test_num; ?>"><?php echo number_format($pat_pay_detail['tot_amount'],2);?></td>
							<td style="text-align:right;" rowspan="<?php echo $pat_test_num; ?>"><?php echo number_format($pat_pay_detail['dis_amt'],2);?></td>
							<td style="text-align:right;" rowspan="<?php echo $pat_test_num; ?>"><?php echo number_format($pat_pay_detail['advance'],2);?></td>
							<td style="text-align:right;" rowspan="<?php echo $pat_test_num; ?>"><?php echo number_format($pat_pay_detail['balance'],2);?></td>
							<td rowspan="<?php echo $pat_test_num; ?>"><?php echo $user_info["name"]; ?></td>
							<?php } ?>
						</tr>
			<?php
						$ts++;
					}
					$i++;
					$tot_rate+=$pat_pay_detail['tot_amount'];
					$tot_dis+=$pat_pay_detail['dis_amt'];
					$tot_paid+=$pat_pay_detail['advance'];
					$tot_bal+=$pat_pay_detail['balance'];
				}
			
		}
		?>
		<tr>
			<th colspan="6" style="text-align:right;">Total</th>
			<th style="text-align:right;"><?php echo number_format($tot_rate,2); ?></th>
			<th style="text-align:right;"><?php echo number_format($tot_dis,2); ?></th>
			<th style="text-align:right;"><?php echo number_format($tot_paid,2); ?></th>
			<th style="text-align:right;"><?php echo number_format($tot_bal,2); ?></th>
			<td></td>
		</tr>
		</table>
		<?php
	}
}
?>
