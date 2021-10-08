<?php

namespace ThemeHouse\Notifier\XFRM\Service\ResourceItem;

use ThemeHouse\Notifier\Action;

/**
 * Class Feature
 * @package ThemeHouse\Notifier\XFRM\Service\ResourceItem
 */
class Feature extends XFCP_Feature
{
    /**
     *
     * @throws \Exception
     */
    protected function onNewFeature()
    {
        parent::onNewFeature();

        Action::trigger($this->resource, 'feature');
    }

    /**
     *
     * @throws \Exception
     */
    protected function onUnfeature()
    {
        parent::onUnfeature();

        Action::trigger($this->resource, 'unfeature');
    }
}
