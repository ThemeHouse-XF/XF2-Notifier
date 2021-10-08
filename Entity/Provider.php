<?php

namespace ThemeHouse\Notifier\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * @property string $provider_id
 * @property boolean $active
 * @property string $provider_class
 * @property array $options
 *
 * @property string $title
 * @property \ThemeHouse\Notifier\NotifierProvider\AbstractProvider|null $handler
 */
class Provider extends Entity
{
    /**
     * @param Structure $structure
     * @return Structure
     */
    public static function getStructure(Structure $structure)
    {
        $structure->table = 'xf_th_notifier_provider';
        $structure->primaryKey = 'provider_id';
        $structure->shortName = 'ThemeHouse\Notifier:Provider';

        $structure->columns = [
            'provider_id' => [
                'type' => self::STR,
                'required' => true,
                'maxLength' => 25,
                'match' => 'alphanumeric',
            ],
            'provider_class' => [
                'type' => self::STR,
                'required' => true,
                'maxLength' => 100,
            ],
            'active' => [
                'type' => self::BOOL,
                'default' => false,
            ],
            'options' => [
                'type' => self::JSON_ARRAY,
                'default' => [],
            ],
        ];

        $structure->getters = [
            'title' => false,
            'handler' => true,
        ];

        return $structure;
    }

    /**
     * @return string|\XF\Phrase
     */
    public function getTitle()
    {
        $handler = $this->handler;

        return $handler ? $handler->getTitle() : '';
    }

    /**
     * @return string
     */
    public function renderOptions()
    {
        $handler = $this->handler;

        return $handler ? $handler->renderOptions() : '';
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        $handler = $this->handler;
        if (!$handler) {
            return false;
        }

        if (!$this->active) {
            return false;
        }

        if (!$this->isUsable()) {
            return false;
        }

        if (!$handler->verifyOptionsValue($this->options ?: [], $error)) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function isUsable()
    {
        $handler = $this->handler;

        return $handler ? $handler->isUsable() : false;
    }

    /**
     * @return \ThemeHouse\Notifier\NotifierProvider\AbstractProvider|null
     * @throws \Exception
     * @throws \Exception
     */
    public function getHandler()
    {
        $class = \XF::stringToClass($this->provider_class, '%s\NotifierProvider\%s');
        if (!class_exists($class)) {
            return null;
        }

        $class = \XF::extendClass($class);
        return new $class($this->app(), $this);
    }

    /**
     * @return \ThemeHouse\Notifier\Repository\Action|\XF\Mvc\Entity\Repository
     */
    protected function getActionRepo()
    {
        return $this->repository('ThemeHouse\Notifier:Action');
    }

    /**
     * @return \ThemeHouse\Notifier\Repository\Provider|\XF\Mvc\Entity\Repository
     */
    protected function getProviderRepo()
    {
        return $this->repository('ThemeHouse\Notifier:Provider');
    }
}