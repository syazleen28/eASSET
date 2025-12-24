<?php
function sendActivationMail($to, $link) {

    $subject = "eASSETS Account Activation";
    $message = "
        <h3>Account Activation</h3>
        <p>Please click the link below to activate your account:</p>
        <p><a href='$link'>$link</a></p>
    ";

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";

    mail($to, $subject, $message, $headers);
}
