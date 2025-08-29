<?php

declare(strict_types = 1);

function get_user(object $con, string $username){
    $stmt = $con->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();
    return $result->fetch_assoc();
}