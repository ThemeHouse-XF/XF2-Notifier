<?php

namespace ThemeHouse\Notifier;

use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;
use XF\Db\Schema\Create;

/**
 * Class Setup
 * @package ThemeHouse\Notifier
 */
class Setup extends AbstractSetup
{
    use StepRunnerInstallTrait;
    use StepRunnerUpgradeTrait;
    use StepRunnerUninstallTrait;

    /**
     *
     */
    public function installStep1()
    {
        $this->schemaManager()->createTable('xf_th_notifier_provider', function (Create $table) {
            $table->addColumn('provider_id', 'varbinary', 25);
            $table->addColumn('provider_class', 'varchar', 100);

            $table->addColumn('active', 'boolean')->setDefault(0);

            $table->addColumn('options', 'mediumblob')->nullable();

            $table->addPrimaryKey('provider_id');
        });
    }

    /**
     *
     */
    public function installStep2()
    {
        $this->schemaManager()->createTable('xf_th_notifier_action', function (Create $table) {
            $table->addColumn('action_id', 'int')->autoIncrement();
            $table->addColumn('content_type', 'varbinary', 25);
            $table->addColumn('provider_ids', 'mediumblob')->nullable();
            $table->addColumn('title', 'varchar', 100);
            $table->addColumn('actions', 'mediumblob')->nullable();

            $table->addColumn('active', 'boolean')->setDefault(1);

            $table->addColumn('options', 'mediumblob')->nullable();
            $table->addColumn('user_criteria', 'mediumblob')->nullable();
        });
    }

    /**
     * @param array $stateChanges
     * @throws \XF\PrintableException
     */
    public function postInstall(array &$stateChanges)
    {
        $this->applyDefaultProviders();
    }

    /**
     * @param int $previousVersion
     * @throws \XF\PrintableException
     */
    protected function applyDefaultProviders($previousVersion = 0)
    {
        $providers = [];

        if (!$previousVersion) {
            $providers[] = [
                'provider_id' => 'slack',
                'provider_class' => 'ThemeHouse\Notifier:SlackProvider',
            ];
        }

        if ($previousVersion < 1000032) {
            $providers[] = [
                'provider_id' => 'mattermost',
                'provider_class' => 'ThemeHouse\Notifier:MattermostProvider',
            ];
        }

        if ($previousVersion < 1000132) {
            $providers[] = [
                'provider_id' => 'discord',
                'provider_class' => 'ThemeHouse\Notifier:DiscordProvider',
            ];
        }

        foreach ($providers as $providerData) {
            $provider = $this->app()->em()->create('ThemeHouse\Notifier:Provider');

            $provider->bulkSet($providerData);
            $provider->save();
        }
    }

    /**
     *
     * @throws \XF\PrintableException
     */
    public function upgrade1000131Step1()
    {
        $provider = $this->app->find('ThemeHouse\Notifier:Provider', 'mattermost');
        $teamName = null;
        $teamId = null;
        if (isset($provider->options['team_id'])) {
            $teamName = $provider->options['team_name'];
            $teamId = $provider->options['team_id'];
        }

        if ($provider) {
            $actions = $this->app->finder('ThemeHouse\Notifier:Action')->fetch();

            foreach ($actions as $action) {
                $providerIds = $action->provider_ids;
                $options = $action->options;

                if (in_array('mattermost', $providerIds)) {
                    if (!isset($options['mattermost'])) {
                        $options['mattermost'] = [];
                    }

                    $options['mattermost']['team_name'] = $teamName;
                    $options['mattermost']['team'] = $teamId;
                }

                /** @var \ThemeHouse\Notifier\Entity\Action $action */
                $action->options = $options;
                $action->save();
            }
        }
    }

    /**
     * @param $previousVersion
     * @param array $stateChanges
     * @throws \XF\PrintableException
     */
    public function postUpgrade($previousVersion, array &$stateChanges)
    {
        $this->applyDefaultProviders($previousVersion);
    }

    /**
     *
     */
    public function uninstallStep1()
    {
        $this->schemaManager()->dropTable('xf_th_notifier_provider');
        $this->schemaManager()->dropTable('xf_th_notifier_action');
    }
}