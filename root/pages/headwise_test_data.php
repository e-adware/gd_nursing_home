<?php
include("../../includes/connection.php");

$date1=$_POST['date1'];
$date2=$_POST['date2'];
$branch_id=$_POST['branch_id'];

// Date format convert
function convert_date($date)
{
	$timestamp = strtotime($date); 
	$new_date = date('d M Y', $timestamp);
	return $new_date;
}

if($_POST["type"]=="head_wise_detail_pat")
{
	$encounter=$_POST["encounter"];
	$head_id=$_POST["head_id"];
	
	$head=mysqli_fetch_array(mysqli_query($link, " select type_name from testmaster where type_id='$head_id' "));
	
	if($encounter==3)
	{
		$str=" SELECT DISTINCT a.`opd_id`,a.`ipd_id` FROM `patient_test_details` a, `uhid_and_opdid` b WHERE a.`patient_id`=b.`patient_id` and a.`ipd_id`=b.`opd_id` and `testid` in ( SELECT `testid` FROM `testmaster` WHERE `type_id`='$head_id' ) AND a.`date` BETWEEN '$date1' AND '$date2' AND a.`ipd_id` IN( SELECT `opd_id` FROM `uhid_and_opdid` WHERE `type`='$encounter' ) and b.`branch_id`='$branch_id' ";
	}
	else
	{
		$str=" SELECT DISTINCT a.`opd_id`,a.`ipd_id` FROM `patient_test_details` a, `uhid_and_opdid` b WHERE a.`patient_id`=b.`patient_id` and a.`opd_id`=b.`opd_id` and a.`testid` in ( SELECT `testid` FROM `testmaster` WHERE `type_id`='$head_id' ) AND a.`date` BETWEEN '$date1' AND '$date2' AND a.`ipd_id`='' and b.`branch_id`='$branch_id' "; // AND `opd_id` IN( SELECT `opd_id` FROM `uhid_and_opdid` WHERE `type`='$encounter' )
	}
	
?>
	<p style="margin-top: 2%;"><b>Patient Wise Test Details from:</b> <?php echo convert_date($date1)." to ".convert_date($date2); ?></p>
		
		<b>Department Name: <?php echo $head["type_name"]; ?></b>
		<button type="button" class="btn btn-print btn-mini text-right print_div" onclick="print_page('<?php echo $_POST["type"];?>','<?php echo $date1;?>','<?php echo $date2;?>','<?php echo $head_id;?>','<?php echo $encounter;?>','<?php echo $branch_id;?>')" style="margin-right: 1%;"><i class="icon-print icon-large"></i> Print</button>
		
		<a class="btn btn-excel btn-mini text-right print_div" href="pages/headwise_patwise_report_excel.php?date1=<?php echo $date1;?>&date2=<?php echo $date2;?>&head_id=<?php echo $head_id;?>&encounter=<?php echo $encounter;?>&branch_id=<?php echo $branch_id;?>"style="margin-right: 1%;"><i class="icon-file icon-large"></i> Excel</a>
	</p>
	<table class="table table-hover">
		<thead class="table_header_fix">
			<tr>
				<th>#</th>
				<!--<th>UHID</th>-->
				<th>Bill No</th>
				<th>Patient Name</th>
				<th>Ref Doctor</th>
				<th>Test Name</th>
				<th>Date</th>
				<th>Total Amount</th>
				<th>Encounter</th>
			</tr>
		</thead>
	<?php
		$n=1;
		$grand_tot=0;
		//$qry=mysqli_query($link, " SELECT DISTINCT `opd_id`,`ipd_id` FROM `patient_test_details` WHERE `testid` in ( SELECT `testid` FROM `testmaster` WHERE `type_id`='$head_id' )  AND `date` BETWEEN '$date1' AND '$date2' AND `opd_id` in ( SELECT `opd_id` FROM `uhid_and_opdid` $encounter_str ) ");
		$qry=mysqli_query($link, $str);
		while($dis_ipd=mysqli_fetch_array($qry))
		{
			if($dis_ipd["opd_id"])
			{
				$pin=$dis_ipd["opd_id"];
			}
			if($dis_ipd["ipd_id"])
			{
				$pin=$dis_ipd["ipd_id"];
			}
			
			$pat_reg=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `uhid_and_opdid` WHERE `opd_id`='$pin' "));
			
			$pat_info=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `patient_info` WHERE `patient_id`='$pat_reg[patient_id]' "));
			
			$ref_doc=mysqli_fetch_array(mysqli_query($link, " SELECT `ref_name` FROM `refbydoctor_master` WHERE `refbydoctorid`='$pat_reg[refbydoctorid]' "));
			
			$pat_typ_text=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `patient_type_master` WHERE `p_type_id`='$pat_reg[type]' "));
			$Encounter=$pat_typ_text['p_type'];
			
			$all_test="";
			$tot_test=0;
			$z=1;
			$test_qry=mysqli_query($link, " SELECT a.`test_rate`, b.`testname` FROM `patient_test_details` a, `testmaster` b WHERE a.`patient_id`='$pat_reg[patient_id]' AND a.`opd_id`='$dis_ipd[opd_id]' AND a.`ipd_id`='$dis_ipd[ipd_id]' AND b.`type_id`='$head_id' AND a.`testid`=b.`testid` ");
			while($test=mysqli_fetch_array($test_qry))
			{
				$all_test.=$z.". ".$test["testname"]."<br>";
				$tot_test+=$test["test_rate"];
				
				$z++;
			}
		?>
			<tr>
				<td><?php echo $n; ?></td>
				<!--<td><?php echo $pat_info['patient_id']; ?></td>-->
				<td><?php echo $pin; ?></td>
				<td><?php echo $pat_info['name']; ?></td>
				<td><?php echo $ref_doc['ref_name']; ?></td>
				<td><?php echo $all_test; ?></td>
				<td><?php echo date("d-M-Y", strtotime($pat_reg["date"])); ?></td>
				<td style="text-align:right;"><?php echo number_format($tot_test,2); ?></td>
				<td><?php echo $Encounter; ?></td>
			</tr>
		<?php
			$n++;
			$grand_tot+=$tot_test;
		}
	?>
		<tr>
			<th colspan="5"></th>
			<th colspan="1"><span class="text-right">Total</span></th>
			<td style="text-align:right;"><?php echo number_format($grand_tot,2); ?></td>
			<td></td>
		</tr>
	</table>
<?php
}

