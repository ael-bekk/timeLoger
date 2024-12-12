<?php


namespace App\OpenApi;

use ApiPlatform\Core\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\Core\OpenApi\Model\PathItem;
use ApiPlatform\Core\OpenApi\OpenApi;
use App\Controller\AbstractController;
use IteratorAggregate;

class OpenApiFactory implements OpenApiFactoryInterface
{

    private OpenApiFactoryInterface $decorated;

    /**
     * @var IteratorAggregate<int, AbstractController>
     */
    private IteratorAggregate $controllers;

    /**
     * @param IteratorAggregate<int, AbstractController> $controllers
     */
    public function __construct(
        IteratorAggregate $controllers,
        OpenApiFactoryInterface $decorated,
    ) {
        $this->controllers = $controllers;
        $this->decorated = $decorated;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function __invoke(array $context = []): OpenApi
    {
        $openApi = $this->decorated->__invoke($context);
        foreach ($this->controllers as $controller) {
            if (!$controller instanceof AbstractController) {
                continue;
            }
            /**
             * @var AbstractController $controller
             */
            $controller->appendDocs($openApi, $context);
        }
        // Hide Get LogTime username route
        /**
         * @var PathItem $path
         */
        foreach ($openApi->getPaths()->getPaths() as $key => $path) {
            if ($key === "/api/log_times/{username}") {
                $openApi->getPaths()->addPath($key, $path->withGet(null));
            }
        }
        return $openApi;
    }
}
