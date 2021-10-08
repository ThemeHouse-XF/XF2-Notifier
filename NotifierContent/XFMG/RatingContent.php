<?php

namespace ThemeHouse\Notifier\NotifierContent\XFMG;

use ThemeHouse\Notifier\Entity\Provider;
use XF\Entity\User;
use XFMG\Entity\Rating;

/**
 * Class RatingContent
 * @package ThemeHouse\Notifier\NotifierContent\XFMG
 *
 * @property Rating content
 */
class RatingContent extends AbstractMediaGalleryContent
{
    /**
     * @return array|mixed
     */
    public function getActions()
    {
        return [
            'create' => \XF::phrase('xfmg_leave_a_rating'),
            #'delete' => \XF::phrase('delete'),
            #'undelete' => \XF::phrase('undelete'),
        ];
    }

    /**
     * @return mixed|string
     */
    public function getUrlForContent()
    {
        return $this->app->router('public')->buildLink('full:media/review', $this->content);
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
        /** @var Rating $rating */
        $rating = $this->content;
        $media = $rating->Media;
        $album = $rating->Album;
        $router = $this->app->router('public');

        if ($media) {
            $mediaNameLink = $handler->buildUrl($router->buildLink('full:media', $media), $media->title);
        } else {
            $mediaNameLink = $handler->buildUrl($router->buildLink('full:media/albums', $album), $album->title);
        }

        $ratingUsernameLink = $handler->buildUrl($router->buildLink('full:members', $rating->User),
            $rating->User->username);

        $userNameLink = $handler->buildUrl($router->buildLink('full:members', $user), $user->username);

        $phraseParams = [
            'userNameLink' => $userNameLink,
            'ratingNameLink' => $handler->buildUrl($this->getUrlForContent(), \XF::phrase('x_stars', [
                'rating' => $rating->rating,
            ])),
            'mediaNameLink' => $mediaNameLink,
            'ratingUsernameLink' => $ratingUsernameLink,
        ];

        switch ($this->actionType) {
            case 'delete':
                if ($rating->isDeleted()) {
                    $type = 'hard';
                    $phraseParams['ratingNameLink'] = \XF::phrase('x_stars', [
                        'rating' => $rating->rating,
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
     * @return mixed|string
     */
    protected function getEntityClass()
    {
        return Rating::class;
    }

    /**
     * @return bool|mixed
     */
    protected function _canUseForContent()
    {
        /** @var \XFMG\Entity\MediaItem $media */
        $media = $this->content->Media;
        $album = $this->content->Album;
        $options = $this->action->options;

        if ($media) {
            $categoryId = $media->category_id;
        } else {
            $categoryId = $album->category_id;
        }

        if (!empty($options['content']['category_ids']) && !in_array(0,
                $options['content']['category_ids']) && !in_array($categoryId, $options['content']['category_ids'])) {
            return false;
        }

        return true;
    }

}