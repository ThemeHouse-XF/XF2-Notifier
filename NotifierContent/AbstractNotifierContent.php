<?php

namespace ThemeHouse\Notifier\NotifierContent;

use ThemeHouse\Notifier\Entity\Action;
use ThemeHouse\Notifier\Entity\Provider;
use XF\App;
use XF\Entity\User;
use XF\Http\Request;
use XF\InlineMod\AbstractHandler;
use XF\Mvc\Entity\Entity;

/**
 * Class AbstractNotifierContent
 * @package ThemeHouse\Notifier\NotifierContent
 */
abstract class AbstractNotifierContent
{
    /**
     * @var App
     */
    protected $app;

    /**
     * @var Entity
     */
    protected $content;
    /**
     * @var Action
     */
    protected $action;
    /**
     * @var null
     */
    protected $actionType;

    /**
     * @var array
     */
    protected $defaultOptions = [];

    /**
     * AbstractNotifierContent constructor.
     * @param App $app
     * @param Action|null $action
     * @param null $actionType
     * @param Entity|null $content
     */
    public function __construct(
        App $app,
        Action $action = null,
        $actionType = null,
        Entity $content = null
    ) {
        $this->app = $app;
        $this->action = $action;
        $this->content = $content;
        $this->actionType = $actionType;

        if ($action) {
            $options = $this->action->options;
            $options['content'] = $this->setupOptions($this->action->options);
            $this->action->options = $options;
        }
    }

    /**
     * @param array $options
     * @return array
     */
    public function setupOptions(array $options)
    {
        if (empty($options['content'])) {
            return [];
        }

        return array_replace($this->defaultOptions, $options['content']);
    }

    /**
     * @param User|null $user
     * @return bool
     */
    public function canUseForContent(User $user = null)
    {
        if (!$this->canUse()) {
            return false;
        }
        if (!$this->content) {
            return false;
        }
        $entityClass = $this->getEntityClass();
        if (!$this->content instanceof $entityClass) {
            return false;
        }

        if ($user) {
            $userCriteria = $this->app->criteria('XF:User', $this->action->user_criteria);

            if (!$userCriteria->isMatched($user)) {
                return false;
            }
        }

        return $this->_canUseForContent();
    }

    /**
     * @return bool
     */
    public function canUse()
    {
        return true;
    }

    /**
     * @return mixed
     */
    protected abstract function getEntityClass();

    /**
     * @return mixed
     */
    protected abstract function _canUseForContent();

    /**
     * @return bool
     */
    public function canUseUserCriteria()
    {
        return false;
    }

    /**
     * @return string
     */
    public function renderActionTabs()
    {
        return '';
    }

    /**
     * @return string
     */
    public function renderActionTabPanes()
    {
        return '';
    }

    /**
     * @return string
     */
    public function renderActionOptions()
    {
        return '';
    }

    /**
     * @param array $options
     * @param null $error
     * @return bool
     */
    public function verifyOptionsValue(array $options, &$error = null)
    {
        $optionsRequest = new Request(
            \XF::app()->inputFilterer(), $options
        );
        return $this->verifyOptions($optionsRequest, $options, $error, false);
    }

    /**
     * @param Request $request
     * @param array $options
     * @param null $error
     * @param bool $save
     * @return bool
     */
    public function verifyOptions(Request $request, array &$options, &$error = null, $save = true)
    {
        return true;
    }

    /**
     * @return mixed
     */
    public abstract function getActions();

    /**
     * @return mixed
     */
    public abstract function getUrlForContent();

    /**
     * @param Provider $provider
     * @param User|null $user
     * @return mixed
     */
    public abstract function buildMessageForProvider(Provider $provider, User $user = null);

    /**
     * @return string
     */
    protected function getMessagePhraseName()
    {
        return 'th_notifier_action_' . $this->content->getEntityContentType() . '_' . $this->actionType;
    }

    /**
     * @return string
     */
    protected function getInlineMessagePhraseName(AbstractHandler $inlineModerationHandler)
    {
        return 'th_notifier_action_' . $inlineModerationHandler->getContentType() . '_' . $this->actionType . '_inline';
    }
}