<?php

/**
 * Multisite params descriptions
 *
 * @category  Duplicator
 * @package   Installer
 * @author    Snapcreek <admin@snapcreek.com>
 * @copyright 2011-2021  Snapcreek LLC
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 */

namespace Duplicator\Installer\Core\Params\Descriptors;

use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Installer\Core\Params\Items\ParamItem;
use Duplicator\Installer\Core\Params\Items\ParamForm;
use Duplicator\Installer\Core\Params\Items\ParamFormSitesOwrMap;
use Duplicator\Installer\Core\Params\Items\ParamOption;
use Duplicator\Installer\Core\Params\Items\ParamFormURLMapping;
use Duplicator\Installer\Core\Params\Models\SiteOwrMap;
use Duplicator\Installer\Utils\Log\Log;
use Duplicator\Libs\Snap\SnapIO;
use Duplicator\Libs\Snap\SnapURL;
use DUPX_InstallerState;

/**
 * class where all parameters are initialized. Used by the param manager
 */
final class ParamDescMultisite implements DescriptorInterface
{

    /**
     * Init params
     *
     * @param ParamItem[]|ParamForm[] $params params list
     *
     * @return void
     */
    public static function init(&$params)
    {
        $archive_config = \DUPX_ArchiveConfig::getInstance();

        $params[PrmMng::PARAM_SUBSITE_ID] = new ParamForm(
            PrmMng::PARAM_SUBSITE_ID,
            ParamForm::TYPE_INT,
            ParamForm::FORM_TYPE_SELECT,
            array(
                'default'      => -1,
                'acceptValues' => array(__CLASS__, 'getSubSiteIdsAcceptValues')
            ),
            array(
                'status' => function (ParamItem $paramObj) {
                    if (
                        DUPX_InstallerState::isInstType(
                            array(
                                DUPX_InstallerState::INSTALL_STANDALONE
                            )
                        )
                    ) {
                        return ParamForm::STATUS_ENABLED;
                    } else {
                        return ParamForm::STATUS_DISABLED;
                    }
                },
                'label'          => 'Subsite:',
                'wrapperClasses' => array('revalidate-on-change'),
                'options'        => array(__CLASS__, 'getSubSiteIdsOptions'),
            )
        );

        $params[PrmMng::PARAM_SUBSITE_OVERWRITE_MAPPING] = new ParamFormSitesOwrMap(
            PrmMng::PARAM_SUBSITE_OVERWRITE_MAPPING,
            ParamFormSitesOwrMap::TYPE_ARRAY_SITES_OWR_MAP,
            ParamFormSitesOwrMap::FORM_TYPE_SITES_OWR_MAP,
            array(
                'default'          => array(),
                'validateCallback' => function ($value, ParamItem $paramObj) {
                    /** @var SiteOwrMap[] $value */

                    if (!DUPX_InstallerState::isAddSiteOnMultisite()) {
                        return true;
                    }

                    $overwriteData  = PrmMng::getInstance()->getValue(PrmMng::PARAM_OVERWRITE_SITE_DATA);
                    $parseUrl       = SnapURL::parseUrl($overwriteData['urls']['home']);
                    $mainSiteDomain = SnapURL::wwwRemove($parseUrl['host']);
                    $mainSitePath   = SnapIO::trailingslashit($parseUrl['path']);
                    $subdomain      = (isset($overwriteData['subdomain']) && $overwriteData['subdomain']);
                    $newSlugs       = array();

                    foreach ($value as $map) {
                        if ($map->getTargetId() > 0) {
                            continue;
                        }

                        if (strlen($map->getNewSlug()) == 0) {
                            $paramObj->setInvalidMessage('New sub site can\'t have new ' . ($subdomain ? 'subdomain' : 'subpath') . ' empty');
                            return false;
                        }

                        $newSlugs[] = ($newSlug = $map->getNewSlug());

                        foreach ($overwriteData['subsites'] as $subsite) {
                            if ($subdomain) {
                                if (strcmp($newSlug . '.' . $mainSiteDomain, $subsite['domain']) === 0) {
                                    $paramObj->setInvalidMessage('New subdomain already exists');
                                    return false;
                                }
                            } else {
                                if (strcmp($mainSitePath . $newSlug, SnapIO::untrailingslashit($subsite['path'])) === 0) {
                                    $paramObj->setInvalidMessage('New subpath already exists');
                                    return false;
                                }
                            }
                        }
                    }

                    if (count($newSlugs) !== count(array_unique($newSlugs))) {
                        $paramObj->setInvalidMessage('Different new sub-sites cannot have the same ' . ($subdomain ? 'subdomain' : 'subpath'));
                        return false;
                    }

                    return true;
                }
            ),
            array(
                'label'          => 'Overwrite mapping',
                'renderLabel'    => false,
                'wrapperClasses' => array('revalidate-on-change')
            )
        );

        $params[PrmMng::PARAM_REPLACE_MODE] = new ParamForm(
            PrmMng::PARAM_REPLACE_MODE,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_RADIO,
            array(
                'default'      => 'legacy',
                'acceptValues' => array(
                    'legacy',
                    'mapping'
                )
            ),
            array(
                'label'   => 'Replace Mode:',
                'options' => array(
                    new ParamOption('legacy', 'Standard', ParamOption::OPT_ENABLED, array('title' => 'Set the files current date time to now')),
                    new ParamOption('mapping', 'Mapping', ParamOption::OPT_ENABLED, array('title' => 'Keep the files date time the same'))
                )
            )
        );

        $params[PrmMng::PARAM_MU_REPLACE] = new ParamFormURLMapping(
            PrmMng::PARAM_MU_REPLACE,
            ParamFormURLMapping::TYPE_ARRAY_STRING,
            ParamFormURLMapping::FORM_TYPE_URL_MAPPING,
            array(
                'default' => $archive_config->getNewUrlsArrayIdVal()
            ),
            array(
                'label'       => 'URLs mapping',
                'renderLabel' => false,
            )
        );

        $params[PrmMng::PARAM_MULTISITE_CROSS_SEARCH] = new ParamForm(
            PrmMng::PARAM_MULTISITE_CROSS_SEARCH,
            ParamForm::TYPE_BOOL,
            ParamForm::FORM_TYPE_CHECKBOX,
            array(
                'default' => (count($archive_config->subsites) <= MAX_SITES_TO_DEFAULT_ENABLE_CORSS_SEARCH)
            ),
            array(
                'status' => function ($paramObj) {
                    if (\DUPX_MU::newSiteIsMultisite()) {
                        return ParamForm::STATUS_ENABLED;
                    } else {
                        return ParamForm::STATUS_SKIP;
                    }
                },
                'label'         => 'Database search:',
                'checkboxLabel' => 'Cross-search between the sites of the network.'
            )
        );
    }

