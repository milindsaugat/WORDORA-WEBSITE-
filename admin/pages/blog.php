<?php
ob_start();
define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/core/helpers.php';
require_once ROOT_PATH . '/models/Hero.php';
require_once ROOT_PATH . '/models/Post.php';
require_once ROOT_PATH . '/models/Category.php';

$adminTitle = 'Blog Visual Section Studio';
include ROOT_PATH . '/admin/includes/header.php';
include ROOT_PATH . '/admin/includes/blog-sections-editor.php';
include ROOT_PATH . '/admin/includes/footer.php';

ob_end_flush();
