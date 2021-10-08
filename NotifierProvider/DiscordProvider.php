<?php

namespace ThemeHouse\Notifier\NotifierProvider;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use ThemeHouse\Notifier\Entity\Action;
use XF\Http\Request;

/**
 * Class DiscordProvider
 * @package ThemeHouse\Notifier\NotifierProvider
 */
class DiscordProvider extends AbstractProvider
{
    /**
     * @var
     */
    protected $apiClient;
    /**
     * @var array
     */
    protected $urls = [];

    /**
     * @return string
     */
    public function renderOptions()
    {
        $params = [
            'provider' => $this->provider
        ];
        return \XF::app()->templater()->renderTemplate('admin:th_notifier_provider_edit_discord', $params);
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
        if (!strlen($options['bot_token'])) {
            $error = \XF::phrase('th_notifier_no_bot_token_specified');
            return false;
        }

        try {
            $this->provider->options = $options;
            $this->apiGet('users/@me');
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (GuzzleException $e) {
            $error = \XF::phrase('th_notifier_invalid_bot_token');
            return false;
        }

        return true;
    }

    /**
     * @param $endpoint
     * @return array|mixed|object
     */
    protected function apiGet($endpoint)
    {
        $client = $this->getApiClient();
        $response = $client->request('GET', $endpoint);
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @return Client
     */
    protected function getApiClient()
    {
        if (!$this->apiClient) {
            $this->apiClient = new Client([
                'base_uri' => 'https://discordapp.com/api/v6',
                'headers' => [
                    'Authorization' => 'Bot ' . $this->provider->options['bot_token'],
                    'Content-Type' => 'application/json',
                ],
            ]);

        }

        return $this->apiClient;
    }

    /**
     * @param $message
     * @param array $options
     */
    public function sendMessage($message, array $options = [])
    {
        if(!$this->provider->active) {
            return;
        }

        if (!$options['channel']) {
            return;
        }

        $this->apiPost('channels/' . $options['channel'] . '/messages', [
            [
                'name' => 'content',
                'contents' => $message
            ]
        ]);
    }

    /**
     * @param $endpoint
     * @param array $data
     * @return array|mixed|object
     */
    protected function apiPost($endpoint, $data = [])
    {
        $client = $this->getApiClient();
        $response = $client->request('POST', $endpoint, [
            'multipart' => $data
        ]);
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @param $url
     * @param null $text
     * @return mixed|string
     */
    public function buildUrl($url, $text = null)
    {
        return $text . ' (' . $url . ') ';
    }

    /**
     * @param Action|null $action
     * @return bool|string
     */
    public function renderActionOptions(Action $action = null)
    {
        $guilds = [];
        foreach ($this->apiGet('users/@me/guilds') as $guild) {
            $guilds[$guild['id']] = array_merge($guild, [
                'channels' => $this->apiGet('guilds/' . $guild['id'] . '/channels')
            ]);
        }

        $params = [
            'action' => $action,
            'channelOptions' => $guilds,
        ];

        return $this->app->templater()->renderTemplate('admin:th_notifier_action_edit_discord', $params);
    }
}