$(document).ready(function()
{
	 //$("#button4").attr("disabled",true);
	//get_id();
	//load_item();
	//load_selected_item();
	
	$("#selectsubstr").keyup(function(e)
	{
		$(this).css("border","");
		$(this).val($(this).val().toUpperCase());
		if(e.keyCode==13)
		{
			if($(this).val()=="0")
			$(this).css("border","1px solid #f00");
			else
			{
				$(this).css("border","");
				$("#tstissueto").focus();
				//$('#selectcategory').select2('focus');
			}
		}
	});
	
	$("#supp").keyup(function(e)
	{
		if($("#supp").val()!="0" && e.keyCode==13)
		{
			$("#r_doc").focus();
		}
	});
	
	$("#txtcntrname").keyup(function(e)
	{
		$(this).css("border","");
		$(this).val($(this).val().toUpperCase());
		if(e.keyCode==13)
		{
			if($(this).val()=="")
			$(this).css("border","1px solid #f00");
			else
			{
				$(this).css("border","");
				lod_batchno();
				$("#selectbatch").focus();
			}
		}
	});
	
	
	
	$("#qnt").keyup(function(e)
	{
		$("#qnt").css({"border":"","box-shadow":""});
		if(e.keyCode==27)
		{
			$("#button4").focus();
		}
		if(e.keyCode==13 && $("#qnt").val()!="")
		{
			if(parseInt($("#qnt").val())==0 || parseInt($("#qnt").val())>parseInt($("#bch_qnt").val()))
			{
				$("#qnt").css({"border":"1px solid #FD2323","box-shadow":"0px 0px 6px 0px #FF3636"});
			}
			else
			{
				//$("#button").focus();
				$("#free").focus();
			}
		}
	});
	
	$("#free").keyup(function(e)
	{
		$("#free").css({"border":"","box-shadow":""});
		if(e.keyCode==27)
		{
			$("#button4").focus();
		}
		if(e.keyCode==13)
		{
			if($("#free").val()=="")
			{
				$("#free").val(0);
			}
			$("#button").focus();
		}
	});
	
	$("#txtcntrname").keyup(function(e)
	{
		if(e.keyCode==27)
		$("#button4").focus();
	});
	
});


function reset_all()
{
	location.reload(true);
}

function insert_data_final()
{
	//alert();
	var len=$(".all_tr").length;
	if($("#supp").val()=="0")
	{
		$("#supp").focus();
	}
	else if(len<1)
	{
		$("html,body").animate({scrollTop: '10px'},500);
		$("#msgg").text("NO ITEM SELECTED");
		//var x=$("#temp_item").offset();
		//var w=$("#msgg").width()/2;
		//$("#msgg").css({'top':'50%','left':'50%'});
		$("#msgg").fadeIn(500);
		setTimeout(function()
		{
			$("#msgg").fadeOut(800,function()
			{
				$("#r_doc").select();$("#r_doc").focus();
			}
		)},800);
	}
	else
	{
		$("#loader").show();
		$("#button").attr("disabled",true);
		$("#button4").attr("disabled",true);
		var all="";
		for(var i=0; i<len; i++)
		{
			var itm=$(".all_tr:eq("+i+")").find('td:eq(1) input:first').val();
			var bch=$(".all_tr:eq("+i+")").find('td:eq(2) input:first').val();
			var qnt=$(".all_tr:eq("+i+")").find('td:eq(3) input:first').val();
			var free=$(".all_tr:eq("+i+")").find('td:eq(4) input:first').val();
			var mrp=$(".all_tr:eq("+i+")").find('td:eq(5) input:first').val();
			var amt=$(".all_tr:eq("+i+")").find('td:eq(6) input:first').val();
			var gst_per=$(".all_tr:eq("+i+")").find('td:eq(6) input:last').val();
			var gst_amt=$(".all_tr:eq("+i+")").find('td:eq(7) input:first').val();
			var expdt=$(".all_tr:eq("+i+")").find('td:eq(7) input:last').val();
			all+=itm+"@@"+bch+"@@"+qnt+"@@"+free+"@@"+mrp+"@@"+amt+"@@"+gst_per+"@@"+gst_amt+"@@"+expdt+"@@#@#";
		}
		$.post("pages/ph_item_return_supplier_ajax.php",
		{
			supp:$("#supp").val(),
			user:$("#user").text().trim(),
			type:"save_items",
			all:all,
		},
		function(data,status)
		{
			$("#loader").hide();
			//alert(data);
			var val=data.split("@govin@");
			if(val[0]=="1")
			{
				alert("Done");
				$(".all_tr").find('td:eq(7) span:first').html("");
			}
			if(val[0]=="0")
			{
				alert("Low Stock");
				var less=val[1];
				var vl=less.split("#@#");
				for(var i=0; i<(vl.length); i++)
				{
					var v=(vl[i]).split("@@");
					for(var j=0; j<(v.length); j++)
					{
						var itm=v[0];
						var bch=v[1];
						if(itm!="" && bch!="")
						{
							//alert(itm+"--"+bch);
							$("."+itm+bch).css("background","#FFD0D0");
							$("."+itm+bch).find('td:eq(7) span:last').css({"margin-left":"20px","position":"absolute","color":"#f00"});
							$("."+itm+bch).find('td:eq(7) span:last').text("Low stock");
						}
					}
				}
				$("#button").attr("disabled",false);
				$("#button4").attr("disabled",false);
			}
		});
	}
}

