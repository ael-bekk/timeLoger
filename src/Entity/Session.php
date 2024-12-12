<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Common\Filter\SearchFilterInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use App\Repository\SessionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\ExistsFilter;

#[ORM\Entity(repositoryClass: SessionRepository::class)]
#[ApiResource(
    collectionOperations: [
        "get" => [
            "security" => "is_granted('ROLE_SESSION_READ')",
        ],
        "post" => [
            "security" => "is_granted('ROLE_SESSION_WRITE')",
        ],
    ],
    itemOperations: [
        "get" => [
            "security" => "is_granted('ROLE_SESSION_READ')",
        ],
        "put" => [
            "security" => "is_granted('ROLE_SESSION_WRITE')",
        ],
    ],
)]
#[ApiFilter(
    filterClass: ExistsFilter::class,
    properties: ["endedAt"],
)]
#[ApiFilter(
    filterClass: DateFilter::class,
    properties: ["startedAt", "endedAt"]
)]
#[ApiFilter(
    filterClass: SearchFilter::class,
    properties: [
        "username" => SearchFilterInterface::STRATEGY_EXACT,
        "hostname" => SearchFilterInterface::STRATEGY_EXACT,
    ],
)]
#[ApiFilter(
    filterClass: OrderFilter::class,
    properties: ['startedAt', 'endedAt'],
)]
#[UniqueEntity("uuid")]
class Session
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    #[ApiProperty(identifier: false)]
    #[Groups(["session:read"])]
    private int $id;

    #[ORM\Column(type: "uuid", unique: true)]
    #[ApiProperty(identifier: true)]
    #[Groups(["session:collection:post", "session:item:read", "session:collection:get"])]
    #[Assert\NotBlank]
    #[Assert\Uuid]
    private Uuid $uuid;

    #[ORM\Column(type: "string", length: 255)]
    #[Groups(["session:collection:post", "session:item:read", "session:collection:get"])]
    #[Assert\NotBlank]
    private string $username;

    #[ORM\Column(type: "string", length: 255)]
    #[Groups(["session:collection:post", "session:item:read", "session:collection:get"])]
    #[Assert\NotBlank]
    private string $hostname;

    #[ORM\Column(type: "datetime_immutable")]
    #[Groups(["session:collection:post", "session:item:read", "session:collection:get"])]
    #[Assert\NotBlank]
    private \DateTimeImmutable $startedAt;

    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    #[Groups(["session:collection:post", "session:item:put", "session:item:read", "session:collection:get"])]
    private ?\DateTimeImmutable $endedAt = null;

    #[ORM\Column(type: "datetime_immutable")]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable("now");
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): ?Uuid
    {
        return $this->uuid;
    }

    public function setUuid(Uuid $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getHostname(): ?string
    {
        return $this->hostname;
    }

    public function setHostname(string $hostname): self
    {
        $this->hostname = $hostname;

        return $this;
    }

    public function getStartedAt(): ?\DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function setStartedAt(\DateTimeImmutable $startedAt): self
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function getEndedAt(): ?\DateTimeImmutable
    {
        return $this->endedAt;
    }

    public function setEndedAt(?\DateTimeImmutable $endedAt): self
    {
        $this->endedAt = $endedAt;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @Groups("session:item:read", "session:collection:get")
     */
    public function getTotalHours(): float
    {
        $startedAt = $this->getStartedAt();
        $endedAt = $this->getEndedAt();
        if ($startedAt === null || $endedAt === null || $endedAt <= $startedAt) {
            return 0;
        }
        $diff = $endedAt->getTimestamp() - $startedAt->getTimestamp();
        // Calculate difference in hours
        return $diff / (60.0 * 60.0);
    }
}
