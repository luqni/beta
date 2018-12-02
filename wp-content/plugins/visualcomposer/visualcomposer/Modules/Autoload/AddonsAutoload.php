<?php

namespace VisualComposer\Modules\Autoload;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Autoload;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Framework\Application as ApplicationVc;
use VisualComposer\Helpers\Traits\EventsFilters;
use VisualComposer\Helpers\Traits\WpFiltersActions;

class AddonsAutoload extends Autoload implements Module
{
    use EventsFilters;
    use WpFiltersActions;

    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct(ApplicationVc $app, $init = true)
    {
        $this->app = $app;
        if ($init) {
            $components = $this->getComponents();
            $this->doComponents($components);
        }

        $this->addEvent(
            'vcv:hub:addons:autoload',
            function ($element) {
                $components = $this->getSingleComponent($element);
                $this->doComponents($components);
            }
        );
    }

    /**
     * @return array
     */
    protected function getComponents()
    {
        $hubHelper = vchelper('HubAddons');
        $all = [
            'helpers' => [],
            'modules' => [],
        ];

        foreach ($hubHelper->getAddons() as $key => $addon) {
            if (isset($addon['phpFiles'])) {
                $all = array_merge_recursive($all, $this->getSingleComponent($addon));
            }
        }

        return $all;
    }

    protected function getSingleComponent($addon)
    {
        $components = $addon['phpFiles'];

        return $this->tokenizeComponents($components);
    }
}