//------------------------item add---------------------------------//

function add_item()
{
	if($("#supp").val()=="0")
	{
		$("#supp").focus();
	}
	else if($("#doc_id").val()=="")
	{
		$("#r_doc").focus();
	}
	else if($("#hguide_id").val()=="")
	{
		$("#hguide").focus();
	}
	else if($("#qnt").val()=="")
	{
		$("#qnt").focus();
	}
	else if(parseInt($("#qnt").val())==0 || parseInt($("#qnt").val())<0)
	{
		$("#qnt").focus();
	}
	else if(parseInt($("#qnt").val())>parseInt($("#bch_qnt").val()))
	{
		setTimeout(function(){$("#qnt").focus();},200);
	}
	else if( ( (parseInt($("#qnt").val())) + (parseInt($("#free").val())) ) > parseInt($("#bch_qnt").val()) )
	{
		setTimeout(function(){$("#free").focus();},200);
	}
	else
	{
		//alert("ok");
		$("#issue_to").attr("disabled",true);
		$("#button4").attr("disabled",false);
		add_item_temp($("#doc_id").val(),$("#r_doc").val(),$("#hguide_id").val(),$("#qnt").val().trim(),$("#free").val().trim(),$("#bch_mrp").val().trim(),$("#bch_gst").val().trim(),$("#bch_exp").val().trim());
		doc_v=1;
		doc_sc=0;
	}
}
function add_item_temp(id,itm_name,bch,qnt,free,rate,gst_per,exp_dt)
{
	//alert(id);
	var rt=(qnt*rate).toFixed(2);
	var tr_len=$('#mytable tr').length;
	var gst=0;
	gst_per=parseFloat(gst_per);
	gst=rt-(rt*(100/(100+gst_per)));
	gst=gst.toFixed(2);
	if(tr_len==0)
	{
		var test_add="<table class='table table-condensed table-bordered table-report' id='mytable'>";
		test_add+="<tr style='background-color:#cccccc'><th>Sl No</th><th>Medicine</th><th>Batch No</th><th>Quantity</th><th>Free</th><th>MRP</th><th>Amount</th><th style='width:5%;'>Remove</th></tr>";
		test_add+="<tr class='all_tr "+id+bch+"'>";
		test_add+="<td>1</td>";
		test_add+="<td>"+itm_name+"<input type='hidden' value='"+id+"' class='itm' /><input type='hidden' value='"+id+bch+"' class='test_id' /></td>";
		test_add+="<td>"+bch+"<input type='hidden' value='"+bch+"' class='batch' /></td>";
		test_add+="<td>"+qnt+"<input type='hidden' value='"+qnt+"' class='qnt' /></td>";
		test_add+="<td>"+free+"<input type='hidden' value='"+free+"' class='free' /></td>";
		test_add+="<td>"+rate+"<input type='hidden' value='"+rate+"' class='mrp' /></td>";
		test_add+="<td>"+rt+"<input type='hidden' value='"+rt+"' class='all_rate' /><input type='hidden' value='"+gst_per+"' class='gst_per' /></td>";
		test_add+="<td style='text-align:center;'><input type='hidden' value='"+gst+"' class='all_gst' /><input type='hidden' value='"+exp_dt+"' class='expdt' /><span onclick='$(this).parent().parent().remove();set_amt()' style='cursor:pointer;color:#c00;'><i class='icon-remove icon-large text-danger'></i></span><span></span></td>";
		test_add+="</tr>";
		test_add+="<tr id='new_tr'><td colspan='6' style='text-align:right;'>Total</td><td colspan='2' id='final_rate'></td></tr>";
		test_add+="</table>";
		//test_add+="<div style='display:none;text-align:center;background-color:#cccccc;font-weight:bold;font-size:15px;padding:5px' id='test_hidden_price'>Total:<span id='test_total_hidden'></span></div>";
		
		$("#temp_item").html(test_add);
		tr_len++;
	
		var tot=0;
		var gst_amt=0;
		var dis_amt=0;
		var tot_ts=document.getElementsByClassName("all_rate");
		var tot_gst=document.getElementsByClassName("all_gst");
		for(var j=0;j<tot_ts.length;j++)
		{
			tot=tot+parseFloat(tot_ts[j].value);
			gst_amt=gst_amt+parseFloat(tot_gst[j].value);
		}
		dis_amt=0;
		tot=tot-dis_amt;
		//tot=Math.floor(tot);
		$("#final_rate").text(tot.toFixed(2));
		tot=Math.round(tot);
		$("#total").val(tot);
		$("#gst").val(gst_amt);
		$("#paid").val(tot);
		$("#discount").val("");
		$("#adjust").val("");
		//$("#test").val("");
	}
	else
	{
		
		var t_ch=0;
		var test_l=document.getElementsByClassName("test_id");
		
		for(var i=0;i<test_l.length;i++)
		{
			if(test_l[i].value==id+bch)
			{
				t_ch=1;
			}
		}
		if(t_ch)
		{

			$("#temp_item").css({'opacity':'0.5'});
			$("#msgg").text("ALREADY SELECTED SAME ITEM AND BATCH NO.");
			//var x=$("#temp_item").offset();
			//var w=$("#msgg").width()/2;
			//$("#msgg").css({'top':'50%','left':'50%'});
			$("#msgg").fadeIn(500);
			setTimeout(function(){$("#msgg").fadeOut(800,function(){$("#temp_item").css({'opacity':'1.0'});$("#r_doc").select();$("#r_doc").focus();
			})},800);
			
		}			
		else
		{
	   
		var tr=document.createElement("tr");
		tr.setAttribute("class","all_tr "+id+bch);
		var td=document.createElement("td");
		var td1=document.createElement("td");
		var td2=document.createElement("td");
		var td3=document.createElement("td");
		var td4=document.createElement("td");
		var td5=document.createElement("td");
		var td6=document.createElement("td");
		var td7=document.createElement("td");
		//var tbody=document.createElement("tbody");
		var tbody="";
		
		td.innerHTML=tr_len;
		td1.innerHTML=itm_name+"<input type='hidden' value='"+id+"' class='itm' /><input type='hidden' value='"+id+bch+"' class='test_id' />";
		td2.innerHTML=bch+"<input type='hidden' value='"+bch+"' class='batch' />";
		td3.innerHTML=qnt+"<input type='hidden' value='"+qnt+"' class='qnt' />";
		td4.innerHTML=free+"<input type='hidden' value='"+free+"' class='free' />";
		td5.innerHTML=rate+"<input type='hidden' value='"+rate+"' class='mrp' />";
		td6.innerHTML=rt+"<input type='hidden' value='"+rt+"' class='all_rate' /><input type='hidden' value='"+gst_per+"' class='gst_per' />";
		td7.innerHTML="<input type='hidden' value='"+gst+"' class='all_gst' /><input type='hidden' value='"+exp_dt+"' class='expdt' /><span onclick='$(this).parent().parent().remove();set_amt()' style='cursor:pointer;color:#c00;'><i class='icon-remove icon-large text-danger'></i></span><span></span>";
		td7.setAttribute("style","text-align:center;");
		tr.appendChild(td);
		tr.appendChild(td1);
		tr.appendChild(td2);
		tr.appendChild(td3);
		tr.appendChild(td4);
		tr.appendChild(td5);
		tr.appendChild(td6);
		tr.appendChild(td7);
		//tbody.appendChild(tr);
		document.getElementById("mytable").appendChild(tr);
		var tot=0;
		var gst_amt=0;
		var dis_amt=0;
		var tot_ts=document.getElementsByClassName("all_rate");
		var tot_gst=document.getElementsByClassName("all_gst");
		for(var j=0;j<tot_ts.length;j++)
		{
			tot=tot+parseFloat(tot_ts[j].value);
			gst_amt=gst_amt+parseFloat(tot_gst[j].value);
		}
		dis_amt=0;
		tot=tot-dis_amt;
		var new_tr="<tr id='new_tr'><td colspan='6' style='text-align:right;'>Total</td><td colspan='2' id='final_rate'></td></tr>";
		$("#new_tr").remove();
		$('#mytable tr:last').after(new_tr);
		//tot=Math.floor(tot);
		$("#final_rate").text(tot.toFixed(2));
		tot=Math.round(tot);
		$("#total").val(tot);
		$("#gst").val(gst_amt);
		$("#paid").val(tot);
		$("#discount").val("");
		$("#adjust").val("");
		}
	}
	//$("#txtbillno").attr("disabled",true);
	//$("#txtreason").attr("disabled",true);
	//$("#selectbatch").val("");
	//$("#txtcntrname").val("");
	//$("#txtqnt").val("");
	//setTimeout(function(){$("#txtcustnm").val("").focus();},300);
	//alert(disc);
	for(var i=0;i<tot_ts.length;i++)
	{
		$(".all_tr:eq("+i+")").find('td:eq(0)').text(i+1);
	}
	//-------------------------------------------------------------
	$("#doc_id").val('');
	$("#r_doc").val('');
	$("#hguide").val('');
	$("#hguide_id").val('');
	$("#bch_qnt").val('');
	$("#bch_mrp").val('');
	$("#mrp").val('');
	$("#bch_gst").val('');
	$("#stock").val('');
	$("#bch_exp").val('');
	$("#expiry").val('');
	$("#qnt").val('');
	$("#free").val('');
	$("#ph").attr('disabled',true);
	$("#supp").attr('disabled',true);
	setTimeout(function(){$("#r_doc").focus();},500);
}
function set_amt()
{
	var tot=0;
	var gst_amt=0;
	var tot_ts=document.getElementsByClassName("all_rate");
	var tot_gst=document.getElementsByClassName("all_gst");
	for(var j=0;j<tot_ts.length;j++)
	{
		tot=tot+parseFloat(tot_ts[j].value);
		gst_amt=gst_amt+parseFloat(tot_gst[j].value);
	}
	
	var new_tr="<tr id='new_tr'><td colspan='6' style='text-align:right;'>Total</td><td colspan='2' id='final_rate'></td></tr>";
	$("#new_tr").remove();
	$('#mytable tr:last').after(new_tr);
	
	$("#final_rate").text(tot.toFixed(2));
	tot=Math.round(tot);
	$("#total").val(tot);
	$("#gst").val(gst_amt);
	$("#paid").val(tot);
	$("#discount").val("");
	$("#adjust").val("");
	$("#txtcustnm").focus();
	for(var i=0;i<tot_ts.length;i++)
	{
		$(".all_tr:eq("+i+")").find('td:eq(0)').text(i+1);
	}
}

