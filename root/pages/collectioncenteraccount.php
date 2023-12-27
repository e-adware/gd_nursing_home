<?php
if($p_info["levelid"]==1)
{
	$branch_str="";
	$branch_display="display:none;";
	$branch_num=mysqli_num_rows(mysqli_query($link, " SELECT `branch_id`,`name` FROM `company_name` WHERE `branch_id`>0 "));
	if($branch_num>1)
	{
		$branch_display="display:;";
	}
	
	$dept_sel_dis="";
}
else
{
	$branch_str=" AND branch_id='$p_info[branch_id]'";
	$branch_display="display:none;";
	
	$dept_sel_dis="disabled";
}

$branch_id=$p_info["branch_id"];
?>
<div id="content-header">
    <div class="header_div"> <span class="header"> <?php echo $menu_info["par_name"]; ?></span></div>
</div>
<div class="container-fluid">
		  <table class="table table-bordered table-condensed">
				<tr>
					<td>
						<div class="input-daterange input-group datepicker">
							<!--<span class="input-group-addon side_name">From</span>
							<input class="form-control datepicker span2" type="text" name="txtfrom" id="txtfrom" value="<?php echo date('Y-m-d');?>"/>
							<span class="input-group-addon side_name">To</span>
							<input class="form-control datepicker span2" type="text" name="txtto" id="txtto" value="<?php echo date('Y-m-d');?>"/>-->
							
							<span class="side_name">From</span>
							<input class="form-control datepicker span2" type="text" name="from" id="txtfrom" value="<?php echo date('Y-m-d'); ?>" style="margin-left: 47px;">
							<span class="side_name">To</span>
							<input class="form-control datepicker span2" type="text" name="to" id="txtto" value="<?php echo date('Y-m-d'); ?>" style="margin-left: 26px;">
						</div>
					</td>
					<td>
						<input class="form-control" type="text" id="txtcntr" name="txtcntr" onkeyup="sel_pr(this.value,event)" style="display:none;" />
						<select id="txtcntrid">
							<option value="0">Select Center</option>
							<?php
							$qrmkt=mysqli_query($link,"select centreno,centrename from centremaster order by centrename ");
							while($qrmkt1=mysqli_fetch_array($qrmkt))
							{
							?>
							<option value="<?php echo $qrmkt1['centreno'];?>" <?php echo $ssel;?>><?php echo $qrmkt1['centrename'];?></option>
							<?php
							}?>
						</select>
						<select id="branch_id" class="span3" style="<?php echo $branch_display; ?>" onChange="view_all()">
						<?php
							$branch_qry=mysqli_query($link, " SELECT `branch_id`,`name` FROM `company_name` WHERE `branch_id`>0 $branch_str ORDER BY `branch_id` ASC ");
							while($branch=mysqli_fetch_array($branch_qry))
							{
								if($branch_id==$branch["branch_id"]){ $branch_sel="selected"; }else{ $branch_sel=""; }
								echo "<option value='$branch[branch_id]' $branch_sel>$branch[name]</option>";
							}
						?>
						</select>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<center>
							<input type="button" name="button" id="button1" value="Patient Wise" class="btn btn-default all" onclick="allreport('1')" />
							<input type="button" name="button" id="button2" value="Test Wise" class="btn btn-default all" onclick="allreport('2')" />
						</center>
					</td>
				</tr>
		  </table>
	<div id="load_data">
	
	</div>
</div>
<div id="loader" style="margin-top:-10%;"></div>
<!-- Loader -->
<link rel="stylesheet" href="../css/loader.css" />
<input type="hidden" id="report_type">
<link rel="stylesheet" href="include/css/jquery-ui.css" />
<script src="include/js/jquery-ui.js"></script>
<script src="include/jquery.ui.timepicker.js"></script><!-- Timepicker js -->
<!-- Time -->
<link rel="stylesheet" href="include/ui-1.10.0/ui-lightness/jquery-ui-1.10.0.custom.min.css" type="text/css" />
<link rel="stylesheet" href="include/jquery.ui.timepicker.css" /><!-- Timepicker css -->
<script>
	$(document).ready(function()
	{
		$("#loader").hide();
		$("#txtfrom").datepicker(
		{
			dateFormat: 'yy-mm-dd',
			maxDate: '0',
		});
		$("#txtto").datepicker(
		{
			dateFormat: 'yy-mm-dd',
			maxDate: '0',
		});
	});
	
	function allreport(r)
	{
		$("#loader").show();
		$("#report_type").val(r);
		$(".all").removeClass('btn-default');
		$(".all").removeClass('btn-primary');
		$("#button"+r).addClass('btn-primary');
		$.post("pages/collectioncenter_report.php",
		{
			rep:$("#report_type").val(),
			cid:$("#txtcntrid").val(),
			branch_id:$("#branch_id").val(),
			fdate:$("#txtfrom").val(),
			tdate:$("#txtto").val(),
			type:"collectionreport",
		},
		 function(data,status)
		 {
			 $("#load_data").html(data)
			 $("#loader").hide();
		 })
	}
	function print_rep(f,cid, bid)
	{
		var url="";
		var tp=btoa(f);
		var cid=btoa(cid);
		var fdate=btoa($("#txtfrom").val());
		var tdate=btoa($("#txtto").val());
		url="pages/collectioncenter_reports_print.php?cid="+cid+"&fdate="+fdate+"&tdate="+tdate+"&type="+tp+"&bid="+btoa(bid);
		
		wind=window.open(url,'Window','scrollbars=1,menubar=1,toolbar=0,height=670,width=1300');
	}
</script>
<style>
#load_data
{
    max-height: 400px;
    overflow-y: scroll;
}
.side_name
{
	border: 1px solid #ddd;
	background-color: #fff;
	padding: 4px;
	position: absolute;
	font-weight:bold;
}
</style>
