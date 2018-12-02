<?php

namespace VisualComposer\Modules\Migrations;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\File;
use VisualComposer\Helpers\Options;

/**
 * Class Templates114Migration
 *
 * This migration fixes the template downloaded images
 *
 * @package VisualComposer\Modules\Migrations
 */
class Assets22Migration extends MigrationsController implements Module
{
    protected $migrationId = 'assets22Migration';

    protected $migrationPriority = 11;

    protected function run(File $fileHelper, Options $optionsHelper)
    {
        // check if folder doesnt exists in wp-content/uploads/visualcomposer-assets
        // check if folder exists in wp-content/visualcomposer-assets
        if (vcvenv('VCV_TF_ASSETS_IN_UPLOADS')) {
            $fileSystem = $fileHelper->getFileSystem();
            if (!$fileSystem) {
                return false;
            }
            if (!$fileSystem->is_dir(VCV_PLUGIN_ASSETS_DIR_PATH)
                && $fileSystem->is_dir(
                    WP_CONTENT_DIR . '/' . VCV_PLUGIN_ASSETS_DIRNAME
                )) {
                usleep(500000);
                if (!$optionsHelper->getTransient('vcv:migration:assets22:lock')) {
                    /** @see \VisualComposer\Modules\Migrations\Assets22Migration::moveFiles */
                    return $this->call('moveFiles');
                } else {
                    return false;
                }
            }
        }

        return vcvenv('VCV_TF_ASSETS_IN_UPLOADS');
    }

    /**
     * @param \VisualComposer\Helpers\File $fileHelper
     * @param \VisualComposer\Helpers\Options $optionsHelper
     *
     * @return bool
     */
    protected function moveFiles(File $fileHelper, Options $optionsHelper)
    {
        //@codingStandardsIgnoreStart
        $optionsHelper->setTransient('vcv:migration:assets22:lock', true, 20);

        $result = $fileHelper->copyDirectory(
            WP_CONTENT_DIR . '/' . VCV_PLUGIN_ASSETS_DIRNAME,
            VCV_PLUGIN_ASSETS_DIR_PATH . '-temp',
            true
        );
        if (!is_wp_error($result) && $result) {
            $resultMove = $fileHelper->getFileSystem()->move(
                VCV_PLUGIN_ASSETS_DIR_PATH . '-temp',
                VCV_PLUGIN_ASSETS_DIR_PATH
            );
            $responseMove = !is_wp_error($resultMove) && $result;
            if (vcvenv('VCV_DEBUG')) {
                if (!$responseMove) {
                    error_log(
                        print_r(
                            [
                                'code' => 2,
                                'is_wp_error' => is_wp_error($resultMove),
                                /** @var $result \WP_Error */
                                'data' => is_wp_error($result) ? $result->get_error_messages() : $result,
                                'codes' => is_wp_error($result) ? $result->get_error_codes() : $result,
                            ],
                            true
                        ),
                        3,
                        VCV_PLUGIN_DIR_PATH . 'not-ok.log'
                    );
                }
            }

            return $responseMove;
        } else {
            if (vcvenv('VCV_DEBUG')) {
                error_log(
                    print_r(
                        [
                            'code' => 1,
                            'is_wp_error' => is_wp_error($result),
                            'data' => is_wp_error($result) ? $result->get_error_messages() : $result,
                            'codes' => is_wp_error($result) ? $result->get_error_codes() : $result,
                        ],
                        true
                    ),
                    3,
                    VCV_PLUGIN_DIR_PATH . 'not-ok.log'
                );
            }
        }

        return false;
        //@codingStandardsIgnoreEnd
    }
}
