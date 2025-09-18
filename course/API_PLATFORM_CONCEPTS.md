# API Platform - Concepts clés pour l'enseignement

## Introduction à API Platform

API Platform est un framework PHP moderne qui permet de créer des APIs REST et GraphQL robustes en quelques minutes. Initialement construit pour Symfony, **API Platform 4** a révolutionné son architecture en adoptant une approche modulaire par composants, permettant désormais le support officiel de **Laravel** et d'autres frameworks PHP.

### Qu'est-ce qu'API Platform ?

API Platform transforme vos entités (Doctrine pour Symfony, Eloquent pour Laravel) en APIs RESTful complètes avec :
- **Génération automatique** des endpoints CRUD
- **Documentation interactive** avec Swagger/OpenAPI
- **Validation** automatique des données
- **Sérialisation** intelligente
- **Filtres et pagination** prêts à l'emploi
- **Négociation de contenu** multi-format

### Architecture modulaire (v4+)

Depuis la version 4, API Platform adopte une **architecture en composants** permettant :

#### 🧩 **Multi-framework**
- **Symfony** : Support natif historique avec Doctrine ORM
- **Laravel** : Support officiel avec Eloquent ORM
- **Autres frameworks** : Architecture extensible pour futurs supports

#### 📦 **Composants découplés**
- **API Platform Core** : Logique métier commune
- **Symfony Bundle** : Intégration Symfony spécifique
- **Laravel Provider** : Intégration Laravel dédiée
- **Standalone Components** : Utilisables indépendamment

#### 🔄 **Adaptateurs ORM**
- **Doctrine** (Symfony) : Relations complexes, migrations
- **Eloquent** (Laravel) : Syntaxe fluide, conventions Rails-like
- **Interface commune** : API uniforme quelque soit l'ORM

### Standards et formats supportés

API Platform supporte nativement plusieurs standards web essentiels :

#### 🌐 **JSON-LD (JavaScript Object Notation for Linked Data)**
- **Format par défaut** d'API Platform
- Ajoute un **contexte sémantique** aux données JSON
- Permet de **lier les données** entre elles (relations)
- **Standard W3C** pour le web sémantique

```json
{
  "@context": "/api/contexts/Book",
  "@id": "/api/books/1",
  "@type": "Book",
  "title": "Symfony : Le Guide Complet",
  "author": "/api/authors/1"
}
```

#### 🔗 **Hydra**
- **Vocabulaire** pour décrire les APIs REST
- Permet la **découverte automatique** des endpoints
- **Navigation hypermedia** dans l'API
- **Client auto-adaptatif** possible

```json
{
  "@context": "/api/contexts/Book",
  "hydra:member": [],
  "hydra:totalItems": 42,
  "hydra:view": {
    "@id": "/api/books?page=1",
    "hydra:first": "/api/books?page=1",
    "hydra:next": "/api/books?page=2"
  }
}
```

Il est conçu pour rendre les réponses d’API auto-descriptives et faciliter la navigation entre ressources via des liens.

#### 📋 **OpenAPI 3.0 (ex-Swagger)**
- **Documentation standardisée** de l'API
- **Interface interactive** pour tester l'API
- **Génération automatique** de clients
- **Spécification** lisible par les machines

#### 📤 **Export OpenAPI**

API Platform génère automatiquement la spécification OpenAPI que vous pouvez exporter :

**Endpoints d'export :**
```bash
# Format JSON
GET /docs.json

# Format YAML (plus lisible)
GET /docs.yaml

# Via ligne de commande
php bin/console api:openapi:export --yaml > api-spec.yaml
php bin/console api:openapi:export --json > api-spec.json
```

**Cas d'usage de l'export :**

1. **Intégrations tierces**
- **Postman/Insomnia** : Import direct des collections
- **API Gateways** : Configuration Kong, AWS API Gateway, Azure APIM
- **Monitoring** : Datadog, New Relic, Pingdom
- **Documentation** : GitBook, Confluence, sites statiques

2. **Gouvernance API**
```yaml
# Exemple de pipeline CI/CD
name: API Governance
on: [push]
jobs: 
  validate-api:
    runs-on: ubuntu-latest
    steps:
      - name: Export OpenAPI spec
        run: curl ${{ secrets.API_URL }}/docs.yaml > current-spec.yaml

      - name: Validate spec
        run: swagger-codegen validate -i current-spec.yaml

      - name: Check breaking changes
        run: swagger-diff previous-spec.yaml current-spec.yaml --check-breaking

      - name: Generate clients
        run: |
          openapi-generator batch generate-clients.yaml
          # Publier les clients générés
```

