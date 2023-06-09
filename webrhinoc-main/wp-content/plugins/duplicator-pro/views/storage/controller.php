<?php

defined("ABSPATH") or die("");

DUP_PRO_Handler::init_error_handler();

global $wpdb;

$current_tab = isset($_REQUEST['tab']) ? sanitize_text_field($_REQUEST['tab']) : 'storage';

switch ($current_tab) {
    case 'storage':
        include(DUPLICATOR____PATH . '/views/storage/storage.controller.php');
        break;
}
