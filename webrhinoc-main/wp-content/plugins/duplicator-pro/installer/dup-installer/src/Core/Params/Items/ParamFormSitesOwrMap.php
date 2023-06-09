<?php

/**
 * @package Duplicator\Installer
 */

namespace Duplicator\Installer\Core\Params\Items;

use Duplicator\Installer\Core\Params\Models\SiteOwrMap;
use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Installer\Utils\Log\Log;
use Duplicator\Libs\Snap\SnapIO;
use Duplicator\Libs\Snap\SnapURL;
use Duplicator\Libs\Snap\SnapUtil;
use DUPX_ArchiveConfig;
use DUPX_InstallerState;
use DUPX_U;
use DUPX_U_Html;
use Exception;

/**
 * Item for overwrite mapping
 */
class ParamFormSitesOwrMap extends ParamForm
{
    const SOFT_LIMIT_NUM_IMPORT = 10;
    const HARD_LIMIT_NUM_IMPORT = 20;

    const TYPE_ARRAY_SITES_OWR_MAP = 'arrayowrmap';
    const FORM_TYPE_SITES_OWR_MAP  = 'sitesowrmap';

    const NAME_POSTFIX_SOURCE_ID = '_source_id';
    const NAME_POSTFIX_TARGET_ID = '_target_id';
    const NAME_POSTFIX_NEW_SLUG  = '_new_slug';

    const STRING_ADD_NEW_SUBSITE = "Add as New Subsite in Network";

    /**
     * Class constructor
     *
     * @param string $name     param identifier
     * @param string $type     TYPE_STRING | TYPE_ARRAY_STRING | ...
     * @param string $formType FORM_TYPE_HIDDEN | FORM_TYPE_TEXT | ...
     * @param array  $attr     list of attributes
     * @param array  $formAttr list of form attributes
     */
    public function __construct($name, $type, $formType, $attr = null, $formAttr = array())
    {
        if ($type != self::TYPE_ARRAY_SITES_OWR_MAP) {
            throw new \Exception('the type must be ' . self::TYPE_ARRAY_SITES_OWR_MAP);
        }

        if ($formType != self::FORM_TYPE_SITES_OWR_MAP) {
            throw new \Exception('the form type must be ' . self::FORM_TYPE_SITES_OWR_MAP);
        }

        parent::__construct($name, $type, $formType, $attr, $formAttr);
    }

    /**
     * Render HTML
     *
     * @return void
     */
    protected function htmlItem()
    {
        if ($this->formType == self::FORM_TYPE_SITES_OWR_MAP) {
            $this->sitesOrwHtml();
        } else {
            parent::htmlItem();
        }
    }

    /**
     * Render subsite owr mapping
     *
     * @return void
     */
    protected function sitesOrwHtml()
    {
        $extraData         = self::getListExtraData();
        $haveMultipleItems = $extraData['sourceInfo']['numSites'] > 1;

        $hardLimit = false;
        $softLimit = false;

        if (count($this->value) >= self::HARD_LIMIT_NUM_IMPORT) {
            $hardLimit = true;
        } elseif (count($this->value) >= self::SOFT_LIMIT_NUM_IMPORT) {
            $softLimit = true;
        }

        $addDisabled = (count($this->value) >= $extraData['sourceInfo']['numSites'] || $hardLimit);

        ?>
        <div 
            class="overwrite_sites_list <?php echo ($haveMultipleItems ? '' : 'no-multiple'); ?>"
            data-list-info="<?php echo DUPX_U::esc_attr(json_encode(self::getListExtraData())); ?>"
        >
            <div class="overwrite_site_item title">
                <div class="col">
                    Source Site
                </div>
                <div class="col">
                    Target Site
                </div>
                <div class="col del">
                    <span class="hidden" >
                        <i class="fa fa-minus-square"></i>
                    </span>
                </div>
            </div>
            <?php
            if (empty($this->value)) {
                $defaultItem = new SiteOwrMap(self::getDefaultSourceId(), 0, '');
                $this->itemOwrHtml($defaultItem, 0);
            } else {
                foreach ($this->value as $index => $siteMap) {
                    $this->itemOwrHtml($siteMap, $index);
                }
            }
            ?>
            <div class="overwrite_site_item add_item">
                <div class="full">
                    <button 
                        type="button" 
                        class="secondary-btn float-right add_button" 
                        data-new-item="<?php echo DUPX_U::esc_attr($this->itemOwrHtml(null, 0, false)); ?>"
                        <?php echo ($addDisabled ? 'disabled' : ''); ?>
                    >
                        Add Site to Import
                    </button>
                </div>
                <p class="overwrite_site_soft_limit_msg maroon <?php echo ($softLimit ? '' : 'no-display');?>" >
                    <i class="fas fa-exclamation-triangle"></i> It is possible to import a larger number of sites simultaneously, 
                    but multiple installations are recommended to prevent stability errors.
                </p>
                <p class="overwrite_site_hard_limit_msg maroon <?php echo ($hardLimit ? '' : 'no-display');?>" >
                    <i class="fas fa-exclamation-triangle"></i> 
                    Maximum number of sites that can be imported in a single installation reached. (<?php echo self::HARD_LIMIT_NUM_IMPORT; ?>) 
                    If you wish to import several sites, carry out separate installations.
                </p>
            </div>
        </div>
        <?php
    }

