<?php


namespace App\ApiPlatform;

use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use ApiPlatform\Core\Metadata\Resource\ResourceMetadata;

class AutoGroupResourceMetadataFactory implements ResourceMetadataFactoryInterface
{
    private ResourceMetadataFactoryInterface $decorated;

    public function __construct(ResourceMetadataFactoryInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    /**
     * @param array<string, mixed> $operations
     * @return array<string, mixed>
     */
    private function updateContextOnOperations(array $operations, string $shortName, bool $isItem): array
    {
        foreach ($operations as $opName => $opOptions) {
            $opOptions['normalization_context'] = $opOptions['normalization_context'] ?? [];
            $opOptions['normalization_context']['groups'] = $opOptions['normalization_context']['groups'] ?? [];
            $opOptions['normalization_context']['groups'] = array_unique(array_merge(
                $opOptions['normalization_context']['groups'],
                $this->getDefaultGroups($shortName, true, $isItem, $opName)
            ));
            $opOptions['denormalization_context'] = $opOptions['denormalization_context'] ?? [];
            $opOptions['denormalization_context']['groups'] = $opOptions['denormalization_context']['groups'] ?? [];
            $opOptions['denormalization_context']['groups'] = array_unique(array_merge(
                $opOptions['denormalization_context']['groups'],
                $this->getDefaultGroups($shortName, false, $isItem, $opName)
            ));
            $operations[$opName] = $opOptions;
        }
        return $operations;
    }

    /**
     * @return string[]
     */
    private function getDefaultGroups(
        string $shortName,
        bool $normalization,
        bool $isItem,
        string $operationName
    ): array {
        $shortName = strtolower($shortName);
        $readOrWrite = $normalization ? 'read' : 'write';
        $itemOrCollection = $isItem ? 'item' : 'collection';
        return [
            // {shortName}:{read/write}
            // e.g. user:read
            sprintf('%s:%s', $shortName, $readOrWrite),
            // {shortName}:{item/collection}:{read/write}
            // e.g. user:collection:read
            sprintf('%s:%s:%s', $shortName, $itemOrCollection, $readOrWrite),
            // {shortName}:{item/collection}:{operationName}
            // e.g. user:collection:get
            sprintf('%s:%s:%s', $shortName, $itemOrCollection, $operationName),
        ];
    }

    public function create(string $resourceClass): ResourceMetadata
    {
        $resourceMetadata = $this->decorated->create($resourceClass);
        $itemOperations = $resourceMetadata->getItemOperations() ?? [];

        $resourceMetadata = $resourceMetadata->withItemOperations(
            $this->updateContextOnOperations(
                $itemOperations,
                (string) $resourceMetadata->getShortName(),
                true
            )
        );
        $collectionOperations = $resourceMetadata->getCollectionOperations() ?? [];
        $resourceMetadata = $resourceMetadata->withCollectionOperations(
            $this->updateContextOnOperations(
                $collectionOperations,
                (string) $resourceMetadata->getShortName(),
                false
            )
        );
        return $resourceMetadata;
    }
}
