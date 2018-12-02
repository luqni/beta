<?php

namespace VisualComposer\Modules\Elements;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\File;
use VisualComposer\Helpers\Hub\Categories;
use VisualComposer\Helpers\Options;
use VisualComposer\Modules\Api\ApiRegisterTrait;
use VisualComposer\Helpers\Hub\Elements;

/**
 * Class ApiController
 * @package VisualComposer\Modules\Elements
 */
class ApiController extends Container implements Module
{
    protected $apiHook = 'elements';

    protected $publicMethods = ['add'];

    use ApiRegisterTrait;

    /**
     * @param $manifestPath
     * @param $elementBaseUrl
     *
     * @param \VisualComposer\Helpers\Hub\Elements $hubElements
     * @param \VisualComposer\Helpers\File $fileHelper
     * @param \VisualComposer\Helpers\Options $optionsHelper
     * @param \VisualComposer\Helpers\Hub\Categories $hubCategories
     *
     * @return bool
     */
    protected function add(
        $manifestPath,
        $elementBaseUrl,
        Elements $hubElements,
        File $fileHelper,
        Options $optionsHelper,
        Categories $hubCategories
    ) {
        $manifestContents = $fileHelper->getContents($manifestPath);
        $manifestData = json_decode($manifestContents, true);
        if (is_array($manifestData) && isset($manifestData['elements']) && isset($manifestData['categories'])) {
            $elements = $optionsHelper->get('hubElements', []);
            $elementBaseUrl = rtrim($elementBaseUrl, '\\/');
            $this->processElements($manifestPath, $elementBaseUrl, $hubElements, $manifestData, $elements);
            $this->processCategories($hubCategories, $manifestData);

            return true;
        }

        return false;
    }

    /**
     * @param $manifestPath
     * @param $elementBaseUrl
     * @param \VisualComposer\Helpers\Hub\Elements $hubElements
     * @param $manifestData
     * @param $elements
     */
    protected function processElements($manifestPath, $elementBaseUrl, Elements $hubElements, $manifestData, $elements)
    {
        foreach ($manifestData['elements'] as $tag => $elementSettings) {
            $elementSettings['key'] = $tag;
            $elementSettings['bundlePath'] = $elementBaseUrl . '/public/dist/element.bundle.js';
            $elementSettings['elementPath'] = $elementBaseUrl . '/' . $tag . '/';
            $elementSettings['elementRealPath'] = '[thirdPartyFullPath]' . dirname($manifestPath) . '/' . $tag
                . '/';
            $elementSettings['assetsPath'] = $elementBaseUrl . '/' . $tag . '/public/';

            $elementSettings = json_decode(
                str_replace(
                    '[publicPath]',
                    $elementBaseUrl . '/' . $tag . '/public',
                    json_encode($elementSettings)
                ),
                true
            );
            if (isset($elementSettings['phpFiles'])) {
                $elementSettings['phpFiles'] = array_map(
                    function ($path) use ($elementSettings, $tag) {
                        return $elementSettings['elementRealPath'] . $path;
                    },
                    $elementSettings['phpFiles']
                );
            }
            $hubElements->addElement($tag, $elementSettings);
        }
    }

    /**
     * @param \VisualComposer\Helpers\Hub\Categories $hubCategories
     * @param $manifestData
     */
    protected function processCategories(Categories $hubCategories, $manifestData)
    {
        foreach ($manifestData['categories'] as $category => $categoryElements) {
            $hubCategories->addCategoryElements($category, $categoryElements['elements']);
        }
    }
}
