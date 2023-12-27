<?php include('../../includes/connection.php');


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
// Time format convert
function convert_time($time)
{
	$time = date("g:i A", strtotime($time));
	return $time;
}
	$typ=$_GET['typ'];
	$date1=$_GET['fdate'];
	$date2=$_GET['tdate'];
	$doc=$_GET['doc'];
	if($typ=="admit")
	{
		$doc_type="Admited Doctor";
		$head="Admited Doctor Patient List";
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
		$doc_type="Consulatnt Doctor";
		$head="Consultant Doctor Patient List";
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
<html>
<head>
	<title>Admit Patient List Report</title>
	<link href="../../css/bootstrap.min.css" type="text/css" rel="stylesheet"/>
</head>
<body>
	<div class="container-fluid">
		<div class="row">
			<div class="">
				<?php include('page_header.php');?>
			</div>
		</div>
		<center><h4><?php echo $head;?></h4></center>
		<table class="table table-condensed table-bordered" style="font-size:12px;">
			<tr>
				<th>UHID</th><th>PIN</th><th>Name</th><th>Sex</th><th>Age (Dob)</th><th><?php echo $doc_type;?></th><th>User</th><th>Date / Time</th>
			</tr>
		<?php
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
				if($doc_type=="Consulatnt Doctor")
				{
					$d=mysqli_fetch_array(mysqli_query($link,"SELECT `Name` FROM `consultant_doctor_master` WHERE `consultantdoctorid` IN (SELECT `attend_doc` FROM `ipd_pat_doc_details` WHERE `patient_id`='$r[patient_id]' AND `ipd_id`='$r[opd_id]')"));
					$doc_name=$d['Name'];
				}
				$u=mysqli_fetch_array(mysqli_query($link,"SELECT `name` FROM `employee` WHERE `emp_id`='$r[user]'"));
			?>
			<tr>
				<td><?php echo $pat['uhid'];?></td>
				<td><?php echo $r['opd_id'];?></td>
				<td><?php echo $pat['name'];?></td>
				<td><?php echo $pat['sex'];?></td>
				<td><?php echo $age;?></td>
				<td><?php echo $doc_name;?></td>
				<td><?php echo $u['name'];?></td>
				<td><?php echo convert_date($r['date'])." ".convert_time($r['time']);?></td>
			</tr>
			<?php
			}
		?>
		</table>
	</div>
</body>
</html>

