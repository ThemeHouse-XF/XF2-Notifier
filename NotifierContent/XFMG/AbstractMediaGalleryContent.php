<?php

namespace ThemeHouse\Notifier\NotifierContent\XFMG;

use ThemeHouse\Notifier\NotifierContent\AbstractNotifierContent;
use XF\Http\Request;
use XFMG\Repository\Category;

/**
 * Class AbstractMediaGalleryContent
 * @package ThemeHouse\Notifier\NotifierContent\XFMG
 */
abstract class AbstractMediaGalleryContent extends AbstractNotifierContent
{
    /**
     * @var array
     */
    protected $defaultOptions = [
        'category_ids' => [0],
    ];

    /**
     * @return bool
     */
    public function canUse()
    {
        $addOns = $this->app->registry()['addOns'];

        if (isset($addOns['XFMG'])) {
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
        return $this->app->templater()->renderMacro('th_notifier_action_edit_xfmg_media_item', 'tabs');
    }

    /**
     * @return string
     */
    public function renderActionTabPanes()
    {
        /** @var Category $categoryRepo */
        $categoryRepo = $this->app->repository('XFMG:Category');
        $categoryTree = $categoryRepo->createCategoryTree($categoryRepo->findCategoryList()->fetch());

        return $this->app->templater()->renderTemplate('admin:th_notifier_action_edit_xfmg_media_item', [
            'showAdditionalMediaCriteria' => $this->showAdditionalMediaCriteria(),
            'action' => $this->action,
            'categoryTree' => $categoryTree,
        ]);
    }

    /**
     * @return bool
     */
    protected function showAdditionalMediaCriteria()
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
        if (empty($options['content']['category_ids']) || in_array(0, $options['content']['category_ids'])) {
            $options['content']['category_ids'] = [0];
        }

        return true;
    }
}