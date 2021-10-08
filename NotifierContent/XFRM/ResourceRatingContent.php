<?php

namespace ThemeHouse\Notifier\NotifierContent\XFRM;

use ThemeHouse\Notifier\Entity\Provider;
use XF\Entity\User;
use XFRM\Entity\ResourceRating;

/**
 * Class ResourceRatingContent
 * @package ThemeHouse\Notifier\NotifierContent\XFRM
 *
 * @property ResourceRating content
 */
class ResourceRatingContent extends AbstractResourceManagerContent
{
    /**
     * @return array|mixed
     */
    public function getActions()
    {
        return [
            'create' => \XF::phrase('thnotifier_leave_a_rating'),
            'delete' => \XF::phrase('delete'),
            'undelete' => \XF::phrase('undelete'),
            'reply' => \XF::phrase('reply'),
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
        /** @var ResourceRating $resourceRating */
        $resourceRating = $this->content;
        $resource = $resourceRating->Resource;
        $router = $this->app->router('public');

        $resourceUserNameLink = $handler->buildUrl($router->buildLink('full:members', $resourceRating->User),
            $resourceRating->User->username);

        $userNameLink = $handler->buildUrl($router->buildLink('full:members', $user), $user->username);

        $phraseParams = [
            'userNameLink' => $userNameLink,
            'resourceRatingNameLink' => $handler->buildUrl($this->getUrlForContent(), \XF::phrase('x_stars', [
                'rating' => $resourceRating->rating,
            ])),
            'resourceNameLink' => $handler->buildUrl($router->buildLink('full:resources', $resource), $resource->title),
            'resourceRatingUsernameLink' => $resourceUserNameLink,
        ];

        switch ($this->actionType) {
            case 'delete':
                if ($resourceRating->isDeleted()) {
                    $type = 'hard';
                    $phraseParams['resourceRatingNameLink'] = \XF::phrase('x_stars', [
                        'rating' => $resourceRating->rating,
                    ]);
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
        return 'th_notifier_action_xfrm_resource_rating_' . $this->actionType;
    }

    /**
     * @return mixed|string
     */
    public function getUrlForContent()
    {
        return $this->app->router('public')->buildLink('full:resources/review', $this->content);
    }

    /**
     * @return mixed|string
     */
    protected function getEntityClass()
    {
        return ResourceRating::class;
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