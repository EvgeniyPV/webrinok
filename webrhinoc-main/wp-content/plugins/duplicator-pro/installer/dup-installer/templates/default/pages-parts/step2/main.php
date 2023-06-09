<?php

/**
 *
 * @package templates/default
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;

$paramsManager = PrmMng::getInstance();
?>

<form 
    id='s2-input-form' 
    method="post" 
    class="content-form"  
    autocomplete="off" 
    data-parsley-validate="true" 
    data-parsley-excluded="input[type=hidden], [disabled], :hidden"
>
    <div class="main-form-content" >
        <div class="hdr-sub1" >
            Options
        </div>
        <!-- START TABS -->
        <div class="hdr-sub1-area tabs-area">
            <div id="tabs" class="no-display">
                <ul>
                    <li><a href="#tabs-database-general">General</a></li>
                    <li><a href="#tabs-database-tables">Tables</a></li>
                </ul>
                <div id="tabs-database-general">
                    <?php dupxTplRender('pages-parts/step2/options-tabs/general'); ?>
                </div>
                <div id="tabs-database-tables">
                    <?php dupxTplRender('pages-parts/step2/options-tabs/tables'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-buttons margin-top-2">
        <div class="content-left">
        </div>
        <div class="content-right" >
            <button id="s2-next-btn-basic" type="button" onclick="DUPX.runDeployment()" class="default-btn">
                Next <i class="fa fa-caret-right"></i>
            </button>
        </div>
    </div>
</form>
