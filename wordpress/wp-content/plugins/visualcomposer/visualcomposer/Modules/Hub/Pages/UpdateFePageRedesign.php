<?php

namespace VisualComposer\Modules\Hub\Pages;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\Hub\Update;
use VisualComposer\Helpers\Options;
use VisualComposer\Helpers\Traits\EventsFilters;
use VisualComposer\Helpers\Url;

class UpdateFePageRedesign extends Container implements Module
{
    use EventsFilters;

    public function __construct()
    {
        if (!vcvenv('VCV_FT_ACTIVATION_REDESIGN')) {
            return;
        }
        $this->addFilter('vcv:editors:frontend:render', 'setUpdatingViewFe', -1);
        $this->addFilter('vcv:frontend:update:extraOutput', 'addUpdateAssets', 10);
    }

    /**
     * @param $response
     * @param \VisualComposer\Helpers\Options $optionsHelper
     * @param \VisualComposer\Helpers\Hub\Update $updateHelper
     *
     * @return mixed
     * @throws \ReflectionException
     */
    protected function setUpdatingViewFe($response, Options $optionsHelper, Update $updateHelper)
    {
        if ($optionsHelper->get('bundleUpdateRequired')) {
            $requiredActions = $updateHelper->getRequiredActions();
            if (!empty($requiredActions['actions']) || !empty($requiredActions['posts'])) {
                $content = vcview(
                    'license/layout',
                    [
                        'slug' => 'vcv-update-fe',
                    ]
                );
                vcvdie(
                    vcview('license/fe-update-wrapper', ['content' => $content])
                );
            } else {
                $optionsHelper->set('bundleUpdateRequired', false);
            }
        }

        return $response;
    }

    protected function addUpdateAssets($response, $payload, Url $urlHelper)
    {
        // Add Vendor JS
        $response = array_merge(
            (array)$response,
            [
                sprintf(
                    '<link rel="stylesheet" href="%s"></link>',
                    $urlHelper->assetUrl(
                        'dist/wpUpdateRedesign.bundle.css?v=' . VCV_VERSION
                    )
                ),
                sprintf(
                    '<script id="vcv-script-vendor-bundle-update" type="text/javascript" src="%s"></script>',
                    $urlHelper->assetUrl(
                        'dist/wpUpdateRedesign.bundle.js?v=' . VCV_VERSION
                    )
                ),
            ]
        );

        return $response;
    }
}
