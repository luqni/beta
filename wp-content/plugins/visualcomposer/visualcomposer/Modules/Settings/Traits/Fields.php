<?php

namespace VisualComposer\Modules\Settings\Traits;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

/**
 * Trait Fields.
 */
trait Fields
{
    /**
     * @var string
     */
    protected $optionGroup = 'vcv-settings';

    /**
     * @var string
     */
    protected $optionSlug = 'vcv-settings';

    /**
     * @param $sectionData
     *
     * @return $this
     */
    protected function addSection($sectionData)
    {
        $sectionData = array_merge(
            [
                'slug' => $this->optionSlug,
                'group' => $this->optionGroup,
                'title' => '',
                'page' => '',
                'callback' => function ($data) {
                    return $data;
                },
            ],
            $sectionData
        );

        add_settings_section(
            $sectionData['group'] . '_' . $sectionData['slug'],
            $sectionData['title'],
            $sectionData['callback'],
            $sectionData['page']
        );

        return $this;
    }

    /**
     * @param $fieldData
     *
     * @return $this
     */
    protected function addField($fieldData)
    {
        $fieldData = array_merge(
            [
                'id' => '',
                'group' => $this->optionGroup,
                'slug' => $this->optionSlug,
                'name' => '',
                'title' => '',
                'page' => '',
                'fieldCallback' => function ($data) {
                    return $data;
                },
                'sanitizeCallback' => function ($data) {
                    return $data;
                },
                'args' => [],
            ],
            $fieldData
        );

        register_setting(
            $fieldData['group'] . '_' . $fieldData['page'],
            VCV_PREFIX . $fieldData['name'],
            $fieldData['sanitizeCallback']
        );
        add_settings_field(
            $fieldData['id'] ? $fieldData['id'] : VCV_PREFIX . $fieldData['name'],
            $fieldData['title'],
            $fieldData['fieldCallback'],
            $fieldData['page'],
            $fieldData['group'] . '_' . $fieldData['slug'],
            $fieldData['args']
        );

        return $this;
    }
}
