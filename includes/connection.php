<?php

$host="localhost";
$username="root";
$password="cGVuZ3Vpbg==";
$db_name="gd_nursing_home";

$link=($GLOBALS["___mysqli_ston"] = mysqli_connect($host, $username, base64_decode($password)));
((bool)mysqli_query($link, "USE " . $db_name));

$link_error="";
if (!$link) {
    //die('Connect Error: ' . mysqli_connect_error());
    $link_error=mysqli_connect_error()." Local";
}

// Project name, logo and address
$brandLogo = "images/client_logo.jpg";
$location = "Guwahati, Assam";
//$brand="Demo HIS";

// Client
//$center="Demo Hospital";
//$signature="For Demo Hospital";

$code="HIS";

//$page_head_line="Dr. Babasaheb Ambedkar Vaidyakiya Pratishtan";

include("global.config.php");

date_default_timezone_set("Asia/Kolkata");
$date=date("Y-m-d");

mysqli_set_charset($link, 'utf8');

$ip_addr=$_SERVER["REMOTE_ADDR"];

$whatsapp=0; // 1=true
$pdf_report=1; // 1=true
$instrument_wise_normal_range=0; // 1=true
$data_mp=1; // 1=true
$doc_comm=1; // 1=true
$reporting_without_sample_receive=1; // 1=true
$reporting_delivery=1; // 1=true
$repeat_parameter=1; // 1=true
//ini_set('display_errors', '1');
//ini_set('display_startup_errors', '1');
//error_reporting(E_ALL);


?>
