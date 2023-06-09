<?php
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/* passed values */
/* @var $recoverPackage DUP_PRO_Package_Recover */
/* @var $recoverPackageId int */
/* @var $recoveablePackages array */
/* @var $selector bool */
/* @var $subtitle string */
/* @var $displayCopyLink bool */
/* @var $displayCopyButton bool */
/* @var $displayLaunch bool */
/* @var $displayDownload bool */
/* @var $displayInfo bool */
/* @var $viewMode string */
/* @var $importFailMessage string */

if (!$selector) {
    return;
}
?>
<div class="dup-pro-recovery-point-selector">
    <?php if (empty($recoveablePackages)) { ?>
        <div class="dup-pro-notice-details">
            <div class="margin-bottom-1" >
                <b><?php DUP_PRO_U::_e('Would you like to create a Recovery Point before running this import?'); ?></b>
            </div>
            <b><?php DUP_PRO_U::_e('How to create:'); ?></b>
            <ol class="dup-pro-simple-style-list" >
                <li>
                    <?php DUP_PRO_U::_e('Open the '); ?>
                    <a href="?page=duplicator-pro" target="_blank"><?php DUP_PRO_U::_e('packages screen'); ?></a>
                    <i class="fas fa-external-link-alt fa-small" ></i>
                    <?php DUP_PRO_U::_e('and create a valid recovery package.'); ?>
                </li>
                <li>
                    <?php DUP_PRO_U::_e('On the packages screen click the package\'s Hamburger menu and select "Set Recovery Point".'); ?>
                </li>
                <li>
                    <span class="dup-pro-recovery-windget-refresh link-style"><?php DUP_PRO_U::_e('Refresh'); ?></span>
                    <?php DUP_PRO_U::_e('this page to show and choose the recovery point'); ?>.
                </li>
            </ol>
        </div>
    <?php } else { ?>
        <div class="dup-pro-recovery-point-selector-area-wrapper" >
            <span class="dup-pro-opening-packages-windows" >
                <a href="?page=duplicator-pro" >[<?php DUP_PRO_U::_e('Create New'); ?>]</a>
            </span> 
            <label>
                <i class="fas fa-question-circle fa-sm"
                   data-tooltip-title="<?php DUP_PRO_U::esc_attr_e("Choose Recovery Point Archive"); ?>"
                   data-tooltip="<?php
                   esc_attr_e(
                       'A Recovery Point allows one to quickly restore the site to a prior state. '
                       . 'To use this, mark a package as the Recovery Point, then copy and save off the associated URL. '
                       . 'Then, if a problem occurs, browse to the URL to launch a streamlined installer to quickly restore the site.', 'duplicator-pro'
                   );
                   ?>">
                </i>
                <b><?php DUP_PRO_U::_e('Step 1 '); ?>:</b> <i><?php DUP_PRO_U::_e('Choose Recovery Point Archive'); ?></i>
            </label>
            <div class="dup-pro-recovery-point-selector-area">
                <select class="recovery-select" name="recovery_package" >
                    <option value=""> -- <?php DUP_PRO_U::_e('Not selected'); ?> -- </option>
                    <?php
                    $currentDay = null;
                    foreach ($recoveablePackages as $package) {
                        $packageDay = date("Y/m/d", strtotime($package['created']));
                        if ($packageDay != $currentDay) {
                            if (!is_null($currentDay)) {
                                ?>
                                </optgroup>
                            <?php } ?>
                            <optgroup label="<?php echo esc_attr($packageDay); ?>">
                                <?php
                                $currentDay = $packageDay;
                            }
                            ?>
                            <option value="<?php echo $package['id']; ?>" <?php selected($recoverPackageId, $package['id']) ?>>
                                <?php echo '[' . $package['created'] . '] ' . $package['name']; ?>
                            </option>
                        <?php } ?>
                    </optgroup>
                </select>             
                <button type="button" class="button recovery-reset" ><?php echo DUP_PRO_U::_e('Reset'); ?></button> 
                <button type="button" class="button button-primary recovery-set" ><?php echo DUP_PRO_U::_e('Set'); ?></button>
            </div>
        </div>
    <?php } ?>
</div>
