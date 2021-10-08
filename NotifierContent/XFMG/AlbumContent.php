<?php

namespace ThemeHouse\Notifier\NotifierContent\XFMG;

use ThemeHouse\Notifier\Entity\Provider;
use XF\Entity\User;
use XFMG\Entity\Album;

/**
 * Class AlbumContent
 * @package ThemeHouse\Notifier\NotifierContent\XFMG
 */
class AlbumContent extends AbstractMediaGalleryContent
{
    /**
     * @return array|mixed
     */
    public function getActions()
    {
        return [
            'create' => \XF::phrase('thnotifier_create'),
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
        /** @var Album $album */
        $album = $this->content;
        $router = $this->app->router('public');

        $albumUserNameLink = $album->username;
        if ($album->User) {
            $albumUserNameLink = $handler->buildUrl($router->buildLink('full:members', $album), $album->username);
        }

        $phraseParams = [
            'userNameLink' => $handler->buildUrl($router->buildLink('full:members', $user), $user->username),
            'albumNameLink' => $handler->buildUrl($this->getUrlForContent(), $album->title),
            'albumUserNameLink' => $albumUserNameLink,
        ];

        switch ($this->actionType) {
            case 'delete':
                if ($album->isDeleted()) {
                    $type = 'hard';
                    $phraseParams['albumNameLink'] = $album->title;
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
        return $this->app->router('public')->buildLink('full:media/albums', $this->content);
    }

    /**
     * @return mixed|string
     */
    protected function getEntityClass()
    {
        return Album::class;
    }

    /**
     * @return bool|mixed
     */
    protected function _canUseForContent()
    {
        /** @var Album $album */
        $album = $this->content;
        $options = $this->action->options;

        if (!empty($options['content']['category_ids']) && !in_array(0,
                $options['content']['category_ids']) && !in_array($album->category_id,
                $options['content']['category_ids'])) {
            return false;
        }

        return true;
    }
}