<?php

namespace ThemeHouse\Notifier\NotifierProvider;

use ThemeHouse\Notifier\Entity\Action;
use ThemeHouse\Notifier\Entity\Provider;
use XF\App;
use XF\Http\Request;

/**
 * Class AbstractProvider
 * @package ThemeHouse\Notifier\NotifierProvider
 */
abstract class AbstractProvider
{
    /**
     * @var App
     */
    protected $app;
    /**
     * @var Provider
     */
    protected $provider;

    /**
     * AbstractProvider constructor.
     * @param App $app
     * @param Provider $provider
     */
    public function __construct(App $app, Provider $provider)
    {
        $this->app = $app;
        $this->provider = $provider;
    }

    /**
     * @return bool
     */
    public function isUsable()
    {
        return true;
    }

    /**
     * @return string
     */
    public function renderOptions()
    {
        return '';
    }

    /**
     * @param array $options
     * @param null $error
     * @return bool
     */
    public function verifyOptionsValue(array $options, &$error = null)
    {
        $optionsRequest = new Request(
            \XF::app()->inputFilterer(), $options
        );
        return $this->verifyOptions($optionsRequest, $options, $error, false);
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
        return true;
    }

    /**
     * @param Action|null $action
     * @return bool
     */
    public function renderActionOptions(Action $action = null)
    {
        return true;
    }

    /**
     * @param Action $action
     * @param array $options
     * @param null $error
     * @return bool
     */
    public function verifyActionOptionsValue(Action $action, array $options, &$error = null)
    {
        $optionsRequest = new Request(
            \XF::app()->inputFilterer(), $options
        );
        return $this->verifyActionOptions($action, $optionsRequest, $options, $error, false);
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
        return true;
    }

    /**
     * @return Provider
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @return \XF\Phrase
     */
    public function getTitle()
    {
        return \XF::phrase('th_notifier_provider_title.' . $this->provider->provider_id);
    }

    /**
     * @param $message
     * @param array $options
     * @return mixed
     */
    public abstract function sendMessage($message, array $options = []);

    /**
     * @param $url
     * @param null $text
     * @return mixed
     */
    public abstract function buildUrl($url, $text = null);
}