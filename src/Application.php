<?php

declare(strict_types=1);

namespace joole\framework;

use joole\framework\data\types\ImmutableArray;
use joole\framework\exception\config\ConfigurationException;
use joole\reflector\Reflector;
use function array_diff;
use function constant;
use function define;
use function defined;
use function in_array;
use function is_file;
use function microtime;
use function round;
use function substr;
use function scan_dir;

/**
 * The Application class is the entry point of a web application.
 *
 * It allows you to manage application processes, its components and modules.
 */
abstract class Application
{

    /**
     * Path with config files.
     *
     * @var null|string
     */
    private ?string $configurationsDirectory = null;

    /**
     * Configurations files.
     *
     * @var ImmutableArray
     */
    private ImmutableArray $configurations;

    /**
     * Required configurations.
     */
    protected const REQUIRED_CONFIGURATIONS = [
        'joole.php',
    ];

    /**
     * Application constructor.
     */
    final public function __construct()
    {
        $this->configurations = new ImmutableArray();
    }

    /**
     * Initializes application.
     *
     * @return void
     *
     * @throws ConfigurationException
     */
    public function init(): void
    {
        $startInitTime = microtime(true);

        $this->loadConfigurations();

        define('INIT_TIME', round(microtime(true) - $startInitTime, 7));
    }

    /**
     * Prepares configurations.
     *
     * @return void
     *
     * @throws ConfigurationException
     */
    protected function loadConfigurations(): void
    {
        $configurationsDirectory = $this->configurationsDirectory;

        if (!$configurationsDirectory || !is_dir($configurationsDirectory)) {
            if (!defined('BASE_CONFIGURATION_PATH') || !is_dir(constant('BASE_CONFIGURATION_PATH'))) {
                throw new ConfigurationException('Configuration path not defined!');
            }

            $this->setConfigurationsDirectory($configurationsDirectory = constant('BASE_CONFIGURATION_PATH'));
        }

        $configurations = scan_dir($configurationsDirectory);
        $requiredConfigurations = static::REQUIRED_CONFIGURATIONS;
        $applicationConfigurations = $this->configurations;
        $reflectedAppConfigs = (new Reflector())->buildFromObject($applicationConfigurations);
        $loadedConfigurationsData = [];

        foreach ($requiredConfigurations as $requiredConfiguration) {
            if (!in_array($requiredConfiguration, $configurations)) {
                throw new ConfigurationException('Configuration file "' . $requiredConfiguration . '" not found!');
            }

            $configName = substr($requiredConfiguration, 0, -4);
            $loadedConfigurationsData[$configName] = require_once $configurationsDirectory . '/' . $requiredConfiguration;
        }

        $configurations = array_diff($configurations, $requiredConfigurations);

        foreach ($configurations as $configuration) {
            if (is_file($configuration) && substr($configuration, -4, 4) === '.php') {
                $configName = substr($configuration, 0, -4);
                $loadedConfigurationsData[$configName] = require_once $configurationsDirectory . '/' . $configuration;
            }
        }

        $reflectedAppConfigs->getProperty('items')->setValue($loadedConfigurationsData);
    }

    /**
     * Calls after application initialization.
     */
    public function run()
    {
        define('APP_STARTED', microtime(true));
    }

    /**
     * Returns ArrayAccess objects with configurations.
     *
     * @return ImmutableArray|array
     */
    final public function getConfigurations(): ImmutableArray|array
    {
        return $this->configurations;
    }

    /**
     * Returns configuration.
     *
     * @param string $configName Configuration's name.
     * @param array|null $default What will be returned if config with given name not found.
     * @return array|null
     */
    final public function getConfig(string $configName, array|null $default = null):array|null{
        return $this->configurations[$configName] ?? $default;
    }

    final public function getConfigurationsDirectory(): string
    {
        return $this->configurationsDirectory;
    }

    final public function setConfigurationsDirectory(string $configurationsPath): void
    {
        if (!is_dir($configurationsPath)) {
            throw new ConfigurationException('Configurations path is not defined');
        }

        $this->configurationsDirectory = $configurationsPath;
    }

}