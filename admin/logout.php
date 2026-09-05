<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/core/helpers.php';

Auth::logout();
redirect('admin/login.php');
