<?php

namespace ThemeHouse\Notifier\XFMG\Entity;

use ThemeHouse\Notifier\Action;

/**
 * Class Rating
 * @package ThemeHouse\Notifier\XFMG\Entity
 */
class Rating extends XFCP_Rating
{
    /**
     *
     * @throws \Exception
     * @throws \Exception
     * @throws \Exception
     * @throws \Exception
     */
    protected function _postSave()
    {
        parent::_postSave();

        if ($this->isInsert()) {
            Action::trigger($this, 'create');
        }
    }

    /**
     *
     * @throws \Exception
     */
    protected function _postDelete()
    {
        parent::_postDelete();

        Action::trigger($this, 'delete');
    }
}
