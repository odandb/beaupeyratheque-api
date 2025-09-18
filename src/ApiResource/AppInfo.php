<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\State\AppInfoProvider;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/app/info',
            provider: AppInfoProvider::class
        )
    ],
    normalizationContext: ['groups' => ['app:read']]
)]
class AppInfo
{
    #[Groups(['app:read'])]
    public string $name;

    #[Groups(['app:read'])]
    public string $version;

    #[Groups(['app:read'])]
    public string $description;

    #[Groups(['app:read'])]
    public string $environment;

    #[Groups(['app:read'])]
    public array $stats;

    #[Groups(['app:read'])]
    public \DateTimeImmutable $timestamp;

    public function __construct(
        string $name,
        string $version,
        string $description,
        string $environment,
        array $stats = []
    ) {
        $this->name = $name;
        $this->version = $version;
        $this->description = $description;
        $this->environment = $environment;
        $this->stats = $stats;
        $this->timestamp = new \DateTimeImmutable();
    }
}