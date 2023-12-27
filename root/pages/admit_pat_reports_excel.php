<?php
session_start();
$c_user=trim($_SESSION['emp_id']);

include'../../includes/connection.php';

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

$date1=$_GET['date1'];
$date2=$_GET['date2'];
$doc=$_GET['doc'];
$typ=$_GET['typ'];

if($typ=="admit")
{
	$filename ="admit_patient_reports_".$date1."_to_".$date2.".xls";
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
	$filename ="attend_patient_reports_".$date1."_to_".$date2.".xls";
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

header('Content-type: application/ms-excel');
header('Content-Disposition: attachment; filename='.$filename);

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
	</div>
</body>
</html>
