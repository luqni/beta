<?php

namespace VisualComposer\Modules\FrontView;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\Traits\WpFiltersActions;

/**
 * Class FrontViewController
 * @package VisualComposer\Modules\FrontView
 */
class FrontViewController extends Container implements Module
{
    use WpFiltersActions;

    /**
     * Controller constructor.
     */
    public function __construct()
    {
        /** @see \VisualComposer\Modules\FrontView\FrontViewController::encode */
        $this->wpAddFilter('the_content', 'encode', 1);
        /** @see \VisualComposer\Modules\FrontView\FrontViewController::decode */
        $this->wpAddFilter('the_content', 'decode', 10);
        /** @see \VisualComposer\Modules\FrontView\FrontViewController::removeOldCommentTags */
        $this->wpAddFilter('the_content', 'removeOldCommentTags');
        /** @see \VisualComposer\Modules\FrontView\FrontViewController::removeIpadMeta */
        $this->wpAddAction('admin_enqueue_scripts', 'removeIpadMeta');
    }

    /**
     * @param $content
     *
     * @return null|string|string[]
     */
    protected function encode($content)
    {
        if (in_array(get_post_meta(get_the_ID(), VCV_PREFIX . 'be-editor', true), ['fe', 'be'])) {
            $content = preg_replace_callback(
                '/((<!--vcv no format-->)(.*?)(<!--vcv no format-->))/si',
                function ($matches) {
                    return '<p>' . $matches[2] .
                        base64_encode(
                            (string)vcfilter(
                                'vcv:frontend:content:encode',
                                do_shortcode($matches[3])
                            )
                        ) .
                        $matches[4]
                        . '</p>';
                },
                $content
            );
        }

        return $content;
    }

    /**
     * @param $content
     *
     * @return null|string|string[]
     */
    protected function decode($content)
    {
        if (in_array(get_post_meta(get_the_ID(), VCV_PREFIX . 'be-editor', true), ['fe', 'be'])) {
            $content = preg_replace_callback(
                '/(\<p\>(<!--vcv no format-->)(.*?)(<!--vcv no format-->)<\/p>)/si',
                function ($matches) {
                    return base64_decode($matches[3]);
                },
                $content
            );
        }

        return $content;
    }

    /**
     * Remove old no formatting tags
     *
     * @param $content
     *
     * @return mixed
     */
    protected function removeOldCommentTags($content)
    {
        $content = str_replace(
            ['<!--vcv no formatting start-->', '<!--vcv no formatting end-->'],
            '',
            $content
        );

        return $content;
    }

    /**
     * @fix Remove scaling on mobile devices #652509233919236
     * Remove iPad meta from FE
     */
    protected function removeIpadMeta()
    {
        $this->wpRemoveAction('admin_head', '_ipad_meta');
    }
}
