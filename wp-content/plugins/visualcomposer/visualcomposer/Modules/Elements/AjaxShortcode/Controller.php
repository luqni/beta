<?php

namespace VisualComposer\Modules\Elements\AjaxShortcode;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\Access\CurrentUser;
use VisualComposer\Helpers\PostType;
use VisualComposer\Helpers\Request;
use VisualComposer\Framework\Container;
use VisualComposer\Helpers\Traits\EventsFilters;
use VisualComposer\Helpers\Traits\WpFiltersActions;

/**
 * Class Controller.
 */
class Controller extends Container implements Module
{
    use WpFiltersActions;
    use EventsFilters;

    /**
     * Controller constructor.
     */
    public function __construct()
    {
        /** @see \VisualComposer\Modules\Elements\AjaxShortcode\Controller::render */
        $this->addFilter(
            'vcv:ajax:elements:ajaxShortcode:adminNonce',
            'render'
        );
    }

    /**
     * @param \VisualComposer\Helpers\Request $requestHelper
     * @param \VisualComposer\Helpers\PostType $postTypeHelper
     * @param \VisualComposer\Helpers\Access\CurrentUser $currentUserAccessHelper
     *
     * @return string
     */
    protected function render(Request $requestHelper, PostType $postTypeHelper, CurrentUser $currentUserAccessHelper)
    {
        $sourceId = (int)$requestHelper->input('vcv-source-id');
        $response = '';
        if ($sourceId && $currentUserAccessHelper->wpAll(['edit_posts', $sourceId])->get()) {
            !defined('CONCATENATE_SCRIPTS') && define('CONCATENATE_SCRIPTS', false);
            $postTypeHelper->setupPost($sourceId);
            global $post;
            // @codingStandardsIgnoreLine
            $post->post_content = $requestHelper->input('vcv-shortcode-string');
            $this->wpAddFilter(
                'print_scripts_array',
                function ($list) {
                    return array_diff($list, ['jquery-core', 'jquery', 'jquery-migrate']);
                }
            );
            ob_start();
            do_action('template_redirect'); // This fixes visual composer shortcodes
            remove_action('wp_head', '_wp_render_title_tag', 1);
            //            remove_action( 'wp_head',             'wp_enqueue_scripts',              1     );
            remove_action('wp_head', 'wp_resource_hints', 2);
            remove_action('wp_head', 'feed_links', 2);
            remove_action('wp_head', 'feed_links_extra', 3);
            remove_action('wp_head', 'rsd_link');
            remove_action('wp_head', 'wlwmanifest_link');
            remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);
            remove_action('wp_head', 'locale_stylesheet');
            remove_action('publish_future_post', 'check_and_publish_future_post', 10);
            remove_action('wp_head', 'noindex', 1);
            remove_action('wp_head', 'print_emoji_detection_script', 7);
            //            remove_action('wp_head', 'wp_print_styles', 8);
            //            remove_action('wp_head', 'wp_print_head_scripts', 9);
            remove_action('wp_head', 'wp_generator');
            remove_action('wp_head', 'rel_canonical');
            remove_action('wp_head', 'wp_shortlink_wp_head', 10);
            remove_action('wp_head', 'wp_custom_css_cb', 101);
            remove_action('wp_head', 'wp_site_icon', 99);

            wp_head();
            $headContents = ob_get_clean();
            ob_start();
            echo do_shortcode($requestHelper->input('vcv-shortcode-string'));
            $shortcodeContents = ob_get_clean();
            ob_start();
            wp_footer();
            $footerContents = ob_get_clean();
            $response = [
                'headerContent' => $headContents,
                'shortcodeContent' => $shortcodeContents,
                'footerContent' => $footerContents,
            ];
        }

        return $response;
    }
}
