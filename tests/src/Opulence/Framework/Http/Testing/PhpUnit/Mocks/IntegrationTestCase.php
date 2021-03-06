<?php
/**
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2016 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */
namespace Opulence\Tests\Framework\Http\Testing\PhpUnit\Mocks;

use Opulence\Applications\Application;
use Opulence\Applications\Tasks\Dispatchers\Dispatcher as TaskDispatcher;
use Opulence\Applications\Tasks\TaskTypes;
use Opulence\Bootstrappers\BootstrapperRegistry;
use Opulence\Bootstrappers\Dispatchers\Dispatcher;
use Opulence\Bootstrappers\Paths;
use Opulence\Debug\Exceptions\Handlers\ExceptionHandler;
use Opulence\Debug\Exceptions\Handlers\IExceptionHandler;
use Opulence\Environments\Environment;
use Opulence\Framework\Debug\Exceptions\Handlers\Http\IExceptionRenderer;
use Opulence\Framework\Http\Bootstrappers\RequestBootstrapper;
use Opulence\Framework\Http\Testing\PhpUnit\Assertions\ResponseAssertions;
use Opulence\Framework\Http\Testing\PhpUnit\Assertions\ViewAssertions;
use Opulence\Framework\Http\Testing\PhpUnit\IntegrationTestCase as BaseIntegrationTestCase;
use Opulence\Framework\Routing\Bootstrappers\RouterBootstrapper;
use Opulence\Http\Responses\Response;
use Opulence\Ioc\Container;
use Opulence\Ioc\IContainer;
use Opulence\Routing\Router;

/**
 * Mocks the HTTP integration test for use in testing
 */
class IntegrationTestCase extends BaseIntegrationTestCase
{
    /** @var array The list of bootstrapper classes to include */
    private static $bootstrappers = [
        RequestBootstrapper::class,
        RouterBootstrapper::class
    ];

    /**
     * Gets the response assertions
     *
     * @return ResponseAssertions
     */
    public function getResponseAssertions() : ResponseAssertions
    {
        return $this->assertResponse;
    }

    /**
     * @return Router
     */
    public function getRouter() : Router
    {
        return $this->router;
    }

    /**
     * Gets the view assertions
     *
     * @return ViewAssertions The view assertions
     */
    public function getViewAssertions() : ViewAssertions
    {
        return $this->assertView;
    }

    /**
     * Sets up the application and container
     */
    public function setUp()
    {
        // Create and bind all of the components of our application
        $paths = new Paths([
            "configs" => __DIR__ . "/../../configs"
        ]);
        $taskDispatcher = new TaskDispatcher();
        // Purposely set this to a weird value so we can test that it gets overwritten with the "test" environment
        $this->environment = new Environment("foo");
        $this->container = new Container();
        $this->container->bindInstance(Paths::class, $paths);
        $this->container->bindInstance(TaskDispatcher::class, $taskDispatcher);
        $this->container->bindInstance(Environment::class, $this->environment);
        $this->container->bindInstance(IContainer::class, $this->container);
        $this->application = new Application($taskDispatcher);

        // Setup the bootstrappers
        $bootstrapperRegistry = new BootstrapperRegistry($paths, $this->environment);
        $bootstrapperDispatcher = new Dispatcher($taskDispatcher, $this->container);
        $bootstrapperRegistry->registerEagerBootstrapper(self::$bootstrappers);
        $taskDispatcher->registerTask(
            TaskTypes::PRE_START,
            function () use ($bootstrapperDispatcher, $bootstrapperRegistry) {
                $bootstrapperDispatcher->dispatch($bootstrapperRegistry);
            }
        );

        parent::setUp();
    }

    /**
     * @inheritdoc
     */
    protected function getExceptionHandler() : IExceptionHandler
    {
        return $this->createMock(ExceptionHandler::class, [], [], "", false);
    }

    /**
     * @inheritdoc
     */
    protected function getExceptionRenderer() : IExceptionRenderer
    {
        /** @var IExceptionRenderer|\PHPUnit_Framework_MockObject_MockObject $renderer */
        $renderer = $this->createMock(IExceptionRenderer::class);
        /** @var Response|\PHPUnit_Framework_MockObject_MockObject $response */
        $response = $this->createMock(Response::class);
        // Mock a 404 status code because this will primarily be used for missing routes in our tests
        $response->expects($this->any())
            ->method("getStatusCode")
            ->willReturn(404);
        $renderer->expects($this->any())
            ->method("getResponse")
            ->willReturn($response);

        return $renderer;
    }

    /**
     * @inheritdoc
     */
    protected function getGlobalMiddleware() : array
    {
        return [];
    }
}