<?php
require_once 'config.php';

Auth::logout();
Utils::redirect('login.php');
?>