    /**
     * Render item html
     *
     * @param SiteOwrMap|null $map   map item
     * @param int             $index current item inder
     * @param bool            $echo  if false return HTML
     *
     * @return void
     */
    protected function itemOwrHtml(SiteOwrMap $map = null, $index = 0, $echo = true)
    {
        ob_start();
        $selectSourceAttrs = array(
            'name'  => $this->getName() . self::NAME_POSTFIX_SOURCE_ID . '[]',
            'class' => 'source_id js-select ' . $this->getFormItemId() . self::NAME_POSTFIX_SOURCE_ID
        );

        $selectTargetAttrs = array(
            'name'  => $this->getName() . self::NAME_POSTFIX_TARGET_ID . '[]',
            'class' => 'target_id js-select ' . $this->getFormItemId() . self::NAME_POSTFIX_TARGET_ID
        );

        $newSlugAttrs = array(
            'name'  => $this->getName() . self::NAME_POSTFIX_NEW_SLUG . '[]',
            'class' => 'new_slug ' . $this->getFormItemId() . self::NAME_POSTFIX_NEW_SLUG
        );

        $extraData = self::getListExtraData();

        if (is_null($map)) {
            $selectSourceAttrs['disabled'] = true;
            $selectedSource                = false;
            $noteSourceSlug                = '';
            $selectTargetAttrs['disabled'] = true;
            $selectedTarget                = 0;
            $noteTargetSlug                = '_____';
            $newSlugAttrs['disabled']      = true;
            $newSlugAttrs['value']         = '';
        } else {
            $selectedSource        = $map->getSourceId();
            $selectedSourceInfo    = $extraData['sourceInfo']['sites']['id_' . $selectedSource];
            $noteSourceSlug        = $selectedSourceInfo['slug'];
            $selectedTarget        = $map->getTargetId();
            $selectedTargetInfo    = $extraData['targetInfo']['sites']['id_' . $selectedTarget];
            $noteTargetSlug        = (strlen($selectedTargetInfo['slug']) == 0 ? '_____' : $selectedTargetInfo['slug']);
            $newSlugAttrs['value'] = $map->getNewSlug();
        }

        $sourceIdsOptions = self::getSourceIdsOptions();
        /*if (count($sourceIdsOptions) <= 1) {
            $selectSourceAttrs['disabled'] = true;
        }*/
        ?>
        <div class="overwrite_site_item">
            <div class="col">
                <select <?php echo DUPX_U_Html::arrayAttrToHtml($selectSourceAttrs); ?> >
                    <?php self::renderSelectOptions($sourceIdsOptions, $selectedSource); ?>
                </select>
                <div class="sub-note source-site-note" >
                    <span class="site-prefix-slug"><?php echo DUpx_u::esc_html($extraData['sourceInfo']['urlPrefix']); ?></span
                    ><span class="site-slug"><?php echo DUpx_u::esc_html($noteSourceSlug); ?></span
                    ><span class="site-postfix-slug"><?php echo DUpx_u::esc_html($extraData['sourceInfo']['urlPostfix']); ?></span>
                </div>
            </div>
            <div class="col">
                <div class="target_select_wrapper" >
                    <select <?php echo DUPX_U_Html::arrayAttrToHtml($selectTargetAttrs); ?> >
                        <?php self::renderSelectOptions(self::getTargetIdsOptions(), $selectedTarget); ?>
                    </select>
                    <div class="new-slug-wrapper">
                        <input 
                            type="text" <?php echo DUPX_U_Html::arrayAttrToHtml($newSlugAttrs); ?> 
                            placeholder="Insert the new site slug"
                        >
                    </div>
                </div>
                <div class="sub-note target-site-note" >
                    <span class="site-prefix-slug"><?php echo DUpx_u::esc_html($extraData['targetInfo']['urlPrefix']); ?></span
                    ><span class="site-slug"><?php echo DUpx_u::esc_html($noteTargetSlug); ?></span
                    ><span class="site-postfix-slug"><?php echo DUpx_u::esc_html($extraData['targetInfo']['urlPostfix']); ?></span>
                </div>
            </div>
            <div class="col del">
                <span class="del_item disabled" title="Remove this site">
                    <i class="fa fa-minus-square"></i>
                </span>
            </div>
        </div>
        <?php
        if ($echo) {
            ob_end_flush();
            return '';
        } else {
            return ob_get_clean();
        }
    }

