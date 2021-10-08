<?php

namespace ThemeHouse\Notifier\NotifierContent\XFRM;

use ThemeHouse\Notifier\Entity\Provider;
use XF\Entity\User;
use XFRM\Entity\ResourceUpdate;

/**
 * Class ResourceUpdateContent
 * @package ThemeHouse\Notifier\NotifierContent\XFRM
 *
 * @property ResourceUpdate content
 */
class ResourceUpdateContent extends AbstractResourceManagerContent
{
    /**
     * @return array|mixed
     */
    public function getActions()
    {
        return [
            'create' => \XF::phrase('xfrm_update_resource'),
            'delete' => \XF::phrase('delete'),
            'undelete' => \XF::phrase('undelete'),
        ];
    }

    /**
     * @param Provider $provider
     * @param User|null $user
     * @return mixed|\XF\Phrase
     */
    public function buildMessageForProvider(Provider $provider, User $user = null)
    {
        $phraseName = $this->getMessagePhraseName();
        $handler = $provider->handler;
        /** @var ResourceUpdate $resourceUpdate */
        $resourceUpdate = $this->content;
        $resource = $resourceUpdate->Resource;
        $router = $this->app->router('public');

        $resourceUserNameLink = $resource->username;
        if ($user) {
            $resourceUserNameLink = $handler->buildUrl($router->buildLink('full:members', $user), $user->username);
        }

        $phraseParams = [
            'userNameLink' => $resourceUserNameLink,
            'resourceUpdateNameLink' => $handler->buildUrl($this->getUrlForContent(), $resourceUpdate->title),
            'resourceNameLink' => $handler->buildUrl($router->buildLink('full:resources', $resource), $resource->title),
            'resourceUserNameLink' => $resourceUserNameLink,
        ];

        switch ($this->actionType) {
            case 'delete':
                if ($resourceUpdate->isDeleted()) {
                    $type = 'hard';
                    $phraseParams['resourceUpdateNameLink'] = $resourceUpdate->title;
                } else {
                    $type = 'soft';
                }

                $phraseParams['deleteType'] = \XF::phrase('th_notifier_' . $type . '_deleted');
                break;
        }

        return \XF::phrase($phraseName, $phraseParams);
    }

    /**
     * @return string
     */
    protected function getMessagePhraseName()
    {
        return 'th_notifier_action_xfrm_resource_update_' . $this->actionType;
    }

    /**
     * @return mixed|string
     */
    public function getUrlForContent()
    {
        return $this->app->router('public')->buildLink('full:resources/update', $this->content);
    }

    /**
     * @return mixed|string
     */
    protected function getEntityClass()
    {
        return ResourceUpdate::class;
    }

    /**
     * @return bool|mixed
     */
    protected function _canUseForContent()
    {
        /** @var \XFRM\Entity\ResourceItem $resource */
        $resource = $this->content->Resource;
        $options = $this->action->options;

        if (!empty($options['content']['resource_category_ids']) && !in_array(0,
                $options['content']['resource_category_ids']) && !in_array($resource->resource_category_id,
                $options['content']['resource_category_ids'])) {
            return false;
        }

        return true;
    }

}