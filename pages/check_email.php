<?php
require "cache.php";

$response = array('exists' => false);

if (isset($_POST['email'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    $check_email_query = "SELECT * FROM membres WHERE email = '$email'";
    $result_email_check = mysqli_query($conn, $check_email_query);

    if (mysqli_num_rows($result_email_check) > 0) {
        $response['exists'] = true;
    }
}

echo json_encode($response);
