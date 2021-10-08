<?php

namespace ThemeHouse\Notifier\Admin\Controller;

use XF\Admin\Controller\AbstractController;

/**
 * Class Notifier
 * @package ThemeHouse\Notifier\Admin\Controller
 */
class Notifier extends AbstractController
{
    /**
     * @return \XF\Mvc\Reply\View
     */
    public function actionIndex()
    {
        return $this->view('ThemeHouse\Notifier:Notifier\Index', 'th_notifier_index');
    }

    /**
     * @return \XF\Mvc\Reply\Reroute|\XF\Mvc\Reply\View
     * @throws \XF\Mvc\Reply\Exception
     */
    public function actionSend()
    {
        $this->assertAdminPermission('th_notifier_sendNotif');
        $this->setSectionContext('th_notifier_send');

        if ($this->isPost()) {
            return $this->rerouteController(__CLASS__, 'do-send');
        }

        $providers = $this->getProviderRepo()->findProvidersForList()->fetch();

        $viewParams = [
            'providers' => $providers,
        ];

        return $this->view('ThemeHouse\Notifier:Notifier\Send', 'th_notifier_send', $viewParams);
    }

    /**
     * @return \ThemeHouse\Notifier\Repository\Provider|\XF\Mvc\Entity\Repository
     */
    protected function getProviderRepo()
    {
        return $this->repository('ThemeHouse\Notifier:Provider');
    }

    /**
     * @return \XF\Mvc\Reply\Redirect
     * @throws \XF\Mvc\Reply\Exception
     * @throws \Exception
     */
    public function actionDoSend()
    {
        $input = $this->filter([
            'message' => 'str',
            'provider_id' => 'str',

            'options' => 'array',
        ]);

        $provider = $this->assertProviderExists($input['provider_id']);

        $handler = $provider->getHandler();

        $handler->sendMessage($input['message'], $input['options'][$provider->provider_id]);

        return $this->redirect($this->buildLink('th-notifier/send'),
            \XF::phrase('th_notifier_message_sent_successfully'));
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
     * @return \ThemeHouse\Notifier\Repository\Action|\XF\Mvc\Entity\Repository
     */
    protected function getActionRepo()
    {
        return $this->repository('ThemeHouse\Notifier:Action');
    }
}
