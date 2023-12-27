<?php
include "../../includes/connection.php";
include "../../includes/global.function.php";

$arr = array();

$q = mysqli_query($link, "SELECT * FROM `ward_master`");
while($r = mysqli_fetch_array($q)) {
        $temp = [];
        $temp["wardId"] = $r["ward_id"];
        $temp["wardName"] = $r["name"];
        array_push($arr, $temp);
}
echo json_encode($arr);