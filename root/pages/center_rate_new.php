<?php
$branch_str=" AND `branch_id`='$p_info[branch_id]'";
$element_style="display:none";
if($p_info["levelid"]==1)
{
	$branch_str="";
	//$element_style="";
}
?>
<!--header-->
<div id="content-header">
    <div class="header_div"> <span class="header"> <?php echo $menu_info["par_name"]; ?></span></div>
</div>
<!--End-header-->
<div class="container-fluid">
	<table class="table table-striped table-bordered">
		<tr>
			<td>
				<select id="branch_id" class="span2" onchange="load_center()" style="<?php echo $element_style; ?>">
				<?php
					$qry=mysqli_query($link, "SELECT `branch_id`,`name` FROM `company_name` WHERE `name`!='' $branch_str ORDER BY `branch_id` ASC");
					while($data=mysqli_fetch_array($qry))
					{
						if($data["branch_id"]==$p_info["branch_id"]){ $branch_sel="selected"; }else{ $branch_sel=""; }
						echo "<option value='$data[branch_id]' $branch_sel>$data[name]</option>";
					}
				?>
				</select>
				
				<select id="centreno" onChange="centre_change()" autofocus >
					<option value="0">--Select centre--</option>
				<?php
					$qry=mysqli_query($link,"SELECT centreno, `centrename` FROM `centremaster` order by `centrename`");
					while($data=mysqli_fetch_array($qry))
					{
				?>
					<option value="<?php echo $data['centreno']; ?>"><?php echo $data['centrename']; ?></option>
				<?php
					}
				?>
				</select>
				
				<button class="btn btn-info" onclick="show_test_list()"><i class="icon-search"></i> View List</button>
			</td>
		</tr>
	</table>
	<div class="">
		<div class="span6" style="margin-left:0px;">
			<b>Select Group</b>
			<select id="group_id" onChange="group_change()" autofocus >
				<option value="0">--Select Group--</option>
			<?php
				$qry=mysqli_query($link," SELECT `group_id`, `group_name` FROM `charge_group_master` WHERE `group_id`>0 ORDER BY `group_name` ASC ");
				while($data=mysqli_fetch_array($qry))
				{
			?>
				<option value="<?php echo $data['group_id']; ?>"><?php echo $data['group_name']; ?></option>
			<?php
				}
			?>
			</select>
			<br>
			<!--<span class="side_name">Search Service</span>-->
			<input type="text" id="test" onkeyup="search_master_test(this.value,event);" style="width:100%;" placeholder="Search Master Service Here" >
			<div id="load_master_test_list" class="scroll_y">
			</div>
		</div>
		<div class="span5">
			<br>
			<!--<span class="side_name_centre centre_test_span" style="display:none;">
				Search Service
			</span>-->
			<input type="text" id="test_centre" onkeyup="search_centre_test(this.value,event);" style="width:100%;" placeholder="Search Centre Service Here" class="centre_test_span">
			
			<br>
			<div id="load_centre_test_list" class="scroll_y">
			</div>
		</div>
	</div>
