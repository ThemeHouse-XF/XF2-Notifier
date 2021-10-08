<?php

namespace ThemeHouse\Notifier\XFRM\Entity;

use ThemeHouse\Notifier\Action;

/**
 * Class ResourceItem
 * @package ThemeHouse\Notifier\XFRM\Entity
 */
class ResourceItem extends XFCP_ResourceItem
{
    /**
     *
     * @throws \Exception
     * @throws \Exception
     * @throws \Exception
     */
    protected function _postSave()
    {
        parent::_postSave();

        if ($this->isInsert()) {
            Action::trigger($this, 'create', $this->User);
        } else {
            if ($this->isChanged('resource_state')) {
                $currentState = $this->resource_state;
                $oldState = $this->getPreviousValue('resource_state');

                if ($currentState === 'visible' && $oldState === 'deleted') {
                    Action::trigger($this, 'undelete');
                }

                if ($currentState === 'deleted' && $oldState !== 'deleted') {
                    Action::trigger($this, 'delete');
                }
            }
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
