<?php
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

// @var $recoverPackage DUP_PRO_Package_Recover

if (isset($recoverPackage) && ($recoverPackage instanceof DUP_PRO_Package_Recover)) {
    $copyLink = $recoverPackage->getInstallLink();
} else {
    $copyLink = '';
}
?>
<span 
    class="dup-pro-recovery-package-small-icon maroon"
    data-dup-copy-value="<?php echo $copyLink; ?>"
    data-dup-copy-title="<?php DUP_PRO_U::_e("Copy Recovery URL to clipboard"); ?>"
    data-dup-copied-title="<?php DUP_PRO_U::_e("Recovery URL copied to clipboard"); ?>">
    <i class="fas fa-undo-alt"></i>
</span>
