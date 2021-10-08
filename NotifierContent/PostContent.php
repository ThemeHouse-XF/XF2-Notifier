<?php

namespace ThemeHouse\Notifier\NotifierContent;

use ThemeHouse\Notifier\Entity\Provider;
use XF\Entity\Post;
use XF\Entity\User;
use XF\Http\Request;
use XF\InlineMod\AbstractHandler;
use XF\Repository\Node;

/**
 * Class PostContent
 * @package ThemeHouse\Notifier\NotifierContent
 */
class PostContent extends AbstractNotifierContent
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
        return $this->app->templater()->renderMacro('th_notifier_action_edit_post', 'tabs');
    }

    /**
     * @return string
     */
    public function renderActionTabPanes()
    {
        /** @var Node $nodeRepo */
        $nodeRepo = $this->app->repository('XF:Node');

        return $this->app->templater()->renderTemplate('admin:th_notifier_action_edit_post', [
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
            'create' => \XF::phrase('reply_to_thread'),
            'delete' => \XF::phrase('delete_post'),
            'undelete' => \XF::phrase('th_notifier_undelete_post'),
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
        /** @var Post $post */
        $post = $this->content;
        $thread = $post->Thread;
        $router = $this->app->router('public');

        $postUserNameLink = $post->username;
        if ($post->User) {
            $postUserNameLink = $handler->buildUrl($router->buildLink('full:members', $post), $post->username);
        }

        $phraseParams = [
            'userNameLink' => $handler->buildUrl($router->buildLink('full:members', $user), $user->username),
            'threadNameLink' => $handler->buildUrl($this->getUrlForContent(), $thread->title),
            'postUserNameLink' => $postUserNameLink,
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
                if ($post->isDeleted()) {
                    $type = 'hard';
                    $phraseParams['threadNameLink'] = $thread->title;
                } else {
                    $type = 'soft';
                }

                $phraseParams['deleteType'] = \XF::phrase('th_notifier_' . $type . '_deleted');
                break;
        }

        return \XF::phrase($phraseName, $phraseParams);
    }

    public function buildInlineMessageForProvider(Provider $provider, AbstractHandler $inlineModerationHandler, User $user = null)
    {
        $phraseName = $this->getInlineMessagePhraseName($inlineModerationHandler);
        $handler = $provider->handler;

        $ids = $this->app->request()->getCookie('inlinemod_post');
        if (!$ids) {
            return;
        }
        $ids = explode(',', $ids);
        $entities = $inlineModerationHandler->getEntities($ids);
        $thread = $entities->first()->Thread;

        $router = $this->app->router('public');

        $phraseParams = [
            'userNameLink' => $handler->buildUrl($router->buildLink('full:members', $user), $user->username),
            'threadNameLink' => $handler->buildUrl($this->getUrlForInlineContent($thread), $thread->title),
            'count' => $entities->count(),
        ];

        switch ($this->actionType) {
            case 'delete':
                if ($this->app->request()->filter('hard_delete', 'bool')) {
                    $type = 'hard';
                    $phraseParams['threadNameLink'] = $thread->title;
                } else {
                    $type = 'soft';
                }

                $phraseParams['deleteType'] = \XF::phrase('th_notifier_' . $type . '_deleted');
                break;
        }

        return \XF::phrase($phraseName, $phraseParams);
    }

    /**
     * @return mixed|string
     */
    public function getUrlForContent()
    {
        return $this->app->router('public')->buildLink('full:posts', $this->content);
    }

    /**
     * @return mixed|string
     */
    public function getUrlForInlineContent($thread)
    {
        return $this->app->router('public')->buildLink('full:threads', $thread);
    }

    /**
     * @return string
     */
    protected function getEntityClass()
    {
        return Post::class;
    }

    /**
     * @return bool
     */
    protected function _canUseForContent()
    {
        /** @var Post $post */
        $post = $this->content;
        $thread = $post->Thread;
        $options = $this->action->options;

        if (!empty($options['content']['node_ids']) && !in_array(0,
                $options['content']['node_ids']) && !in_array($thread->node_id, $options['content']['node_ids'])) {
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