**Avantages de l'export :**
- **Écosystème outillage** : Accès à tous les outils OpenAPI
- **Interopérabilité** : Standard reconnu par l'industrie
- **Automatisation** : CI/CD, tests, génération de code
- **Collaboration** : Partage facile avec les équipes frontend/mobile

#### 🎯 **Autres formats supportés**

API Platform supporte une **négociation de contenu automatique** permettant aux clients de demander différents formats via l'header `Accept` :
 - JSON (application/json)
 - HAL (application/hal+json)
 - XML (application/xml)
 - CSV (text/csv)
 - YAML (application/x-yaml)

### Pourquoi ces standards ?

| Standard | Avantage | Cas d'usage |
|----------|----------|-------------|
| **JSON-LD** | Données liées et sémantiques | APIs complexes avec relations |
| **Hydra** | Navigation automatique | Clients intelligents |
| **OpenAPI** | Documentation standardisée | Intégration tierce |
| **JSON** | Simplicité et performance | Applications mobiles/SPA |
| **HAL** | Navigation hypermedia | APIs REST matures |
| **CSV** | Export de données | Rapports et analyses |

---

## Concepts pratiques

Ce document présente les concepts essentiels d'API Platform à travers des exemples pratiques basés sur une bibliothèque (Book, Author, Review).

## 1. Annotations/Attributs API Platform

### `#[ApiResource]`
L'attribut principal qui expose automatiquement une entité comme ressource API.

```php
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(),
        new Put(),
        new Patch(),
        new Delete()
    ],
    normalizationContext: ['groups' => ['book:read']],
    denormalizationContext: ['groups' => ['book:write']],
    paginationEnabled: true,
    paginationItemsPerPage: 10
)]
class Book
```

**Points d'enseignement :**
- Configuration des opérations CRUD disponibles
- Contrôle de la sérialisation avec les contextes
- Pagination automatique

## 2. Groupes de sérialisation

### Organisation des données exposées
Les groupes permettent de contrôler quelles propriétés sont exposées dans quels contextes.

```php
#[Groups(['book:read', 'book:write', 'author:read'])]
private ?string $title = null;

#[Groups(['book:read'])]
private ?int $id = null;
```

**Points d'enseignement :**
- Séparation lecture/écriture
- Gestion des relations circulaires
- Contrôle fin de l'exposition des données

## 3. Validation automatique

### Contraintes Symfony Validator
API Platform utilise automatiquement les contraintes de validation Symfony.

```php
#[Assert\NotBlank]
#[Assert\Length(min: 2, max: 255)]
private ?string $title = null;

#[Assert\Range(min: 1, max: 5)]
private ?int $rating = null;

#[Assert\Isbn]
private ?string $isbn = null;
```

**Points d'enseignement :**
- Validation automatique côté serveur
- Messages d'erreur structurés
- Contraintes métier personnalisées

## 4. Relations entre entités

### OneToMany / ManyToOne
Gestion automatique des relations Doctrine.

```php
// Dans Book.php
#[ORM\ManyToOne(inversedBy: 'books')]
#[Groups(['book:read', 'book:write'])]
private ?Author $author = null;

// Dans Author.php
#[ORM\OneToMany(targetEntity: Book::class, mappedBy: 'author')]
#[Groups(['author:read'])]
private Collection $books;
```

**Points d'enseignement :**
- Inclusion/exclusion des relations dans l'API
- Éviter les références circulaires
- Chargement optimisé des relations

> [!TIP]
>
> Déclarer systématiquement les relations inverses dans Doctrine n’est pas toujours nécessaire : 
> si elles ne servent pas fonctionnellement dans votre code, elles risquent surtout de provoquer des chargements 
> et hydratations supplémentaires d’objets (voire des requêtes SQL inutiles), ce qui peut dégrader les performances ; 
> il vaut donc mieux ne déclarer une relation inverse que lorsqu’on a réellement besoin de naviguer dans les deux sens.

## 5. Pagination

### Configuration automatique
```php
#[ApiResource(
    paginationEnabled: true,
    paginationItemsPerPage: 10
)]
```

**Endpoints générés :**
- `GET /api/books?page=1`
- `GET /api/books?itemsPerPage=20`

