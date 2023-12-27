<?php
include '../includes/connection.php';
$type = $_POST['type'];

$date = date("Y-m-d");
$time = date("H:i:s");

if ($type == 'checkConnection') {
    if ($link) {
        $arr = array("Status" => "Connected");
    } else {
        $arr = array("Status" => "Connection Failed");
    }
}
if ($type == 'checkUpdate') {
    $client_app_id = $_POST['app_id'];
    $app_ver = mysqli_fetch_array(mysqli_query($link, "SELECT * FROM `android_app_updates` WHERE `app_id` = '$client_app_id'"));

    $arr = array("version" => "$app_ver[version]");
}

echo json_encode($arr);