    /**
     * Update params after overwrite logic
     *
     * @param ParamItem[]|ParamForm[] $params params list
     *
     * @return void
     */
    public static function updateParamsAfterOverwrite($params)
    {
    }

    /**
     * Get overwrite map by source id
     *
     * @param int $sourceId subsite source id
     *
     * @return SiteOwrMap|bool false if don't exists
     */
    public static function getOwrMapBySourceId($sourceId)
    {
        static $indexCache = array();

        if (!isset($indexCache[$sourceId])) {
            /** @var SiteOwrMap[] $overwriteMapping */
            $overwriteMapping = PrmMng::getInstance()->getValue(PrmMng::PARAM_SUBSITE_OVERWRITE_MAPPING);

            foreach ($overwriteMapping as $map) {
                if ($map->getSourceId() == $sourceId) {
                    $indexCache[$sourceId] = $map;
                    break;
                }
            }
            if (!isset($indexCache[$sourceId])) {
                $indexCache[$sourceId] = false;
            }
        }

        return $indexCache[$sourceId];
    }

    /**
     * Return option
     * @return \ParamOption[]
     */
    public static function getSubSiteIdsOptions()
    {
        $archive_config = \DUPX_ArchiveConfig::getInstance();
        $options        = array();
        foreach ($archive_config->subsites as $subsite) {
            $optStatus = (
                    !DUPX_InstallerState::isImportFromBackendMode() ||
                    (
                        count($subsite->filteredTables) === 0 &&
                        count($subsite->filteredPaths) === 0
                    )
                ) ?
                ParamOption::OPT_ENABLED :
                ParamOption::OPT_DISABLED;
            $label     = $subsite->domain . $subsite->path;
            $options[] = new ParamOption($subsite->id, $label, $optStatus);
        }
        return $options;
    }

    /**
     *
     * @return int[]
     */
    public static function getSubSiteIdsAcceptValues()
    {
        $archive_config = \DUPX_ArchiveConfig::getInstance();
        $acceptValues   = array(-1);
        foreach ($archive_config->subsites as $subsite) {
            if (
                !DUPX_InstallerState::isImportFromBackendMode() ||
                (
                    count($subsite->filteredTables) === 0 &&
                    count($subsite->filteredPaths) === 0
                )
            ) {
                $acceptValues[] = $subsite->id;
            }
        }
        return $acceptValues;
    }
}
