<?php

namespace ThemeHouse\Notifier\Repository;

use ThemeHouse\Notifier\NotifierContent\AbstractNotifierContent;
use XF\Entity\User;
use XF\InlineMod\AbstractHandler;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Repository;

/**
 * Class Action
 * @package ThemeHouse\Notifier\Repository
 */
class Action extends Repository
{
    /**
     * @param Entity $content
     * @param $actionType
     * @param User|null $user
     * @return bool|\ThemeHouse\Notifier\Entity\Action
     * @throws \Exception
     * @throws \Exception
     */
    public function getAppropriateActionForTrigger(Entity $content, $actionType, User $user = null)
    {
        $actions = $this->getFinder()
            ->where('content_type', $content->getEntityContentType())
            ->where('active', 1)
            ->whereSql('FIND_IN_SET(' . $this->db()->quote($actionType) . ', actions)')
            ->fetch();

        /** @var \ThemeHouse\Notifier\Entity\Action $action */
        foreach ($actions as $action) {
            if (!$action->canUseForContent($content, $user)) {
                continue;
            }

            return $action;
        }

        return false;
    }

    public function getAppropriateActionForTriggerInline(AbstractHandler $handler, $actionType, User $user = null)
    {
        $actions = $this->getFinder()
            ->where('content_type', $handler->getContentType())
            ->where('active', 1)
            ->whereSql('FIND_IN_SET(' . $this->db()->quote($actionType) . ', actions)')
            ->fetch();

        /** @var \ThemeHouse\Notifier\Entity\Action $action */
        foreach ($actions as $action) {
            // if (!$action->canUseForContent($content, $user)) {
            //     continue;
            // }

            return $action;
        }

        return false;
    }

    /**
     * @return \XF\Mvc\Entity\Finder
     */
    protected function getFinder()
    {
        return $this->finder('ThemeHouse\Notifier:Action');
    }

    /**
     * @param $contentType
     * @return bool
     * @throws \Exception
     */
    public function isContentTypeValid($contentType)
    {
        $contentTypes = $this->getAvailableContentTypes();

        return in_array($contentType, $contentTypes);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getAvailableContentTypes()
    {
        $contentTypeFields = $this->app()->getContentTypeField('th_notifier_handler_class');

        $contentTypes = [];
        foreach ($contentTypeFields as $contentType => $fieldValue) {
            /** @var AbstractNotifierContent $handler */
            $handler = $this->getHandlerForContentType($contentType);
            if (!$handler || !$handler->canUse()) {
                continue;
            }
            $contentTypes[] = $contentType;
        }

        return $contentTypes;
    }

    /**
     * @param $contentType
     * @param \ThemeHouse\Notifier\Entity\Action|null $action
     * @param null $actionType
     * @param Entity|null $content
     * @return null
     * @throws \Exception
     */
    public function getHandlerForContentType(
        $contentType,
        \ThemeHouse\Notifier\Entity\Action $action = null,
        $actionType = null,
        Entity $content = null
    ) {
        $contentField = $this->app()->getContentTypeFieldValue($contentType, 'th_notifier_handler_class');
        if (!$contentField) {
            return null;
        }

        $class = \XF::stringToClass($contentField, '%s\NotifierContent\%s');

        if (!class_exists($class)) {
            return null;
        }

        $class = \XF::extendClass($class);
        return new $class($this->app(), $action, $actionType, $content);
    }

    /**
     * @return array
     * @throws \Exception
     * @throws \Exception
     * @throws \Exception
     */
    public function getAvailableContentTypeOptions()
    {
        $contentTypes = $this->getAvailableContentTypes();

        $options = [];
        foreach ($contentTypes as $contentType) {
            $options[$contentType] = $this->app()->getContentTypePhrase($contentType);
        }

        return $options;
    }

    /**
     * @param \ThemeHouse\Notifier\Entity\Action $action
     * @param Entity $content
     * @param null $actionType
     * @return null
     * @throws \Exception
     */
    public function getHandlerForContent(
        \ThemeHouse\Notifier\Entity\Action $action,
        Entity $content,
        $actionType = null
    ) {
        $contentType = $content->getEntityContentType();

        return $this->getHandlerForContentType($contentType, $action, $actionType, $content);
    }
}