</div>
<input type="hidden" id="centre_test_sorter" value="ASC">
<link rel="stylesheet" href="../css/select2.min.css" />
<link rel="stylesheet" href="../css/loader.css" />
<script src="../js/select2.min.js"></script>
<script>
	$(document).ready(function()
	{
		$("#centreno").select2({ theme: "classic" });
		$("#centreno").select2("focus");
		
		//load_center();
		search_master_test('');
		
		setTimeout(function(){
			load_centre_test('','');
		},100);
	});
	function load_center()
	{
		$.post("pages/new_opd_registration_data.php",
		{
			type:"load_center",
			branch_id:$("#branch_id").val(),
			patient_id:$("#patient_id").val(),
			opd_id:$("#opd_id").val(),
		},
		function(data,status)
		{
			$("#centreno").html(data);
		})
	}
	function centre_change()
	{
		load_centre_test('','');
	}
	function group_change()
	{
		search_master_test('');
		load_centre_test('','');
	}
	function search_master_test(val,e)
	{
		$.post("pages/center_rate_data.php",
		{
			type:"load_master_test",
			val:val,
			group_id:$("#group_id").val(),
			branch_id:$("#branch_id").val(),
		},
		function(data,status)
		{
			$("#load_master_test_list").html(data);
		})
	}
	function search_centre_test(val,e)
	{
		load_centre_test(val,'')
	}
	function load_centre_test(val,testid)
	{
		$.post("pages/center_rate_data.php",
		{
			type:"load_centre_test",
			centreno:$("#centreno").val(),
			group_id:$("#group_id").val(),
			val:val,
			testid:testid,
			centre_test_sorter:$("#centre_test_sorter").val().trim(),
		},
		function(data,status)
		{
			$("#load_centre_test_list").html(data);
			if($("#centreno").val()==0)
			{
				$(".centre_test_span").hide();
			}
			else
			{
				$(".centre_test_span").show();
			}
		})
	}
	function cm_rate_up(testid,service_category,e)
	{
		$("#centre_test_sorter").val("DESC");
		if(e.which==13)
		{
			if($("#centreno").val()==0)
			{
				alert("Select Centre");
				$("#centreno").select2("focus");
				return false;
			}
			
			centre_rate_change($("#centreno").val(),testid,service_category,$("#cm_rate"+testid).val(),"M");
			
			$(".cm_rate").css({"border":"3px solid #ccc"});
			$("#cm_rate"+testid).css({"border":"3px solid green"});
		}
		
		var a=$("#cm_rate"+testid).val();
		var n=a.length;
		var numex=/^[0-9.]+$/;
		if(a[n-1].match(numex))
		{
			
		}
		else
		{
			a=a.slice(0,n-1);
			$("#cm_rate"+testid).val(a)
		}
	}
	function cc_rate_up(testid,service_category,e)
	{
		if(e.which==13)
		{
			if($("#centreno").val()==0)
			{
				alert("Select Centre");
				$("#centreno").select2("focus");
				return false;
			}
			
			centre_rate_change($("#centreno").val(),testid,service_category,$("#cc_rate"+testid).val(),"C");
			
			$("#cc_rate"+testid).css({"border":"2px solid green"});
		}
		
		var a=$("#cc_rate"+testid).val();
		var n=a.length;
		var numex=/^[0-9.]+$/;
		if(a[n-1].match(numex))
		{
			
		}
		else
		{
			a=a.slice(0,n-1);
			$("#cc_rate"+testid).val(a)
		}
	}
	function centre_rate_change(centreno,testid,service_category,c_rate,frm)
	{
		//alert(centreno+' '+testid+" "+c_rate);
		$.post("pages/center_rate_data.php",
		{
			type:"save_centre_test",
			group_id:$("#group_id").val(),
			centreno:centreno,
			testid:testid,
			service_category:service_category,
			c_rate:c_rate,
		},
		function(data,status)
		{
			load_centre_test('',testid);
			
			setTimeout(function(){
				
				if(frm=="M")
				{
					$("#ctr"+testid).css({"background":"#00ff003d none repeat scroll 0% 0%"});
					$("#cm_rate"+testid).focus();
				}
				if(frm=="C")
				{
					$("#cc_rate"+testid).css({"border":"3px solid green"});
					$("#cc_rate"+testid).focus();
				}
			},200);
			setTimeout(function(){
				//$("#ctr"+testid).css({"background":"#F5F5F5"});
			},5000);
		})
	}
	
	function test_code_up(testid,e)
	{
		if(e.which==13)
		{
			if($("#centreno").val()==0)
			{
				alert("Select Centre");
				$("#centreno").select2("focus");
				return false;
			}
			
			$.post("pages/center_rate_data.php",
			{
				type:"save_centre_test_code",
				centreno:$("#centreno").val(),
				testid:testid,
				test_code:$("#test_code"+testid).val(),
			},
			function(data,status)
			{
				load_centre_test('',testid);
				
				setTimeout(function(){
					//$("#ctr"+testid).css({"background":"#00ff003d none repeat scroll 0% 0%"});
					$("#test_code"+testid).focus().css({"border":"3px solid green"});;
				},200);
				setTimeout(function(){
					//$("#ctr"+testid).css({"background":"#F5F5F5"});
				},5000);
			})
		}
	}
	function delete_centre_test(serv_id,service_category)
	{
		bootbox.dialog({
			//title: "Patient Re-visit ?",
			message: "<h5>Are you sure want to delete this from "+$("#centreno").find('option:selected').text()+"</h5>",
			buttons: {
				cancel: {
					label: '<i class="icon-remove"></i> Cancel',
					className: "btn btn-inverse",
					callback: function() {
					  bootbox.hideAll();
					}
				},
				confirm: {
					label: '<i class="icon-ok"></i> Delete',
					className: "btn btn-danger",
					callback: function() {
						$.post("pages/center_rate_data.php",
						{
							type:"delete_centre_test",
							centreno:$("#centreno").val(),
							serv_id:serv_id,
							service_category:service_category,
						},
						function(data,status)
						{
							if(data==1)
							{
								var msg="Deleted";
								load_centre_test('',serv_id);
							}
							if(data==2 || data==3)
							{
								var msg="Error, Try again";
							}
							bootbox.dialog({ message: "<h5>"+msg+"</h5>"});
							
							setTimeout(function(){
								bootbox.hideAll();
							},2000);
						})
					}
				}
			}
		});
	}
	function show_test_list()
	{
		var centreno=$("#centreno").val();
		var group_id=$("#group_id").val();
		
		if(centreno==0)
		{
			alert("Select Centre");
			return false;
		}
		
		var url="pages/center_rate_test_list.php?v=0&centreno="+centreno+"&group="+group_id;
		window.open(url,'Window','scrollbars=1,menubar=1,toolbar=0,height=670,width=1000');
	}
	
	function cm_rate_opd_v_up(docid,service_category,e)
	{
		$("#centre_test_sorter").val("DESC");
		if(e.which==13)
		{
			if($("#centreno").val()==0)
			{
				alert("Select Centre");
				$("#centreno").select2("focus");
				return false;
			}
			
			centre_rate_opd_v_change($("#centreno").val(),docid,service_category,$("#cm_rate_opd_v"+docid).val(),"M");
			
			$(".cm_rate_opd_v").css({"border":"3px solid #ccc"});
			$("#cm_rate_opd_v"+docid).css({"border":"3px solid green"});
		}
		
		var a=$("#cm_rate_opd_v"+docid).val();
		var n=a.length;
		var numex=/^[0-9.]+$/;
		if(a[n-1].match(numex))
		{
			
		}
		else
		{
			a=a.slice(0,n-1);
			$("#cm_rate_opd_v"+docid).val(a)
		}
	}
	function centre_rate_opd_v_change(centreno,docid,service_category,c_rate,frm)
	{
		//alert(centreno+' '+testid+" "+c_rate);
		$.post("pages/center_rate_data.php",
		{
			type:"save_centre_opd_v",
			group_id:$("#group_id").val(),
			centreno:centreno,
			docid:docid,
			service_category:service_category,
			c_rate:c_rate,
		},
		function(data,status)
		{
			load_centre_test('',docid);
			
			setTimeout(function(){
				
				if(frm=="M")
				{
					$("#ctr"+docid).css({"background":"#00ff003d none repeat scroll 0% 0%"});
					$("#cm_rate_opd_v"+docid).focus();
				}
				if(frm=="C")
				{
					$("#cc_rate_opd_v"+docid).css({"border":"3px solid green"});
					$("#cc_rate_opd_v"+docid).focus();
				}
			},200);
			setTimeout(function(){
				//$("#ctr"+testid).css({"background":"#F5F5F5"});
			},5000);
		})
	}
	
	function cm_rate_opd_r_up(docid,service_category,e)
	{
		$("#centre_test_sorter").val("DESC");
		if(e.which==13)
		{
			if($("#centreno").val()==0)
			{
				alert("Select Centre");
				$("#centreno").select2("focus");
				return false;
			}
			
			centre_rate_opd_r_change($("#centreno").val(),docid,service_category,$("#cm_rate_opd_r"+docid).val(),"M");
			
			$(".cm_rate_opd_r").css({"border":"3px solid #ccc"});
			$("#cm_rate_opd_r"+docid).css({"border":"3px solid green"});
		}
		
		var a=$("#cm_rate_opd_r"+docid).val();
		var n=a.length;
		var numex=/^[0-9.]+$/;
		if(a[n-1].match(numex))
		{
			
		}
		else
		{
			a=a.slice(0,n-1);
			$("#cm_rate_opd_r"+docid).val(a)
		}
	}
	function centre_rate_opd_r_change(centreno,docid,service_category,c_rate,frm)
	{
		//alert(centreno+' '+testid+" "+c_rate);
		$.post("pages/center_rate_data.php",
		{
			type:"save_centre_opd_r",
			group_id:$("#group_id").val(),
			centreno:centreno,
			docid:docid,
			service_category:service_category,
			c_rate:c_rate,
		},
		function(data,status)
		{
			load_centre_test('',docid);
			
			setTimeout(function(){
				
				if(frm=="M")
				{
					$("#ctr"+docid).css({"background":"#00ff003d none repeat scroll 0% 0%"});
					$("#cm_rate_opd_r"+docid).focus();
				}
				if(frm=="C")
				{
					$("#cc_rate_opd_r"+docid).css({"border":"3px solid green"});
					$("#cc_rate_opd_r"+docid).focus();
				}
			},200);
			setTimeout(function(){
				//$("#ctr"+testid).css({"background":"#F5F5F5"});
			},5000);
		})
	}
	
	function cc_rate_opd_v_up(docid,service_category,e)
	{
		if(e.which==13)
		{
			if($("#centreno").val()==0)
			{
				alert("Select Centre");
				$("#centreno").select2("focus");
				return false;
			}
			
			centre_rate_opd_v_change($("#centreno").val(),docid,service_category,$("#cc_rate_opd_v"+docid).val(),"C");
			
			$("#cc_rate_opd_v"+docid).css({"border":"2px solid green"});
		}
		
		var a=$("#cc_rate_opd_v"+docid).val();
		var n=a.length;
		var numex=/^[0-9.]+$/;
		if(a[n-1].match(numex))
		{
			
		}
		else
		{
			a=a.slice(0,n-1);
			$("#cc_rate_opd_v"+docid).val(a)
		}
	}
	
	function centre_rate_opd_v_change(centreno,docid,service_category,c_rate,frm)
	{
		//alert(centreno+' '+docid+" "+service_category+" "+c_rate);
		$.post("pages/center_rate_data.php",
		{
			type:"save_centre_opd_v",
			group_id:$("#group_id").val(),
			centreno:centreno,
			docid:docid,
			service_category:service_category,
			c_rate:c_rate,
		},
		function(data,status)
		{
			load_centre_test('',docid);
			
			setTimeout(function(){
				
				if(frm=="M")
				{
					$("#ctr"+docid).css({"background":"#00ff003d none repeat scroll 0% 0%"});
					$("#cm_rate_opd_v"+docid).focus();
				}
				if(frm=="C")
				{
					$("#cc_rate_opd_v"+docid).css({"border":"3px solid green"});
					$("#cc_rate_opd_v"+docid).focus();
				}
			},200);
			setTimeout(function(){
				//$("#ctr"+docid).css({"background":"#F5F5F5"});
			},5000);
		})
	}
	
	function cc_rate_opd_r_up(docid,service_category,e)
	{
		if(e.which==13)
		{
			if($("#centreno").val()==0)
			{
				alert("Select Centre");
				$("#centreno").select2("focus");
				return false;
			}
			
			centre_rate_opd_r_change($("#centreno").val(),docid,service_category,$("#cc_rate_opd_r"+docid).val(),"C");
			
			$("#cc_rate_opd_r"+docid).css({"border":"2px solid green"});
		}
		
		var a=$("#cc_rate_opd_r"+docid).val();
		var n=a.length;
		var numex=/^[0-9.]+$/;
		if(a[n-1].match(numex))
		{
			
		}
		else
		{
			a=a.slice(0,n-1);
			$("#cc_rate_opd_r"+docid).val(a)
		}
	}
	
	function centre_rate_opd_r_change(centreno,docid,service_category,c_rate,frm)
	{
		//alert(centreno+' '+docid+" "+service_category+" "+c_rate);
		$.post("pages/center_rate_data.php",
		{
			type:"save_centre_opd_r",
			group_id:$("#group_id").val(),
			centreno:centreno,
			docid:docid,
			service_category:service_category,
			c_rate:c_rate,
		},
		function(data,status)
		{
			load_centre_test('',docid);
			
			setTimeout(function(){
				
				if(frm=="M")
				{
					$("#ctr"+docid).css({"background":"#00ff003d none repeat scroll 0% 0%"});
					$("#cm_rate_opd_r"+docid).focus();
				}
				if(frm=="C")
				{
					$("#cc_rate_opd_r"+docid).css({"border":"3px solid green"});
					$("#cc_rate_opd_r"+docid).focus();
				}
			},200);
			setTimeout(function(){
				//$("#ctr"+docid).css({"background":"#F5F5F5"});
			},5000);
		})
	}
	
</script>
<style>
.scroll_y
{
	overflow-y:scroll;
	height:450px;
}
.side_name
{
	border: 1px solid #ddd;
	background-color: #fff;
	padding: 4px;
	position: absolute;
	font-weight:bold;
}
.side_name_centre
{
	border: 1px solid #ddd;
	background-color: #fff;
	padding: 4px;
	position: absolute;
	font-weight:bold;
	margin-left: 0%;
}
</style>
