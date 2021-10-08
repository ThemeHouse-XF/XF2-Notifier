<?php

namespace ThemeHouse\Notifier;

use XF\Entity\User;
use XF\InlineMod\AbstractHandler;
use XF\Mvc\Entity\Entity;

/**
 * Class Action
 * @package ThemeHouse\Notifier
 */
class Action
{
    public static $inlineAction = false;

    /**
     * @param Entity $content
     * @param $actionType
     * @param User|null $user
     * @throws \Exception
     * @throws \Exception
     */
    public static function trigger(Entity $content, $actionType, User $user = null)
    {
        if (self::$inlineAction === true) {
            return;
        }

        if (!$user) {
            $user = \XF::visitor();
        }

        /** @var \ThemeHouse\Notifier\Repository\Action $actionRepo */
        $actionRepo = \XF::app()->repository('ThemeHouse\Notifier:Action');

        $action = $actionRepo->getAppropriateActionForTrigger($content, $actionType, $user);

        if ($action) {
            $action->trigger($content, $actionType, $user);
        }
    }

    public static function triggerInline(AbstractHandler $handler, $actionType, $actions, User $user = null)
    {
        if (!$user) {
            $user = \XF::visitor();
        }

        /** @var \ThemeHouse\Notifier\Repository\Action $actionRepo */
        $actionRepo = \XF::app()->repository('ThemeHouse\Notifier:Action');

        $action = $actionRepo->getAppropriateActionForTriggerInline($handler, $actionType, $user);

        if ($action) {
            $action->triggerInline($handler, $actionType, $actions, $user);

            Action::$inlineAction = true;
        }
    }
}