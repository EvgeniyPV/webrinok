<?php

/**
 * Utility class for setting up Multi-site data
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\MU
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\Descriptors\ParamDescMultisite;
use Duplicator\Installer\Utils\Log\Log;
use Duplicator\Installer\Core\Params\Descriptors\ParamDescUsers;
use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Libs\Snap\JsonSerialize\JsonSerialize;

class DUPX_MU
{

    public static function newSiteIsMultisite()
    {
        return DUPX_InstallerState::isInstType(
            array(
                    DUPX_InstallerState::INSTALL_MULTISITE_SUBDOMAIN,
                    DUPX_InstallerState::INSTALL_MULTISITE_SUBFOLDER,
                    DUPX_InstallerState::INSTALL_RBACKUP_MULTISITE_SUBDOMAIN,
                    DUPX_InstallerState::INSTALL_RBACKUP_MULTISITE_SUBFOLDER,
                    DUPX_InstallerState::INSTALL_RECOVERY_MULTISITE_SUBDOMAIN,
                    DUPX_InstallerState::INSTALL_RECOVERY_MULTISITE_SUBFOLDER
                )
        );
    }

    /**
     *
     * @return array
     */
    public static function getSuperAdminsUserIds($dbh)
    {
        $result        = array();
        $paramsManager = PrmMng::getInstance();
        $archiveConfig = DUPX_ArchiveConfig::getInstance();

        $base_prefix      = $paramsManager->getValue(PrmMng::PARAM_DB_TABLE_PREFIX);
        $users_table_name = "{$base_prefix}users";

        // Super admin should remain
        $siteAdmins = is_array($archiveConfig->mu_siteadmins) ? $archiveConfig->mu_siteadmins : array();
        if (!empty($siteAdmins)) {
            $sql                  = "SELECT ID FROM {$users_table_name} WHERE user_login IN ('" . implode("','", $siteAdmins) . "')";
            $super_admins_results = DUPX_DB::queryToArray($dbh, $sql);
            foreach ($super_admins_results as $super_admins_result) {
                $result[] = $super_admins_result[0];
            }
        }
        return $result;
    }

    public static function overwriteSubsitesInit() {
        $paramsManager = PrmMng::getInstance();
        /** @var SiteOwrMap[] $overwriteMapping */
        $overwriteMapping = PrmMng::getInstance()->getValue(PrmMng::PARAM_SUBSITE_OVERWRITE_MAPPING);
        $sendData = JsonSerialize::serialize($overwriteMapping, JsonSerialize::JSON_SERIALIZE_SKIP_CLASS_NAME);

        $errorMessage = '';
        $numSubsites = count($overwriteMapping);
        if (($subsitesInfo = DUPX_REST::getInstance()->subsiteActions($sendData, $numSubsites, $errorMessage)) == false) {
            Log::info('Creation subisites error, message: ' . $errorMessage);
            throw new Exception('Can\'t create a new sub site message :' . $errorMessage);
        }

        $overwriteData = $paramsManager->getValue(PrmMng::PARAM_OVERWRITE_SITE_DATA);

        foreach ($subsitesInfo as $subsiteInfo) {
            if ($subsiteInfo['targetId'] === 0) {
                $overwriteData['subsites'][] = $subsiteInfo['info'];
                Log::info('NEW SUBSITE CREATED ON ID: ' . $subsiteInfo['info']['id'] . ' URL ' . $subsiteInfo['info']['fullSiteUrl']);

                if (($owrMap = ParamDescMultisite::getOwrMapBySourceId($subsiteInfo['sourceId'])) == false) {
                    throw new Exception('OwrMap object not boud by id :' . $subsiteInfo['sourceId']);
                }
                $owrMap->setTargetId($subsiteInfo['info']['id']);
            }
        }

        $paramsManager->setValue(PrmMng::PARAM_OVERWRITE_SITE_DATA, $overwriteData);
        $paramsManager->setValue(PrmMng::PARAM_SUBSITE_OVERWRITE_MAPPING,  $overwriteMapping);

        DUPX_Ctrl_Params::setParamsOnAddSiteOnMultisite();
        $paramsManager->save();
    }

    /**
     *
     * @param int $subsiteId
     * @param \mysqli $dbh
     */
    public static function updateOptionsTableForStandalone($subsiteId, $dbh)
    {
        $paramsManager = PrmMng::getInstance();
        $archiveConfig = DUPX_ArchiveConfig::getInstance();

        $base_prefix             = $paramsManager->getValue(PrmMng::PARAM_DB_TABLE_PREFIX);
        $retained_subsite_prefix = $archiveConfig->getSubsitePrefixByParam($subsiteId);
        $optionsTable            = DUPX_DB_Functions::getOptionsTableName();

        if ($retained_subsite_prefix != $base_prefix) {
            DUPX_UpdateEngine::updateTablePrefix($dbh, $optionsTable, 'option_name', $retained_subsite_prefix, $base_prefix);
        }

        if ($archiveConfig->mu_generation < 2) {
            $escapedOptionsTable = mysqli_real_escape_string($dbh, $optionsTable);
            $uploadsPath         = $paramsManager->getValue(PrmMng::PARAM_PATH_UPLOADS_NEW); //upload_url_path','uploadPath
            $sql                 = "UPDATE `$escapedOptionsTable` SET `option_value` = '$uploadsPath' WHERE `option_name` = 'uploadPath' AND `option_value` != ''";
            DUPX_DB::queryNoReturn($dbh, $sql);

            $uploadsUrl = $paramsManager->getValue(PrmMng::PARAM_URL_UPLOADS_NEW);
            $sql        = "UPDATE `$escapedOptionsTable` SET `option_value` = '$uploadsUrl' WHERE `option_name` = 'upload_url_path' AND `option_value` != ''";
            DUPX_DB::queryNoReturn($dbh, $sql);
        }
    }

    /**
     *
     * @param int $id
     * @return bool|array
     */
    public static function getSubsiteOverwriteById($id)
    {
        static $indexCache = array();

        if (!isset($indexCache[$id])) {
            $paramsManager = PrmMng::getInstance();
            $overwriteData = $paramsManager->getValue(PrmMng::PARAM_OVERWRITE_SITE_DATA);

            foreach ($overwriteData['subsites'] as $subsite) {
                if ($subsite['id'] == $id) {
                    $indexCache[$id] = $subsite;
                    break;
                }
            }

            if (!isset($indexCache[$id])) {
                $indexCache[$id] = false;
            }
        }

        return $indexCache[$id];
    }

    /**
     * Purge non_site where meta_key in wp_usermeta starts with data from other subsite or root site.
     *
     * @param int             $subsiteId
     * @param resource|mysqli $dbh
     * @return void
     */
    public static function purgeRedundantDataForStandalone($subsiteId, $dbh)
    {
        $paramsManager = PrmMng::getInstance();
        if (ParamDescUsers::getUsersMode() != ParamDescUsers::USER_MODE_OVERWRITE) {
            Log::info("STANDALONE: skip purging redundant data beacause user mode is " . ParamDescUsers::getUsersMode());
            return;
        }

        Log::info("STANDALONE: purging redundant data. Considering ");

        $archiveConfig    = DUPX_ArchiveConfig::getInstance();
        $base_prefix      = $paramsManager->getValue(PrmMng::PARAM_DB_TABLE_PREFIX);
        $remove_redundant = $paramsManager->getValue(PrmMng::PARAM_REMOVE_RENDUNDANT);

        $users_table_name        = DUPX_DB_Functions::getUserTableName();
        $usermeta_table_name     = DUPX_DB_Functions::getUserMetaTableName();
        $retained_subsite_prefix = $archiveConfig->getSubsitePrefixByParam($subsiteId);
        $superAdminUsersIds      = self::getSuperAdminsUserIds($dbh);
        Log::info("SUPER USER IDS: " . Log::v2str($superAdminUsersIds), Log::LV_DETAILED);

        //Remove all users which are not associated with the subsite that is being installed
        if ($remove_redundant) {
            $sql             = "SELECT user_id,meta_key FROM {$usermeta_table_name} WHERE meta_key LIKE '{$base_prefix}%_capabilities' OR meta_key = '{$base_prefix}capabilities'";
            $retain_meta_key = $retained_subsite_prefix . "capabilities";
            $results         = DUPX_DB::queryToArray($dbh, $sql);
            Log::info(print_r($results, true));
            $keep_users      = $superAdminUsersIds;
            foreach ($results as $result) {
                //$result[0] - user_id
                //$result[1] - meta_key
                if ($result[1] == $retain_meta_key) {
                    $keep_users[] = $result[0];
                }
            }
            $keep_users     = array_unique($keep_users);
            $keep_users_str = '(' . implode(',', $keep_users) . ')';

            Log::info("KEEP USERS IDS: " . Log::v2str($keep_users), Log::LV_DETAILED);

            DUPX_DB::chunksDelete($dbh, $users_table_name, "id NOT IN " . $keep_users_str);
            DUPX_DB::chunksDelete($dbh, $usermeta_table_name, "user_id NOT IN " . $keep_users_str);
        }

        // Remove unused metauser key prefix
        $escPergPrefix        = mysqli_real_escape_string($dbh, preg_quote($base_prefix, null /* no delimiter */));
        $escPergSubsitePrefix = mysqli_real_escape_string($dbh, preg_quote($retained_subsite_prefix, null /* no delimiter */));
        if ($retained_subsite_prefix == $base_prefix) {
            Log::info('CLEAN META KEYS ON USER META ' . $base_prefix . '[0-9]+_');
            $where = "meta_key REGEXP '^" . $escPergPrefix . "[0-9]+_'";
        } else {
            Log::info('CLEAN META KEYS ON USER META ' . $base_prefix . ' EXCEPT ' . $retained_subsite_prefix);
            $where = "meta_key NOT REGEXP '^" . $escPergSubsitePrefix . "' AND meta_key REGEXP '^" . $escPergPrefix . "'";
        }
        DUPX_DB::chunksDelete($dbh, $usermeta_table_name, $where);

        if ($retained_subsite_prefix != $base_prefix) {
            DUPX_UpdateEngine::updateTablePrefix($dbh, $usermeta_table_name, 'meta_key', $retained_subsite_prefix, $base_prefix);
        }

        if (!empty($superAdminUsersIds)) {
            $updateables = array(
                $base_prefix . 'capabilities' => mysqli_real_escape_string($dbh, DUPX_WPConfig::ADMIN_SERIALIZED_SECURITY_STRING),
                $base_prefix . 'user_level'   => DUPX_WPConfig::ADMIN_LEVEL
            );

            // Ad permission for superadmin users
            foreach ($superAdminUsersIds as $suId) {
                foreach ($updateables as $meta_key => $meta_value) {
                    if (($result = DUPX_DB::mysqli_query($dbh, "SELECT `umeta_id` FROM {$usermeta_table_name} WHERE `user_id` = {$suId} AND meta_key = '{$meta_key}'")) !== false) {
                        //If entry is present UPDATE otherwise INSERT
                        if ($result->num_rows > 0) {
                            $umeta_id = $result->fetch_object()->umeta_id;
                            if (DUPX_DB::mysqli_query($dbh, "UPDATE {$usermeta_table_name} SET `meta_value` = '{$meta_value}' WHERE `umeta_id` = {$umeta_id}") === false) {
                                Log::info("Could not update meta field {$meta_key} for user with id {$suId}");
                            }
                        } else {
                            if (DUPX_DB::mysqli_query($dbh, "INSERT INTO `{$usermeta_table_name}` (user_id, meta_key, meta_value) VALUES ({$suId}, '{$meta_key}', '$meta_value')") === false) {
                                Log::info("Could not populate meta field {$meta_key} with the value {$meta_value} for user with id {$suId}");
                            }
                        }
                        $result->free();
                    }
                }
            }
        }
    }
}
