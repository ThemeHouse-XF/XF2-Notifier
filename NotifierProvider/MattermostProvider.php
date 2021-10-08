<?php

namespace ThemeHouse\Notifier\NotifierProvider;

use Pnz\MattermostClient\ApiClient;
use Pnz\MattermostClient\Exception\Domain\MissingAccessTokenException;
use Pnz\MattermostClient\HttpClientConfigurator;
use ThemeHouse\Notifier\Entity\Action;
use XF\Http\Request;
use XF\Phrase;

/**
 * Class MattermostProvider
 * @package ThemeHouse\Notifier\NotifierProvider
 */
class MattermostProvider extends AbstractProvider
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
        return \XF::app()->templater()->renderTemplate('admin:th_notifier_provider_edit_mattermost', $params);
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
            $error = \XF::phrase('th_notifier_must_specify_mattermost_access_token');
            return false;
        }

        if ($save) {
            $client = $this->getApiClient();

            try {
                 $client->teams()->getTeams();
            } /** @noinspection PhpRedundantCatchClauseInspection */ catch (MissingAccessTokenException $e) {
                $error = \XF::phrase('th_notifier_specified_token_not_mattermost_personal_access_token');
                return false;
            } catch (\Exception $e) {
                $error = \XF::phrase('th_notifier_specified_base_url_is_invalid');
                return false;
            }
        }

        return true;
    }

    /**
     * @param Action|null $action
     * @return bool|string
     */
    public function renderActionOptions(Action $action = null)
    {
        $teams = $this->getApiClient()->teams()->getTeams();

        $teamOptions = [];
        $channelOptions = [];
        $currentTeamChannelOptions = [];

        /** @var \Pnz\MattermostClient\Model\Team\Team $team */
        foreach ($teams as $team) {
            $teamOptions[$team->getId()] = $team->getDisplayName();

            $channels = $this->getApiClient()->teams()->getTeamPublicChannels($team->getId());

            $teamChannelOptions = [];

            /** @var \Pnz\MattermostClient\Model\Channel\Channel $channel */
            foreach ($channels as $channel) {
                $teamChannelOptions[$channel->getId()] = $channel->getDisplayName();
            }

            $channelOptions[$team->getId()] = $teamChannelOptions;

            if (isset($action->options['mattermost']['team']) && $action->options['mattermost']['team'] == $team->getId()) {
                $currentTeamChannelOptions = $teamChannelOptions;
            }
        }

        $channelOptionsForJson = [];

        foreach ($channelOptions as $teamId => $channels) {
            $channelOptionsForJson[$teamId] = [];

            foreach ($channels as $value => $label) {
                $channelOptionsForJson[$teamId][] = [
                    'value' => $value,
                    'label' => $label,
                ];
            }
        }

        $params = [
            'action' => $action,
            'teamOptions' => $teamOptions,
            'channelOptions' => $channelOptions,
            'channelOptionsJson' => json_encode($channelOptionsForJson),
            'currentTeamChannelOptions' => $currentTeamChannelOptions,
        ];

        return $this->app->templater()->renderTemplate('admin:th_notifier_action_edit_mattermost', $params);
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
        if (empty($options['mattermost']['channel'])) {
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

        $this->getApiClient()->posts()->createPost([
            'channel_id' => $channel,
            'message' => $message,
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

        return '[' . $text . '](' . $url . ')';
    }

    /**
     * @return ApiClient
     */
    protected function getApiClient()
    {
        if (!$this->apiClient) {
            $endpoint = $this->provider->options['base_url'] . '/api/v4';

            $configurator = (new HttpClientConfigurator())
                ->setEndpoint($endpoint)
                ->setToken($this->provider->options['api_key']);

            $this->apiClient = ApiClient::configure($configurator);
        }

        return $this->apiClient;
    }

}