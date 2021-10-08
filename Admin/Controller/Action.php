<?php

namespace ThemeHouse\Notifier\Admin\Controller;

use XF\Admin\Controller\AbstractController;
use XF\Http\Request;
use XF\Mvc\FormAction;
use XF\Mvc\ParameterBag;

/**
 * Class Action
 * @package ThemeHouse\Notifier\Admin\Controller
 */
class Action extends AbstractController
{
    /**
     * @return \XF\Mvc\Reply\View
     */
    public function actionIndex()
    {
        $actions = $this->em()->getFinder('ThemeHouse\Notifier:Action')->fetch();
        $groupedActions = $actions->groupBy('content_type');

        $contentTypes = [];
        foreach ($groupedActions as $contentType => $items) {
            $contentTypes[$contentType] = $this->app()->getContentTypePhrase($contentType);
        }

        $viewParams = [
            'groupedActions' => $groupedActions,
            'contentTypes' => $contentTypes,
        ];

        return $this->view('ThemeHouse\Notifier:Action\List', 'th_notifier_action_list', $viewParams);
    }

    /**
     * @return \XF\Mvc\Reply\Message
     */
    public function actionToggle()
    {
        /** @var \XF\ControllerPlugin\Toggle $plugin */
        $plugin = $this->plugin('XF:Toggle');
        return $plugin->actionToggle('ThemeHouse\Notifier:Action');
    }

    /**
     * @param ParameterBag $params
     * @return \XF\Mvc\Reply\Error|\XF\Mvc\Reply\Redirect|\XF\Mvc\Reply\View
     * @throws \XF\Mvc\Reply\Exception
     */
    public function actionDelete(ParameterBag $params)
    {
        $action = $this->assertActionExists($params['action_id']);

        /** @var \XF\ControllerPlugin\Delete $plugin */
        $plugin = $this->plugin('XF:Delete');
        return $plugin->actionDelete(
            $action,
            $this->buildLink('th-notifier/actions/delete', $action),
            $this->buildLink('th-notifier/actions/edit', $action),
            $this->buildLink('th-notifier/actions'),
            $action->title
        );
    }

    /**
     * @param $id
     * @param $with
     * @param $phraseKey
     * @return \ThemeHouse\Notifier\Entity\Action|\XF\Mvc\Entity\Entity
     * @throws \XF\Mvc\Reply\Exception
     */
    protected function assertActionExists($id, $with = null, $phraseKey = null)
    {
        return $this->assertRecordExists('ThemeHouse\Notifier:Action', $id, $with, $phraseKey);
    }

    /**
     * @param ParameterBag $params
     * @return \XF\Mvc\Reply\View
     * @throws \XF\Mvc\Reply\Exception
     */
    public function actionEdit(ParameterBag $params)
    {
        $action = $this->assertActionExists($params['action_id']);

        return $this->actionAddEdit($action);
    }

    /**
     * @param \ThemeHouse\Notifier\Entity\Action $action
     * @return \XF\Mvc\Reply\View
     */
    protected function actionAddEdit(\ThemeHouse\Notifier\Entity\Action $action)
    {
        $userCriteria = $this->app->criteria('XF:User', $action->user_criteria);

        $providers = $this->getProviderRepo()->findProvidersForList()->fetch();

        $viewParams = [
            'userCriteria' => $userCriteria,
            'action' => $action,
            'providers' => $providers,
        ];

        return $this->view('ThemeHouse\Notifier:Action\Edit', 'th_notifier_action_edit', $viewParams);
    }

    /**
     * @return \XF\Mvc\Reply\Error|\XF\Mvc\Reply\Redirect|\XF\Mvc\Reply\View
     * @throws \Exception
     */
    public function actionAdd()
    {
        $contentType = $this->filter('content_type', 'string');

        if (!$contentType && !$this->isPost()) {
            $viewParams = [
                'contentTypeOptions' => $this->getActionRepo()->getAvailableContentTypeOptions(),
            ];

            return $this->view('ThemeHouse\Notifier:Action\SelectContentType', 'th_notifier_action_add', $viewParams);
        }

        if ($this->isPost() && $contentType) {
            return $this->redirect($this->buildLink('th-notifier/actions/add', null, ['content_type' => $contentType]));
        }

        if (!$this->getActionRepo()->isContentTypeValid($contentType)) {
            return $this->error(\XF::phrase('th_notifier_invalid_content_type_selected'));
        }

        /** @var \ThemeHouse\Notifier\Entity\Action $action */
        $action = $this->em()->create('ThemeHouse\Notifier:Action');
        $action->content_type = $contentType;

        return $this->actionAddEdit($action);
    }

    /**
     * @return \ThemeHouse\Notifier\Repository\Action|\XF\Mvc\Entity\Repository
     */
    protected function getActionRepo()
    {
        return $this->repository('ThemeHouse\Notifier:Action');
    }

    /**
     * @param ParameterBag $params
     * @return \XF\Mvc\Reply\Error|\XF\Mvc\Reply\Redirect
     * @throws \XF\Mvc\Reply\Exception
     * @throws \XF\PrintableException
     * @throws \Exception
     */
    public function actionSave(ParameterBag $params)
    {
        if ($params['action_id']) {
            $action = $this->assertActionExists($params['action_id']);
        } else {
            $contentType = $this->filter('content_type', 'str');

            if (!$this->getActionRepo()->isContentTypeValid($contentType)) {
                return $this->error(\XF::phrase('th_notifier_invalid_content_type_selected'));
            }

            /** @var \ThemeHouse\Notifier\Entity\Action $action */
            $action = $this->em()->create('ThemeHouse\Notifier:Action');
            $action->content_type = $contentType;
        }

        $this->actionSaveProcess($action)->run();

        return $this->redirect($this->buildLink('th-notifier/actions') . $this->buildLinkHash($action->action_id));
    }

    /**
     * @param \ThemeHouse\Notifier\Entity\Action $action
     * @return FormAction
     */
    protected function actionSaveProcess(\ThemeHouse\Notifier\Entity\Action $action)
    {
        $form = $this->formAction();

        $input = $this->filter([
            'title' => 'str',
            'provider_ids' => 'array',
            'actions' => 'array',
            'user_criteria' => 'array',
        ]);

        $form->validate(function (FormAction $form) use ($input, $action) {
            $options = $this->filter('options', 'array');
            if (empty($options['content'])) {
                $options['content'] = [];
            }

            $request = new Request($this->app->inputFilterer(), $options, [], []);

            $handler = $action->getContentHandler();
            if ($handler && !$handler->verifyOptions($request, $options, $error)) {
                $form->logError($error);
            }

            foreach ($input['provider_ids'] as $providerId) {
                $handler = $action->getProviderHandler($providerId);
                if ($handler && !$handler->verifyActionOptions($action, $request, $options, $error)) {
                    $form->logError($error);
                }
            }

            $action->options = $options;
        });

        $form->basicEntitySave($action, $input);

        return $form;
    }

    /**
     * @param $action
     * @param ParameterBag $params
     * @throws \XF\Mvc\Reply\Exception
     */
    protected function preDispatchController($action, ParameterBag $params)
    {
        $providers = $this->getProviderRepo()->findProvidersForList()->fetch();
        if (!$providers->count()) {
            throw $this->exception($this->notFound(\XF::phrase('th_notifier_no_providers_are_configured_yet')));
        }
    }

    /**
     * @return \ThemeHouse\Notifier\Repository\Provider|\XF\Mvc\Entity\Repository
     */
    protected function getProviderRepo()
    {
        return $this->repository('ThemeHouse\Notifier:Provider');
    }

}