## 6. Documentation automatique

### OpenAPI/Swagger intégré
API Platform génère automatiquement :
- Schéma OpenAPI 3.0
- Interface Swagger UI
- Documentation interactive

**Accès :** `/api/docs`

## 7. Filtres (concepts avancés)

### Filtres de recherche intégrés
```php
#[ApiFilter(SearchFilter::class, properties: ['title' => 'partial'])]
#[ApiFilter(RangeFilter::class, properties: ['publicationYear'])]
#[ApiFilter(OrderFilter::class, properties: ['title', 'publicationYear'])]
```

**Exemples d'usage :**
- `GET /api/books?title=symfony`
- `GET /api/books?publicationYear[gte]=2020`
- `GET /api/books?order[title]=asc`

### Filtres personnalisés

Au-delà des filtres intégrés, API Platform permet de créer des **filtres personnalisés** pour des besoins métier spécifiques.

#### 🎯 **Création d'un filtre personnalisé**

```php
// src/Filter/PublicationYearFilter.php
class PublicationYearFilter extends AbstractFilter
{
    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, Operation $operation = null, array $context = []): void
    {
        if ($property !== 'publicationYear') {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $parameterName = $queryNameGenerator->generateParameterName($property);

        // Format: "1990s" - filtre par décennie
        if (preg_match('/^(\d{4})s$/', $value, $matches)) {
            $decade = (int) $matches[1];
            $queryBuilder
                ->andWhere(sprintf('%s.%s >= :%s_start', $rootAlias, $property, $parameterName))
                ->andWhere(sprintf('%s.%s < :%s_end', $rootAlias, $property, $parameterName))
                ->setParameter($parameterName . '_start', $decade)
                ->setParameter($parameterName . '_end', $decade + 10);
        }
        // Autres formats: "1990-2000", ">2000", "<2000", "2000"
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            'publicationYear' => [
                'property' => 'publicationYear',
                'type' => Type::BUILTIN_TYPE_STRING,
                'required' => false,
                'description' => 'Filtre par année de publication. Formats: "2000", "1990s", "1990-2000", ">2000", "<2000"',
                'openapi' => [
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
```

#### 🔧 **Application du filtre à l'entité**

```php
// src/Entity/Book.php
#[ApiResource(...)]
#[ApiFilter(PublicationYearFilter::class)]
class Book
{
    #[ORM\Column]
    private ?int $publicationYear = null;
    // ...
}
```

#### 🌍 **Exemple : Filtre de nationalité avancé**

```php
// src/Filter/NationalityFilter.php
class NationalityFilter extends AbstractFilter
{
    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, /* ... */): void
    {
        if ($property !== 'nationality') {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $parameterName = $queryNameGenerator->generateParameterName($property);

        if (str_contains($value, ',')) {
            // Format: "French,German,Italian" - plusieurs nationalités
            $nationalities = array_map('trim', explode(',', $value));
            $queryBuilder
                ->andWhere(sprintf('LOWER(%s.%s) IN (:%s)', $rootAlias, $property, $parameterName))
                ->setParameter($parameterName, array_map('strtolower', $nationalities));
        } elseif (str_starts_with($value, '!')) {
            // Format: "!French" - exclure une nationalité
            $excludedNationality = strtolower(substr($value, 1));
            $queryBuilder
                ->andWhere(sprintf('LOWER(%s.%s) != :%s', $rootAlias, $property, $parameterName))
                ->setParameter($parameterName, $excludedNationality);
        } elseif (str_contains($value, '*')) {
            // Format: "Fren*" - recherche avec wildcard
            $pattern = str_replace('*', '%', strtolower($value));
            $queryBuilder
                ->andWhere(sprintf('LOWER(%s.%s) LIKE :%s', $rootAlias, $property, $parameterName))
                ->setParameter($parameterName, $pattern);
        }
    }
}
```

#### 📋 **Exemples d'usage des filtres personnalisés**

**Filtre PublicationYearFilter :**
```bash
# Livres des années 2000
GET /api/books?publicationYear=2000s

# Livres entre 1990 et 2000
GET /api/books?publicationYear=1990-2000

# Livres après 2010
GET /api/books?publicationYear=>2010

# Livres avant 1980
GET /api/books?publicationYear=<1980
```

