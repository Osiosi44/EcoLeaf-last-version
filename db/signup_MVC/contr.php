<?php
// handeles inputs form the user

declare(strict_types = 1);


function is_input_empty(...$inputs){
    foreach($inputs as $input){
        if(empty($input)){
            return true;
        }
    }
    return false;
}

function is_email_invalid(string $email){
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        return true;
    }else{
        return false;
    }
}

function is_username_taken(object $con, string $username){
    if(get_username($con, $username)){
        return true;
    }else{
        return false;
    }
}

function is_email_registered(object $con, string $email){
    if(get_email($con, $email)){
        return true;
    }else{
        return false;
    }
}

function is_contact_registered(object $con, string $contact){
    if(get_contact($con, $contact)){
        return true;
    }else{
        return false;
    }
}