if($_POST["type"]=="head_wise_test_detail")
{
	$encounter=$_POST["encounter"];
	$head_id=$_POST["head_id"];
	
	$head=mysqli_fetch_array(mysqli_query($link, " select type_name from testmaster where type_id='$head_id' "));
	
	//$encounter_str=" AND c.`type`='$encounter'";
	
	if($encounter==3)
	{
		$encounter_str=" AND a.opd_id='' AND a.ipd_id!=''";
	}
	else
	{
		$encounter_str=" AND a.opd_id!='' AND a.ipd_id=''";	
	}
	
?>
	<p style="margin-top: 2%;"><b>Department Wise Test Details from:</b> <?php echo convert_date($date1)." to ".convert_date($date2); ?>
		<br>
		<b>Department Name: <?php echo $head["type_name"]; ?></b>
		<button type="button" class="btn btn-print btn-mini text-right print_div" onclick="print_page('<?php echo $_POST["type"];?>','<?php echo $date1;?>','<?php echo $date2;?>','<?php echo $head_id;?>','<?php echo $encounter;?>','<?php echo $branch_id;?>')" style="margin-right: 1%;"><i class="icon-print icon-large"></i> Print</button>
		
		<a class="btn btn-excel btn-mini text-right print_div" href="pages/headwise_testwise_report_excel.php?date1=<?php echo $date1;?>&date2=<?php echo $date2;?>&head_id=<?php echo $head_id;?>&encounter=<?php echo $encounter;?>&branch_id=<?php echo $branch_id;?>"style="margin-right: 1%;"><i class="icon-file icon-large"></i> Excel</a>
	</p>
	<table class="table table-hover">
		<thead class="table_header_fix">
			<tr>
				<th>#</th>
				<th>Test Name</th>
				<th>No. of Test</th>
				<th>Total Amount</th>
			</tr>
		</thead>
	<?php
		
		$n=1;
		$total_test_num=0;
		$grand_total=0;
		
		$qry=mysqli_query($link, " SELECT DISTINCT a.`testid` FROM `patient_test_details` a, `testmaster` b , `uhid_and_opdid` c WHERE a.`testid`=b.`testid` AND a.`patient_id`=c.`patient_id` AND (a.`opd_id`=c.`opd_id` OR a.`ipd_id`=c.`opd_id`) AND a.`date` BETWEEN '$date1' AND '$date2' AND b.`type_id`='$head_id' $encounter_str and c.`branch_id`='$branch_id' ORDER BY b.`testname` ");
		
		//~ $qry=mysqli_query($link, " SELECT DISTINCT a.`testid` FROM `patient_test_details` a, `testmaster` b WHERE a.`testid`=b.`testid` AND a.`date` BETWEEN '$date1' AND '$date2' AND b.`type_id`='$head_id' $encounter_str ORDER BY b.`testname` ");
		while($dist_testid=mysqli_fetch_array($qry))
		{
			$test_info=mysqli_fetch_array(mysqli_query($link, " SELECT `testname` FROM `testmaster` WHERE `testid`='$dist_testid[testid]' "));
			
			$test_num=mysqli_num_rows(mysqli_query($link, " SELECT * FROM `patient_test_details`  a, `uhid_and_opdid` c WHERE a.`patient_id`=c.`patient_id` AND (a.`opd_id`=c.`opd_id` OR a.`ipd_id`=c.`opd_id`) AND a.`testid`='$dist_testid[testid]' AND a.`date` BETWEEN '$date1' AND '$date2' $encounter_str and c.`branch_id`='$branch_id' "));
			
			$test_amount=mysqli_fetch_array(mysqli_query($link, " SELECT SUM(`test_rate`) AS `test_sum` FROM `patient_test_details` a, `uhid_and_opdid` c WHERE a.`patient_id`=c.`patient_id` AND (a.`opd_id`=c.`opd_id` OR a.`ipd_id`=c.`opd_id`) AND a.`testid`='$dist_testid[testid]' AND a.`date` BETWEEN '$date1' AND '$date2' $encounter_str and c.`branch_id`='$branch_id' "));
		?>
			<tr>
				<td><?php echo $n;?></td>
				<td><?php echo $test_info['testname'];?></td>
				<td><?php echo $test_num;?></td>
				<td style="text-align:right;"><?php echo number_format($test_amount["test_sum"],2);?></td>
			</tr>
		<?php
			$total_test_num+=$test_num;
			$grand_total+=$test_amount["test_sum"];
			$n++;
		}
	?>
		<tr>
			<th colspan="2"><span class="text-right">Grand Total: </span></th>
			<td><?php echo $total_test_num; ?></td>
			<td style="text-align:right;"><?php echo number_format($grand_total,2); ?></td>
		</tr>
	</table>
<?php
}


if($_POST["type"]=="qwqwqwqwqwq")
{
?>
	<p style="margin-top: 2%;"><b>Details from:</b> <?php echo convert_date($date1)." to ".convert_date($date2); ?></p>
	
<?php
}
?>
