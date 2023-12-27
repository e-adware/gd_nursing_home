<?php
include "../../includes/connection.php";
include "../../includes/global.function.php";

$date = date("Y-m-d");
$time = date("H:i:s");
$arr = array();
$type = $_POST["type"];

if ($type == "getData")
{
    $uhid = $_POST["uhid"];
    $ipd = $_POST["ipd"];

    // echo "SELECT * FROM `ipd_pat_vital` WHERE `patient_id`='$uhid' AND `ipd_id`='$ipd' ORDER BY `record_date` DESC, `record_time` DESC";
    $qry = mysqli_query($link, "SELECT * FROM `ipd_pat_vital` WHERE `patient_id`='$uhid' AND `ipd_id`='$ipd' ORDER BY `record_date` DESC, `record_time` DESC");
    $num = mysqli_num_rows($qry);
    if ($num > 0)
    {
        $n = 1;
        while ($r = mysqli_fetch_array($qry))
        {
            $user_info = mysqli_fetch_array(mysqli_query($link, "SELECT `name` FROM `employee` WHERE `emp_id`='$r[record_by]'"));
            $temp = array();
            $temp["vitalId"] = $r["id"];
            $temp["record_date"] = convert_date_g($r["record_date"]);
            $temp["record_time"] = convert_time($r["record_time"]);
            $temp["weight"] = $r["weight"];
            $temp["height"] = $r["height"];
            $temp["bmi"] = $r["BMI_1"] . "." . $r["BMI_2"];
            $temp["spo2"] = $r["spo2"];
            $temp["head"] = $r["head_circumference"];
            $temp["midarm"] = $r["medium_circumference"];
            $temp["pr"] = $r["PR"];
            $temp["rr"] = $r["RR"];
            $temp["bp"] = $r["systolic"] . "/" . $r["diastolic"];
            $temp["pulse"] = $r["pulse"];
            $temp["temperature"] = $r["temp"];
            $temp["note"] = $r["note"];
            $temp["record_by"] = $user_info["name"];
            $temp["intake"] = $r["intake_output_record"];
            array_push($arr, $temp);
        }
    }
}
if ($type == "doctorList")
{
    $q = mysqli_query($link, "SELECT * FROM `employee` WHERE `status`='0' AND `levelid` IN(5,11) ORDER BY `name`");
    while ($r = mysqli_fetch_array($q))
    {
        $temp = [];
        $temp["doctorId"] = $r["emp_id"];
        $temp["doctorName"] = $r["name"];
        array_push($arr, $temp);
    }
}