**Filtre NationalityFilter :**
```bash
# Auteurs français
GET /api/authors?nationality=French

# Auteurs français, allemands ou italiens
GET /api/authors?nationality=French,German,Italian

# Auteurs non américains
GET /api/authors?nationality=!American

# Auteurs dont la nationalité commence par "Fren"
GET /api/authors?nationality=Fren*

# Format array
GET /api/authors?nationality[]=French&nationality[]=German
```

### Avantages des filtres personnalisés

| Avantage | Description |
|----------|-------------|
| **Logique métier** | Filtres adaptés aux besoins spécifiques |
| **Performance** | Requêtes SQL optimisées |
| **Documentation** | Intégration automatique dans OpenAPI |
| **Flexibilité** | Support de formats de query complexes |
| **Réutilisabilité** | Filtres réutilisables sur plusieurs entités |

**Points d'enseignement :**
- **Extension d'AbstractFilter** : Structure de base
- **Manipulation de QueryBuilder** : Génération de requêtes SQL
- **Expressions régulières** : Parsing des paramètres complexes
- **Documentation OpenAPI** : Méthode getDescription()
- **Sécurité** : Paramètres liés pour éviter l'injection SQL

## 8. Extensions API Platform

Les **extensions** permettent de modifier automatiquement toutes les requêtes d'une ressource sans créer de filtres explicites. Idéal pour le filtrage global, la sécurité ou les conditions métier.

### Extension de publication

```php
// src/Extension/BookPublishedExtension.php
use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;

class BookPublishedExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, Operation $operation = null, array $context = []): void
    {
        if ($resourceClass !== Book::class) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder
            ->andWhere(sprintf('%s.isPublished = :published', $rootAlias))
            ->setParameter('published', true);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, Operation $operation = null, array $context = []): void
    {
        if ($resourceClass !== Book::class) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder
            ->andWhere(sprintf('%s.isPublished = :published', $rootAlias))
            ->setParameter('published', true);
    }
}
```

L'extension s'applique **automatiquement** à toutes les requêtes GET sur les livres, cachant les livres non publiés.

## 9. Serializer Context Builder

Le **Serializer Context Builder** permet de personnaliser la sérialisation au-delà des groupes standards.

### Configuration personnalisée

```php
// src/Serializer/BookContextBuilder.php
use ApiPlatform\Serializer\SerializerContextBuilderInterface;

class BookContextBuilder implements SerializerContextBuilderInterface
{
    public function __construct(
        private SerializerContextBuilderInterface $decorated,
        private Security $security
    ) {}

    public function createFromRequest(Request $request, bool $normalization, array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);

        $resourceClass = $context['resource_class'] ?? null;

        if ($resourceClass === Book::class && $normalization) {
            $user = $this->security->getUser();

            if ($user && in_array('ROLE_ADMIN', $user->getRoles())) {
                // Admin voit les données privées
                $context['groups'][] = 'book:admin';
            }

            // Ajouter les stats pour les collections
            if ($request->attributes->get('_route') === 'api_books_get_collection') {
                $context['groups'][] = 'book:stats';
            }
        }

        return $context;
    }
}
```

Le **Serializer Context Builder** permet d'adapter dynamiquement la sérialisation selon l'utilisateur, la route ou d'autres critères.

## 10. Gestion des erreurs

### Réponses d'erreur structurées
API Platform retourne automatiquement des erreurs au format standard :

```json
{
  "@context": "/api/contexts/ConstraintViolationList",
  "@type": "ConstraintViolationList",
  "hydra:title": "An error occurred",
  "violations": [
    {
      "propertyPath": "title",
      "message": "This value should not be blank."
    }
  ]
}
```

## 11. Authentification et sécurité JWT

### Qu'est-ce que JWT ?

**JWT (JSON Web Token)** est un standard ouvert (RFC 7519) qui définit un moyen compact et autonome de transmettre des informations entre parties sous forme d'objet JSON signé.

#### 🔑 **Structure d'un JWT**
Un JWT se compose de trois parties séparées par des points (.) :

```
Header.Payload.Signature
```

**Exemple :**
```
eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c
```

#### 📦 **Contenu du token**

1. **Header** (Base64 encodé)
```json
{
  "alg": "HS256",
  "typ": "JWT"
}
```

2. **Payload** (Base64 encodé)
```json
{
  "sub": "1234567890",
  "email": "user@example.com",
  "roles": ["ROLE_USER"],
  "iat": 1516239022,
  "exp": 1516242622
}
```

