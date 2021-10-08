<?php

namespace ThemeHouse\Notifier\XF\Entity;

use ThemeHouse\Notifier\Action;

/**
 * Class Post
 * @package ThemeHouse\Notifier\XF\Entity
 */
class Post extends XFCP_Post
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

        if (!$this->isFirstPost()) {
            if ($this->isInsert()) {
                Action::trigger($this, 'create', $this->User);
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
