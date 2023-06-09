<?php

defined("ABSPATH") or die("");

/**
 * Wordpress utility functions
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2
 *
 * @package DUP_PRO
 * @subpackage classes/utilities
 * @copyright (c) 2017, Snapcreek LLC
 * @license https://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since 3.8.9
 *
 */

/**
 * Wordpress utility functions
 */
class DUP_PRO_WP_U
{

    /**
     *
     * @param int $blogId // f multisite and blogId > 0 return the user of blog
     * @return array
     */
    public static function getAdminUserLists($blogId = 0)
    {
        $args = array(
            'fields' => array('id', 'user_login')
        );

        if (is_multisite()) {
            $args['blog_id'] = $blogId;
            if ($blogId == 0) {
                $args['login__in'] = get_site_option('site_admins');
            }
        } else {
            $args['role'] = 'administrator';
        }

        return get_users($args);
    }

    public static function getPostTypesCount()
    {
        $postTypes     = get_post_types();
        $postTypeCount = array();

        foreach ($postTypes as $postName) {
            $postObj = get_post_type_object($postName);
            if (!$postObj->public) {
                continue;
            }
            $postCountForTypes = (array) wp_count_posts($postName);
            $postCount         = 0;
            foreach ($postCountForTypes as $num) {
                $postCount += $num;
            }
            $postTypeCount[$postObj->label] = $postCount;
        }

        return $postTypeCount;
    }
}
