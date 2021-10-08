<?php

namespace ThemeHouse\Notifier\XFRM\Entity;

use ThemeHouse\Notifier\Action;

/**
 * Class ResourceUpdate
 * @package ThemeHouse\Notifier\XFRM\Entity
 */
class ResourceUpdate extends XFCP_ResourceUpdate
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
            Action::trigger($this, 'create');
        } else {
            if ($this->isChanged('message_state')) {
                $currentState = $this->message_state;
                $oldState = $this->getPreviousValue('message_state');

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
