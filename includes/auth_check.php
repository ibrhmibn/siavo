<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}
?>