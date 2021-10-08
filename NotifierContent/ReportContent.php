<?php

namespace ThemeHouse\Notifier\NotifierContent;

use ThemeHouse\Notifier\Entity\Provider;
use XF\Entity\Report;
use XF\Entity\User;
use XF\Http\Request;

/**
 * Class ReportContent
 * @package ThemeHouse\Notifier\NotifierContent
 */
class ReportContent extends AbstractNotifierContent
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
        return $this->app->templater()->renderMacro('th_notifier_action_edit_report', 'tabs');
    }

    /**
     * @return string
     */
    public function renderActionTabPanes()
    {
        return $this->app->templater()->renderTemplate('admin:th_notifier_action_edit_report', [
            'action' => $this->action,
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
        if (empty($options['content']['content_types']) || in_array(0, $options['content']['content_types'])) {
            $options['content']['content_types'] = [0];
        }

        return true;
    }

    /**
     * @return array
     */
    public function getActions()
    {
        return [
            'create' => \XF::phrase('th_notifier_create_report'),
            'resolve' => \XF::phrase('th_notifier_resolve_report'),
            'reject' => \XF::phrase('th_notifier_reject_report'),
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
        /** @var Report $report */
        $report = $this->content;
        $router = $this->app->router('public');

        $phraseParams = [
            'userNameLink' => $handler->buildUrl($router->buildLink('full:members', $user), $user->username),
            'reportNameLink' => $handler->buildUrl($this->getUrlForContent(), $report->title),
        ];

        return \XF::phrase($phraseName, $phraseParams);
    }

    /**
     * @return mixed|string
     */
    public function getUrlForContent()
    {
        return $this->app->router('public')->buildLink('full:reports', $this->content);
    }

    /**
     * @return string
     */
    protected function getEntityClass()
    {
        return Report::class;
    }

    /**
     * @return bool
     */
    protected function _canUseForContent()
    {
        /** @var Report $report */
        $report = $this->content;
        $options = $this->action->options;

        if (!empty($options['content']['content_types']) && !in_array(0,
                $options['content']['content_types']) && !in_array($report->content_type,
                $options['content']['node_ids'])) {
            return false;
        }

        return true;
    }

}