3. **Signature** (Chiffrée avec clé secrète)

### Configuration avec Lexik JWT Bundle

#### 🛠️ **Installation et configuration**

```yaml
# config/packages/security.yaml
security:
  password_hashers:
    App\Entity\User: 'auto'

  providers:
    users:
      entity:
        class: App\Entity\User
        property: email

  firewalls:
    main:
      stateless: true
      provider: users
      json_login:
        check_path: /auth
        username_path: email
        password_path: password
        success_handler: lexik_jwt_authentication.handler.authentication_success
        failure_handler: lexik_jwt_authentication.handler.authentication_failure
      jwt: ~

  access_control:
    - { path: ^/auth, roles: PUBLIC_ACCESS }
    - { path: ^/docs, roles: PUBLIC_ACCESS }
    - { path: ^/, roles: IS_AUTHENTICATED_FULLY }
```

#### 🔐 **Génération des clés**

```bash
# Générer les clés privée/publique
php bin/console lexik:jwt:generate-keypair

# Vérifier la configuration
php bin/console lexik:jwt:check-config
```

### Sécurisation avec API Platform

#### 🛡️ **Sécurité au niveau des ressources**

```php
#[ApiResource(
    operations: [
        new GetCollection(security: "is_granted('ROLE_ADMIN')"),
        new Get(security: "is_granted('ROLE_ADMIN') or object == user"),
        new Post(securityPostDenormalize: "is_granted('ROLE_ADMIN') or object == user"),
        new Put(security: "is_granted('ROLE_ADMIN') or object == user"),
        new Patch(security: "is_granted('ROLE_ADMIN') or object == user"),
        new Delete(security: "is_granted('ROLE_ADMIN')")
    ]
)]
class User
```

**Points d'enseignement :**
- `security` : Contrôle avant hydratation
- `securityPostDenormalize` : Contrôle après hydratation
- `object == user` : Accès à ses propres données
- Hiérarchie des rôles : `ROLE_ADMIN` hérite de `ROLE_USER`

#### 🎯 **Contrôle d'accès granulaire**

```php
// Lecture publique, écriture authentifiée (Books, Authors, Reviews)
#[ApiResource(
    operations: [
        new GetCollection(),  // Public
        new Get(),           // Public
        new Post(security: "is_granted('ROLE_USER')"),    // Authentifié
        new Put(security: "is_granted('ROLE_USER')"),     // Authentifié
        new Patch(security: "is_granted('ROLE_USER')"),   // Authentifié
        new Delete(security: "is_granted('ROLE_USER')")   // Authentifié
    ]
)]

// Utilisateurs connectés uniquement (Users)
new GetCollection(security: "is_granted('ROLE_USER')")

// Administrateurs uniquement
new GetCollection(security: "is_granted('ROLE_ADMIN')")

// Propriétaire ou admin
new Get(security: "is_granted('ROLE_ADMIN') or object == user")

// Contrôle après validation
new Post(securityPostDenormalize: "is_granted('ROLE_ADMIN')")
```

### Workflow d'authentification

#### 📝 **1. Connexion**
```http
POST /auth
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password123"
}
```

**Réponse :**
```json
{
  "token": "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9...",
  "refresh_token": "def50200..."
}
```

#### 🔑 **2. Utilisation du token**
```http
GET /api/books
Authorization: Bearer eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9...
```

#### ❌ **3. Gestion des erreurs**
```json
// Token expiré (401)
{
  "@context": "/api/contexts/Error",
  "@type": "hydra:Error",
  "hydra:title": "An error occurred",
  "hydra:description": "Expired JWT Token"
}

// Accès refusé (403)
{
  "@context": "/api/contexts/Error",
  "@type": "hydra:Error",
  "hydra:title": "An error occurred",
  "hydra:description": "Access Denied."
}
```

### Avantages de JWT

| Avantage | Description |
|----------|-------------|
| **Stateless** | Pas de stockage serveur, scalabilité |
| **Autonome** | Contient toutes les infos nécessaires |
| **Cross-domain** | Fonctionne entre différents domaines |
| **Mobile-friendly** | Idéal pour applications mobiles |
| **Standard** | RFC 7519, interopérable |

### Bonnes pratiques sécurité