//------------------------item search---------------------------------//
function load_refdoc1()
{
		//$("#ref_doc").fadeIn(200);
		//$("#r_doc").select();
		setTimeout(function(){ $("#chk_val2").val(1)},100);
}
var doc_tr=1;
var doc_sc=0;
function load_refdoc(val,e,typ)
{
		var unicode=e.keyCode? e.keyCode : e.charCode;
		//alert(unicode);
		if(unicode!=13)
		{
			if(unicode!=40 && unicode!=38)
			{
				if(unicode==27)
				{
					$("#button4").focus();
				}
				else
				{
					$("#ref_doc").html("<img src='../images/ajax-loader.gif' />");
					//$("#ref_doc").fadeIn(100);
					$.post("pages/ph_item_return_supplier_ajax.php",
					{
						type:typ,
						val:val.trim(),
					},
					function(data,status)
					{
						if(data!="")
						{
							$("#ref_doc").html(data);
							$("#ref_doc").fadeIn(100);
						}
						else
						{
							$("#ref_doc").slideUp(100);
						}
						doc_tr=1;
						doc_sc=0;
					})
				}
			}
			else if(unicode==40)
			{
				var chk=doc_tr+1;
				var cc=document.getElementById("doc"+chk).innerHTML;
				if(cc)
				{
					doc_tr=doc_tr+1;
					$("#doc"+doc_tr).css({'color': '#419641','transform':'scale(0.95)','font-weight':'bold','transition':'all .2s'});
					var doc_tr1=doc_tr-1;
					$("#doc"+doc_tr1).css({'color': 'black','transform':'scale(1.0)','font-weight':'normal','transition':'all .2s'});
					var z3=doc_tr%1;
					if(z3==0)
					{
						$("#ref_doc").scrollTop(doc_sc)
						doc_sc=doc_sc+30;
					}
				}
			}
			else if(unicode==38)
			{
				var chk=doc_tr-1;
				var cc=document.getElementById("doc"+chk).innerHTML;
				if(cc)
				{
					doc_tr=doc_tr-1;
					$("#doc"+doc_tr).css({'color': '#419641','transform':'scale(0.95)','font-weight':'bold','transition':'all .2s'});
					var doc_tr1=doc_tr+1;
					$("#doc"+doc_tr1).css({'color': 'black','transform':'scale(1.0)','font-weight':'normal','transition':'all .2s'});
					var z3=doc_tr%1;
					if(z3==0)
					{
						doc_sc=doc_sc-30;
						$("#ref_doc").scrollTop(doc_sc)
					}
				}
			}
		}
		else
		{
			$("#r_doc").css('border','');
			var cen_chk1=document.getElementById("chk_val2").value;
			if(cen_chk1!=0)
			{
				var docs=document.getElementById("dvdoc"+doc_tr).innerHTML.split("#");
				var doc_naam=docs[2].trim();
				$("#doc_info").fadeIn(200);
				doc_load(docs[1],doc_naam);
			}
		}
}
function doc_load(id,name)
{
	$("#r_doc").val(name);
	$("#doc_id").val(id);
	//$("#doc_info").html("");
	$("#ref_doc").fadeOut(200);
	$("#hguide").val('');
	$("#hguide_id").val('');
	$("#bch_qnt").val('');
	$("#bch_mrp").val('');
	$("#bch_gst").val('');
	$("#stock").val('');
	$("#bch_exp").val('');
	$("#qnt").val('');
	$("#hguide").focus();
	doc_tr=1;
	doc_sc=0;
}
//------------------------item search end---------------------------------//
//-----------------------------------------Load Doctor List Onfocus-------------------------------||
function hguide_focus()
{
	$("#hguide_div").fadeIn(200);
	$("#hguide").select();
	setTimeout(function(){ $("#chk_val2").val(1)},200);
}
//----------------------------------Load/Save/Choose Doctor Onkeyup/Enter-------------------------||

