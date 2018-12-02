<?php

namespace VisualComposer\Helpers\Hub;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Illuminate\Support\Helper;

class Update implements Helper
{
    /**
     * @param array $json
     *
     * @return array
     * @throws \ReflectionException
     */
    public function getRequiredActions($json = [])
    {
        $optionsHelper = vchelper('Options');
        $loggerHelper = vchelper('Logger');
        if (empty($json) || !isset($json['actions'])) {
            $json = $optionsHelper->getTransient('bundleUpdateJson');
            if (!$json) {
                $json = [];
                // Current json is expired, need to update actions
                $savedJson = $this->checkVersion();
                if (!vcIsBadResponse($savedJson)) {
                    // Everything is ok need to parse $requiredActions['actions']
                    $json = $savedJson['json'];
                } else {
                    // TODO: Errors
                    // Logger::add error
                    $loggerHelper->log('Failed to update required actions list #10012');
                }
            }
        }
        list($needUpdatePost, $requiredActions) = vchelper('HubBundle')->loopActions($json);
        $reRenderPosts = array_unique($needUpdatePost);
        $requiredActions = vchelper('Data')->arrayDeepUnique($requiredActions);
        $postsActions = [];
        if (count($reRenderPosts) > 0) {
            $tempPosts = $this->createPostUpdateObjects($reRenderPosts);
            $postsActions = $tempPosts[0]['data'];
        }

        return ['actions' => $requiredActions, 'posts' => $postsActions];
    }

    public function createPostUpdateObjects(array $posts)
    {
        $result = [];
        $frontendHelper = vchelper('Frontend');
        foreach ($posts as $id) {
            $post = get_post($id);
            if (!is_null($post)) {
                $result[] = [
                    'id' => $id,
                    'editableLink' => $frontendHelper->getEditableUrl($id),
                    'name' => get_the_title($id),
                ];
            }
        }

        return [['action' => 'updatePosts', 'data' => $result]];
    }

    /**
     * @param array $json
     *
     * @return bool
     */
    public function checkIsUpdateRequired($json = [])
    {
        if (empty($json) || !isset($json['actions'])) {
            return false;
        }
        list($needUpdatePost, $requiredActions) = vchelper('HubBundle')->loopActions($json);

        return !empty($requiredActions) || !empty($needUpdatePost);
    }

    /**
     * Remove trashed posts
     *
     * @return array
     */
    public function getUpdatePosts()
    {
        $optionsHelper = vchelper('Options');
        $updatePosts = $optionsHelper->get('hubAction:updatePosts', []);
        $canUpdate = [];

        foreach ($updatePosts as $updatePost) {
            $post = get_post($updatePost);
            // @codingStandardsIgnoreLine
            if ($post && $post->post_status !== 'trash') {
                $canUpdate[] = $updatePost;
            }
        }

        return $canUpdate;
    }

