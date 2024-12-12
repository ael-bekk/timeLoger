<?php


namespace App\Serializer;

use ApiPlatform\Core\Serializer\SerializerContextBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Add serialization groups depending on the user's role
 * @package App\Serializer
 */
class RolesContextBuilder implements SerializerContextBuilderInterface
{

    private SerializerContextBuilderInterface $decorated;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        SerializerContextBuilderInterface $decorated,
        TokenStorageInterface $tokenStorage
    ) {
        $this->decorated = $decorated;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param string[] $groups
     * @return string[]
     */
    private function prependRole(array $groups, string $role): array
    {
        $roleGroups = [];
        foreach ($groups as $group) {
            $roleGroups[] = "$role:".$group;
        }
        return $roleGroups;
    }

    /**
     * @param array<string, mixed>|null $extractedAttributes
     * @return array<string, mixed>
     */
    public function createFromRequest(Request $request, bool $normalization, array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);
        $token = $this->tokenStorage->getToken();

        $groups = $context['groups'];
        if ($token !== null) {
            $roles = $token->getRoleNames();
            foreach ($roles as $role) {
                $context['groups'] = array_merge($context['groups'], $this->prependRole($groups, strtolower($role)));
            }
        }
        return $context;
    }
}
