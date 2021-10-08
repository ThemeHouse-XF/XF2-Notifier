<?php

namespace ThemeHouse\Notifier\XF\Entity;

use ThemeHouse\Notifier\Action;

/**
 * Class Thread
 * @package ThemeHouse\Notifier\XF\Entity
 */
class Thread extends XFCP_Thread
{
    /**
     *
     * @throws \Exception
     * @throws \Exception
     * @throws \Exception
     * @throws \Exception
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
            if ($this->isChanged('discussion_state')) {
                $currentState = $this->discussion_state;
                $oldState = $this->getPreviousValue('discussion_state');

                if ($currentState === 'visible' && $oldState === 'deleted') {
                    Action::trigger($this, 'undelete');
                }

                if ($currentState === 'deleted' && $oldState !== 'deleted') {
                    Action::trigger($this, 'delete');
                }
            }
        }

        if ($this->isChanged('discussion_open')) {
            $open = $this->discussion_open;

            if ($open && !$this->isInsert()) {
                Action::trigger($this, 'open');
            }

            if (!$open) {
                Action::trigger($this, 'close');
            }
        }

        if ($this->isChanged('sticky')) {
            $sticky = $this->sticky;

            if ($sticky) {
                Action::trigger($this, 'stick');
            }

            if (!$sticky && !$this->isInsert()) {
                Action::trigger($this, 'unstick');
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
