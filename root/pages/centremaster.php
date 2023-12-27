<?php
$emp_id=trim($_SESSION["emp_id"]);
$branch_display="display:none;";
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
<!--header-->
<div id="content-header">
    <div class="header_div"> <span class="header"> Center Master</span></div>
</div>
<!--End-header-->
<div class="container-fluid">
	<div class="row">
		<div class="span7">
			<table class="table   table-bordered table-condensed">
				<tr>
					<td> ID</td>
					<td><input type="text" name="txtid" id="txtid" class="imp"  readonly="readonly"  /></td>
					<input type="hidden" name="txtlgid" id="txtlgid" value="" autocomplete="off" />
				</tr>
				<tr style="<?php echo $branch_display; ?>">
					<td >Branch</td>
					<td >
						<select id="branch_id" class="" onchange="lod_refraldoctor()" style="<?php echo $element_style; ?>">
						<?php
							$qry=mysqli_query($link, "SELECT `branch_id`,`name` FROM `company_name` WHERE `name`!='' $branch_str ORDER BY `branch_id` ASC");
							while($data=mysqli_fetch_array($qry))
							{
								if($data["branch_id"]==$p_info["branch_id"]){ $branch_sel="selected"; }else{ $branch_sel=""; }
								echo "<option value='$data[branch_id]' $branch_sel>$data[name]</option>";
							}
						?>
						</select>
					</td>
				</tr>
				<tr>
					<td >Name</td>
					<td ><input type="text" name="intext1" id="txtname" class="intext" size="35" value="" autocomplete="off" /></td>
				</tr>
				<tr>
					<td >Address</td>
					<td ><input type="text" name="intext2" id="txtaddress" class="intext" size="35" value="" autocomplete="off" /></td>
				</tr>
				<tr>
					<td >Phone</td>
					<td ><input type="text" name="intext3" id="txtphon" size="35" class="intext" autocomplete="off"/></td>
				</tr>
				<tr>
				<tr>
					<td>Allow Credit</td>
					<td><label><input type="checkbox" id="credit" >Credit</label></td>
				</tr>
			   <tr>
				   <td >Credit Limit</td>
				   <td ><input type="text" name="intext6" id="txtcreditlmit" size="15" class="intext" autocomplete="off"/></td>  
				</tr>
				<tr>
					<td colspan="2">
						<label>
							<span style="display:;"><input type="checkbox" id="chkinsurance" >Insurance  &nbsp;&nbsp;&nbsp;</span>
							<span style="display:none;"><input type="checkbox" id="backup" >Backup &nbsp;&nbsp;&nbsp;</span>
						</label>
				   </td>
				</tr>
				<tr>
					<td colspan="2" style="text-align:center" >
						<input type="button" name="intext18" id="button" value="Submit" onclick="Insert();" class="btn btn-info" />
						<input type="button" name="button2" id="button2" onclick="clearr();" value="Reset" class="btn btn-danger" />
						<input type="button" name="button3" id="button3" onclick="popitup('pages/center_list_rpt.php');" value="View" class="btn btn-success" />
					</td>
				</tr>
			</table>                 
		</div>
		<div class="span4">
			<table  class="table   table-bordered table-condensed"  >
				<tr>
				   <td colspan="5">Name: <input type="text" id="txtdoc" size="35" onkeyup="sel_pr(this.value,event)"/></td>
				</tr>
			</table>
			<div style="height:350px; overflow-x:hidden" id="laod_doctor"> 
			</div> 
		</div>
	</div>
