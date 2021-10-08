<?php

namespace ThemeHouse\Notifier\Repository;

use XF\Mvc\Entity\Repository;

/**
 * Class Provider
 * @package ThemeHouse\Notifier\Repository
 */
class Provider extends Repository
{
    /**
     * @param bool $activeOnly
     * @return \XF\Mvc\Entity\Finder
     */
    public function findProvidersForList($activeOnly = true)
    {
        $finder = $this->getFinder();

        if ($activeOnly) {
            $finder->where('active', true);
        }

        return $finder;
    }

    /**
     * @param $providerId
     * @return null|\ThemeHouse\Notifier\Entity\Provider|\XF\Mvc\Entity\Entity
     */
    public function getProviderbyId($providerId)
    {
        return $this->em->find('ThemeHouse\Notifier:Provider', $providerId);
    }

    /**
     * @return \XF\Mvc\Entity\Finder
     */
    public function getFinder()
    {
        return $this->finder('ThemeHouse\Notifier:Provider');
    }
}