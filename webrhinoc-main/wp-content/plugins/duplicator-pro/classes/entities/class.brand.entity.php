<?php

/**
 * Brand entity layer
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2
 *
 * @package DUP_PRO
 * @subpackage classes/entities
 * @copyright (c) 2017, Snapcreek LLC
 * @license https://opensource.org/licenses/GPL-3.0 GNU Public License
 *
 * @todo Finish Docs
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Libs\Snap\SnapIO;

/* @var $global DUP_PRO_Global_Entity */
/* @var $brand DUP_PRO_Brand_Entity */

require_once(DUPLICATOR_PRO_PLUGIN_PATH . '/classes/entities/class.json.entity.base.php');
// For those brand types that do not require any configuration ahead of time
abstract class DUP_PRO_Brand_Modes
{
    const keepPlugin   = 0;
    const removePlugin = 1;
}

// For those brand types that do not require any configuration ahead of time
abstract class DUP_PRO_BRAND_IDS
{
    const defaultBrand = -2;
}

class DUP_PRO_Brand_Entity extends DUP_PRO_JSON_Entity_Base
{
    public $name         = '';
    public $notes        = '';
    public $editable     = true;
    public $logo         = '<i class="fa fa-bolt fa-sm"></i> Duplicator Pro';
    public $attachments = array();
    protected $brandMode     = DUP_PRO_Brand_Modes::removePlugin;
    protected $is_freelancer_plus = false;
    function __construct()
    {
        $this->is_freelancer_plus = \Duplicator\Addons\ProBase\License\License::isFreelancer();
        parent::__construct();
        $this->name = '';
    }

    public static function get_all()
    {
        $default_brand = self::get_default_brand();
        $self = new DUP_PRO_Brand_Entity();
        if ($self->is_freelancer_plus) {
            $brands = self::get_by_type(get_class());
        } else {
            $brands = array();
        }
        array_unshift($brands, $default_brand);
        return $brands;
    }

    public static function delete_by_id($brand_id)
    {
        try {
            $self = new DUP_PRO_Brand_Entity();
            if ($self->is_freelancer_plus) {
                parent::delete_by_id_base($brand_id);
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    public static function get_by_id($id, $prepare_files = false)
    {
        $self = new DUP_PRO_Brand_Entity();
        if ($id <= 0 || $id == DUP_PRO_BRAND_IDS::defaultBrand || !$self->is_freelancer_plus) {
            return self::get_default_brand();
        }

        $brand = self::get_by_id_and_type($id, get_class());
        if ($prepare_files === true) {
            if (isset($brand->attachments) && count($brand->attachments) > 0) {
                $self->prepare_attachments_to_installer($brand->attachments);
            } else {
                $self->prepare_attachments_to_installer(null);
            }
        }

        return $brand;
    }

    public function get_mode_text()
    {
        $txt = DUP_PRO_U::__('Unknown');
        switch ($this->brandMode) {
            case DUP_PRO_Brand_Modes::keepPlugin:
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 $txt = DUP_PRO_U::__('Keep Plugin');

                break;
            case DUP_PRO_Brand_Modes::removePlugin:
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             $txt = DUP_PRO_U::__('Remove Plugin');

                break;
        }

        return $txt;
    }


    public function save()
    {
        global $wpdb;
        if ($this->is_freelancer_plus) {
            parent::save();
        }
    }

    /**
     * Collect all attachments into `$this->attachments`
     * @param  string/array     $attachments    -image paths inside /wp-content/uploads folder, Accept array or comma delimited array
     */
    public function attachments($attachments)
    {
        if (!is_array($attachments)) {
            $attachments = array_map("trim", preg_split('/(;|,)/', $attachments));
        }

        $upload_dir = wp_upload_dir();
        $dir = $upload_dir['basedir'];
//Uploads folder
        $dir = str_replace(array('\\','//'), array('/','/'), $dir);
        foreach ($attachments as $attachment) {
            if (file_exists("{$dir}{$attachment}")) {
                $this->attachments[] = $attachment;
            }
        }
    }

    public static function get_default_brand()
    {
        $global = DUP_PRO_Global_Entity::get_instance();
        $default_brand = new DUP_PRO_Brand_Entity();
        $default_brand->name                 = DUP_PRO_U::__('Default');
        $default_brand->notes                = DUP_PRO_U::__('The default content used when a brand is not defined');
        $default_brand->id                   = DUP_PRO_BRAND_IDS::defaultBrand;
        $default_brand->logo                 = '<i class="fa fa-bolt fa-sm"></i> ' . DUP_PRO_U::__('Duplicator Pro');
        $default_brand->editable             = false;
        $default_brand->attachments          = array();
        return $default_brand;
    }


    /*=========================================
     * PRIVATE AND PROTECTED AREA
     */


    /*
     * PRIVATE: This function prepare image files inside installer assets/images
     * @pharam $type            array/null     -add attachments in array or use null to delete all files inside installer
     * @return                  bool/int
     **/
    private function prepare_attachments_to_installer($attachments = array())
    {
        $installer = DUPLICATOR_PRO_PLUGIN_PATH . "/installer/dup-installer/assets/images/brand";
        if ($attachments === null) {
            if (file_exists($installer) && is_dir($installer)) {
                return SnapIO::rrmdir($installer);
            }

            return true;
        }

        if (!is_array($attachments)) {
            return false;
        }

        if (count($attachments) === 0) {
            $attachments = $this->attachments;
        }

        if (count($attachments) > 0) {
            $this->prepare_attachments_to_installer(null);
            $upload_dir = wp_upload_dir();
            $dir = $upload_dir['basedir'];
//Uploads folder
            $dir = str_replace(array('\\','//'), array('/','/'), $dir);
            SnapIO::mkdir($installer);
            $copy = array();
            foreach ($attachments as $attachment) {
                if (file_exists("{$dir}{$attachment}")) {
                    SnapIO::mkdir(dirname("{$installer}{$attachment}"), 0755, true);
                    if (@copy("{$dir}{$attachment}", "{$installer}{$attachment}") === false) {
                            DUP_PRO_Log::error("Error copying {$dir}{$attachment} to {$installer}{$attachment}", '', false);
                    } else {
                                $copy[] = 1;
                    }
                }
            }

            return count($copy) > 0;
        }

        return false;
    }
}
