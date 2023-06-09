<?php

defined("ABSPATH") or die("");

/**
 * Used to generate a thick box inline dialog such as an alert or confirm pop-up
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2
 *
 * @package DUP_PRO
 * @subpackage classes/ui
 * @copyright (c) 2017, Snapcreek LLC
 * @license https://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since 3.3.0
 *
 */
class DUP_PRO_UI_Dialog
{
    /**
     *
     * @var int
     */
    protected static $uniqueIdCounter = 0;

    /**
     * if not empty is class of box
     *
     * @var string
     */
    public $boxClass = '';

    /**
     * if false don't disaply ok,confirm and cancel buttons
     *
     * @var bool
     */
    public $showButtons = true;

    /**
     * if not empty set class on wrapper buttons div
     *
     * @var string
     */
    public $wrapperClassButtons = null;

    /**
     * The title that shows up in the dialog
     */
    public $title;

    /**
     * The message displayed in the body of the dialog
     */
    public $message;

    /**
     * The width of the dialog the default is used if not set
     * Alert = 475px (default) |  Confirm = 500px (default)
     */
    public $width;

    /**
     * The height of the dialog the default is used if not set
     * Alert = 125px (default) |  Confirm = 150px (default)
     */
    public $height;

    /**
     * When the progress meter is running show this text
     * Available only on confirm dialogs
     */
    public $progressText;

    /**
     * When true a progress meter will run until page is reloaded
     * Available only on confirm dialogs
     */
    public $progressOn = true;

    /**
     * The javascript call back method to call when the 'Yes' or 'Ok' button is clicked
     */
    public $jsCallback = null;

    /**
     *
     * @var string
     */
    public $okText     = '';

    /**
     *
     * @var string
     */
    public $cancelText = '';

    /**
     * If true close dialog on confirm
     * @var bool
     */
    public $closeOnConfirm = false;

    /**
     * The id given to the full dialog
     */
    private $id;

    /**
     * A unique id that is added to all id elements
     */
    private $uniqid;

    /**
     *  Init this object when created
     */
    public function __construct()
    {
        add_thickbox();
        $this->progressText = DUP_PRO_U::__('Processing please wait...');
        $this->uniqid       = ++self::$uniqueIdCounter;
        $this->id           = 'dpro-dlg-' . $this->uniqid;
        $this->okText       = DUP_PRO_U::__('OK');
        $this->cancelText   = DUP_PRO_U::__('Cancel');
    }

    /**
     *
     * @return int
     */
    public function getUniqueIdCounter()
    {
        return $this->uniqid;
    }

    /**
     * Gets the unique id that is assigned to each instance of a dialog
     *
     * @return int      The unique ID of this dialog
     */
    public function getID()
    {
        return $this->id;
    }

    /**
     * Gets the unique id that is assigned to each instance of a dialogs message text
     *
     * @return int      The unique ID of the message
     */
    public function getMessageID()
    {
        return "{$this->id}_message";
    }

    /**
     * Initialize the alert base html code used to display when needed
     *
     * @return string   The html content used for the alert dialog
     */
    public function initAlert()
    {
        ?>
        <div id="<?php echo $this->id; ?>" style="display:none;" >
            <div id="<?php echo $this->id; ?>-alert-txt" class="dpro-dlg-alert-txt <?php echo $this->boxClass; ?>" >
                <span id="<?php echo $this->id; ?>_message">
                    <?php echo $this->message; ?>
                </span>
            </div>
            <?php if ($this->showButtons) { ?>
                <div class="dpro-dlg-alert-btns <?php echo $this->wrapperClassButtons; ?>" >
                    <input 
                        id="<?php echo $this->id; ?>-confirm" 
                        type="button" 
                        class="button button-large" 
                        value="<?php echo esc_attr($this->okText); ?>" 
                        onclick="<?php echo esc_attr($this->closeAlert(false)); ?>" 
                    >
                </div>
            <?php } ?>
        </div>
        <?php
    }

    /**
     * Shows the alert base JS code used to display when needed
     *
     * @return string   The JS content used for the alert dialog
     */
    public function showAlert()
    {
        $this->width  = is_numeric($this->width) ? $this->width : 500;
        $this->height = is_numeric($this->height) ? $this->height : 175;

        $title = esc_js($this->title);
        $id    = esc_js($this->id);
        $html = "tb_show('" . $title . "', '#TB_inline?width=" . $this->width . "&height=" . $this->height . "&inlineId=" . $id  . "');" .
            "var styleData = jQuery('#TB_window').attr('style') + 'height: " . esc_js($this->height) . "px !important';\n" .
            "jQuery('#TB_window').attr('style', styleData);" .
            "DuplicatorTooltip.reload();";

        echo $html;
    }


