<?php
include('../../includes/connection.php');

$filename ="pat_cancel_export_to_excel.xls";
header('Content-type: application/ms-excel');
header('Content-Disposition: attachment; filename='.$filename);
$date1=$_GET['date1'];
$date2=$_GET['date2'];
$encounter=$_GET['encounter'];
$user_entry=$_GET['user_entry'];
$rupees_symbol="&#x20b9; ";

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
// Date format convert
function convert_date($date)
{
	$timestamp = strtotime($date); 
	$new_date = date('d-M-Y', $timestamp);
	return $new_date;
}
$user="";
if($user_entry>0)
{
	$user=" and `user`='$user_entry'";
}
$type="";
if($encounter>0)
{
	$type=" and `type`='$encounter'";
}
?>
<table class="table table-bordered table-condensed table-report">
	<tr>
		<th>#</th>
		<th>UHID / PIN</th>
		<th>Bill No</th>
		<th>Name</th>
		<th>Cancel date</th>
		<th><span class="text-right">Bill Amount</span></th>
		<th><span class="text-right">Discount</span></th>
		<th><span class="text-right">Reason</span></th>
		<th><span class="text-right">User</span></th>
		<th><span class="text-right">Encounter</span></th>
	</tr>
	<?php
			$i=1;
			$cashamt=0;
			$disamt=0;
			$patientdel=mysqli_query($link, "SELECT * FROM `cancel_payment` WHERE `date` between '$date1' and '$date2' $user $type order by `date`  ");
			
			while($d=mysqli_fetch_array($patientdel))
			{
				$pat_info=mysqli_fetch_array(mysqli_query($link, "select * from patient_info where patient_id='$d[patient_id]'"));
				
				$pat_typ_text=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `patient_type_master` WHERE `p_type_id`='$d[type]' "));
				$encounter=$pat_typ_text['p_type'];
				
				if($d["type"]==1) // OPD
				{
					$cashamt=$cashamt+$d['amount'];
					$disamt=$disamt+$d['discount'];
					//$encounter="OPD";
				}
				if($d["type"]==2) // LAB
				{
					$cashamt=$cashamt+$d['amount'];
					$disamt=$disamt+$d['discount'];
					//$encounter="Lab";
				}
				if($d["type"]==3) // IPD
				{
					$cashamt=$cashamt+$d['amount'];
					$disamt=$disamt+$d['discount'];
					//$encounter="IPD";
				}
				if($d["type"]>3) // Casualty
				{
					$cashamt=$cashamt+$d['amount'];
					$disamt=$disamt+$d['discount'];
					//$encounter="Casualty";
				}
				
				
				$quser=mysqli_fetch_array(mysqli_query($link, " SELECT `name` FROM `employee` WHERE `emp_id`='$d[user]' "));
	?>
			<tr>
				<td><?php echo $i;?></td>
				<td><?php echo $pat_info['uhid']." / ".$d['ipd_id'];?></td>
				<td><?php echo $d['bill_no'];?></td>
				<td><?php echo $pat_info['name'];?></td>
				<td><?php echo convert_date($d['date']);?></td>
				<td><span class="text-right"><?php echo $rupees_symbol.number_format($d['amount'],2);?></span></td>
				<td><span class="text-right"><?php echo $rupees_symbol.number_format($d['discount'],2);?></span></td>
				<td><span class="text-right"><?php echo $d['reason'];?></span></td>
				<td><span class="text-right"><?php echo $quser['name'];?></span></td>
				<td><span class="text-right"><?php echo $encounter;?></span></td>
			</tr>
	<?php
				$i++;
			}
		?>
	<tr>
		<td colspan="4">&nbsp;</td>
	  <td colspan=""><span class="text-right"><strong>Total :</strong></span></td>
	  <td><span class="text-right"><strong><?php echo $rupees_symbol.number_format($cashamt,2);?> </strong></span></td>
	  <td><span class="text-right"><strong><?php echo $rupees_symbol.number_format($disamt,2);?> </strong></span></td>
	  <td colspan="3">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="4">&nbsp;</td>
	  <td colspan=""><span class="text-right"><strong>Total Cancel Amount :</strong></span></td>
	  <td><span class="text-right"><strong><?php echo $rupees_symbol.number_format(($cashamt+$disamt),2);?> </strong></span></td>
	  <td colspan="4">&nbsp;</td>
	</tr>
</table>
<link rel="stylesheet" href="../../css/bootstrap.min.css" />
<link rel="stylesheet" href="../../css/custom.css" />
