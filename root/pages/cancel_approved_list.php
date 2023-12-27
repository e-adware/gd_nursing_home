<?php
if($p_info["levelid"]==1 && $p_info["branch_id"]==1)
{
	$branch_str="";
	$branch_display="";
}
else
{
	$branch_str=" AND branch_id='$p_info[branch_id]'";
	$branch_display="display:none;";
}

$branch_id=$p_info["branch_id"];
?>
<!--header-->
<div id="content-header">
    <div class="header_div"> <span class="header"> <?php echo $menu_info["par_name"]; ?></span></div>
</div>
<!--End-header-->
<div class="container-fluid">
	<input type="text" id="search_data" onkeyup="view_all()" placeholder="Search">
	<select id="branch_id" class="span3" onChange="view_all()" style="<?php echo $branch_display; ?>">
	<?php
		$branch_qry=mysqli_query($link, " SELECT `branch_id`,`name` FROM `company_name` WHERE `branch_id`>0 $branch_str ORDER BY `branch_id` ASC ");
		while($branch=mysqli_fetch_array($branch_qry))
		{
			if($branch_id==$branch["branch_id"]){ $branch_sel="selected"; }else{ $branch_sel=""; }
			echo "<option value='$branch[branch_id]' $branch_sel>$branch[name]</option>";
		}
	?>
	</select>
	<div id="load_all" class="ScrollStyle">
		
	</div>
</div>
<input type="hidden" id="list_start" value="50">
<!-- Loader -->
<div id="loader" style="display:;margin-top:-10%;"></div>
<link rel="stylesheet" href="../css/loader.css" />

<input type="button" data-toggle="modal" data-target="#myModal" id="mod" style="display:none"/>
<input type="hidden" id="mod_chk" value="0"/>
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-body">
				<div id="results"> </div>
			</div>
		</div>
	</div>
</div>

<script>
	$(document).ready(function()
	{
		$("#loader").hide();
		view_all();
		
		$('#load_all').on('scroll', function() {
			var div_height = $(this).get(0).scrollHeight;
			var div = $(this).get(0);
			
			if(div.scrollTop + div.clientHeight >= div.scrollHeight) {
				var list_start=$("#list_start").val().trim();
				list_start=parseInt(list_start)+50;
				$("#list_start").val(list_start);
				view_all();
			}
		});
	});
	function view_all()
	{
		$("#loader").show();
		$.post("pages/cancel_request_data.php",
		{
			type:"patient_cancel_request_approve_list",
			val:$("#search_data").val(),
			list_start:$("#list_start").val(),
			branch_id:$("#branch_id").val(),
		},
		function(data,status)
		{
			$("#loader").hide();
			$("#load_all").show().html(data);
		})
	}
	function delete_patient_cancel_request(uhid,opd_id,slno)
	{
		var msg="Are you sure want to delete this patient ?";
		bootbox.dialog({
			//title: "Patient Re-visit ?",
			message: "<h5>"+msg+"</h5>",
			buttons: {
				cancel: {
					label: '<i class="icon-remove"></i> Cancel',
					className: "btn btn-inverse",
					callback: function() {
					  bootbox.hideAll();
					}
				},
				confirm: {
					label: '<i class="icon-ok"></i> Confirm',
					className: "btn btn-danger",
					callback: function() {
						
						bootbox.dialog({ message: "<span id='discharge_text'><b>Deleting</b> <img src='../images/loading.gif' height='10' width='30'/> <img src='../images/loading.gif' height='10' width='30'/> <img src='../images/loading.gif' height='10' width='30'/> <img src='../images/loading.gif' height='10' width='30'/> <img src='../images/loading.gif' height='10' width='30'/> </span>",closeButton: false});
						
						$.post("pages/cancel_request_data.php",
						{
							type:"delete_patient_cancel_request",
							uhid:uhid,
							opd_id:opd_id,
							slno:slno,
							user:$('#user').text().trim(),
						},
						function(data,status)
						{
							//alert(data);
							setTimeout(function(){
								bootbox.hideAll();
								bootbox.dialog({ message: "<h5>"+data+"</h5>",closeButton: false});
							},1500);
							setTimeout(function(){
								bootbox.hideAll();
								window.location.reload(true);
							},2500);
						})
					}
				}
			}
		});
	}
</script>
<style>
<!--
.alert_msg
{
	position: absolute;
	top: 20%;
	left: 40%;
	color: green;
}-->
#myModal
{
	left: 23%;
	width:95%;
}
.modal.fade.in
{
	top: 3%;
}
.modal-body
{
	max-height: 540px;
}
</style>