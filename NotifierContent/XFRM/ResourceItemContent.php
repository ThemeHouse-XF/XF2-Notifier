<?php

namespace ThemeHouse\Notifier\NotifierContent\XFRM;

use ThemeHouse\Notifier\Entity\Provider;
use XF\Entity\User;
use XFRM\Entity\ResourceItem;

/**
 * Class ResourceItemContent
 * @package ThemeHouse\Notifier\NotifierContent\XFRM
 */
class ResourceItemContent extends AbstractResourceManagerContent
{
    /**
     * @return array|mixed
     */
    public function getActions()
    {
        return [
            'create' => \XF::phrase('xfrm_add_resource'),
            'delete' => \XF::phrase('delete'),
            'undelete' => \XF::phrase('undelete'),
            'feature' => \XF::phrase('xfrm_resource_quick_feature'),
            'unfeature' => \XF::phrase('xfrm_resource_quick_unfeature'),
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
        /** @var ResourceItem $resource */
        $resource = $this->content;
        $router = $this->app->router('public');

        $resourceUserNameLink = $resource->username;
        if ($resource->User) {
            $resourceUserNameLink = $handler->buildUrl($router->buildLink('full:members', $resource),
                $resource->username);
        }

        $phraseParams = [
            'userNameLink' => $handler->buildUrl($router->buildLink('full:members', $user), $user->username),
            'resourceNameLink' => $handler->buildUrl($this->getUrlForContent(), $resource->title),
            'resourceUserNameLink' => $resourceUserNameLink,
        ];

        switch ($this->actionType) {
            case 'create':
                $userNameLink = $resource->username;
                if ($user) {
                    $userNameLink = $handler->buildUrl($router->buildLink('full:members', $resource),
                        $resource->username);
                }
                $phraseParams['userNameLink'] = $userNameLink;
                break;
            case 'delete':
                if ($resource->isDeleted()) {
                    $type = 'hard';
                    $phraseParams['resourceNameLink'] = $resource->title;
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
        return 'th_notifier_action_xfrm_resource_' . $this->actionType;
    }

    /**
     * @return mixed|string
     */
    public function getUrlForContent()
    {
        return $this->app->router('public')->buildLink('full:resources', $this->content);
    }

    /**
     * @return bool
     */
    protected function showAdditionalResourceCriteria()
    {
        return false;
    }

    /**
     * @return mixed|string
     */
    protected function getEntityClass()
    {
        return ResourceItem::class;
    }

    /**
     * @return bool|mixed
     */
    protected function _canUseForContent()
    {
        /** @var ResourceItem $resource */
        $resource = $this->content;
        $options = $this->action->options;

        if (!empty($options['content']['resource_category_ids']) && !in_array(0,
                $options['content']['resource_category_ids']) && !in_array($resource->resource_category_id,
                $options['content']['resource_category_ids'])) {
            return false;
        }

        return true;
    }

}