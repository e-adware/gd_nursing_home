<?php
include("../../includes/connection.php");

$time=date('H:i:s');
$date=date("Y-m-d");

$type=$_POST['type'];

// Age Calculator
function age_calculator($dob)
{
	$from = new DateTime($dob);
	$to   = new DateTime('today');
	$year=$from->diff($to)->y;
	$month=$from->diff($to)->m;
	if($year==0)
	{
		//$month=$from->diff($to)->m;
		if($month==0)
		{
			$day=$from->diff($to)->d;
			return $day." Days";
		}else
		{
			return $month." Months";
		}
	}else
	{
		return $year.".".$month." Years";
	}
}
// Date format convert
function convert_date($date)
{
	if($date)
	{
		$timestamp = strtotime($date); 
		$new_date = date('d-M-Y', $timestamp);
		return $new_date;
	}
}

if($type=="view_admit_pat_list")
{
	$typ=$_POST['typ'];
	$date1=$_POST['date1'];
	$date2=$_POST['date2'];
	$doc=$_POST['consultantdoctorid'];
	if($typ=="admit")
	{
		$doc_type="Admited Doctor";
		if($doc==0)
		{
			$q="SELECT * FROM `uhid_and_opdid` WHERE `date` BETWEEN '$date1' AND '$date2' AND `type`='3'";
		}
		else
		{
			$q="SELECT * FROM `uhid_and_opdid` WHERE `date` BETWEEN '$date1' AND '$date2' AND `opd_id` IN (SELECT `ipd_id` FROM `ipd_pat_doc_details` WHERE `admit_doc`='$doc') AND `type`='3'";
		}
	}
	if($typ=="attend")
	{
		$doc_type="Consultant Doctor";
		if($doc==0)
		{
			$q="SELECT * FROM `uhid_and_opdid` WHERE `date` BETWEEN '$date1' AND '$date2' AND `type`='3'";
		}
		else
		{
			$q="SELECT * FROM `uhid_and_opdid` WHERE `date` BETWEEN '$date1' AND '$date2' AND `opd_id` IN (SELECT `ipd_id` FROM `ipd_pat_doc_details` WHERE `attend_doc`='$doc') AND `type`='3'";
		}
	}
	//echo $q;
	$qry=mysqli_query($link,$q);
	
	?>
	<button type="button" class="btn btn-info" onclick="print_page('<?php echo $typ;?>','<?php echo $date1;?>','<?php echo $date2;?>','<?php echo $doc;?>')"><i class="icon-print icon-large"></i> Print</button>
	
	<a class="btn btn-info" href="pages/admit_pat_reports_excel.php?date1=<?php echo $date1;?>&date2=<?php echo $date2;?>&doc=<?php echo $doc;?>&typ=<?php echo $typ;?>" ><i class="icon-file icon-large"></i>Excel</a>
	
	<table class="table table-condensed table-bordered">
		<tr>
			<th>#</th><th>UHID</th><th>PIN</th><th>Name</th><th>Sex</th><th>Age (Dob)</th><th><?php echo $doc_type;?></th><th>User</th><th>Date / Time</th>
		</tr>
	<?php
	$i=1;
		
		while($r=mysqli_fetch_array($qry))
		{
			$doc_name="";
			$pat=mysqli_fetch_array(mysqli_query($link,"SELECT `uhid`,`name`,`sex`,`dob`,`age`,`age_type`,`phone` FROM `patient_info` WHERE `patient_id`='$r[patient_id]'"));
			if($pat["dob"]!=""){ $age=age_calculator($pat["dob"])." (".$pat["dob"].")"; }else{ $age=$pat["age"]." ".$pat["age_type"]; }
			if($doc_type=="Admited Doctor")
			{
				$d=mysqli_fetch_array(mysqli_query($link,"SELECT `Name` FROM `consultant_doctor_master` WHERE `consultantdoctorid` IN (SELECT `admit_doc` FROM `ipd_pat_doc_details` WHERE `patient_id`='$r[patient_id]' AND `ipd_id`='$r[opd_id]')"));
				$doc_name=$d['Name'];
			}
			if($doc_type=="Consultant Doctor")
			{
				$d=mysqli_fetch_array(mysqli_query($link,"SELECT `Name` FROM `consultant_doctor_master` WHERE `consultantdoctorid` IN (SELECT `attend_doc` FROM `ipd_pat_doc_details` WHERE `patient_id`='$r[patient_id]' AND `ipd_id`='$r[opd_id]')"));
				$doc_name=$d['Name'];
			}
			$u=mysqli_fetch_array(mysqli_query($link,"SELECT `name` FROM `employee` WHERE `emp_id`='$r[user]'"));
		?>
		<tr>
			<td><?php echo $i;?></td>
			<td><?php echo $pat['uhid'];?></td>
			<td><?php echo $r['opd_id'];?></td>
			<td><?php echo $pat['name'];?></td>
			<td><?php echo $pat['sex'];?></td>
			<td><?php echo $age;?></td>
			<td><?php echo $doc_name;?></td>
			<td><?php echo $u['name'];?></td>
			<td><?php echo convert_date($r['date'])." ".$r['time'];?></td>
		</tr>
		<?php
		$i++;}
	?>
	</table>
	<?php
}

if($type=="")
{
	
}
?>