    /**
     * Get default type attributes
     *
     * @param string $type param type
     *
     * @return array
     */
    protected static function getDefaultAttrForType($type)
    {
        $attrs = parent::getDefaultAttrForType($type);
        if ($type == self::TYPE_ARRAY_SITES_OWR_MAP) {
            $attrs['default'] = array();
        }
        return $attrs;
    }

    /**
     * Apply filter to value input
     *
     * @param array $superObject query string values
     *
     * @return array
     */
    public function getValueFilter($superObject)
    {
        if (($items = json_decode($superObject[$this->getName()], true)) == false) {
            throw new \Exception('Invalid json string');
        }
        return $items;
    }

    /**
     * Return sanitized value
     *
     * @param mixed $value value input
     *
     * @return SiteOwrMap[]
     */
    public function getSanitizeValue($value)
    {
        if (!is_array($value)) {
            return array();
        }

        for ($i = 0; $i < count($value); $i++) {
            $newSlug = (isset($value[$i]['newSlug']) ? SnapUtil::sanitizeNSCharsNewlineTrim($value[$i]['newSlug']) : '');
            $newSlug = preg_replace('/[\s"\'\\\\\/&?#,\.:;]+/m', '', $newSlug);
            $value[$i] = array(
                'sourceId' => (isset($value[$i]['sourceId']) ? (int) $value[$i]['sourceId'] : -1),
                'targetId' => (isset($value[$i]['sourceId']) ? (int) $value[$i]['targetId'] : -1),
                'newSlug'  => $newSlug
            );
        }

        return $value;
    }

    /**
     * Check if value is valid
     *
     * @param mixed $value         value
     * @param mixed $validateValue variable passed by reference. Updated to validated value in the case, the value is a valid value.
     *
     * @return bool true if is a valid value for this object
     */
    public function isValid($value, &$validateValue = null)
    {
        $validateValue = array();

        try {
            foreach ($value as $item) {
                if ($item instanceof SiteOwrMap) {
                    $validateValue[] = $item;
                    continue;
                }

                $validateValue[] = new SiteOwrMap(
                    $item['sourceId'],
                    $item['targetId'],
                    $item['newSlug']
                );
            }

            if (($result = $this->callValidateCallback($validateValue)) === false) {
                $validateValue = null;
            }
        } catch (Exception $e) {
            Log::info('Validation error message: ' . $e->getMessage());
            return false;
        }

        return $result;
    }

    /**
     * Set value from array. This function is used to set data from json array
     *
     * @param array $data form data
     *
     * @return boolean
     */
    public function fromArrayData($data)
    {
        $result = parent::fromArrayData($data);
        return $result;
    }

    /**
     * return array dato to store in json array data
     *
     * @return array
     */
    public function toArrayData()
    {
        $result          = parent::toArrayData();
        $result['value'] = array();
        foreach ($this->value as $obj) {
            $result['value'][] = $obj->jsonSerialize();
        }
        return $result;
    }

