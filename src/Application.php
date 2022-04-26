<?php

declare(strict_types=1);

namespace joole\framework;

use joole\framework\component\BaseComponent;
use joole\framework\component\ComponentInterface;
use joole\framework\data\types\ImmutableArray;
use joole\framework\exception\config\ConfigurationException;
use joole\framework\http\request\Request;
use joole\framework\http\response\Response;
use joole\framework\routing\Router;
use joole\reflector\object\ReflectedObject;
use joole\reflector\Reflector;
use function array_diff;
use function constant;
use function define;
use function defined;
use function in_array;
use function is_file;
use function microtime;
use function round;
use function scan_dir;
use function str_ends_with;
use function substr;

/**
 * The Application class is the entry point of a web application.
 *
 * It allows you to manage application processes, its components and modules.
 */
abstract class Application
{

    /**
     * Request.
     *
     * @var Request
     */
    public readonly Request $request;

    /**
     * Response.
     *
     * @var Response
     */
    public readonly Response $response;

    /**
     * Path with config files.
     *
     * @var null|string
     */
    private ?string $configurationsDirectory = null;

    /**
     * Components.
     *
     * @var ComponentInterface[]
     */
    private array $components = [];

    /**
     * Configurations.
     *
     * @var ImmutableArray
     */
    private ImmutableArray $configurations;

    private Router $router;

    /**
     * Required configurations.
     */
    protected const REQUIRED_CONFIGURATIONS = [
        'joole.php',
        'app.php',
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
        /** @var ReflectedObject $reflectedAppConfigs */
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
            if (is_file($configuration) && str_ends_with($configuration, '.php')) {
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
        $this->request = new Request();
        $this->response = (new Response())
            ->withHeader('X-Powered-By', ['Joole Base v2.32'])
            ->withHeader('Content-Type', ['text/html; charset=utf-8']);

        print($this->router->handleRequest());
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
    final public function getConfig(string $configName, array|null $default = null): array|null
    {
        return $this->configurations[$configName] ?? $default;
    }

    /**
     * Returns configurations directory.
     *
     * @return string
     */
    final public function getConfigurationsDirectory(): string
    {
        return $this->configurationsDirectory;
    }

    /**
     * Sets configurations directory.
     *
     * @param string $configurationsPath
     * @throws ConfigurationException
     */
    final public function setConfigurationsDirectory(string $configurationsPath): void
    {
        if (!is_dir($configurationsPath)) {
            throw new ConfigurationException('Configurations path is not defined');
        }

        $this->configurationsDirectory = $configurationsPath;
    }

    /**
     * Runs component.
     *
     * @param BaseComponent $component
     * @param array $config
     */
    final public function registerComponent(BaseComponent $component, array $config = [])
    {
        $components = &$this->components;

        $component->init($config);
        $component->run($this);

        $components[$component->getId()] = $component;
    }

    /**
     * Sets router for application.
     *
     * @param Router $router
     */
    final public function setRouter(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Returns component by id.
     *
     * @param string $id Component id.
     *
     * @return ComponentInterface|null Returns null when component not found.
     */
    final public function getComponent(string $id): ?ComponentInterface
    {
        return $this->components[$id] ?? null;
    }

    final public function getRouter(): Router
    {
        return $this->router;
    }

}