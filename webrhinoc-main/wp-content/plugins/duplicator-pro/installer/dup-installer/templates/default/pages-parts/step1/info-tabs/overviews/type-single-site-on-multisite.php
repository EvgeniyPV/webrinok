<?php

/**
 *
 * @package templates/default
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

if (DUPX_InstallerState::instTypeAvaiable(DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBDOMAIN)) {
    $instTypeClass = 'install-type-' . DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBDOMAIN;
    $title         = 'Install package single site in subdomain multisite';
} elseif (DUPX_InstallerState::instTypeAvaiable(DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBFOLDER)) {
    $instTypeClass = 'install-type-' . DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBFOLDER;
    $title         = 'Install package single site in subfolder multisite';
} else {
    return;
}

$display = DUPX_InstallerState::getInstance()->isInstType(
    array(
        DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBDOMAIN,
        DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBFOLDER
    )
);
?>
<div class="overview-description <?php echo $instTypeClass . ($display ? '' : ' no-display'); ?>">
    <h2><?php echo $title; ?></h2>
    <p>
        This installation will insert the package site into the current multisite installation.
    </p>
</div>