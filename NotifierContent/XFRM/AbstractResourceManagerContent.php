<?php

namespace ThemeHouse\Notifier\NotifierContent\XFRM;

use ThemeHouse\Notifier\NotifierContent\AbstractNotifierContent;
use XF\Http\Request;
use XFMG\Repository\Category;

/**
 * Class AbstractResourceManagerContent
 * @package ThemeHouse\Notifier\NotifierContent\XFRM
 */
abstract class AbstractResourceManagerContent extends AbstractNotifierContent
{
    /**
     * @var array
     */
    protected $defaultOptions = [
        'resource_category_ids' => [0],
    ];

    /**
     * @return bool
     */
    public function canUse()
    {
        $addOns = $this->app->registry()['addOns'];

        if (isset($addOns['XFRM'])) {
            return true;
        }

        return false;
    }

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
        return $this->app->templater()->renderMacro('th_notifier_action_edit_xfrm_resource', 'tabs');
    }

    /**
     * @return string
     */
    public function renderActionTabPanes()
    {
        /** @var Category $categoryRepo */
        $categoryRepo = $this->app->repository('XFRM:Category');
        $categoryTree = $categoryRepo->createCategoryTree($categoryRepo->findCategoryList()->fetch());

        return $this->app->templater()->renderTemplate('admin:th_notifier_action_edit_xfrm_resource', [
            'showAdditionalResourceCriteria' => $this->showAdditionalResourceCriteria(),
            'action' => $this->action,
            'categoryTree' => $categoryTree,
        ]);
    }

    /**
     * @return bool
     */
    protected function showAdditionalResourceCriteria()
    {
        return true;
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
        if (empty($options['content']['resource_category_ids']) || in_array(0,
                $options['content']['resource_category_ids'])) {
            $options['content']['resource_category_ids'] = [0];
        }

        return true;
    }
}