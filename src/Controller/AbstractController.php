<?php


namespace App\Controller;

use ApiPlatform\Core\OpenApi\OpenApi;

abstract class AbstractController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{

    /**
     * @return array<string, mixed>
     */
    protected function getTooManyRequestsResponse(): array
    {
        return [
            'description' => "The rate limit is exceeded, you should wait before retrying again",
            'headers' => [
                'X-RateLimit-Remaining' => [
                    'description' => 'The number of attempts available',
                    'type' => 'number'
                ],
                'X-RateLimit-Retry-After' => [
                    'description' => 'A timestamp indicating when it is safe to retry the request',
                    'type' => 'timestamp'
                ],
                'X-RateLimit-Limit' => [
                    'description' => 'The maximum requests allowed',
                    'type' => 'number'
                ]
            ],
        ];
    }

    /**
     * @param array<string, mixed> $context
     */
    abstract public function appendDocs(OpenApi $openApi, array $context = []): OpenApi;
}
