<?php
include("../../includes/connection.php");
include("../../includes/global.function.php");
include("../../includes/idgeneration.function.php");

$date=date("Y-m-d"); // important
$date1=date("Y-m-d");
$time=date("H:i:s");

function convert_date_only_sm_year($date)
{
	$timestamp = strtotime($date); 
	$new_date = date('y', $timestamp);
	return $new_date;
}

if($_POST["type"]=="cntermaster_id") //// cntermaster_id
{
	echo $vid=nextId("C","centremaster","centreno","100");
}
if($_POST["type"]=="cntermaster") //// cntermaster
{
	$branch_id=$_POST['branch_id'];
	$val=$_POST['val'];
	if($val)
	{
		$q="SELECT * FROM `centremaster` WHERE `centrename` like '$val%' AND `branch_id`='$branch_id'";
	}
	else
	{
		$q="SELECT * FROM `centremaster` where `branch_id`='$branch_id' order by `centrename`";
	}
	
	$qrpdct=mysqli_query($link, $q);
	$i=1;
?>
<table class="table table-striped table-bordered table-condensed">
	<tr>
		<th>ID</th>
		<th>Name</th>
		<th>Address</th>
		<th>Phone</th>
		<th></th>
	</tr>
<?php
	while($qrpdct1=mysqli_fetch_array($qrpdct))
	{
?>
		<tr style="cursor:pointer"  onclick="javascript:val_load_new('<?php echo $qrpdct1['centreno'];?>')" id="rad_test<?php echo $i;?>">
			<td id="prod<?php echo $i;?>"><?php echo $qrpdct1['centreno'];?></td>
			<td><?php echo $qrpdct1['centrename'];?></td>
			<td><?php echo $qrpdct1['add1'];?></td>
			<td><?php echo $qrpdct1['phoneno'];?></td>
			<td><span onclick="delete_data('<?php echo $qrpdct1['centreno'];?>')"> <img height="15" width="15" src="../images/delete.ico"/></a></td>
		</tr>
<?php	
		$i++;
	}
?>
</table>
<?php
}
if($_POST["type"]=="cntermaster_load") //// cntermaster_load
{
	$tid=$_POST['doid1'];
	$qrm=mysqli_fetch_array(mysqli_query($link, "select * from centremaster where centreno='$tid' "));
	$val=$tid.'#'.$qrm[centrename].'#'.$qrm[add1].'#'.$qrm[phoneno].'#'.$qrm[credit_limit].'#'.$qrm[allow_credit].'#'.$qrm[insurance].'#'.$qrm[backup];
	echo $val;
}

if($_POST["type"]=="cntermaster_save")
{
	$branch_id=$_POST['branch_id'];
	$cid=$_POST['cid'];
	$cname=$_POST['cname'];
	$address=$_POST['address'];
	$phone=$_POST['phone'];
	$crdtlmt=$_POST['crdtlmt'];
	 
	$credit=$_POST['credit'];
	
	$vinsurance=$_POST['vinsurance'];
	$backup=$_POST['backup'];
	
	if(!$crdtlmt){ $crdtlmt=0; }
	if(!$credit){ $credit=0; }
	if(!$vinsurance){ $vinsurance=0; }
	if(!$backup){ $backup=0; }
	
	$qr=mysqli_fetch_array(mysqli_query($link, "select centreno from centremaster where centreno='$cid'"));
	if($qr)
	{
		mysqli_query($link, "update centremaster set branch_id='$branch_id',centrename='$cname',add1='$address',phoneno='$phone',credit_limit='$crdtlmt',allow_credit='$credit',insurance='$vinsurance',backup='$backup' where centreno='$cid'");
		
		mysqli_query($link, " UPDATE `patient_source_master` SET `source_type`='$cname' WHERE `centreno`='$cid' ");
	}
	else
	{
		mysqli_query($link, "insert into centremaster(branch_id,centreno,centrename,add1,phoneno,credit_limit,allow_credit,insurance,backup) values('$branch_id','$cid','$cname','$address','$phone','$crdtlmt','$credit','$vinsurance','$backup')");
		
		$qr=mysqli_fetch_array(mysqli_query($link, "select centreno from centremaster where centreno='$cid'"));
		if($qr)
		{
			mysqli_query($link, " INSERT INTO `patient_source_master`(`source_type`, `centreno`, `type`) VALUES ('$cname','$cid','0') ");
		}
	}
}
if($_POST["type"]=="cntermaster_delete")
{
	$subp=$_POST['subp'];
	mysqli_query($link, " DELETE FROM `centremaster` WHERE `centreno`='$subp' ");
	mysqli_query($link, " DELETE FROM `patient_source_master` WHERE `centreno`='$subp' ");
}
?>
