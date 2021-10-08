<?php

namespace ThemeHouse\Notifier\Admin\Controller;

use XF\Admin\Controller\AbstractController;
use XF\Http\Request;
use XF\Mvc\FormAction;
use XF\Mvc\ParameterBag;

/**
 * Class Provider
 * @package ThemeHouse\Notifier\Admin\Controller
 */
class Provider extends AbstractController
{
    /**
     * @return \XF\Mvc\Reply\View
     */
    public function actionIndex()
    {
        $providers = $this->getProviderRepo()->findProvidersForList(false)->fetch();

        $activeProviders = $providers->filter(function (\ThemeHouse\Notifier\Entity\Provider $provider) {
            return $provider->isValid() === true;
        });
        $inactiveProviders = $providers->filter(function (\ThemeHouse\Notifier\Entity\Provider $provider) {
            return $provider->isValid() === false;
        });

        $viewParams = [
            'activeProviders' => $activeProviders,
            'inactiveProviders' => $inactiveProviders,
        ];

        return $this->view('ThemeHouse\Notifier:Provider\List', 'th_notifier_provider_list', $viewParams);
    }

    /**
     * @return \ThemeHouse\Notifier\Repository\Provider|\XF\Mvc\Entity\Repository
     */
    protected function getProviderRepo()
    {
        return $this->repository('ThemeHouse\Notifier:Provider');
    }

    /**
     * @param ParameterBag $params
     * @return \XF\Mvc\Reply\View
     * @throws \XF\Mvc\Reply\Exception
     */
    public function actionEdit(ParameterBag $params)
    {
        $provider = $this->assertProviderExists($params['provider_id']);
        return $this->providerAddEdit($provider);
    }

    /**
     * @param $id
     * @param $with
     * @param $phraseKey
     * @return \ThemeHouse\Notifier\Entity\Provider|\XF\Mvc\Entity\Entity
     * @throws \XF\Mvc\Reply\Exception
     */
    protected function assertProviderExists($id, $with = null, $phraseKey = null)
    {
        return $this->assertRecordExists('ThemeHouse\Notifier:Provider', $id, $with, $phraseKey);
    }

    /**
     * @param \ThemeHouse\Notifier\Entity\Provider $provider
     * @return \XF\Mvc\Reply\View
     */
    protected function providerAddEdit(\ThemeHouse\Notifier\Entity\Provider $provider)
    {
        $viewParams = [
            'provider' => $provider,
        ];

        return $this->view('ThemeHouse\Notifier:Provider\Edit', 'th_notifier_provider_edit', $viewParams);
    }

    /**
     * @param ParameterBag $params
     * @return \XF\Mvc\Reply\Redirect
     * @throws \XF\Mvc\Reply\Exception
     * @throws \XF\PrintableException
     */
    public function actionSave(ParameterBag $params)
    {
        $this->assertPostOnly();

        $provider = $this->assertProviderExists($params['provider_id']);

        $this->providerSaveProcess($provider)->run();

        return $this->redirect($this->buildLink('th-notifier/providers') . $this->buildLinkHash($provider->provider_id));
    }

    /**
     * @param \ThemeHouse\Notifier\Entity\Provider $provider
     * @return FormAction
     */
    protected function providerSaveProcess(\ThemeHouse\Notifier\Entity\Provider $provider)
    {
        $form = $this->formAction();

        $input = $this->filter([
            'active' => 'bool',
        ]);

        $form->validate(function (FormAction $form) use ($provider) {
            $options = $this->filter('options', 'array');

            $request = new Request($this->app->inputFilterer(), $options, [], []);

            $provider->options = $options;

            $handler = $provider->getHandler();
            if ($handler && !$handler->verifyOptions($request, $options, $error)) {
                $form->logError($error);
            }
            $provider->options = $options;
        });

        $form->basicEntitySave($provider, $input);

        return $form;
    }

    /**
     * @param $action
     * @param ParameterBag $params
     * @throws \XF\Mvc\Reply\Exception
     */
    protected function preDispatchController($action, ParameterBag $params)
    {
        $this->assertAdminPermission('th_notifier_manageProv');
    }


}
