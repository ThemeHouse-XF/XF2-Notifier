<?php

namespace ThemeHouse\Notifier\XFRM\Entity;

use ThemeHouse\Notifier\Action;

/**
 * Class ResourceRating
 * @package ThemeHouse\Notifier\XFRM\Entity
 */
class ResourceRating extends XFCP_ResourceRating
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
        } else {
            if ($this->isChanged('rating_state')) {
                $currentState = $this->rating_state;
                $oldState = $this->getPreviousValue('rating_state');

                if ($currentState === 'visible' && $oldState === 'deleted') {
                    Action::trigger($this, 'undelete');
                }

                if ($currentState === 'deleted' && $oldState !== 'deleted') {
                    Action::trigger($this, 'delete');
                }
            }

            if ($this->isChanged('author_response')) {
                $response = $this->author_response;
                if (!empty($response)) {
                    Action::trigger($this, 'reply');
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
