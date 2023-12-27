<?php
include('../includes/connection.php');
include("../../includes/global.function.php");
include("../../includes/idgeneration.function.php");

$dateFrom = $_POST["dateFrom"];
$dateTo = $_POST["dateTo"];
$date1 = date("Y-m-d", strtotime($dateFrom));
$date2 = date("Y-m-d", strtotime($dateTo));
$encounter = $_POST["encounterId"];
$branch_id = $_POST["branchId"];
if(!$branch_id)
{
	$branch_id=1;
}

$branch_str=" AND `opd_id` IN(SELECT `opd_id` FROM `uhid_and_opdid` WHERE `branch_id`='$branch_id')";
$branch_str_a=" AND a.`opd_id` IN(SELECT `opd_id` FROM `uhid_and_opdid` WHERE `branch_id`='$branch_id')";
$branch_str_b=" AND b.`opd_id` IN(SELECT `opd_id` FROM `uhid_and_opdid` WHERE `branch_id`='$branch_id')";

$result = array();

//code starts
$user_name="All";
$encounter_pay_type=0;
if($encounter>0)
	{
		$pat_typ_text=mysqli_fetch_array(mysqli_query($link, " SELECT `type` FROM `patient_type_master` WHERE `p_type_id`='$encounter' "));
		$encounter_pay_type=$pat_typ_text["type"];
	}
    if($encounter_pay_type==1)
	{
		$pat_typ=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `patient_type_master` WHERE `p_type_id`='$encounter' "));
		$pat_typ_encounter=$pat_typ["p_type"];
        $n=1;
		$tot_bill=$tot_dis=$tot_paid=$tot_bal=$tot_visit_fee=$tot_regd_fee=$tot_refund_amount=$tot_tax_amount=0;
        $pat_reg_qry=mysqli_query($link, " SELECT * FROM `uhid_and_opdid` WHERE `date` between '$date1' AND '$date2' AND `type`='$encounter' $branch_str $user_str ORDER BY `slno` ASC ");
		while($pat_reg=mysqli_fetch_array($pat_reg_qry))
		{
			$pat_info=mysqli_fetch_array(mysqli_query($link, "select * from patient_info where patient_id='$pat_reg[patient_id]'"));
			
			$uhid_id     =$pat_info["patient_id"];
			$patient_id  =$pat_reg["patient_id"];
			$opd_id      =$pat_reg["opd_id"];
			
			$pat_show=0;
			$bill_amt=$discount=$paid=$balance=$visit_fee=$regd_fee=$refund_amount=$tax_amount=0;
			
			$con_pat_pay=mysqli_fetch_array(mysqli_query($link, "select * from consult_patient_payment_details where patient_id='$pat_reg[patient_id]' and opd_id='$pat_reg[opd_id]'"));
			
			$bill_amt=$con_pat_pay['tot_amount'];
			$visit_fee=$con_pat_pay['visit_fee'];
			$regd_fee=$con_pat_pay['regd_fee'];
			
			$check_paid=mysqli_fetch_array(mysqli_query($link, " SELECT ifnull(SUM(a.`amount`),0) AS `paid`, ifnull(SUM(a.`discount_amount`),0) AS `discount`, ifnull(SUM(a.`refund_amount`),0) AS `refund`, ifnull(SUM(a.`tax_amount`),0) AS `tax` FROM `payment_detail_all` a, `uhid_and_opdid` b WHERE a.`patient_id`=b.`patient_id` AND a.`opd_id`=b.`opd_id` AND a.`date`=b.`date` AND a.`patient_id`='$patient_id' and a.`opd_id`='$opd_id' AND a.`date` between '$date1' AND '$date2' $user_str_a "));
			
			$paid           =$check_paid["paid"];
			$discount       =$check_paid["discount"];
			//$refund_amount  =$check_paid["refund"];
			$tax_amount     =$check_paid["tax"];
			
			$check_refund=mysqli_fetch_array(mysqli_query($link, " SELECT ifnull(SUM(a.`refund_amount`),0) AS `refund` FROM `payment_detail_all` a, `uhid_and_opdid` b WHERE a.`patient_id`=b.`patient_id` AND a.`opd_id`=b.`opd_id` AND a.`date`!=b.`date` AND a.`patient_id`='$patient_id' and a.`opd_id`='$opd_id' "));
			$refund_amount_other  =$check_refund["refund"];
			
			if($refund_amount_other>0)
			{
				$bill_amt+=$refund_amount_other;
			}
			
			$settle_amount=$paid+$discount+$tax_amount-$refund_amount;
			
			$balance=$bill_amt-$settle_amount;
			
			$quser=mysqli_fetch_array(mysqli_query($link, " SELECT `name` FROM `employee` WHERE `emp_id`='$pat_reg[user]' "));
			$pat_typ_text=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `patient_type_master` WHERE `p_type_id`='$pat_reg[type]' "));
			$encounter_name=$pat_typ_text['p_type'];
            
            $result = ["OpdId" => $pat_reg["opd_id"], "PatientName" => $pat_info["name"], ];


            $n++;
			
			$tot_bill+=$bill_amt;
			$tot_visit_fee+=$visit_fee;
			$tot_regd_fee+=$regd_fee;
			$tot_dis+=$discount;
			$tot_refund_amount+=$refund_amount;
			$tot_tax_amount+=$tax_amount;
			$tot_paid+=$paid;
			$tot_bal+=$balance;
		}
        $con_bal_qry=mysqli_query($link, " SELECT a.*, b.`type` FROM `payment_detail_all` a, `uhid_and_opdid` b WHERE a.`patient_id`=b.`patient_id` AND a.`opd_id`=b.`opd_id` AND a.`date`!=b.`date` AND a.`date` between '$date1' AND '$date2' AND b.`type`='$encounter' $user_str_a $branch_str_b ORDER BY a.`pay_id` ASC ");
		$con_bal_num=mysqli_num_rows($con_bal_qry);
		$zz=0;
		if($con_bal_num>0)
		{
			
			$zz=1;
			$n=1;
			$tot_bal_bill_amt=$tot_bal_discount=$tot_bal_paid=$tot_bal_balance=$tot_bal_visit_fee=$tot_bal_regd_fee=$tot_bal_refund_amount=$tot_bal_tax_amount=0;
			while($con_bal=mysqli_fetch_array($con_bal_qry))
			{
				$pat_info=mysqli_fetch_array(mysqli_query($link, "select * from patient_info where patient_id='$con_bal[patient_id]'"));
				
				$uhid_id     =$pat_info["patient_id"];
				$patient_id  =$con_bal["patient_id"];
				$opd_id      =$con_bal["opd_id"];
				
				$bal_pat_show=0;
				$bal_bill_amt=$bal_discount=$bal_paid=$bal_balance=$bal_visit_fee=$bal_regd_fee=$bal_refund_amount=$bal_tax_amount=0;
				
				$bal_paid=$con_bal['amount'];
				
				$con_pat_pay=mysqli_fetch_array(mysqli_query($link, "select * from consult_patient_payment_details where patient_id='$con_bal[patient_id]' and opd_id='$con_bal[opd_id]'"));
				
				$bal_bill_amt=$con_pat_pay['tot_amount'];
				
				$check_paid=mysqli_fetch_array(mysqli_query($link, " SELECT ifnull(SUM(a.`amount`),0) AS `paid`, ifnull(SUM(a.`discount_amount`),0) AS `discount`, ifnull(SUM(a.`refund_amount`),0) AS `refund`, ifnull(SUM(a.`tax_amount`),0) AS `tax` FROM `payment_detail_all` a, `uhid_and_opdid` b WHERE a.`patient_id`=b.`patient_id` AND a.`opd_id`=b.`opd_id` AND a.`date`!=b.`date` AND a.`patient_id`='$patient_id' and a.`opd_id`='$opd_id' AND a.`date` between '$date1' AND '$date2' AND a.`pay_id`='$con_bal[pay_id]' $user_str_a "));
				
				$bal_paid           =$check_paid["paid"];
				$bal_discount       =$check_paid["discount"];
				$bal_refund_amount  =$check_paid["refund"];
				$bal_tax_amount     =$check_paid["tax"];
				
				$bal_visit_fee=$con_pat_pay['visit_fee'];
				$bal_regd_fee=$con_pat_pay['regd_fee'];
				
				if($bal_discount<0)
				{
					$bal_discount=0;
				}
				
				$quser=mysqli_fetch_array(mysqli_query($link, " SELECT `name` FROM `employee` WHERE `emp_id`='$con_bal[user]' "));
				$pat_typ_text=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `patient_type_master` WHERE `p_type_id`='$con_bal[type]' "));
				$encounter_name=$pat_typ_text['p_type'];
                  $tot_bal_discount+=$bal_discount;
                    $tot_dis+=$bal_discount;
                    $tot_bal_refund_amount+=$bal_refund_amount;
                    $tot_refund_amount+=$bal_refund_amount;
                    $tot_bal_tax_amount+=$bal_tax_amount;
                    $tot_bal_paid+=$bal_paid;
                    //~ $tot_bal_balance+=$bal_balance;
                    
                }
                }
                if($tot_refund_amount>0)
		        { $net_receive_amt= $tot_paid+$tot_bal_paid-$tot_refund_amount;
                }
}
	if($encounter_pay_type==2)
	{
		$pat_typ=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `patient_type_master` WHERE `p_type_id`='$encounter' "));
		$pat_typ_encounter=$pat_typ["p_type"];

        	$n=1;
		$tot_bill=$tot_dis=$tot_paid=$tot_bal=$tot_refund_amount=$tot_tax_amount=0;
		
		$pat_reg_qry=mysqli_query($link, " SELECT * FROM `uhid_and_opdid` WHERE `date` between '$date1' AND '$date2' AND `type`='$encounter' $user_str $branch_str ORDER BY `slno` ASC ");
		while($pat_reg=mysqli_fetch_array($pat_reg_qry))
		{
			$pat_info=mysqli_fetch_array(mysqli_query($link, "select * from patient_info where patient_id='$pat_reg[patient_id]'"));
			
			$uhid_id     =$pat_info["patient_id"];
			$patient_id  =$pat_reg["patient_id"];
			$opd_id      =$pat_reg["opd_id"];
			
			$pat_show=0;
			$bill_amt=$discount=$paid=$balance=$refund_amount=$tax_amount=0;
			
			$inv_pat_pay=mysqli_fetch_array(mysqli_query($link, "select * from invest_patient_payment_details where patient_id='$pat_reg[patient_id]' and opd_id='$pat_reg[opd_id]'"));
			
			$bill_amt=$inv_pat_pay['tot_amount'];
			
			$check_paid=mysqli_fetch_array(mysqli_query($link, " SELECT ifnull(SUM(a.`amount`),0) AS `paid`, ifnull(SUM(a.`discount_amount`),0) AS `discount`, ifnull(SUM(a.`refund_amount`),0) AS `refund`, ifnull(SUM(a.`tax_amount`),0) AS `tax` FROM `payment_detail_all` a, `uhid_and_opdid` b WHERE a.`patient_id`=b.`patient_id` AND a.`opd_id`=b.`opd_id` AND a.`date`=b.`date` AND a.`patient_id`='$patient_id' and a.`opd_id`='$opd_id' AND a.`date` between '$date1' AND '$date2' $user_str_a "));
			
			$paid           =$check_paid["paid"];
			$discount       =$check_paid["discount"];
			$refund_amount  =$check_paid["refund"];
			$tax_amount     =$check_paid["tax"];
			
			$check_refund=mysqli_fetch_array(mysqli_query($link, " SELECT ifnull(SUM(a.`refund_amount`),0) AS `refund` FROM `payment_detail_all` a, `uhid_and_opdid` b WHERE a.`patient_id`=b.`patient_id` AND a.`opd_id`=b.`opd_id` AND a.`date`!=b.`date` AND a.`patient_id`='$patient_id' and a.`opd_id`='$opd_id' "));
			$refund_amount_other  =$check_refund["refund"];
			
			$check_refund_discount=mysqli_fetch_array(mysqli_query($link, " SELECT ifnull(SUM(a.`discount_amount`),0) AS `discount` FROM `payment_detail_all` a, `uhid_and_opdid` b WHERE a.`patient_id`=b.`patient_id` AND a.`opd_id`=b.`opd_id` AND a.`date`!=b.`date` AND a.`patient_id`='$patient_id' and a.`opd_id`='$opd_id' AND `discount_amount`<0 "));
			$refund_discount_other  =$check_refund_discount["discount"];
			
			$bill_amt+=$refund_amount_other+abs($refund_discount_other);
			
			$settle_amount=$paid+$discount+$tax_amount-$refund_amount;
			
			$balance=$bill_amt-$settle_amount;
			
			$quser=mysqli_fetch_array(mysqli_query($link, " SELECT `name` FROM `employee` WHERE `emp_id`='$pat_reg[user]' "));
			$pat_typ_text=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `patient_type_master` WHERE `p_type_id`='$pat_reg[type]' "));
			$encounter_name=$pat_typ_text['p_type'];
            $n++;
			
			$tot_bill+=$bill_amt;
			$tot_dis+=$discount;
			$tot_refund_amount+=$refund_amount;
			$tot_tax_amount+=$rax_amount;
			$tot_paid+=$paid;
			$tot_bal+=$balance;
		}
        $inv_bal_qry=mysqli_query($link, " SELECT a.*, b.`type` FROM `payment_detail_all` a, `uhid_and_opdid` b WHERE a.`patient_id`=b.`patient_id` AND a.`opd_id`=b.`opd_id` AND a.`date`!=b.`date` AND a.`date` between '$date1' AND '$date2' AND b.`type`='$encounter' $user_str_a $branch_str_b ORDER BY a.`pay_id` ASC ");
		$inv_bal_num=mysqli_num_rows($inv_bal_qry);
		$zz=0;
		if($inv_bal_num>0)
		{
			
			$zz=1;
			$n=1;
			$tot_bal_bill_amt=$tot_bal_discount=$tot_bal_paid=$tot_bal_balance=$tot_bal_visit_fee=$tot_bal_regd_fee=$tot_bal_refund_amount=0;
			while($inv_bal=mysqli_fetch_array($inv_bal_qry))
			{
				$pat_info=mysqli_fetch_array(mysqli_query($link, "select * from patient_info where patient_id='$inv_bal[patient_id]'"));
				
				$uhid_id    =$pat_info["patient_id"];
				$patient_id  =$inv_bal["patient_id"];
				$opd_id      =$inv_bal["opd_id"];
				
				$bal_pat_show=0;
				$bal_bill_amt=$bal_discount=$bal_paid=$bal_balance=$bal_refund_amount=$bal_tax_amount=0;
				
				$bal_paid=$inv_bal['amount'];
				
				$inv_pat_pay=mysqli_fetch_array(mysqli_query($link, "select * from invest_patient_payment_details where patient_id='$inv_bal[patient_id]' and opd_id='$inv_bal[opd_id]'"));
				
				$bal_bill_amt=$inv_pat_pay['tot_amount'];
				
				$check_paid=mysqli_fetch_array(mysqli_query($link, " SELECT ifnull(SUM(a.`amount`),0) AS `paid`, ifnull(SUM(a.`discount_amount`),0) AS `discount`, ifnull(SUM(a.`refund_amount`),0) AS `refund`, ifnull(SUM(a.`tax_amount`),0) AS `tax` FROM `payment_detail_all` a, `uhid_and_opdid` b WHERE a.`patient_id`=b.`patient_id` AND a.`opd_id`=b.`opd_id` AND a.`date`!=b.`date` AND a.`patient_id`='$patient_id' and a.`opd_id`='$opd_id' AND a.`date` between '$date1' AND '$date2' AND a.`pay_id`='$inv_bal[pay_id]' $user_str_a "));
				
				$bal_paid           =$check_paid["paid"];
				$bal_discount       =$check_paid["discount"];
				$bal_refund_amount  =$check_paid["refund"];
				$bal_tax_amount     =$check_paid["tax"];
				
				if($bal_discount<0)
				{
					$bal_discount=0;
				}
				
				$quser=mysqli_fetch_array(mysqli_query($link, " SELECT `name` FROM `employee` WHERE `emp_id`='$inv_bal[user]' "));
				$pat_typ_text=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `patient_type_master` WHERE `p_type_id`='$inv_bal[type]' "));
				$encounter_name=$pat_typ_text['p_type'];
                $n++;
				
				//$tot_bal_bill_amt+=$bal_bill_amt;
				$tot_bal_discount+=$bal_discount;
				$tot_dis+=$bal_discount;
				$tot_bal_refund_amount+=$bal_refund_amount;
				$tot_refund_amount+=$bal_refund_amount;
				$tot_bal_paid+=$bal_paid;
				//$tot_bal_balance+=$bal_balance;
				
			}
            }
            if($tot_refund_amount>0)
		{ $net_receive_amt = $tot_paid+$tot_bal_paid-$tot_refund_amount; }
        
        }
	if($encounter!=3 && $encounter_pay_type==3)
	{
		$pat_typ=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `patient_type_master` WHERE `p_type_id`='$encounter' "));
		$pat_typ_encounter=$pat_typ["p_type"];
        	$n=1;
			$tot_bill_amt=$tot_discount=$tot_paid=$tot_refund_amount=$tot_tax_amount=$tot_balance=0;
			$pat_reg_qry=mysqli_query($link, " SELECT * FROM `uhid_and_opdid` WHERE `date` between '$date1' AND '$date2' AND `type`='$encounter' $user_str $branch_str ORDER BY `slno` ASC ");
			while($pat_reg=mysqli_fetch_array($pat_reg_qry))
			{
				$pat_info=mysqli_fetch_array(mysqli_query($link, "select * from patient_info where patient_id='$pat_reg[patient_id]'"));
				
				$uhid_id     =$pat_info["patient_id"];
				$patient_id  =$pat_reg["patient_id"];
				$opd_id      =$pat_reg["opd_id"];
				
				$pat_show=0;
				$bill_amt=$discount=$paid=$refund_amount=$tax_amount=$balance=0;
				
				$tot_serv=mysqli_fetch_array(mysqli_query($link, " SELECT ifnull(SUM(`amount`),0) AS `sum_tot_amt` FROM `ipd_pat_service_details` WHERE `patient_id`='$pat_reg[patient_id]' AND `ipd_id`='$pat_reg[opd_id]'"));
				
				$bill_amt=$tot_serv['sum_tot_amt'];
				
				$check_paid=mysqli_fetch_array(mysqli_query($link, " SELECT ifnull(SUM(a.`amount`),0) AS `paid`, ifnull(SUM(a.`discount_amount`),0) AS `discount`, ifnull(SUM(a.`refund_amount`),0) AS `refund`, ifnull(SUM(a.`tax_amount`),0) AS `tax` FROM `payment_detail_all` a, `uhid_and_opdid` b WHERE a.`patient_id`=b.`patient_id` AND a.`opd_id`=b.`opd_id` AND a.`date`=b.`date` AND a.`patient_id`='$patient_id' and a.`opd_id`='$opd_id' AND a.`date` between '$date1' AND '$date2' $user_str_a "));
				
				$paid           =$check_paid["paid"];
				$discount       =$check_paid["discount"];
				$refund_amount  =$check_paid["refund"];
				$tax_amount     =$check_paid["tax"];
				
				$check_refund=mysqli_fetch_array(mysqli_query($link, " SELECT ifnull(SUM(a.`refund_amount`),0) AS `refund` FROM `payment_detail_all` a, `uhid_and_opdid` b WHERE a.`patient_id`=b.`patient_id` AND a.`opd_id`=b.`opd_id` AND a.`date`!=b.`date` AND a.`patient_id`='$patient_id' and a.`opd_id`='$opd_id' "));
				$refund_amount_other  =$check_refund["refund"];
				
				if($refund_amount_other>0)
				{
					$bill_amt+=$refund_amount_other;
				}
				
				$settle_amount=$paid+$discount+$tax_amount-$refund_amount;
				
				$balance=$bill_amt-$settle_amount;
				
				$quser=mysqli_fetch_array(mysqli_query($link, " SELECT `name` FROM `employee` WHERE `emp_id`='$pat_reg[user]' "));
				$pat_typ_text=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `patient_type_master` WHERE `p_type_id`='$pat_reg[type]' "));
				$encounter_name=$pat_typ_text['p_type'];
                $n++;
				
				$tot_bill_amt+=$bill_amt;
				$tot_dis+=$discount;
				$tot_refund_amount+=$refund_amount;
				$tot_tax_amount+=$tax_amount;
				$tot_paid+=$paid;
				$tot_balance+=$balance;
			}
            $inv_bal_qry=mysqli_query($link, " SELECT a.*, b.`type` FROM `payment_detail_all` a, `uhid_and_opdid` b WHERE a.`patient_id`=b.`patient_id` AND a.`opd_id`=b.`opd_id` AND a.`date`!=b.`date` AND a.`date` between '$date1' AND '$date2' AND b.`type`='$encounter' $user_str_a $branch_str_b ORDER BY a.`pay_id` ASC ");
		$inv_bal_num=mysqli_num_rows($inv_bal_qry);
		$zz=0;
		if($inv_bal_num>0)
		{
			
			$zz=1;
			$n=1;
			$tot_bal_bill_amt=$tot_bal_discount=$tot_bal_paid=$tot_bal_balance=$tot_bal_visit_fee=$tot_bal_regd_fee=$tot_bal_refund_amount=0;
			while($inv_bal=mysqli_fetch_array($inv_bal_qry))
			{
				$pat_info=mysqli_fetch_array(mysqli_query($link, "select * from patient_info where patient_id='$inv_bal[patient_id]'"));
				
				$uhid_id    =$pat_info["patient_id"];
				$patient_id  =$inv_bal["patient_id"];
				$opd_id      =$inv_bal["opd_id"];
				
				$bal_pat_show=0;
				$bal_bill_amt=$bal_discount=$bal_paid=$bal_balance=$bal_refund_amount=$bal_tax_amount=0;
				
				$bal_paid=$inv_bal['amount'];
				
				$tot_serv=mysqli_fetch_array(mysqli_query($link, " SELECT ifnull(SUM(`amount`),0) AS `sum_tot_amt` FROM `ipd_pat_service_details` WHERE `patient_id`='$patient_id' AND `ipd_id`='$opd_id'"));
				
				$bal_bill_amt=$tot_serv['sum_tot_amt'];
				
				$check_paid=mysqli_fetch_array(mysqli_query($link, " SELECT ifnull(SUM(a.`amount`),0) AS `paid`, ifnull(SUM(a.`discount_amount`),0) AS `discount`, ifnull(SUM(a.`refund_amount`),0) AS `refund`, ifnull(SUM(a.`tax_amount`),0) AS `tax` FROM `payment_detail_all` a, `uhid_and_opdid` b WHERE a.`patient_id`=b.`patient_id` AND a.`opd_id`=b.`opd_id` AND a.`date`!=b.`date` AND a.`patient_id`='$patient_id' and a.`opd_id`='$opd_id' AND a.`date` between '$date1' AND '$date2' AND a.`pay_id`='$inv_bal[pay_id]' $user_str_a "));
				
				$bal_paid           =$check_paid["paid"];
				$bal_discount       =$check_paid["discount"];
				$bal_refund_amount  =$check_paid["refund"];
				$bal_tax_amount     =$check_paid["tax"];
				
				$quser=mysqli_fetch_array(mysqli_query($link, " SELECT `name` FROM `employee` WHERE `emp_id`='$inv_bal[user]' "));
				$pat_typ_text=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `patient_type_master` WHERE `p_type_id`='$inv_bal[type]' "));
				$encounter_name=$pat_typ_text['p_type'];
                $n++;
				
				//$tot_bal_bill_amt+=$bal_bill_amt;
				$tot_bal_discount+=$bal_discount;
				$tot_dis+=$bal_discount;
				$tot_bal_refund_amount+=$bal_refund_amount;
				$tot_refund_amount+=$bal_refund_amount;
				$tot_bal_paid+=$bal_paid;
				//$tot_bal_balance+=$bal_balance;
				
			}
            }
            if($tot_refund_amount>0)
		{
$net_receive_amt = $tot_paid+$tot_bal_paid-$tot_refund_amount;
        }

        }
	if($encounter==3)
	{
		$grand_tot_paid=$grand_tot_discount=0;
		
		$pat_typ=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `patient_type_master` WHERE `p_type_id`='$encounter' "));
		$pat_typ_encounter=$pat_typ["p_type"];
        $n=1;
			$tot_advance_paid=0;
			$adv_bal_qry=mysqli_query($link, " SELECT a.*, b.`type` FROM `payment_detail_all` a, `uhid_and_opdid` b WHERE a.`patient_id`=b.`patient_id` AND a.`opd_id`=b.`opd_id` AND a.`date` between '$date1' AND '$date2' AND a.`payment_type`='Advance' AND b.`type`='$encounter' $user_str_a $branch_str_b ORDER BY a.`pay_id` ASC ");
			while($adv_bal=mysqli_fetch_array($adv_bal_qry))
			{
				$pat_show=0;
				$advance_paid=0;
				
				$advance_paid=$adv_bal["amount"];
				
				$pat_info=mysqli_fetch_array(mysqli_query($link, "select * from patient_info where patient_id='$adv_bal[patient_id]'"));
				
				$uhid_id=$pat_info["patient_id"];
				
				$quser=mysqli_fetch_array(mysqli_query($link, " SELECT `name` FROM `employee` WHERE `emp_id`='$adv_bal[user]' "));
				$pat_typ_text=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `patient_type_master` WHERE `p_type_id`='$adv_bal[type]' "));
				$encounter_name=$pat_typ_text['p_type'];
                $n++;
				
				$tot_advance_paid+=$advance_paid;
				$grand_tot_paid+=$advance_paid;
			}
            $n=1;
			$tot_bill_amt=$tot_discount=$tot_final_pay=$tot_refund_amount=$tot_tax_amount=$tot_balance=0;
			$adv_bal_qry=mysqli_query($link, " SELECT a.*, b.`type` FROM `payment_detail_all` a, `uhid_and_opdid` b WHERE a.`patient_id`=b.`patient_id` AND a.`opd_id`=b.`opd_id` AND a.`date` between '$date1' AND '$date2' AND a.`payment_type`='Final' AND a.`payment_mode`!='Credit' AND b.`type`='$encounter' $user_str_a $branch_str_b ORDER BY a.`pay_id` ");
			while($adv_bal=mysqli_fetch_array($adv_bal_qry))
			{
				$pat_info=mysqli_fetch_array(mysqli_query($link, "select * from patient_info where patient_id='$adv_bal[patient_id]'"));
				
				$uhid_id=$pat_info["patient_id"];
				
				$patient_id=$adv_bal["patient_id"];
				$ipd=$opd_id=$adv_bal["opd_id"];
				
				$pat_show=0;
				$bill_amt=$discount=$final_pay=$refund_amount=$tax_amount=$balance=$prev_pay=0;
				
				$baby_serv_tot=0;
				$baby_ot_total=0;
				$delivery_qry=mysqli_query($link," SELECT * FROM `ipd_pat_delivery_det` WHERE patient_id='$patient_id' and ipd_id='$ipd' ");
				while($delivery_check=mysqli_fetch_array($delivery_qry))
				{
					$baby_tot_serv=mysqli_fetch_array(mysqli_query($link," SELECT ifnull(sum(`amount`),0) as tots FROM `ipd_pat_service_details` WHERE patient_id='$delivery_check[baby_uhid]' and ipd_id='$delivery_check[baby_ipd_id]' "));
					$baby_serv_tot+=$baby_tot_serv["tots"];
					
					// OT Charge Baby
					$baby_ot_tot_val=mysqli_fetch_array(mysqli_query($link,"select ifnull(sum(amount),0) as g_tot from ot_pat_service_details where patient_id='$delivery_check[baby_uhid]' and ipd_id='$delivery_check[baby_ipd_id]' "));
					$baby_ot_total+=$baby_ot_tot_val["g_tot"];
					
				}
				
				$no_of_days_val=mysqli_fetch_array(mysqli_query($link,"select * from ipd_pat_service_details where patient_id='$patient_id' and ipd_id='$ipd' and `group_id`='141' "));
				$no_of_days=$no_of_days_val["ser_quantity"];
				
				$tot_serv1=mysqli_fetch_array(mysqli_query($link," SELECT ifnull(sum(`amount`),0) as tots FROM `ipd_pat_service_details` WHERE patient_id='$patient_id' and ipd_id='$ipd' and group_id='141' "));
				$tot_serv_amt1=$tot_serv1["tots"];
				//$tot_serv_amt1=$tot_serv1["tots"]*$no_of_days;
				
				$tot_serv2=mysqli_fetch_array(mysqli_query($link," SELECT ifnull(sum(`amount`),0) as tots FROM `ipd_pat_service_details` WHERE patient_id='$patient_id' and ipd_id='$ipd' and group_id!='141' "));
				$tot_serv_amt2=$tot_serv2["tots"];
				
				// OT Charge
				$ot_tot_val=mysqli_fetch_array(mysqli_query($link,"select ifnull(sum(amount),0) as g_tot from ot_pat_service_details where patient_id='$patient_id' and ipd_id='$ipd' "));
				$ot_total=$ot_tot_val["g_tot"];
				
				// Total
				$bill_amt=$tot_serv_amt1+$tot_serv_amt2+$baby_serv_tot+$ot_total+$baby_ot_total;
				
				$discount=$adv_bal['discount_amount'];
				$tax_amount=$adv_bal['tax_amount'];
				$refund_amount=$adv_bal['refund_amount'];
				$final_pay=$adv_bal['amount'];
				
				$check_refund=mysqli_fetch_array(mysqli_query($link, " SELECT ifnull(SUM(`refund_amount`),0) AS `refund` FROM `payment_detail_all` WHERE `patient_id`='$patient_id' AND `opd_id`='$ipd' AND `payment_type`='Refund' $user_str "));
				$refund_amount+=$check_refund['refund'];
				
				$check_paid=mysqli_fetch_array(mysqli_query($link, " SELECT ifnull(SUM(`amount`),0) AS `paid`, ifnull(SUM(`discount_amount`),0) AS `discount`, ifnull(SUM(`refund_amount`),0) AS `refund`, ifnull(SUM(`tax_amount`),0) AS `tax` FROM `payment_detail_all` WHERE `patient_id`='$patient_id' AND `opd_id`='$ipd' AND `payment_type`='Advance' $user_str "));
				
				$prev_pay      =$check_paid["paid"];
				
				$balance=($bill_amt-$discount-$tax_amount-$final_pay-$prev_pay+$refund_amount);
				
				$quser=mysqli_fetch_array(mysqli_query($link, " SELECT `name` FROM `employee` WHERE `emp_id`='$adv_bal[user]' "));
				$pat_typ_text=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `patient_type_master` WHERE `p_type_id`='$adv_bal[type]' "));
				$encounter_name=$pat_typ_text['p_type'];

                $n++;
				
				$tot_bill_amt+=$bill_amt;
				$tot_discount+=$discount;
				$grand_tot_discount+=$discount;
				$tot_final_pay+=$final_pay;
				$grand_tot_paid+=$final_pay;
				$tot_refund_amount+=$refund_amount;
				$tot_balance+=$balance;
			}
            $adv_bal_qry=mysqli_query($link, " SELECT a.*, b.`type` FROM `payment_detail_all` a, `uhid_and_opdid` b WHERE a.`patient_id`=b.`patient_id` AND a.`opd_id`=b.`opd_id` AND a.`date` between '$date1' AND '$date2' AND a.`payment_type`='Balance' AND b.`type`='$encounter' $user_str_a ORDER BY a.`pay_id` ASC ");
		$adv_bal_num=mysqli_num_rows($adv_bal_qry);
		if($adv_bal_num>0)
		{$n=1;
			$tot_balance_paid=$tot_bal_discount=0;
			while($adv_bal=mysqli_fetch_array($adv_bal_qry))
			{
				$pat_show=0;
				$advance_paid=0;
				$bal_discount=0;
				
				$balance_paid=$adv_bal["amount"];
				
				if($adv_bal["discount_amount"]>0)
				{
					$bal_discount=$adv_bal["discount_amount"];
				}
				
				$pat_info=mysqli_fetch_array(mysqli_query($link, "select * from patient_info where patient_id='$adv_bal[patient_id]'"));
				
				$uhid_id=$pat_info["patient_id"];
				
				$quser=mysqli_fetch_array(mysqli_query($link, " SELECT `name` FROM `employee` WHERE `emp_id`='$adv_bal[user]' "));
				$pat_typ_text=mysqli_fetch_array(mysqli_query($link, " SELECT * FROM `patient_type_master` WHERE `p_type_id`='$adv_bal[type]' "));
				$encounter_name=$pat_typ_text['p_type'];
                $n++;
				
				$tot_balance_paid+=$balance_paid;
				$grand_tot_paid+=$balance_paid;
				$tot_bal_discount+=$bal_discount;
				$grand_tot_discount+=$bal_discount;
			}
            }
            }
echo json_encode($result);
?>