#### 🔒 **Côté serveur**
- **Clés fortes** : RSA 2048+ ou ECDSA
- **Expiration courte** : 15-60 minutes
- **Refresh tokens** : Renouvellement sécurisé
- **HTTPS obligatoire** : Chiffrement transport

#### 📱 **Côté client**
- **Stockage sécurisé** : pas de localStorage
- **httpOnly cookies** : Protection XSS
- **Gestion expiration** : Auto-refresh
- **Logout propre** : Invalidation tokens

## 11. Opérations personnalisées et State Providers

### Qu'est-ce qu'une opération personnalisée ?

Au-delà du CRUD classique, API Platform permet de créer des **opérations personnalisées** pour des besoins métier spécifiques : statistiques, calculs, agrégations, actions complexes, etc.

#### 🎯 **State Providers : la nouvelle approche (v4+)**

Les **State Providers** remplacent les contrôleurs pour la logique métier personnalisée :

```php
// src/ApiResource/AppInfo.php
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
    public array $stats;

    public function __construct(string $name, string $version, array $stats = [])
    {
        $this->name = $name;
        $this->version = $version;
        $this->stats = $stats;
    }
}
```

#### 🔧 **Implémentation du State Provider**

```php
// src/State/AppInfoProvider.php
class AppInfoProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire('%kernel.environment%')] private string $environment,
        private BookRepository $bookRepository,
        private AuthorRepository $authorRepository
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): AppInfo
    {
        $stats = [
            'total_books' => $this->bookRepository->count([]),
            'total_authors' => $this->authorRepository->count([]),
            'php_version' => PHP_VERSION,
            'symfony_version' => \Symfony\Component\HttpKernel\Kernel::VERSION,
        ];

        return new AppInfo(
            name: 'Beaupeyratheque API',
            version: '1.0.0',
            stats: $stats
        );
    }
}
```

#### 📊 **Exemple de réponse**

```json
{
  "@context": "/api/contexts/AppInfo",
  "@id": "/api/app/info",
  "@type": "AppInfo",
  "name": "Beaupeyratheque API",
  "version": "1.0.0",
  "stats": {
    "total_books": 42,
    "total_authors": 15,
    "php_version": "8.4.12",
    "symfony_version": "7.3.0"
  }
}
```

### Avantages des State Providers

| Avantage | Description |
|----------|-------------|
| **Testabilité** | Plus facile à tester unitairement |
| **Réutilisabilité** | Logique réutilisable entre opérations |
| **Type safety** | Meilleur support des types PHP |
| **Performance** | Optimisations possibles |
| **Architecture** | Séparation claire des responsabilités |

### Cas d'usage typiques

#### 🔢 **Statistiques et métriques**
```php
// GET /api/stats/dashboard
new Get(uriTemplate: '/stats/dashboard', provider: DashboardStatsProvider::class)
```

#### 🔍 **Recherche complexe**
```php
// GET /api/search?q=symfony&category=tech
new Get(uriTemplate: '/search', provider: SearchProvider::class)
```

#### 📈 **Rapports**
```php
// GET /api/reports/monthly
new Get(uriTemplate: '/reports/monthly', provider: MonthlyReportProvider::class)
```

#### ⚡ **Actions métier**
```php
// POST /api/books/{id}/publish
new Post(uriTemplate: '/books/{id}/publish', processor: PublishBookProcessor::class)
```

**Points d'enseignement :**
- **Séparation des responsabilités** : Providers vs Entities
- **Injection de dépendances** : Services dans les providers
- **Données calculées** : Pas de persistance nécessaire
- **APIs métier** : Au-delà du CRUD

## 12. Upload de fichiers

API Platform supporte l'upload de fichiers via **VichUploaderBundle**, permettant de gérer facilement les fichiers avec validation et stockage automatique.

### Configuration VichUploader avec Flysystem

VichUploader peut utiliser **FlysystemBundle** pour une abstraction du stockage fichier, permettant de basculer facilement entre local et cloud.

```yaml
# config/packages/vich_uploader.yaml
vich_uploader:
    db_driver: orm
    storage: flysystem
    use_flysystem_to_resolve_uri: true
    mappings:
        media_object:
            uri_prefix: /media
            upload_destination: media_storage  # Référence au filesystem Flysystem
            namer: Vich\UploaderBundle\Naming\SmartUniqueNamer
            inject_on_load: false
            delete_on_update: true
            delete_on_remove: true
```

#### 🗂️ **Configuration Flysystem**