if ($type == "addVital")
{
    $height = $_POST["height"];
    $weight = $_POST["weight"];
    $mac = $_POST["midArmCircumference"];
    $head = $_POST["headCircumference"];
    $bmi = explode(".", $_POST["bmi"]);
    $bmi1 = $bmi[0];
    $bmi2 = $bmi[1];
    $spo2 = $_POST["spo2"];
    $pulse = $_POST["pulse"];
    $temperature = $_POST["temperature"];
    $pr = $_POST["pr"];
    $rr = $_POST["rr"];
    $systolic = $_POST["systolic"];
    $diastolic = $_POST["diastolic"];
    $intake = $_POST["intake"];
    $recTime = $_POST["recordTime"];
    $recordBy = $_POST["recordBy"];
    $user = $_POST["user"];
    $note = $_POST["note"];

    if (!$height)
    {
        $height = 0;
    }
    if (!$weight)
    {
        $weight = 0;
    }
    if (!$mac)
    {
        $mac = 0;
    }
    if (!$head)
    {
        $head = 0;
    }
    if (!$_POST["bmi"])
    {
        $bmi1 = 0;
        $bmi2 = 0;
    }
    if (!$spo2)
    {
        $spo2 = 0;
    }
    if (!$pulse)
    {
        $pulse = 0;
    }
    if (!$temperature)
    {
        $temperature = 0;
    }
    if (!$pr)
    {
        $pr = 0;
    }
    if (!$rr)
    {
        $rr = 0;
    }
    if (!$systolic)
    {
        $systolic = 0;
    }
    if (!$diastolic)
    {
        $diastolic = 0;
    }
    if (!$intake)
    {
        $intake = "";
    }
    if ($_POST['id'] == 0)
    {
        if (mysqli_query($link, "INSERT INTO `ipd_pat_vital`(`patient_id`, `ipd_id`, `weight`, `height`, `medium_circumference`, `BMI_1`, `BMI_2`, `spo2`, `pulse`, `head_circumference`, `PR`, `RR`, `temp`, `systolic`, `diastolic`, `note`, `intake_output_record`, `record_by`, `record_date`, `record_time`, `date`, `time`, `user`) VALUES ('$_POST[unit]', '$_POST[ipdId]', '$weight', '$height', '$mac', '$bmi1', '$bmi2', '$spo2', '$pulse', '$head', '$pr', '$rr', '$temperature', '$systolic', '$diastolic', '$note', '$intake', '$recordBy', '$date', '$recTime', '$date', '$time', '$user')"))
        {
            $arr = array("status" => "added");
        }
        else
        {
            $arr = array("status" => "failed insert");
        }
    }
    else
    {
        if (mysqli_query($link, "UPDATE `ipd_pat_vital` SET `weight`='$weight',`height`='$height',`medium_circumference`='$mac',`BMI_1`='$bmi1',`BMI_2`='$bmi2',`spo2`='$spo2',`pulse`='$pulse',`head_circumference`='$head',`PR`='$pr',`RR`='$rr',`temp`='$temperature',`systolic`='$systolic',`diastolic`='$diastolic',`note`='$note',`intake_output_record`='$intake',`record_by`='$recordBy',`user`='$user' WHERE `id`='$_POST[id]'"))
        {
            $arr = array("status" => "updated");
        }
        else
        {
            $arr = array("status" => "UPDATE `ipd_pat_vital` SET `weight`='$weight',`height`='$height',`medium_circumference`='$mac',`BMI_1`='$bmi1',`BMI_2`='$bmi2',`spo2`='$spo2',`pulse`='$pulse',`head_circumference`='$head',`PR`='$pr',`RR`='$rr',`temp`='$temperature',`systolic`='$systolic',`diastolic`='$diastolic',`note`='$note',`intake_output_record`='$intake',`record_by`='$recordBy',`user`='$user' WHERE `id`='$_POST[id]'");
        }
    }
}
if ($type == 'editData')
{
    $id = $_POST['vital_id'];
    $qry = mysqli_fetch_array(mysqli_query($link, "SELECT * FROM `ipd_pat_vital` WHERE `id` = '$id'"));
    $record_doctor = mysqli_fetch_array(mysqli_query($link, "SELECT `name` FROM `employee` WHERE `emp_id`='$qry[record_by]'"));

    $temp = [];
    $temp['vitalId'] = $id;
    $temp['record_date'] = $qry['record_date'];
    $temp['record_time'] = $qry['record_time'];
    $temp['weight'] = $qry['weight'];
    $temp['height'] = $qry['height'];
    $temp['bmi'] = $qry['BMI_1'] . "." . $qry['BMI_2'];
    $temp['spo2'] = $qry['spo2'];
    $temp['head'] = $qry['head_circumference'];
    $temp['midarm'] = $qry['medium_circumference'];
    $temp['pr'] = $qry['PR'];
    $temp['rr'] = $qry['RR'];
    $temp['temperature'] = $qry['temp'];
    $temp['systolic'] = $qry['systolic'];
    $temp['diastolic'] = $qry['diastolic'];
    $temp['pulse'] = $qry['pulse'];
    $temp['note'] = $qry['note'];
    $temp['record_by'] = $qry['record_by'];
    $temp['record_doctor'] = $record_doctor['name'];
    $temp['intake'] = $qry['intake_output_record'];

    array_push($arr, $temp);
}

echo json_encode($arr);

