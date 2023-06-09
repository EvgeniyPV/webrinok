<?php
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

// @var $viewMode string // single | list
// @var $adminMessageViewModeSwtich bool

DUP_PRO_Handler::init_error_handler();

switch ($viewMode) {
    case DUP_PRO_CTRL_import::VIEW_MODE_ADVANCED:
        $viewModeClass = 'view-list-item';
        break;
    case DUP_PRO_CTRL_import::VIEW_MODE_BASIC:
    default:
        $viewModeClass = 'view-single-item';
        break;
}

if ($adminMessageViewModeSwtich) {
    require dirname(__FILE__) . '/import-message-view-mode-switch.php';
}
?> 

<div class="dup-pro-tab-content-wrapper" >
    <div id="dup-pro-import-phase-one" >
        <?php require dirname(__FILE__) . '/import-step1.php'; ?>
    </div>
    <div id="dup-pro-import-phase-two" class="no-display" >
        <?php require dirname(__FILE__) . '/import-step2.php'; ?>
    </div>
</div>
<?php
require_once DUPLICATOR_PRO_PLUGIN_PATH . '/views/tools/recovery/widget/recovery-widget-scripts.php';
require dirname(__FILE__) . '/import-scripts.php';
