<?php

namespace ThemeHouse\Notifier\Entity;

use ThemeHouse\Notifier\NotifierContent\AbstractNotifierContent;
use XF\Entity\User;
use XF\InlineMod\AbstractHandler;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * Class Action
 * @package ThemeHouse\Notifier\Entity
 *
 * PROPERTIES
 * @property integer action_id
 * @property string content_type
 * @property array provider_ids
 * @property string title
 * @property array actions
 * @property bool active
 * @property array options
 * @property array user_criteria
 *
 * GETTERS
 * @property AbstractNotifierContent contentHandler
 */
class Action extends Entity
{
    /**
     * @param Structure $structure
     * @return Structure
     */
    public static function getStructure(Structure $structure)
    {
        $structure->table = 'xf_th_notifier_action';
        $structure->primaryKey = 'action_id';
        $structure->shortName = 'ThemeHouse\Notifier:Action';

        $structure->columns = [
            'action_id' => [
                'type' => self::UINT,
                'autoIncrement' => true,
            ],
            'content_type' => [
                'type' => self::STR,
                'maxLength' => 25,
            ],
            'provider_ids' => [
                'type' => self::LIST_COMMA,
                'required' => true,
            ],
            'title' => [
                'type' => self::STR,
                'required' => true,
            ],
            'actions' => [
                'type' => self::LIST_COMMA,
                'required' => true,
            ],
            'active' => [
                'type' => self::BOOL,
                'default' => true,
            ],
            'options' => [
                'type' => self::JSON_ARRAY,
                'default' => [],
            ],
            'user_criteria' => [
                'type' => self::JSON_ARRAY,
                'default' => [],
            ],
        ];

        $structure->getters = [
            'contentHandler' => true,
        ];

        return $structure;
    }

    /**
     * @param Entity $content
     * @param $actionType
     * @param User|null $user
     * @throws \Exception
     */
    public function trigger(Entity $content, $actionType, User $user = null)
    {
        $contentHandler = $this->getContentHandler($content, $actionType);
        if (!$contentHandler) {
            return;
        }

        foreach ($this->provider_ids as $providerId) {
            $providerHandler = $this->getProviderHandler($providerId);
            $provider = $providerHandler->getProvider();
            if (!$providerHandler) {
                continue;
            }

            $options = $this->options;
            if (isset($options[$providerId])) {
                $options = $options[$providerId];
            }

            $message = $contentHandler->buildMessageForProvider($provider, $user);
            $providerHandler->sendMessage($message, $options);
        }
    }

    public function triggerInline(AbstractHandler $handler, $actionType, $actions, User $user = null)
    {
        $contentHandler = $this->getContentHandler(null, $actionType);
        if (!$contentHandler) {
            return;
        }

        foreach ($this->provider_ids as $providerId) {
            $providerHandler = $this->getProviderHandler($providerId);
            $provider = $providerHandler->getProvider();
            if (!$providerHandler) {
                continue;
            }

            $options = $this->options;
            if (isset($options[$providerId])) {
                $options = $options[$providerId];
            }

            $message = $contentHandler->buildInlineMessageForProvider($provider, $handler, $user);
            $providerHandler->sendMessage($message, $options);
        }
    }

    /**
     * @param Entity|null $content
     * @param null $actionType
     * @return AbstractNotifierContent|null
     * @throws \Exception
     */
    public function getContentHandler(Entity $content = null, $actionType = null)
    {
        if ($content) {
            return $this->getActionRepo()->getHandlerForContent($this, $content, $actionType);
        }

        return $this->getActionRepo()->getHandlerForContentType($this->content_type, $this, $actionType);
    }

    /**
     * @return \ThemeHouse\Notifier\Repository\Action|\XF\Mvc\Entity\Repository
     */
    protected function getActionRepo()
    {
        return $this->repository('ThemeHouse\Notifier:Action');
    }

    /**
     * @param $providerId
     * @return \ThemeHouse\Notifier\NotifierProvider\AbstractProvider|null
     */
    public function getProviderHandler($providerId)
    {
        $provider = $this->getProviderRepo()->getProviderById($providerId);
        if (!$provider) {
            return null;
        }

        return $provider->handler;
    }

    /**
     * @return \ThemeHouse\Notifier\Repository\Provider|\XF\Mvc\Entity\Repository
     */
    protected function getProviderRepo()
    {
        return $this->repository('ThemeHouse\Notifier:Provider');
    }

    /**
     * @param Entity $content
     * @param User|null $user
     * @return bool
     * @throws \Exception
     */
    public function canUseForContent(Entity $content, User $user = null)
    {
        $contentHandler = $this->getContentHandler($content);

        return $contentHandler ? $contentHandler->canUseForContent($user) : false;
    }

    /**
     * @return \XF\Phrase
     */
    public function getContentTypePhrase()
    {
        return $this->app()->getContentTypePhrase($this->content_type);
    }

    /**
     * @param $criteria
     * @return bool
     */
    protected function verifyUserCriteria(&$criteria)
    {
        $userCriteria = $this->app()->criteria('XF:User', $criteria);
        $criteria = $userCriteria->getCriteria();
        return true;
    }
}