    /**
     * Get subsite slug by subsitedata
     *
     * @param object|array $subsite     subsite info
     * @param string       $mainUrl     main site url
     * @param bool         $isSubdomain if true is subdomain
     *
     * @return string
     */
    public static function getSubsiteSlug($subsite, $mainUrl, $isSubdomain)
    {
        $subsite = (object) $subsite;
        if ($isSubdomain) {
            $mainDomain = SnapURL::wwwRemove(SnapURL::parseUrl($mainUrl, PHP_URL_HOST));
            $subDomain  = SnapURL::wwwRemove($subsite->domain);

            if ($subDomain == $mainDomain) {
                return '/';
            } elseif (strpos($subDomain, '.' . $mainDomain) !== false) {
                return substr($subDomain, 0, strpos($subDomain, '.' . $mainDomain));
            } else {
                return $subDomain;
            }
        } else {
            $maiPath = SnapIO::trailingslashit((string) SnapURL::parseUrl($mainUrl, PHP_URL_PATH));
            $subsitePath = SnapIO::trailingslashit($subsite->path);

            if ($maiPath == $subsitePath) {
                return '/';
            } else {
                return trim(SnapIO::getRelativePath($subsitePath, $maiPath));
            }
        }
    }

    /**
     * Get subsites list in packages
     *
     * @return \ParamOption[]
     */
    public static function getSourceIdsOptions()
    {
        static $sourceOpt = null;

        if (is_null($sourceOpt)) {
            $archiveConfig = DUPX_ArchiveConfig::getInstance();
            $sourceOpt     = array();
            $isSubdomain   = ($archiveConfig->mu_mode == 1);
            $mainSiteUrl   = $archiveConfig->getRealValue('siteUrl');
            $groupUrl      = ($isSubdomain ? SnapURL::removeScheme($mainSiteUrl, true) : SnapIO::trailingslashit($mainSiteUrl));

            foreach ($archiveConfig->subsites as $subsite) {
                $optStatus = (
                        !DUPX_InstallerState::isImportFromBackendMode() ||
                        (
                            count($subsite->filteredTables) === 0 &&
                            count($subsite->filteredPaths) === 0
                        )
                    ) ?
                    ParamOption::OPT_ENABLED :
                    ParamOption::OPT_DISABLED;
                $option    = new ParamOption(
                    $subsite->id,
                    self::getSubsiteSlug($subsite, $mainSiteUrl, $isSubdomain),
                    $optStatus
                );
                $option->setOptGroup($groupUrl);

                $sourceOpt[] = $option;
            }
        }
        return $sourceOpt;
    }

    /**
     * Get default source id
     *
     * @return bool|int
     */
    protected static function getDefaultSourceId()
    {
        if (empty(DUPX_ArchiveConfig::getInstance()->subsites)) {
            return false;
        }
        return DUPX_ArchiveConfig::getInstance()->subsites[0]->id;
    }

