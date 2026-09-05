<?php
/**
 * WORDORA Admin — Careers & Editorial Guild Studio
 * 
 * Output buffering ensures redirect() can send clean HTTP 302 headers
 * even after header.php has begun rendering HTML.
 */
ob_start();

define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/core/helpers.php';

$adminTitle = 'Careers & Editorial Guild Studio';

include ROOT_PATH . '/admin/includes/header.php';
include ROOT_PATH . '/admin/includes/careers-sections-editor.php';
include ROOT_PATH . '/admin/includes/footer.php';

ob_end_flush();
