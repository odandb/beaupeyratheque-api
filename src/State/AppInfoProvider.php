<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\AppInfo;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use App\Repository\ReviewRepository;
use App\Repository\UserRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class AppInfoProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire('%kernel.environment%')] private string $environment,
        private BookRepository $bookRepository,
        private AuthorRepository $authorRepository,
        private ReviewRepository $reviewRepository,
        private UserRepository $userRepository
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): AppInfo
    {
        // Calculer les statistiques
        $stats = [
            'total_books' => $this->bookRepository->count([]),
            'total_authors' => $this->authorRepository->count([]),
            'total_reviews' => $this->reviewRepository->count([]),
            'total_users' => $this->userRepository->count([]),
            'php_version' => PHP_VERSION,
            'symfony_version' => \Symfony\Component\HttpKernel\Kernel::VERSION,
        ];

        return new AppInfo(
            name: 'Beaupeyratheque API',
            version: '1.0.0',
            description: 'API de gestion de bibliothèque avec authentification JWT',
            environment: $this->environment,
            stats: $stats
        );
    }
}