    /**
     * Close tick box
     *
     * @param boolean $echo if true echo javascript else return string
     * @return string
     */
    public function closeAlert($echo = true)
    {
        $onClickClose = '';
        if (!is_null($this->jsCallback)) {
            $onClickClose .= $this->jsCallback . ';';
        }
        $onClickClose .= 'tb_remove();';
        if ($echo) {
            echo $onClickClose;
            return '';
        } else {
            return $onClickClose;
        }
    }

    /**
     * js code to update html message content from js var name
     *
     * @param string $jsVarName
     */
    public function updateMessage($jsVarName)
    {
        $js = '$("#' . $this->getID() . '_message").html(' . $jsVarName . ');';
        echo $js;
    }

    /**
     * Shows the confirm base JS code used to display when needed
     *
     * @return string   The JS content used for the confirm dialog
     */
    public function initConfirm()
    {
        $progress_data  = '';
        $progress_func2 = '';

        $onClickConfirm = '';
        if (!is_null($this->jsCallback)) {
            $onClickConfirm .= $this->jsCallback . ';';
        }

        //Enable the progress spinner
        if ($this->progressOn) {
            $progress_func1 = "__dpro_dialog_" . $this->uniqid;
            $progress_func2 = ";{$progress_func1}(this)";
            $progress_data  = <<<HTML
				<div class='dpro-dlg-confirm-progress' id="{$this->id}-progress">
                    <br/><br/>
                    <i class='fa fa-circle-notch fa-spin fa-lg fa-fw'></i> {$this->progressText}</div>
				<script> 
					function {$progress_func1}(obj) 
					{
                        (function($,obj){
                            console.log($('#{$this->id}'));
                            // Set object for reuse
                            var e = $(obj);
                            // Check and set progress
                            if($('#{$this->id}-progress'))  $('#{$this->id}-progress').show();
                            // Check and set confirm button
                            if($('#{$this->id}-confirm'))   $('#{$this->id}-confirm').attr('disabled', 'true');
                            // Check and set cancel button
                            if($('#{$this->id}-cancel'))    $('#{$this->id}-cancel').attr('disabled', 'true');
                        }(window.jQuery, obj));
					}
				</script>
HTML;
            $onClickConfirm .= $progress_func2 . ';';
        }

        if ($this->closeOnConfirm) {
            $onClickConfirm .= 'tb_remove();';
        } ?>
        <div id="<?php echo $this->id; ?>" style="display:none">
            <div class="dpro-dlg-confirm-txt" id="<?php echo $this->id; ?>-confirm-txt">
                <div id="<?php echo $this->id; ?>_message">
                    <?php echo $this->message; ?>
                </div>
                <?php echo $progress_data; ?>
            </div>
            <?php if ($this->showButtons) { ?>
                <div class="dpro-dlg-confirm-btns <?php echo $this->wrapperClassButtons; ?>" >
                    <input 
                        id="<?php echo $this->id; ?>-confirm" 
                        type="button" 
                        class="button button-large" 
                        value="<?php echo esc_attr($this->okText); ?>" 
                        onclick="<?php echo esc_attr($onClickConfirm); ?>" 
                    >
                    <input 
                        id="<?php echo $this->id; ?>-cancel" 
                        type="button" 
                        class="button button-large" 
                        value="<?php echo esc_attr($this->cancelText); ?>" 
                        onclick="tb_remove();" 
                    >
                </div>
            <?php  } ?>
        </div>
        <?php
    }

    /**
     * Shows the confirm base JS code used to display when needed
     *
     * @return string   The JS content used for the confirm dialog
     */
    public function showConfirm()
    {
        $this->width  = is_numeric($this->width) ? $this->width : 500;
        $this->height = is_numeric($this->height) ? $this->height : 225;
        $html         = "tb_show('" . esc_js($this->title) . "', '#TB_inline?width=" . esc_js($this->width) . "&height=" . esc_js($this->height) . "&inlineId=" . esc_js($this->id) . "');\n" .
            "var styleData = jQuery('#TB_window').attr('style') + 'height: " . esc_js($this->height) . "px !important';\n" .
            "jQuery('#TB_window').attr('style', styleData); DuplicatorTooltip.reload();";

        echo $html;
    }
}
