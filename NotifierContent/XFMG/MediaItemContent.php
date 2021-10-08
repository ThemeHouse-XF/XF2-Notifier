<?php

namespace ThemeHouse\Notifier\NotifierContent\XFMG;

use ThemeHouse\Notifier\Entity\Provider;
use XF\Entity\User;
use XFMG\Entity\MediaItem;

/**
 * Class MediaItemContent
 * @package ThemeHouse\Notifier\NotifierContent\XFMG
 */
class MediaItemContent extends AbstractMediaGalleryContent
{
    /**
     * @return array|mixed
     */
    public function getActions()
    {
        return [
            'create' => \XF::phrase('xfmg_add_media'),
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
        /** @var MediaItem $media */
        $media = $this->content;
        $router = $this->app->router('public');

        $mediaUserNameLink = $media->username;
        if ($media->User) {
            $mediaUserNameLink = $handler->buildUrl($router->buildLink('full:members', $media), $media->username);
        }

        $phraseParams = [
            'userNameLink' => $handler->buildUrl($router->buildLink('full:members', $user), $user->username),
            'mediaNameLink' => $handler->buildUrl($this->getUrlForContent(), $media->title),
            'mediaUserNameLink' => $mediaUserNameLink,
        ];

        switch ($this->actionType) {
            case 'delete':
                if ($media->isDeleted()) {
                    $type = 'hard';
                    $phraseParams['mediaNameLink'] = $media->title;
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
        return $this->app->router('public')->buildLink('full:media', $this->content);
    }

    /**
     * @return bool
     */
    protected function showAdditionalMediaCriteria()
    {
        return false;
    }

    /**
     * @return mixed|string
     */
    protected function getEntityClass()
    {
        return MediaItem::class;
    }

    /**
     * @return bool|mixed
     */
    protected function _canUseForContent()
    {
        /** @var MediaItem $media */
        $media = $this->content;
        $options = $this->action->options;

        if (!empty($options['content']['category_ids']) && !in_array(0,
                $options['content']['category_ids']) && !in_array($media->category_id,
                $options['content']['category_ids'])) {
            return false;
        }

        return true;
    }

}