    /**
     *
     * @return int[]
     */
    protected static function getSubSiteIdsAcceptValues()
    {
        $archiveConfig = DUPX_ArchiveConfig::getInstance();
        $acceptValues  = array(-1);
        foreach ($archiveConfig->subsites as $subsite) {
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

    /**
     * Get existing subsites list on import site
     *
     * @return \ParamOption[]
     */
    public static function getTargetIdsOptions()
    {
        static $targetOpt = null;

        if (is_null($targetOpt)) {
            $overwriteData = PrmMng::getInstance()->getValue(PrmMng::PARAM_OVERWRITE_SITE_DATA);
            $isSubdomain   = $overwriteData['subdomain'];
            $mainSiteUrl   = $overwriteData['urls']['home'];
            $groupUrl      = ($isSubdomain ? SnapURL::removeScheme($mainSiteUrl, true) : SnapIO::trailingslashit($mainSiteUrl));
            $targetOpt     = array();

            if (!is_array($overwriteData) || !isset($overwriteData['subsites'])) {
                return $targetOpt;
            }

            $targetOpt[] = new ParamOption(
                0,
                'New Subsite',
                ParamOption::OPT_ENABLED
            );

            foreach ($overwriteData['subsites'] as $subsite) {
                $option = new ParamOption(
                    $subsite['id'],
                    self::getSubsiteSlug($subsite, $mainSiteUrl, $isSubdomain),
                    ParamOption::OPT_ENABLED
                );
                $option->setOptGroup('OVERWRITE ' . $groupUrl);
                $targetOpt[] = $option;
            }
        }

        return $targetOpt;
    }

    /**
     * Get extra fata for data attribute list
     *
     * @return array
     */
    protected static function getListExtraData()
    {
        static $extraData = null;

        if (is_null($extraData)) {
            $archiveConfig = DUPX_ArchiveConfig::getInstance();
            $extraData     = array(
                'softLimit' => self::SOFT_LIMIT_NUM_IMPORT,
                'hardLimit' => self::HARD_LIMIT_NUM_IMPORT
            );

            $isSubdomain             = ($archiveConfig->mu_mode == 1);
            $mainSiteUrl             = $archiveConfig->getRealValue('siteUrl');
            $extraData['sourceInfo'] = array(
                'numSites'   => count(self::getSourceIdsOptions()),
                'urlPrefix'  => self::prefixSlugByURL($mainSiteUrl, $isSubdomain),
                'urlPostfix' => self::postfixSlugByURL($mainSiteUrl, $isSubdomain),
                'sites'      => array()
            );
            foreach ($archiveConfig->subsites as $subsite) {
                $extraData['sourceInfo']['sites']['id_' . $subsite->id] = array(
                    'slug' => self::getSubsiteSlug($subsite, $mainSiteUrl, $isSubdomain)
                );
            }

            $overwriteData           = PrmMng::getInstance()->getValue(PrmMng::PARAM_OVERWRITE_SITE_DATA);
            $isSubdomain             = $overwriteData['subdomain'];
            $mainSiteUrl             = $overwriteData['urls']['home'];
            $extraData['targetInfo'] = array(
                'numSites'   => count(self::getTargetIdsOptions()),
                'urlPrefix' => self::prefixSlugByURL($mainSiteUrl, $isSubdomain),
                'urlPostfix' => self::postfixSlugByURL($mainSiteUrl, $isSubdomain),
                'sites' => array(
                    'id_0' => array(
                        'id'   => 0,
                        'slug' => '_____'
                    )
                )
            );
            foreach ($overwriteData['subsites'] as $subsite) {
                $extraData['targetInfo']['sites']['id_' . $subsite['id']] = array(
                    'slug' => self::getSubsiteSlug($subsite, $mainSiteUrl, $isSubdomain)
                );
            }
        }
        return  $extraData;
    }

    /**
     * Get prefix URL slug
     *
     * @param string $url         URL string
     * @param bool   $isSubdomain if true is subdomain
     *
     * @return string
     */
    protected static function prefixSlugByURL($url, $isSubdomain = false)
    {
        $parseUrl = SnapURL::parseUrl($url);
        if ($isSubdomain) {
            return $parseUrl['scheme'] . '://';
        } else {
            return SnapIO::trailingslashit($url);
        }
    }

    /**
     * Get prefix URL slug
     *
     * @param string $url         URL string
     * @param bool   $isSubdomain if true is subdomain
     *
     * @return string
     */
    protected static function postfixSlugByURL($url, $isSubdomain = false)
    {
        if (!$isSubdomain) {
            return '';
        }
        $parseUrl = SnapURL::parseUrl($url);
        return '.' . SnapURL::wwwRemove($parseUrl['host']);
    }



    /**
     * Return prefix element data
     *
     * @return array
     */
    protected static function getPrefixNewSlug()
    {
        $overwriteData = PrmMng::getInstance()->getValue(PrmMng::PARAM_OVERWRITE_SITE_DATA);
        $urlNew        = $overwriteData['urls']['home'];
        $parseUrl      = SnapURL::parseUrl($urlNew);

        $result = array('type' => 'label');
        if (isset($overwriteData['subdomain']) && $overwriteData['subdomain']) {
            $result['label'] = $parseUrl['scheme'] . '://';
        } else {
            $result['label']          = $urlNew . '/';
            $result['attrs']['title'] = $result['label'];
        }
        return $result;
    }

    /**
     * Return postfix element data
     *
     * @return array
     */
    protected static function getPostfixNewSlug()
    {
        $overwriteData = PrmMng::getInstance()->getValue(PrmMng::PARAM_OVERWRITE_SITE_DATA);
        if (!isset($overwriteData['subdomain']) || !$overwriteData['subdomain']) {
            return array('type' => 'none');
        }
        $urlNew   = $overwriteData['urls']['home'];
        $parseUrl = SnapURL::parseUrl($urlNew);

        $result                   = array(
            'type'  => 'label',
            'label' => '.' . SnapURL::wwwRemove($parseUrl['host'])
        );
        $result['attrs']['title'] = $result['label'];
    }
}
