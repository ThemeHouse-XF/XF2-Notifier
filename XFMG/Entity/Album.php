<?php

namespace ThemeHouse\Notifier\XFMG\Entity;

use ThemeHouse\Notifier\Action;

/**
 * Class Album
 * @package ThemeHouse\Notifier\XFMG\Entity
 */
class Album extends XFCP_Album
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
            if ($this->isChanged('album_state')) {
                $currentState = $this->album_state;
                $oldState = $this->getPreviousValue('album_state');

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
