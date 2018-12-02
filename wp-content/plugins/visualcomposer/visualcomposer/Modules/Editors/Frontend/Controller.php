<?php

namespace VisualComposer\Modules\Editors\Frontend;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\Access\EditorPostType;
use VisualComposer\Helpers\Access\UserCapabilities;
use VisualComposer\Helpers\Frontend;
use VisualComposer\Helpers\PostType;
use VisualComposer\Helpers\Traits\WpFiltersActions;
use VisualComposer\Helpers\Views;
use VisualComposer\Helpers\Request;
use VisualComposer\Framework\Container;
use VisualComposer\Helpers\Traits\EventsFilters;
use VisualComposer\Helpers\Url;

/**
 * Class Controller.
 */
class Controller extends Container implements Module
{
    use EventsFilters;
    use WpFiltersActions;

    /**
     * Frontend constructor.
     */
    public function __construct()
    {
        /** @see \VisualComposer\Modules\Editors\Frontend\Controller::renderEditorBase */
        $this->addFilter('vcv:editors:frontend:render', 'renderEditorBase');
        /** @see \VisualComposer\Modules\Editors\Frontend\Controller::init */
        defined('WP_ADMIN') && WP_ADMIN
        && $this->wpAddFilter(
            'secure_auth_redirect',
            function ($response) {
                $this->call('init');

                return $response;
            }
        );
    }

    /**
     * @param \VisualComposer\Helpers\Request $requestHelper
     * @param \VisualComposer\Helpers\Url $urlHelper
     * @param \VisualComposer\Helpers\PostType $postTypeHelper
     * @param \VisualComposer\Helpers\Frontend $frontendHelper
     * @param \VisualComposer\Helpers\Access\EditorPostType $editorPostTypeHelper
     *
     * @return bool|void
     * @throws \Exception
     */
    protected function init(
        Request $requestHelper,
        Url $urlHelper,
        PostType $postTypeHelper,
        Frontend $frontendHelper,
        EditorPostType $editorPostTypeHelper
    ) {
        global $pagenow;
        // Require an action parameter.
        if ($frontendHelper->isFrontend()) {
            $urlHelper->redirectIfUnauthorized();
            $sourceId = (int)$requestHelper->input('vcv-source-id');
            if (!$sourceId) {
                if ($pagenow === 'post-new.php') {
                    $postType = 'post';
                    $allowedHiddenPosts = ['vcv_headers', 'vcv_footers', 'vcv_sidebars'];
                    if (in_array($requestHelper->input('post_type'), $allowedHiddenPosts)
                        || in_array(
                            $requestHelper->input('post_type'),
                            get_post_types(['show_ui' => true]),
                            true
                        )) {
                        $postType = $requestHelper->input('post_type');
                    }
                    $post = \get_default_post_to_edit($postType, true);
                    $sourceId = $post->ID;
                } else {
                    return false;
                }
            }
            $post = $postTypeHelper->setupPost($sourceId);
            // @codingStandardsIgnoreLine
            if (isset($post->post_type) && $editorPostTypeHelper->isEditorEnabled($post->post_type)) {
                $content = vcfilter('vcv:editors:frontend:render', '');

                if (empty($content)) {
                    wp_die(
                        '<h1>' . __('Cheatin&#8217; uh?', 'vcwb') . '</h1>' .
                        '<p>' . __('Sorry, you are not allowed to create posts as this user.', 'vcwb') . '</p>',
                        403
                    );
                }

                /** @noinspection PhpInconsistentReturnPointsInspection */
                return $this->terminate($content);
            }
        }

        return false;
    }

    /**
     * @param $content
     *
     * @throws \Exception
     */
    protected function terminate($content)
    {
        vcvdie($content);
    }

    /**
     * @param \VisualComposer\Helpers\Views $templates
     * @param \VisualComposer\Helpers\Frontend $frontendHelper
     * @param \VisualComposer\Helpers\Access\UserCapabilities $userCapabilitiesHelper
     *
     * @return bool|string
     */
    protected function renderEditorBase(
        Views $templates,
        Frontend $frontendHelper,
        UserCapabilities $userCapabilitiesHelper
    ) {
        global $post;
        $sourceId = $post->ID;
        if (is_numeric($sourceId) && $userCapabilitiesHelper->canEdit($sourceId)) {
            $feError = intval(get_option('page_for_posts')) === $sourceId ? 'page_for_posts' : false;

            return $templates->render(
                'editor/frontend/frontend.php',
                [
                    'editableLink' => $frontendHelper->getEditableUrl($sourceId),
                    'preRenderOutput' => vcfilter('vcv:frontend:preRenderOutput', []),
                    'feError' => $feError,
                ]
            );
        }

        return false;
    }

    protected function addFeOopsAssets($response, $payload, Url $urlHelper)
    {
        // Add Vendor JS
        $response = array_merge(
            (array)$response,
            [
                sprintf(
                    '<link rel="stylesheet" href="%s"></link>',
                    $urlHelper->assetUrl(
                        'dist/wpfeoops.bundle.css?v=' . VCV_VERSION
                    )
                ),
                sprintf(
                    '<script id="vcv-script-vendor-bundle-fe-oops" type="text/javascript" src="%s"></script>',
                    $urlHelper->assetUrl(
                        'dist/wpfeoops.bundle.js?v=' . VCV_VERSION
                    )
                ),
            ]
        );

        return $response;
    }
}
