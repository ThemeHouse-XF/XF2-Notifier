<?php

namespace ThemeHouse\Notifier\XFMG\Entity;

use ThemeHouse\Notifier\Action;

/**
 * Class MediaItem
 * @package ThemeHouse\Notifier\XFMG\Entity
 */
class MediaItem extends XFCP_MediaItem
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
            if ($this->isChanged('media_state')) {
                $currentState = $this->media_state;
                $oldState = $this->getPreviousValue('media_state');

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
