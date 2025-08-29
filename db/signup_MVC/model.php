<?php
// handles queries / interacts with the database

declare(strict_types=1);

function get_username(object $con, string $username){
    $query = "SELECT username FROM users WHERE username = ?;";
    $stmt = $con->prepare($query);
    $stmt->bind_Param('s', $username);
    $stmt->execute();

    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function get_email(object $con, string $email){
    $query = "SELECT email FROM users WHERE email = ?;";
    $stmt = $con->prepare($query);
    $stmt->bind_Param('s', $email);
    $stmt->execute();

    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function get_contact(object $con, string $contact){
    $query = "SELECT phone_number FROM users WHERE phone_number = ?;";
    $stmt = $con->prepare($query);
    $stmt->bind_Param('s', $contact);
    $stmt->execute();

    $result = $stmt->get_result();
    return $result->fetch_assoc();
}