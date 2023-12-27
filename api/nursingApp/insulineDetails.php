<?php
include "../../includes/connection.php";
include "../../includes/global.function.php";

$date = date("Y-m-d");
$time = date("H:i:s");

$arr = array();
$type = $_POST['type'];
$ipd_id = $_POST['ipd'];
$patient_id = $_POST['uhid'];

if ($type == "getData") {
    $qry = mysqli_query($link, "SELECT * FROM `ipd_pat_insulin_details` WHERE `patient_id`='$patient_id' AND `ipd_id` = '$ipd_id'");

    // echo "SELECT * FROM `ipd_pat_insulin_details` WHERE `patient_id`='$patient_id' AND `ipd_id` = '$ipd_id'";
    while ($r = mysqli_fetch_array($qry)) {
        $insuline_name = mysqli_fetch_array(mysqli_query($link, "SELECT `name` FROM `insulin_type_master` WHERE `insulin_id` = '$r[insulin_id]'"));
        $doctor_name = mysqli_fetch_array(mysqli_query($link, "SELECT `name` FROM `consultant_doctor_master` WHERE `consultantdoctorid` = '$r[consultantdoctorid]'"));
        $given_by = mysqli_fetch_array(mysqli_query($link, "SELECT `name` FROM `employee` WHERE `emp_id` = '$r[given_by]'"));

        $temp = array();
        $temp['id'] = $r['slno'];
        $temp['insuline_name'] = $insuline_name['name'];
        $temp['dosage'] = $r['dosage'];
        $temp['note'] = $r['insulin_note'];
        $temp['doctor'] = $doctor_name['name'];
        $temp['given_by'] = $given_by['name'];
        $temp['given_date'] = convert_date_g($r['given_date']);
        $temp['given_time'] = convert_time($r['given_time']);

        array_push($arr, $temp);

    }
}
if ($type == "insulineList") {
    $qry = mysqli_query($link, "SELECT * FROM `insulin_type_master`");
    while ($r = mysqli_fetch_array($qry)) {
        $temp = [];
        $temp["insulineId"] = $r["insulin_id"];
        $temp["insulineName"] = $r["name"];
        array_push($arr, $temp);
    }
}

if ($type == "doctorList") {
    $qry = mysqli_query($link, "SELECT a.* FROM `consultant_doctor_master` a, `employee` b WHERE a.`emp_id`=b.`emp_id` AND b.`status`='0' ORDER BY `Name`");
    while ($r = mysqli_fetch_array($qry)) {
        $temp = [];
        $temp["doctorId"] = $r["consultantdoctorid"];
        $temp["doctorName"] = $r["Name"];
        array_push($arr, $temp);
    }
}
if ($type == "givenByList") {
    $qry = mysqli_query($link, "SELECT * FROM `employee` WHERE `status`='0' AND `levelid` IN(5,11) ORDER BY `name`");

    while ($r = mysqli_fetch_array($qry)) {
        $temp = [];
        $temp["givenById"] = $r["emp_id"];
        $temp["givenByName"] = $r["name"];
        array_push($arr, $temp);
    }
}

if ($type == "addInsuline") {
    $id = $_POST['id'];
    $insuline_name = $_POST['insulineName'];
    $dosage = $_POST['dosage'];
    $note = $_POST['note'];
    $consultantdoctorid = $_POST['doctorId'];
    $given_by = $_POST['givenById'];
    $given_date = $_POST['givenDate'];
    $given_time = $_POST['givenTime'];
    $user = $_POST['user'];


    if ($id == 0) {
        if (mysqli_query($link, "INSERT INTO `ipd_pat_insulin_details`(`patient_id`, `ipd_id`, `insulin_id`, `dosage`, `insulin_note`, `consultantdoctorid`, `given_by`, `given_date`, `given_time`, `user`, `date`, `time`) VALUES ('$patient_id','$ipd_id','$insuline_name','$dosage','$note','$consultantdoctorid','$given_by','$date','$given_time','$user','$date','$time')")) {
            $arr = array("status" => "added");
        } else {
            $arr = array("status" => "failed");
        }
    } else {
        if (mysqli_query($link, "UPDATE `ipd_pat_insulin_details` SET `insulin_id`='$insuline_name',`dosage`='$dosage',`insulin_note`='$note',`consultantdoctorid`='$consultantdoctorid',`given_by`='$given_by',`given_time`='$given_time',`user`='$user' WHERE `slno`='$id'")) {
            $arr = array("status" => "updated");
        } else {
            $arr = array("status" => "failed");
        }
    }

}

if ($type == "getOldData") {
    $qry = mysqli_query($link, "SELECT * FROM `ipd_pat_insulin_details` WHERE `slno` = '$_POST[insuline_id]'");
    while ($r = mysqli_fetch_array($qry)) {
        $insuline_name = mysqli_fetch_array(mysqli_query($link, "SELECT `name` FROM `insulin_type_master` WHERE `insulin_id` = '$r[insulin_id]'"));
        $doctor_name = mysqli_fetch_array(mysqli_query($link, "SELECT `Name` FROM `consultant_doctor_master` WHERE `consultantdoctorid` = '$r[consultantdoctorid]'"));
        $given_by = mysqli_fetch_array(mysqli_query($link, "SELECT `name` FROM `employee` WHERE `emp_id` = '$r[given_by]'"));

        $temp = [];
        $temp['id'] = $r['slno'];
        $temp['insulineNameId'] = $r['insulin_id'];
        $temp['insulineName'] = $insuline_name['name'];
        $temp['dosage'] = $r['dosage'];
        $temp['note'] = $r['insulin_note'];
        $temp['doctorId'] = $r['consultantdoctorid'];
        $temp['doctorName'] = $doctor_name['Name'];
        $temp['givenById'] = $r['given_by'];
        $temp['givenBy'] = $given_by['name'];
        $temp['recordTime'] = $r['given_time'];
        $temp['recordDate'] = $r['given_date'];

        array_push($arr, $temp);
    }
}
echo json_encode($arr);