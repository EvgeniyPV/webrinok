<?php

defined("ABSPATH") or die("");

DUP_PRO_Handler::init_error_handler();

global $wpdb;

//COMMON HEADER DISPLAY

$current_tab = isset($_REQUEST['tab']) ? sanitize_text_field($_REQUEST['tab']) : 'schedules';

switch ($current_tab) {
    case 'schedules':
        include(DUPLICATOR____PATH . '/views/schedules/schedule.controller.php');
        break;
}