```yaml
# config/packages/flysystem.yaml
flysystem:
    storages:
        media_storage:
            # Local pour développement
            adapter: 'local'
            options:
                directory: '%kernel.project_dir%/public/media'

            # AWS S3 pour production
            # adapter: 'aws'
            # options:
            #     client: 'aws_s3_client'
            #     bucket: '%env(AWS_S3_BUCKET)%'
            #     prefix: 'media'

            # Google Cloud Storage
            # adapter: 'gcloud'
            # options:
            #     client: 'gcloud_storage_client'
            #     bucket: '%env(GCLOUD_BUCKET)%'
```

#### ☁️ **Avantages Flysystem**

| Avantage | Description |
|----------|-------------|
| **Abstraction** | Même code pour local/cloud |
| **Flexibilité** | Change de provider via config |
| **Performance** | CDN integration automatique |
| **Évolutivité** | Local → S3/GCS sans modification code |
| **Environnements** | Dev local, Prod cloud |

#### 🔄 **Migration local → cloud**

1. **Développement** : Stockage local dans `/public/media`
2. **Production** : Switch vers S3/GCS via configuration
3. **Aucun changement** dans le code PHP
4. **URLs générées** automatiquement selon le provider

### Entité MediaObject dédiée

```php
#[Vich\Uploadable]
#[ORM\Entity]
#[ApiResource(
    types: ['https://schema.org/MediaObject'],
    operations: [
        new Get(),
        new GetCollection(),
        new Post(
            inputFormats: ['multipart' => ['multipart/form-data']],
            validationContext: ['groups' => ['Default', 'media_object_create']],
            openapi: new Model\Operation(
                requestBody: new Model\RequestBody(
                    content: new \ArrayObject([
                        'multipart/form-data' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'file' => [
                                        'type' => 'string',
                                        'format' => 'binary'
                                    ]
                                ]
                            ]
                        ]
                    ])
                )
            )
        )
    ],
    normalizationContext: ['groups' => ['media_object:read']]
)]
class MediaObject
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[Groups(['media_object:read'])]
    private ?int $id = null;

    #[ApiProperty(types: ['https://schema.org/contentUrl'], writable: false)]
    #[Groups(['media_object:read'])]
    public ?string $contentUrl = null;

    #[Vich\UploadableField(mapping: 'media_object', fileNameProperty: 'filePath')]
    #[Assert\NotNull(groups: ['media_object_create'])]
    #[Assert\File(maxSize: '5M', mimeTypes: ['image/jpeg', 'image/png', 'image/webp'])]
    public ?File $file = null;

    #[ApiProperty(writable: false)]
    #[ORM\Column(nullable: true)]
    public ?string $filePath = null;
}
```

### Utilisation dans Book

```php
class Book
{
    // Relation vers MediaObject avec type Schema.org
    #[ORM\ManyToOne]
    #[Groups(['book:read', 'book:write'])]
    #[ApiProperty(types: ['https://schema.org/image'])]
    private ?MediaObject $image = null;

    public function getImage(): ?MediaObject
    {
        return $this->image;
    }

    public function setImage(?MediaObject $image): static
    {
        $this->image = $image;
        return $this;
    }
}
```

### Architecture des Serializers

Pour que l'upload fonctionne correctement, il faut plusieurs composants de sérialisation spécialisés :

#### 🔧 **MultipartDecoder**

```php
// src/Serializer/Decoder/MultipartDecoder.php
final class MultipartDecoder implements DecoderInterface
{
    public const FORMAT = 'multipart';

    public function __construct(private RequestStack $requestStack) {}

    public function decode(string $data, string $format, array $context = []): mixed
    {
        $request = $this->requestStack->getCurrentRequest();

        return array_map(static function (string $element) {
            // Décode automatiquement les valeurs JSON dans multipart
            $decoded = json_decode($element, true);
            return \is_array($decoded) ? $decoded : $element;
        }, $request->request->all()) + $request->files->all();
    }

    public function supportsDecoding(string $format): bool
    {
        return self::FORMAT === $format;
    }
}
```

**Rôle** : Traite les requêtes `Content-Type: multipart/form-data` pour extraire fichiers et données.

#### 🔄 **MediaObjectNormalizer**