</div>
<script>
	$(document).ready(function(){
		lod_refraldoctor();
		get_id();
	});
	var doc_v=1;
	var doc_sc=0;
	function sel_pr(val,e) ///for load patient 
	{
			
		var unicode=e.keyCode? e.keyCode : e.charCode;
		if(unicode==13)
		{
			var chk=$("#chk").val();
			if(chk!="0")
			{
			var prod=document.getElementById("prod"+doc_v).innerHTML;
			val_load_new(prod);
			
			}
		}
		else if(unicode==40)
		{
			$("#chk").val("1");
			var chk=doc_v+1;
			var cc=document.getElementById("rad_test"+chk).innerHTML;
			if(cc)
			{
				doc_v=doc_v+1;
				$("#rad_test"+doc_v).css({'font-weight':'bold','color': 'red','transform':'scale(0.95)','transition':'all .2s'});
				var doc_v1=doc_v-1;
				$("#rad_test"+doc_v1).css({'font-weight':'normal','color': 'black','transform':'scale(1.0)','transition':'all .2s'});
				var z2=doc_v%3;
				if(z2==0)
				{
					$("#laod_doctor").scrollTop(doc_sc)
					doc_sc=doc_sc+90;
				}
			}	
		}
		else if(unicode==38)
		{
			$("#chk").val("1");
			var chk=doc_v-1;
			var cc=document.getElementById("rad_test"+chk).innerHTML;
			if(cc)
			{
				doc_v=doc_v-1;
				$("#rad_test"+doc_v).css({'font-weight':'bold','color': 'red','transform':'scale(0.95)','transition':'all .2s'});
				var doc_v1=doc_v+1;
				$("#rad_test"+doc_v1).css({'font-weight':'normal','color': 'black','transform':'scale(1.0)','transition':'all .2s'});
				var z2=doc_v%3;
				if(z2==0)
				{
					doc_sc=doc_sc-90;
					$("#laod_doctor").scrollTop(doc_sc);
				}
			}
		}
		else
		{
			$.post("pages/centremaster_data.php",
			{
				val:val,
				type:"cntermaster",
				branch_id:$("#branch_id").val(),
			},
			function(data,status)
			{
				$("#laod_doctor").html(data);
			})
		}
	}

	function popitup(url)
	{
		var branch_id=$("#branch_id").val();
		url=url+"?branch_id="+btoa(branch_id);
		newwindow=window.open(url,'window','left=10,top=10,height=600,witdh=600,menubar=1,resizeable=0,scrollbars=1');
	}


	function numentry(id) //for Numeric value support in the text field
	{
	var num=document.getElementById(id);

	var numex=/^[0-9]+$/;
	//var nume=/a-z/
	if(!num.value.match(numex))
	{
		num.value="";
	}
	}

	function lod_refraldoctor()
	{
		$.post("pages/centremaster_data.php",
		{
			type:"cntermaster",
			branch_id:$("#branch_id").val(),
		},
		function(data,status)
		{
			$("#laod_doctor").html(data)
			
		})
	}
	function get_focus()
	{
		$("#txtname").focus();
	}

	function get_id()//For get refdoctor Id
	{
		$.post("pages/centremaster_data.php",
		{
		  type:"cntermaster_id",
		},
		function(data,status)
		{
			$("#txtid").val(data);
			get_focus();
		})

	}

	function val_load_new(doid1)
	{
		$.post("pages/centremaster_data.php",
		{
			type:"cntermaster_load",
			doid1:doid1,
		},
		function(data,status)
		{
			var val=data.split("#");
			document.getElementById("txtid").value=val[0];
			document.getElementById("txtname").value=val[1];
			document.getElementById("txtaddress").value=val[2];
			document.getElementById("txtphon").value=val[3];
			document.getElementById("txtcreditlmit").value=val[4];
			if(val[5]>0)
			 {
			   document.getElementById("credit").checked=true;
			 }
			 else
			 { 
				 document.getElementById("credit").checked=false;
			 }
			 
			 if(val[6]>0)
			 {
				 
				document.getElementById("chkinsurance").checked=true;
			 }
			 else
			 {
				 document.getElementById("chkinsurance").checked=false;
			 }
			 if(val[7]>0)
			 {
				 
				document.getElementById("backup").checked=true;
			 }
			 else
			 {
				 document.getElementById("backup").checked=false;
			 }
		}
	)
	}

	function tab_next(e) 
	{ 	
		var unicode=e.keyCode? e.keyCode : e.charCode; 	
		if(unicode==13) 
		{ 		
			var act=document.activeElement.id;
			if(!act)
			{
			   document.getElementById("info1").focus();
			}
			else 
			{    
				var clsn=$("#"+act).attr("class");    
				var nam=$("#"+act).attr("name"); 
				var val=nam.replace( /^\D+/g, ''); 
				val=parseInt(val)+1; 
				document.getElementsByName(clsn+val)[0].focus();
		   }
		 }
	}

	function clearr()
	{
	var a=document.getElementsByTagName("input");
	for(var i=0;i<a.length;i++)
	{
		if(a[i].type=="text")
		{
			if(i==0 )
			{
			}
			else
			{
			a[i].value=""; 
			
			}
		}
		document.getElementById("chkvaccu").checked=false;
		document.getElementById("chkntrqrd").checked=false;
		document.getElementById("chkdiscnt").checked=false;
		document.getElementById("credit").checked=false;
		document.getElementById("chkinsurance").checked=false;
		document.getElementById("backup").checked=false;
	}

	   get_id();
	  
	  
	   
	}

	function Insert() //For Insert
	{
		///////////////For Check blanj Field
		var jj=1;
		var chk=document.getElementsByClassName("imp")
		for(var i=0;i<chk.length;i++)
		{
			if(chk[i].value=="")
			{
				document.getElementById(chk[i].id).placeholder="Can not be blank";
				jj=0;	
			}
			
		}
		
	   var vname=document.getElementById("txtname").value;
	   if(vname=="")
	   {
		   alert("Please enter the Center Name..");
		   jj=0;
		   $("#txtname").focus();
	   }
	   
	   var vinsurance=0;
	   var backup=0;
		
		var vinsurance1=document.getElementById("chkinsurance"); 
		var backup1=document.getElementById("backup"); 
		
		if(vinsurance1.checked)
		{
			vinsurance=1;
		}
		if(backup1.checked)
		{
			backup=1;
		}
		
		
		if(jj==1)
		{
			///////end ////////
			
			$.post("pages/centremaster_data.php",
			{
				type:"cntermaster_save",
				branch_id:$("#branch_id").val(),
				cid:$("#txtid").val(),
				cname:$("#txtname").val(),
				address:$("#txtaddress").val(),
				phone:$("#txtphon").val(),
				crdtlmt:$("#txtcreditlmit").val(),
				credit:$("#credit:checked").length,
				vinsurance:vinsurance,
				backup:backup,
			},
			function(data,status)
			{
				alert("Data Saved");
				lod_refraldoctor();
				get_id();
				clearr();
			})
		}
	}


	function delete_data(subp)//for delete
	{
		bootbox.dialog({
			//title: "Patient Re-visit ?",
			message: "<h5>Are you sure want to delete this test</h5>",
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
						$.post("pages/centremaster_data.php",
						{
							subp:subp,
							type:"cntermaster_delete",
						},
						function(data,status)
						{
							lod_refraldoctor();
							get_id();
							clearr();
						})
					}
				}
			}
		});
	}

</script>
<style>
/*.reference{
	width:700px;}
.reference td{
	padding:5px;
	text-align:center;
	height:5px;
	width:auto;
	min-width:100px;}
	*/
.reference td img{
	margin:0;}
</style>