    public function getVariables()
    {
        $urlHelper = vchelper('Url');
        $currentUserAccessHelper = vchelper('AccessCurrentUser');
        $editorPostTypeHelper = vchelper('AccessEditorPostType');
        $requiredHelper = vchelper('Request');

        if (vchelper('Options')->get('bundleUpdateRequired')) {
            $requiredActions = vchelper('HubUpdate')->getRequiredActions();
        } else {
            $requiredActions = [
                'actions' => [],
                'posts' => [],
            ];
        }
        $variables[] = [
            'key' => 'VCV_UPDATE_ACTIONS',
            'value' => $requiredActions,
            'type' => 'constant',
        ];
        $variables[] = [
            'key' => 'VCV_UPDATE_PROCESS_ACTION_URL',
            'value' => $urlHelper->adminAjax(['vcv-action' => 'hub:action:adminNonce']),
            'type' => 'constant',
        ];
        $variables[] = [
            'key' => 'VCV_UPDATE_SKIP_POST_URL',
            'value' => $urlHelper->adminAjax(['vcv-action' => 'hub:action:postUpdate:skipPost']),
            'type' => 'constant',
        ];
        $variables[] = [
            'key' => 'VCV_UPDATE_WP_BUNDLE_URL',
            'value' => $urlHelper->to('public/dist/wp.bundle.js'),
            'type' => 'constant',
        ];
        $variables[] = [
            'key' => 'VCV_UPDATE_VENDOR_URL',
            'value' => $urlHelper->to('public/dist/vendor.bundle.js'),
            'type' => 'constant',
        ];
        $variables[] = [
            'key' => 'VCV_UPDATE_GLOBAL_VARIABLES_URL',
            'value' => $urlHelper->adminAjax(
                ['vcv-action' => 'elements:globalVariables:adminNonce']
            ),
            'type' => 'constant',
        ];
        $variables[] = [
            'key' => 'VCV_PLUGIN_VERSION',
            'value' => VCV_VERSION,
            'type' => 'constant',
        ];
        if ($currentUserAccessHelper->wpAll('edit_pages')->get() && $editorPostTypeHelper->isEditorEnabled('page')) {
            $variables[] = [
                'key' => 'VCV_CREATE_NEW_URL',
                'value' => vcfilter('vcv:about:postNewUrl', 'post-new.php?post_type=page&vcv-action=frontend'),
                'type' => 'constant',
            ];
            $variables[] = [
                'key' => 'VCV_CREATE_NEW_TEXT',
                'value' => __('Create new page', 'vcwb'),
                'type' => 'constant',
            ];
        } elseif ($currentUserAccessHelper->wpAll('edit_posts')->get()
            && $editorPostTypeHelper->isEditorEnabled(
                'post'
            )
        ) {
            $variables[] = [
                'key' => 'VCV_CREATE_NEW_URL',
                'value' => vcfilter('vcv:about:postNewUrl', 'post-new.php?vcv-action=frontend'),
                'type' => 'constant',
            ];

            $variables[] = [
                'key' => 'VCV_CREATE_NEW_TEXT',
                'value' => __('Create new post', 'vcwb'),
                'type' => 'constant',
            ];
        }

        $vcvRef = $requiredHelper->input('vcv-ref');
        if (!$vcvRef) {
            $vcvRef = 'getting-started';
        }

        $variables[] = [
            'key' => 'VCV_PREMIUM_URL',
            'value' => admin_url('admin.php?page=vcv-go-premium&vcv-ref=' . $vcvRef),
            'type' => 'constant',
        ];

        return $variables;
    }

    /**
     * @return array|bool
     * @throws \ReflectionException
     */
    public function checkVersion()
    {
        $hubBundleHelper = vchelper('HubBundle');
        $tokenHelper = vchelper('Token');
        $token = $tokenHelper->getToken();
        if ($token) {
            $url = $hubBundleHelper->getJsonDownloadUrl(['token' => $token]);
            $json = $hubBundleHelper->getRemoteBundleJson($url);
            if ($json) {
                return $this->processJson($json);
            }
        }

        return ['status' => false];
    }

    /**
     * @param $json
     *
     * @return bool|array
     * @throws \ReflectionException
     */
    protected function processJson($json)
    {
        if (is_array($json) && isset($json['actions'])) {
            $this->processTeasers($json['actions']);
            $optionsHelper = vchelper('Options');
            $hubUpdateHelper = vchelper('HubUpdate');
            if ($hubUpdateHelper->checkIsUpdateRequired($json)) {
                $optionsHelper->set('bundleUpdateRequired', true);
                // Save in database cache for 30m
                $optionsHelper->setTransient('bundleUpdateJson', $json, 1800);
            }

            return ['status' => true, 'json' => $json];
        }

        return false;
    }

    protected function processTeasers($actions)
    {
        if (isset($actions['hubTeaser'])) {
            vcevent('vcv:hub:process:action:hubTeaser', ['teasers' => $actions['hubTeaser']]);
        }
        if (isset($actions['hubAddons'])) {
            vcevent('vcv:hub:process:action:hubAddons', ['teasers' => $actions['hubAddons']]);
        }
        if (isset($actions['hubTemplates'])) {
            vcevent('vcv:hub:process:action:hubTemplates', ['teasers' => $actions['hubTemplates']]);
        }
    }
}
