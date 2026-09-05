<?php
/**
 * WORDORA Admin — Case Studies Visual Section Studio
 * 
 * Output buffering is started here so that redirect() in the editor
 * can always send clean HTTP 302 headers, even after header.php has
 * begun rendering HTML. The buffer is implicitly flushed at script end
 * or explicitly cleared by redirect() via ob_end_clean().
 */
ob_start();

define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/core/helpers.php';
require_once ROOT_PATH . '/models/Hero.php';
require_once ROOT_PATH . '/models/CaseStudy.php';

$adminTitle = 'Case Studies Visual Section Studio';
include ROOT_PATH . '/admin/includes/header.php';
include ROOT_PATH . '/admin/includes/case-studies-sections-editor.php';
include ROOT_PATH . '/admin/includes/footer.php';

ob_end_flush();