```php
// src/Serializer/Normalizer/MediaObjectNormalizer.php
final class MediaObjectNormalizer implements NormalizerInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.jsonld.normalizer.item')]
        private NormalizerInterface $normalizer,
        private StorageInterface $storage,
    ) {}

    public function normalize($data, string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED] = true;

        // Génère l'URL publique automatiquement
        $data->contentUrl = $this->storage->resolveUri($data, 'file');

        return $this->normalizer->normalize($data, $format, $context);
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof MediaObject && !isset($context[self::ALREADY_CALLED]);
    }
}
```

**Rôle** : Convertit automatiquement le `filePath` en URL publique accessible (`contentUrl`).

#### 🛡️ **UploadedFileDenormalizer**

```php
// src/Serializer/Denormalizer/UploadedFileDenormalizer.php
final class UploadedFileDenormalizer implements DenormalizerInterface
{
    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): mixed
    {
        return $data; // Passe le fichier tel quel à VichUploader
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = []): bool
    {
        return $data instanceof UploadedFile;
    }
}
```

**Rôle** : Empêche la dénormalisation des fichiers uploadés, les laisse passer directement à VichUploader.

### Organisation des Serializers

```
src/Serializer/
├── Decoder/
│   └── MultipartDecoder.php          # Traite multipart/form-data
├── Normalizer/
│   └── MediaObjectNormalizer.php     # Entity → JSON (+ URL generation)
└── Denormalizer/
    └── UploadedFileDenormalizer.php   # Préserve les UploadedFile
```

### Workflow d'upload en 2 étapes

#### 📤 **1. Upload du fichier vers MediaObject**

```http
POST /api/media_objects
Content-Type: multipart/form-data

file: [BINARY_IMAGE_DATA]
```

**Réponse :**
```json
{
  "@context": "/api/contexts/MediaObject",
  "@id": "/api/media_objects/1",
  "@type": "MediaObject",
  "id": 1,
  "contentUrl": "/media/book-507f1f77bcf86cd799439011.jpg"
}
```

#### 📚 **2. Création du livre avec référence**

```http
POST /api/books
Content-Type: application/json

{
  "title": "Symfony Guide",
  "author": "/api/authors/1",
  "publicationYear": 2024,
  "image": "/api/media_objects/1"
}
```

**Réponse avec image :**
```json
{
  "@context": "/api/contexts/Book",
  "@id": "/api/books/1",
  "@type": "Book",
  "id": 1,
  "title": "Symfony Guide",
  "image": {
    "@id": "/api/media_objects/1",
    "@type": "MediaObject",
    "contentUrl": "/media/book-507f1f77bcf86cd799439011.jpg"
  }
}
```

### Accès aux fichiers

| Type | URL | Description |
|------|-----|-------------|
| **Upload** | `POST /api/books` avec `imageFile` | Upload multipart |
| **Visualisation** | `GET /uploads/books/filename.jpg` | Accès direct fichier |
| **Métadonnées** | `GET /api/books/1` | JSON avec `imageName` |
| **Suppression** | `DELETE /api/books/1` | Supprime fichier automatiquement |

### Validation et sécurité

```php
#[Assert\Image(
    maxSize: '5M',                    // Taille max 5MB
    mimeTypes: [                     // Types autorisés
        'image/jpeg',
        'image/png',
        'image/webp'
    ],
    maxWidth: 2000,                  // Résolution max
    maxHeight: 2000
)]
private ?File $imageFile = null;
```

### Avantages VichUploader

| Avantage | Description |
|----------|-------------|
| **Automatique** | Gestion fichier + métadonnées |
| **Validation** | Contraintes taille/type intégrées |
| **Nommage** | SmartUniqueNamer évite conflits |
| **Nettoyage** | Suppression auto des anciens fichiers |
| **Performance** | Cache invalidation avec updatedAt |

**Points d'enseignement :**
- **Séparation concerns** : File vs filename vs metadata
- **Validation** : Sécurité uploads (taille, type, résolution)
- **Stockage** : Organisation fichiers par mapping

## Commandes utiles

### Docker Compose
```bash
# Démarrer les conteneurs
docker compose up -d
# Arrêter les conteneurs
docker compose down
# Voir les logs
docker compose logs -f
# Accéder au conteneur PHP
docker compose exec php bash
```

### Dans le conteneur Docker
```bash
# Exécuter les migrations
php bin/console doctrine:migrations:migrate

# Vider le cache
php bin/console cache:clear

# Créer un utilisateur
php bin/console app:create-user user@example.com password123 John Do
```
