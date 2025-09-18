<?php

namespace App\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\PropertyInfo\Type;

class PublicationYearFilter extends AbstractFilter
{
    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, Operation $operation = null, array $context = []): void
    {
        if ($property !== 'publicationYear') {
            return;
        }

        if (!$value || !is_string($value)) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $parameterName = $queryNameGenerator->generateParameterName($property);

        // Gestion des différents formats de filtre
        if (preg_match('/^(\d{4})s$/', $value, $matches)) {
            // Format: "1990s" - filtre par décennie
            $decade = (int) $matches[1];
            $queryBuilder
                ->andWhere(sprintf('%s.%s >= :%s_start', $rootAlias, $property, $parameterName))
                ->andWhere(sprintf('%s.%s < :%s_end', $rootAlias, $property, $parameterName))
                ->setParameter($parameterName . '_start', $decade)
                ->setParameter($parameterName . '_end', $decade + 10);
        } elseif (preg_match('/^(\d{4})-(\d{4})$/', $value, $matches)) {
            // Format: "1990-2000" - filtre par plage
            $startYear = (int) $matches[1];
            $endYear = (int) $matches[2];
            $queryBuilder
                ->andWhere(sprintf('%s.%s >= :%s_start', $rootAlias, $property, $parameterName))
                ->andWhere(sprintf('%s.%s <= :%s_end', $rootAlias, $property, $parameterName))
                ->setParameter($parameterName . '_start', $startYear)
                ->setParameter($parameterName . '_end', $endYear);
        } elseif (preg_match('/^>(\d{4})$/', $value, $matches)) {
            // Format: ">2000" - après une année
            $year = (int) $matches[1];
            $queryBuilder
                ->andWhere(sprintf('%s.%s > :%s', $rootAlias, $property, $parameterName))
                ->setParameter($parameterName, $year);
        } elseif (preg_match('/^<(\d{4})$/', $value, $matches)) {
            // Format: "<2000" - avant une année
            $year = (int) $matches[1];
            $queryBuilder
                ->andWhere(sprintf('%s.%s < :%s', $rootAlias, $property, $parameterName))
                ->setParameter($parameterName, $year);
        } elseif (preg_match('/^\d{4}$/', $value)) {
            // Format: "2000" - année exacte
            $year = (int) $value;
            $queryBuilder
                ->andWhere(sprintf('%s.%s = :%s', $rootAlias, $property, $parameterName))
                ->setParameter($parameterName, $year);
        }
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            'publicationYear' => [
                'property' => 'publicationYear',
                'type' => Type::BUILTIN_TYPE_STRING,
                'required' => false,
                'description' => 'Filtre par année de publication. Formats acceptés: "2000" (année exacte), "1990s" (décennie), "1990-2000" (plage), ">2000" (après), "<2000" (avant)',
                'openapi' => [
                    'example' => '2000s',
                    'examples' => [
                        'Année exacte' => ['value' => '2000'],
                        'Décennie' => ['value' => '2000s'],
                        'Plage d\'années' => ['value' => '1990-2000'],
                        'Après une année' => ['value' => '>2000'],
                        'Avant une année' => ['value' => '<2000'],
                    ],
                ],
            ],
        ];
    }
}