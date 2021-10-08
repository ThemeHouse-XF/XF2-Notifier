<?php

namespace ThemeHouse\Notifier\NotifierContent;

use ThemeHouse\Notifier\Entity\Provider;
use XF\Entity\Thread;
use XF\Entity\User;
use XF\Http\Request;
use XF\InlineMod\AbstractHandler;
use XF\Repository\Node;

/**
 * Class ThreadContent
 * @package ThemeHouse\Notifier\NotifierContent
 *
 * @property \XF\Entity\Node content
 */
class ThreadContent extends AbstractNotifierContent
{
    /**
     * @var array
     */
    protected $defaultOptions = [
        'node_ids' => [0],
        'thread_discussion_open' => -1,
        'thread_sticky' => -1,
    ];

    /**
     * @return bool
     */
    public function canUseUserCriteria()
    {
        return true;
    }

    /**
     * @return mixed|string
     */
    public function renderActionTabs()
    {
        return $this->app->templater()->renderMacro('th_notifier_action_edit_thread', 'tabs');
    }

    /**
     * @return string
     */
    public function renderActionTabPanes()
    {
        /** @var Node $nodeRepo */
        $nodeRepo = $this->app->repository('XF:Node');

        return $this->app->templater()->renderTemplate('admin:th_notifier_action_edit_thread', [
            'action' => $this->action,
            'nodeTree' => $nodeRepo->createNodeTree($nodeRepo->getFullNodeList()),
        ]);
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
        if (empty($options['content']['node_ids']) || in_array(0, $options['content']['node_ids'])) {
            $options['content']['node_ids'] = [0];
        }

        return true;
    }

    /**
     * @return array
     */
    public function getActions()
    {
        return [
            'create' => \XF::phrase('post_thread'),
            'delete' => \XF::phrase('delete_thread'),
            'undelete' => \XF::phrase('th_notifier_undelete_thread'),
            'close' => \XF::phrase('close_thread'),
            'open' => \XF::phrase('open_thread'),
            'stick' => \XF::phrase('stick_thread'),
            'unstick' => \XF::phrase('unstick_thread'),
        ];
    }

    /**
     * @param Provider $provider
     * @param User|null $user
     * @return \XF\Phrase
     */
    public function buildMessageForProvider(Provider $provider, User $user = null)
    {
        $phraseName = $this->getMessagePhraseName();
        $handler = $provider->handler;
        /** @var Thread $thread */
        $thread = $this->content;
        $router = $this->app->router('public');

        $phraseParams = [
            'userNameLink' => $handler->buildUrl($router->buildLink('full:members', $user), $user->username),
            'threadNameLink' => $handler->buildUrl($this->getUrlForContent(), $thread->title),
        ];

        switch ($this->actionType) {
            case 'create':
                $userNameLink = $thread->username;
                if ($user) {
                    $userNameLink = $handler->buildUrl($router->buildLink('full:members', $user), $user->username);
                }
                $phraseParams['userNameLink'] = $userNameLink;
                break;
            case 'delete':
                if ($thread->isDeleted()) {
                    $type = 'hard';
                    $threadNameLink = $thread->title;
                } else {
                    $type = 'soft';
                    $threadNameLink = $handler->buildUrl($this->getUrlForContent(), $thread->title);
                }

                $phraseParams = array_merge($phraseParams, [
                    'deleteType' => \XF::phrase('th_notifier_' . $type . '_deleted'),
                    'threadNameLink' => $threadNameLink,
                ]);
                break;
        }

        return \XF::phrase($phraseName, $phraseParams);
    }

    public function buildInlineMessageForProvider(Provider $provider, AbstractHandler $inlineModerationHandler, User $user = null)
    {
        $phraseName = $this->getInlineMessagePhraseName($inlineModerationHandler);
        $handler = $provider->handler;

        $ids = $this->app->request()->getCookie('inlinemod_thread');
        if (!$ids) {
            return;
        }
        $ids = explode(',', $ids);
        $entities = $inlineModerationHandler->getEntities($ids);
        $forum = $entities->first()->Forum;

        $router = $this->app->router('public');

        $phraseParams = [
            'userNameLink' => $handler->buildUrl($router->buildLink('full:members', $user), $user->username),
            'forumNameLink' => $handler->buildUrl($this->getUrlForInlineContent($forum), $forum->title),
            'count' => $entities->count(),
        ];

        switch ($this->actionType) {
            case 'delete':
                if ($this->app->request()->filter('hard_delete', 'bool')) {
                    $type = 'hard';
                } else {
                    $type = 'soft';
                }

                $phraseParams = array_merge($phraseParams, [
                    'deleteType' => \XF::phrase('th_notifier_' . $type . '_deleted'),
                ]);
                break;
        }

        return \XF::phrase($phraseName, $phraseParams);
    }

    /**
     * @return mixed|string
     */
    public function getUrlForContent()
    {
        return $this->app->router('public')->buildLink('full:threads', $this->content);
    }

    /**
     * @return mixed|string
     */
    public function getUrlForInlineContent($forum)
    {
        return $this->app->router('public')->buildLink('full:forums', $forum);
    }

    /**
     * @return string
     */
    protected function getEntityClass()
    {
        return Thread::class;
    }

    /**
     * @return bool
     */
    protected function _canUseForContent()
    {
        $options = $this->action->options;
        /** @var Thread $thread */
        $thread = $this->content;

        if (!empty($options['content']['node_ids']) && !in_array(0,
                $options['content']['node_ids']) && !in_array($this->content->node_id,
                $options['content']['node_ids'])) {
            return false;
        }

        if ($options['content']['thread_discussion_open'] > -1) {
            if ($options['content']['thread_discussion_open'] != $thread->discussion_open) {
                return false;
            }
        }

        if ($options['content']['thread_sticky'] > -1) {
            if ($options['content']['thread_sticky'] != $thread->sticky) {
                return false;
            }
        }

        return true;
    }

}