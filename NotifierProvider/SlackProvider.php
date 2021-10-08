<?php

namespace ThemeHouse\Notifier\NotifierProvider;

use JoliCode\Slack\Api\Model\ObjsChannel;
use JoliCode\Slack\ClientFactory;
use ThemeHouse\Notifier\Entity\Action;
use XF\Http\Request;
use XF\Phrase;

/**
 * Class SlackProvider
 * @package ThemeHouse\Notifier\NotifierProvider
 */
class SlackProvider extends AbstractProvider
{
    /**
     * @var
     */
    protected $apiClient;

    /**
     * @return string
     */
    public function renderOptions()
    {
        $params = [
            'provider' => $this->provider
        ];
        return \XF::app()->templater()->renderTemplate('admin:th_notifier_provider_edit_slack', $params);
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
        if (empty($options['api_key'])) {
            $error = \XF::phrase('th_notifier_must_specify_slack_api_token');
            return false;
        }

        if (strpos($options['api_key'], 'xoxb-') !== 0) {
            $error = \XF::phrase('th_notifier_specified_token_not_slack_bot_token');
            return false;
        }

        if ($save) {
            $client = $this->getApiClient();
            $auth = $client->authTest()->getOk();

            if (!$auth) {
                $error = \XF::phrase('th_notifier_specified_token_not_slack_bot_token');
                return false;
            }
        }

        return true;
    }

    /**
     * @return \JoliCode\Slack\Api\Client
     */
    protected function getApiClient()
    {
        if (!$this->apiClient) {
            $this->apiClient = ClientFactory::create($this->provider->options['api_key']);
        }

        return $this->apiClient;
    }

    /**
     * @param Action|null $action
     * @return bool|string
     */
    public function renderActionOptions(Action $action = null)
    {
        $channels = $this->getApiClient()->conversationsList();

        $channels = $channels->getChannels();

        $channelOptions = [];

        foreach ($channels as $channel) {
            /** @var ObjsChannel $channel */
            $channelOptions[$channel->id] = $channel->name;
        }

        $params = [
            'action' => $action,
            'channelOptions' => $channelOptions,
        ];

        return $this->app->templater()->renderTemplate('admin:th_notifier_action_edit_slack', $params);
    }

    /**
     * @param Action $action
     * @param Request $request
     * @param array $options
     * @param null $error
     * @param bool $save
     * @return bool
     */
    public function verifyActionOptions(
        Action $action,
        Request $request,
        array &$options,
        &$error = null,
        $save = true
    ) {
        if (empty($options['slack']['channel'])) {
            $error = \XF::phrase('th_notifier_you_must_select_a_valid_slack_channel');
            return false;
        }

        return true;
    }

    /**
     * @param $message
     * @param array $options
     * @return mixed|void
     */
    public function sendMessage($message, array $options = [])
    {
        if(!$this->provider->active) {
            return;
        }

        if ($message instanceof Phrase) {
            $message = $message->render('raw');
        }

        $channel = $options['channel'];

        $this->getApiClient()->chatPostMessage([
            'channel' => $channel,
            'text' => $message,
        ]);
    }

    /**
     * @param $url
     * @param null $text
     * @return mixed|string
     */
    public function buildUrl($url, $text = null)
    {
        if (!$text) {
            $text = $url;
        }

        return '<' . $url . '|' . $text . '>';
    }

}