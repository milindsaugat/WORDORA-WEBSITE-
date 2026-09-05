<?php
ob_start();
define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/core/helpers.php';

$adminTitle = 'Homepage Visual Section Studio';

include ROOT_PATH . '/admin/includes/header.php';
include ROOT_PATH . '/admin/includes/homepage-sections-editor.php';
include ROOT_PATH . '/admin/includes/footer.php';

ob_end_flush();
