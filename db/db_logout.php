<?php
require_once __DIR__ . '/app.php';
require_post();
destroy_login();
redirect('login.php');
