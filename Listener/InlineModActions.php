<?php

namespace ThemeHouse\Notifier\Listener;

use ThemeHouse\Notifier\Action;

/**
 * Class InlineModActions
 * @package ThemeHouse\Notifier\Listener
 */
class InlineModActions
{
    public static function inlineModActions(\XF\InlineMod\AbstractHandler $handler, \XF\App $app, array &$actions)
    {
        if (!$app->request()->isPost()) {
            return;
        }

        if ($handler->getContentType() == 'post') {

            if ($app->request()->filter('action', 'str') == 'delete' && $app->request()->filter('ids', 'array-uint')) {
                Action::triggerInline($handler, 'delete', $actions);
            }

            if ($app->request()->filter('action', 'str') == 'undelete') {
                Action::triggerInline($handler, 'undelete', $actions);
            }

        }

        if ($handler->getContentType() == 'thread') {

            if ($app->request()->filter('action', 'str') == 'delete' && $app->request()->filter('ids', 'array-uint')) {
                Action::triggerInline($handler, 'delete', $actions);
            }

            if ($app->request()->filter('action', 'str') == 'undelete') {
                Action::triggerInline($handler, 'undelete', $actions);
            }

            if ($app->request()->filter('action', 'str') == 'lock') {
                Action::triggerInline($handler, 'close', $actions);
            }

            if ($app->request()->filter('action', 'str') == 'unlock') {
                Action::triggerInline($handler, 'open', $actions);
            }

            if ($app->request()->filter('action', 'str') == 'stick') {
                Action::triggerInline($handler, 'stick', $actions);
            }

            if ($app->request()->filter('action', 'str') == 'unstick') {
                Action::triggerInline($handler, 'unstick', $actions);
            }

        }
    }
}
