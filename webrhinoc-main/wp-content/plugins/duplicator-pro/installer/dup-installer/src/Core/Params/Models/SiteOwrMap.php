<?php

/**
 * @package Duplicator\Installer
 */

namespace Duplicator\Installer\Core\Params\Models;

use Duplicator\Installer\Core\Params\Items\ParamFormSitesOwrMap;
use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Libs\Snap\JsonSerialize\AbstractJsonSerializable;
use DUPX_ArchiveConfig;
use Exception;

class SiteOwrMap extends AbstractJsonSerializable
{
    /** @var int */
    protected $sourceId = -1;
    /** @var int */
    protected $targetId = -1;
    /** @var string */
    protected $newSlug = '';
    /** @var string */
    protected $blogName = null;

    /**
     * Class constructor
     *
     * @param int    $sourceId source subsite id
     * @param int    $targetId target subsite id
     * @param string $newSlug  new slug on new site
     */
    public function __construct($sourceId, $targetId, $newSlug = '')
    {
        if ($sourceId < 1) {
            throw new Exception('Source id [' . $sourceId . ']invalid ');
        }

        if ($targetId < 0) {
            throw new Exception('Target id [' . $targetId . ']invalid ');
        }

        if (($sourceObj = DUPX_ArchiveConfig::getInstance()->getSubsiteObjById($sourceId)) === false) {
            throw new Exception('Source site info don\'t exists');
        }

        $this->sourceId = $sourceId;
        $this->targetId = $targetId;
        $this->newSlug  = $newSlug;
        $this->blogName = $sourceObj->blogname;
    }

    /**
     * Get the value of targetId
     *
     * @return string
     */
    public function getTargetId()
    {
        return $this->targetId;
    }

    /**
     * Update target id
     *
     * @param int $targetId new target id
     *
     * @return void
     */
    public function setTargetId($targetId)
    {
        $this->targetId = (int) $targetId;
    }

    /**
     * Get the value of sourceId
     *
     * @return string
     */
    public function getSourceId()
    {
        return $this->sourceId;
    }

    /**
     * Get the value of newSlug
     *
     * @return string
     */
    public function getNewSlug()
    {
        return $this->newSlug;
    }

    /**
     * Get source sibsite info
     *
     * @return array
     */
    public function getSourceSiteInfo()
    {
        if (($info = \DUPX_ArchiveConfig::getInstance()->getSubsiteObjById($this->sourceId)) == false) {
            return false;
        }

        return (array) $info;
    }

    /**
     * Return target site info
     *
     * @return array|bool
     */
    public function getTargetSiteInfo()
    {
        $overwriteData = PrmMng::getInstance()->getValue(PrmMng::PARAM_OVERWRITE_SITE_DATA);
        foreach ($overwriteData['subsites'] as $subsite) {
            if ($subsite['id'] == $this->targetId) {
                return $subsite;
            }
        }

        return false;
    }

    /**
     * Return subsite blog name
     *
     * @return string
     */
    public function getBlogname()
    {
        return $this->sourceObj->blogname;
    }
}
