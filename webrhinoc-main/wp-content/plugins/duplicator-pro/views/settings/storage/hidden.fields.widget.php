<?php
defined("ABSPATH") or die("");

wp_nonce_field(DUP_PRO_CTRL_Storage_Setting::NONCE_ACTION);
?>
<input type="hidden" name="action" value="<?php echo self::FORM_ACTION; ?>">
<input type="hidden" name="page"   value="<?php echo DUP_PRO_Constants::$SETTINGS_SUBMENU_SLUG ?>">
<input type="hidden" name="tab" value="<?php echo self::MAIN_TAB; ?>">
<input type="hidden" name="subtab" value="<?php echo self::$currentSubTab; ?>">