function hguide_up(val,e,typ)
{
		var unicode=e.keyCode? e.keyCode : e.charCode;
		if(unicode!=13)
		{
			if(unicode!=40 && unicode!=38)
			{
				if(unicode==27)
				{
					$("#button4").focus();
				}
				else
				{
					$("#hguide_div").html("<img src='../images/ajax-loader.gif' />");
					$("#hguide_div").fadeIn(200);
					$.post("pages/ph_item_return_supplier_ajax.php",
					{
						val:val,
						item_id:$("#doc_id").val().trim(),
						type:typ,
					},
					function(data,status)
					{
						$("#hguide_div").html(data);	
						doc_tr=1;
						doc_sc=0;
					})
				}
			}
			else if(unicode==40)
			{
				var chk=doc_tr+1;
				var cc=document.getElementById("hg"+chk).innerHTML;
				if(cc)
				{
					doc_tr=doc_tr+1;
					$("#hg"+doc_tr).css({'color': '#419641','transform':'scale(0.95)','font-weight':'bold','transition':'all .2s'});
					var doc_tr1=doc_tr-1;
					$("#hg"+doc_tr1).css({'color': 'black','transform':'scale(1.0)','font-weight':'normal','transition':'all .2s'});
					var z3=doc_tr%1;
					if(z3==0)
					{
						$("#hguide_div").scrollTop(doc_sc)
						doc_sc=doc_sc+30;
					}
				}
			}
			else if(unicode==38)
			{
				var chk=doc_tr-1;
				var cc=document.getElementById("hg"+chk).innerHTML;
				if(cc)
				{
					doc_tr=doc_tr-1;
					$("#hg"+doc_tr).css({'color': '#419641','transform':'scale(0.95)','font-weight':'bold','transition':'all .2s'});
					var doc_tr1=doc_tr+1;
					$("#hg"+doc_tr1).css({'color': 'black','transform':'scale(1.0)','font-weight':'normal','transition':'all .2s'});
					var z3=doc_tr%1;
					if(z3==0)
					{
						doc_sc=doc_sc-30;
						$("#hguide_div").scrollTop(doc_sc)
					}
				}
			}
			
		}
		else
		{
			var cen_chk1=document.getElementById("chk_val2").value;
			if(cen_chk1!=0)
			{
				var docs=document.getElementById("dvhguide"+doc_tr).innerHTML.split("#");
				var bch=docs[1].trim();
				var qnt=docs[2].trim();
				var mrp=docs[3].trim();
				var gst=docs[4].trim();
				var exp_dt=docs[5].trim();
				//alert(bch+"-"+qnt+"-"+mrp+"-"+exp_dt);
				hguide_load(bch,qnt,mrp,gst,exp_dt);
				$("#hguide_info").fadeIn(200);
				
			}
		}
}
function hguide_load(bch,qnt,mrp,gst,exp_dt)
{
	$("#hguide").val(bch);
	$("#hguide_id").val(bch);
	$("#bch_qnt").val(qnt);
	$("#stock").val(qnt);
	$("#bch_mrp").val(mrp);
	$("#mrp").val(mrp);
	$("#bch_gst").val(gst);
	$("#bch_exp").val(exp_dt);
	$("#expiry").val(exp_dt);
	$("#hguide_info").html("");
	$("#hguide_div").fadeOut(200);
	$("#qnt").focus();
	doc_tr=1;
	doc_sc=0;
}
//-----------------------------------------end-----------------------------------//
