<?php
include('../../includes/connection.php');
if(!empty($_POST['phone'] && !empty($_POST['password']))) {
    $phone = $_POST['phone'];
    $password = md5($_POST['password']);
    $result = array();
    if($link) {
        $sql = "SELECT * FROM `employee` WHERE `phone` = '$phone'";
        $result = mysqli_query($link, $sql);
        if(mysqli_num_rows($result) != 0) {
            $row = mysqli_fetch_assoc($result);
            if($phone == $row['phone'] && $password == $row['password']) {
                try {
                    $apikey = bin2hex(random_bytes(23));
                } catch(Exception $e) {
                    $apikey = bin2hex(uniqid($email, true));
                }
                $sqlUpdate = "UPDATE `employee` SET `api_key` = '$apikey' WHERE `phone` = '$phone'";
                if(mysqli_query($link, $sqlUpdate)) {
                    if($row['levelid'] == '1' || $row['levelid'] == '22') {
                        $levelid = '1';
                    } else {
                        $levelid = $row['levelid'];
                    }
                    $result = array("Status" => "Success", "Message" => "Login Success", "emp_id" => $row['emp_id'], "name" => $row['name'], "phone" => $row['phone'], "levelid" => $levelid, "apikey" => $apikey);

                } else {
                    $result = array("Status" => "Failed", "Message" => "Login Failed");
                }
            } else {
                $result = array("Status" => "Error", "Message" => "UserID/Password Mismatch");
            }
        } else {
            $result = array("Status" => "Error", "Message" => "UserID/Password Mismatch");
        }
    } else {
        $result = array("Status" => "Failed", "Message" => "DB Connection Failed");
    }
} else {
    $result = array("Status" => "Failed", "Message" => "All Fields are required");
} 

echo json_encode($result);

?>
