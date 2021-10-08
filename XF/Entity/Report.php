<?php

namespace ThemeHouse\Notifier\XF\Entity;

use ThemeHouse\Notifier\Action;
use XF\Mvc\Entity\Structure;

/**
 * Class Report
 * @package ThemeHouse\Notifier\XF\Entity
 */
class Report extends XFCP_Report
{
    /**
     * @param Structure $structure
     * @return Structure
     */
    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        $structure->contentType = 'report';

        return $structure;
    }

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
            if ($this->isChanged('report_state')) {
                $reportState = $this->report_state;

                if ($reportState === 'resolved') {
                    Action::trigger($this, 'resolve');
                }

                if ($reportState === 'rejected') {
                    Action::trigger($this, 'reject');
                }
            }
        }
    }
}
