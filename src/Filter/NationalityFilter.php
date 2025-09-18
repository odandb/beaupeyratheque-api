<?php

namespace App\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\PropertyInfo\Type;

class NationalityFilter extends AbstractFilter
{
    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, Operation $operation = null, array $context = []): void
    {
        if ($property !== 'nationality') {
            return;
        }

        if (!$value) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $parameterName = $queryNameGenerator->generateParameterName($property);

        if (is_string($value)) {
            // Gestion des différents formats
            if (str_contains($value, ',')) {
                // Format: "French,German,Italian" - plusieurs nationalités
                $nationalities = array_map('trim', explode(',', $value));
                $nationalities = array_filter($nationalities); // Supprimer les valeurs vides

                if (!empty($nationalities)) {
                    $queryBuilder
                        ->andWhere(sprintf('LOWER(%s.%s) IN (:%s)', $rootAlias, $property, $parameterName))
                        ->setParameter($parameterName, array_map('strtolower', $nationalities));
                }
            } elseif (str_starts_with($value, '!')) {
                // Format: "!French" - exclure une nationalité
                $excludedNationality = strtolower(substr($value, 1));
                $queryBuilder
                    ->andWhere(sprintf('LOWER(%s.%s) != :%s OR %s.%s IS NULL', $rootAlias, $property, $parameterName, $rootAlias, $property))
                    ->setParameter($parameterName, $excludedNationality);
            } elseif (str_contains($value, '*')) {
                // Format: "Fren*" - recherche avec wildcard
                $pattern = str_replace('*', '%', strtolower($value));
                $queryBuilder
                    ->andWhere(sprintf('LOWER(%s.%s) LIKE :%s', $rootAlias, $property, $parameterName))
                    ->setParameter($parameterName, $pattern);
            } else {
                // Format: "French" - recherche exacte insensible à la casse
                $queryBuilder
                    ->andWhere(sprintf('LOWER(%s.%s) = :%s', $rootAlias, $property, $parameterName))
                    ->setParameter($parameterName, strtolower($value));
            }
        } elseif (is_array($value)) {
            // Support des arrays pour les requêtes de type nationality[]=French&nationality[]=German
            $nationalities = array_filter(array_map('trim', $value));
            if (!empty($nationalities)) {
                $queryBuilder
                    ->andWhere(sprintf('LOWER(%s.%s) IN (:%s)', $rootAlias, $property, $parameterName))
                    ->setParameter($parameterName, array_map('strtolower', $nationalities));
            }
        }
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            'nationality' => [
                'property' => 'nationality',
                'type' => Type::BUILTIN_TYPE_STRING,
                'required' => false,
                'description' => 'Filtre par nationalité. Formats acceptés: "French" (exacte), "French,German" (multiples), "!French" (exclure), "Fren*" (wildcard)',
                'openapi' => [
                    'example' => 'French',
                    'examples' => [
                        'Nationalité exacte' => ['value' => 'French'],
                        'Multiples nationalités' => ['value' => 'French,German,Italian'],
                        'Exclure une nationalité' => ['value' => '!American'],
                        'Recherche partielle' => ['value' => 'Fren*'],
                        'Array de nationalités' => [
                            'value' => ['French', 'German'],
                            'summary' => 'nationality[]=French&nationality[]=German'
                        ],
                    ],
                ],
            ],
            'nationality[]' => [
                'property' => 'nationality',
                'type' => Type::BUILTIN_TYPE_ARRAY,
                'required' => false,
                'description' => 'Filtre par multiples nationalités (format array)',
            ],
        ];
    }
}