<?php


namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Controller\GetLogTime;

#[ApiResource(
    collectionOperations: [
        "get" => [
            "controller" => GetLogTime::class,
            "security" => "is_granted('ROLE_LOGTIME_READ')",
            "openapi_context" => [
                "parameters" => [
                    [
                        "name" => "start_date",
                        "description" => "Start date in the following format: Y-m-d",
                        "in" => "query",
                        "required" => true,
                        "type" => "date",
                    ],
                    [
                        "name" => "end_date",
                        "description" => "End date in the following format: Y-m-d",
                        "in" => "query",
                        "required" => true,
                        "type" => "date",
                    ],
                    [
                        "name" => "username",
                        "description" => "Get the logtime for a specific username",
                        "in" => "query",
                        "required" => false,
                        "type" => "string",
                    ],
                ],
            ],
        ],
    ],
    itemOperations: ["get"],
    formats: ["jsonld", "csv" => "text/plain"],
)]
class LogTime
{
    #[ApiProperty(identifier: true)]
    #[Groups(["logtime:read"])]
    private string $username;

    #[Groups(["logtime:read"])]
    private int $totalHours;

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalHours(): int
    {
        return $this->totalHours;
    }

    public function setTotalHours(int $totalHours): self
    {
        $this->totalHours = $totalHours;
        return $this;
    }
}
