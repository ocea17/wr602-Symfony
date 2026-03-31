# WR602 - PDF Generator

> Générateur de PDF en ligne développé avec Symfony 7.2

[![Symfony](https://img.shields.io/badge/Symfony-7.2-black)](https://symfony.com)
[![PHP](https://img.shields.io/badge/PHP-8.3-blue)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green)](LICENSE)

## Description

Application web permettant de générer des PDF à partir de différentes sources : URL, HTML, formulaires, fichiers Office, images, etc. Système d'abonnement avec 3 plans (FREE, BASIC, PREMIUM) et intégration Stripe pour les paiements.

[![Voir PDF Generator en ligne](https://img.shields.io/badge/Accéder%20au%20site-PDF%20Generator-green)](https://pdfgenerator.oceaneleite.fr/)

## Fonctionnalités

### Génération de PDF
- URL vers PDF
- HTML vers PDF
- Formulaire vers PDF
- Texte vers PDF
- Fusion de PDFs
- Séparation de PDFs
- Capture d'écran vers PDF
- Fichier Office vers PDF (Word, Excel, PowerPoint)

### Gestion utilisateurs
- Inscription / Connexion
- Profil utilisateur personnalisable
- Gestion des contacts
- Historique des PDF générés
- Envoi de PDF par email aux contacts

### Système d'abonnement
- 3 plans : FREE, BASIC, PREMIUM
- Limites de génération par jour
- Stockage de PDF
- Paiement Stripe intégré
- Webhook Stripe pour mise à jour automatique

### Qualité du code
- PHPStan niveau 5 (0 erreur)
- PHP CS Fixer (PSR-12)
- Tests unitaires (PHPUnit)
- Sécurité (Composer audit)

## Installation

### Prérequis

- Docker & Docker Compose
- Git

### Étapes
```bash
# 1. Clone le projet
git clone https://github.com/ocea17/wr602-Symfony.git
cd wr602-Symfony

# 2. Lance Docker
docker-compose up -d

# 3. Installe les dépendances
docker exec -it symfony-web-2026 composer install

# 4. Crée la base de données
docker exec -it symfony-web-2026 php bin/console doctrine:database:create
docker exec -it symfony-web-2026 php bin/console doctrine:migrations:migrate -n

# 5. Charge les fixtures
docker exec -it symfony-web-2026 php bin/console doctrine:fixtures:load -n

# 6. Accède à l'application
http://localhost:8319
```

## Note  

**Le projet est entièrement fonctionnel SANS configuration Stripe/Email.**

Les comptes de démo sont déjà créés et vous pouvez :
- Générer tous types de PDF
- Gérer les contacts
- Consulter l'historique
- Changer de plan via commande

Stripe et l'email sont optionnels pour tester l'application.

## Configuration

### Utilisation rapide (SANS Stripe/Email)

Pour tester le projet sans configurer Stripe ni l'email :
```bash
# Connectez-vous avec un compte démo
# Email: demo.premium@pdfgenerator.com
# Password: demo123
```

**Autres comptes disponibles** :
```
demo.free@pdfgenerator.com / demo123    (Plan FREE)
demo.basic@pdfgenerator.com / demo123   (Plan BASIC)
demo.premium@pdfgenerator.com / demo123 (Plan PREMIUM)
```

**Changer le plan d'un utilisateur manuellement** :
```bash
docker exec -it symfony-web-2026 php bin/console app:change-plan demo.free@pdfgenerator.com PREMIUM
```

**Fonctionnalités disponibles sans config** :
- Génération de PDF (tous types)
- Gestion des contacts
- Historique des PDF
- Changement de plan (via commande)

**Fonctionnalités nécessitant la config** :
- Paiement Stripe (peut être contourné avec la commande ci-dessus)
- Envoi d'emails

---

### Configuration complète (optionnel)

Si vous souhaitez tester les paiements Stripe et l'envoi d'emails :

#### 1. Variables d'environnement

Copie `.env.example` vers `.env.local` dans le dossier `www/` :
```bash
cp www/.env.example www/.env.local
```

Édite `www/.env.local` et configure :
```bash
###> stripe/stripe-php ###
STRIPE_SECRET_KEY=sk_test_votre_clé_stripe
STRIPE_PUBLISHABLE_KEY=pk_test_votre_clé_stripe
STRIPE_WEBHOOK_SECRET=whsec_votre_secret_webhook
###< stripe/stripe-php ###

###> symfony/mailer ###
MAILER_DSN=smtp://votre_login:votre_mdp@smtp-relay.brevo.com:587
###< symfony/mailer ###
```

#### 2. Stripe Webhook (développement local)

Pour tester les paiements en local :
```bash
# 1. Installe Stripe CLI
# https://stripe.com/docs/stripe-cli

# 2. Connecte-toi à Stripe
stripe login

# 3. Écoute les webhooks
stripe listen --forward-to localhost:8319/subscription/webhook
```

La commande affichera un `webhook signing secret` à copier dans `.env.local`.

#### 3. Carte de test Stripe
```
Numéro: 4242 4242 4242 4242
Date: N'importe quelle date future
CVC: N'importe quel 3 chiffres
```

### Commandes utiles

**Changer le plan d'un utilisateur** :
```bash
docker exec -it symfony-web-2026 php bin/console app:change-plan <email> <PLAN>
# Exemple: php bin/console app:change-plan demo.free@pdfgenerator.com PREMIUM
```

**Traiter la queue PDF (génération asynchrone)** :
```bash
docker exec -it symfony-web-2026 php bin/console app:handle-queue
```

**Note** : En production, cette commande devrait être dans un cron.

## Problèmes connus et solutions

### Extension PHP cURL

**Problème** : Le SDK Stripe nécessite l'extension PHP cURL, mais elle n'est pas disponible dans l'image Docker utilisée (`mmi3docker/symfony-2024`), ce qui a entraîné des erreurs bloquantes. 

**Solution implémentée**  : Utilisation de `SymfonyStripeHttpClient` qui contourne cURL en utilisant le `HttpClient` de Symfony.

**Fichiers concernés** :
- `src/Service/SymfonyStripeHttpClient.php` - Wrapper HTTP sans cURL
- `src/Service/StripeService.php` - Force l'utilisation du wrapper
- `src/Controller/SubscriptionController.php` - Injection du HttpClient

**Résultat** : Stripe fonctionne parfaitement sans cURL ! 

## Tests et qualité du code

### Lancer les tests
```bash
# Tests unitaires (3/3 passent)
docker exec -it symfony-web-2026 vendor/bin/phpunit

# Analyse statique PHPStan (niveau 5, 0 erreur)
docker exec -it symfony-web-2026 vendor/bin/phpstan analyse

# Formatage du code PSR-12 (appliqué)
docker exec -it symfony-web-2026 vendor/bin/php-cs-fixer fix

# Audit de sécurité (0 vulnérabilité)
docker exec -it symfony-web-2026 composer audit
```

### Métriques de qualité

-  **PHPStan** : Niveau 5, 0 erreur
-  **PHP CS Fixer** : PSR-12 appliqué
-  **Tests unitaires** : 3/3 passent (100%)
-  **Sécurité** : 0 vulnérabilité (Composer audit)
-  **Architecture** : BaseController pour type-safe authentication
-  **Documentation** : SECURITY.md, README.md

## Sécurité

Voir [SECURITY.md](SECURITY.md) pour les détails complets.

**Mesures implémentées** :
-  Hachage bcrypt des mots de passe
-  Protection CSRF sur tous les formulaires
-  Contrôle d'accès basé sur les rôles (RBAC)
-  Validation des uploads (type, taille)
-  Protection XSS (Twig auto-escaping)
-  Protection SQL injection (Doctrine ORM)
-  Vérification signature webhook Stripe

## Technologies

- **Framework** : Symfony 7.2
- **PHP** : 8.3
- **Base de données** : MariaDB 10.8
- **PDF Generator** : Gotenberg
- **Paiement** : Stripe (SDK v7)
- **Email** : Brevo (SMTP)
- **CSS** : Tailwind CSS
- **Icons** : Lucide
- **Tests** : PHPUnit 12.5
- **Analyse statique** : PHPStan niveau 5
- **Code style** : PHP CS Fixer (PSR-12)


## Services Docker

| Service | Image | Port | Description |
|---------|-------|------|-------------|
| web | mmi3docker/symfony-2024 | 8319 | Application Symfony |
| database | mariadb:10.8 | 3306 | Base de données |
| phpmyadmin | phpmyadmin | 8080 | Interface MySQL |
| gotenberg | gotenberg/gotenberg:8 | 3000 | Générateur PDF |
| maildev | maildev/maildev | 1080 | Serveur mail dev |

## Auteur

**Océane Leite**
- Email : oce.leite1712@gmail.com
- Portfolio : [oceaneleite.fr](https://oceaneleite.fr)
- GitHub : [@ocea17](https://github.com/ocea17)

## Licence

MIT

## Contexte académique

Projet réalisé dans le cadre du BUT MMI (Métiers du Multimédia et de l'Internet) - Parcours Développement Web.

IUT de Troyes - Université de Reims Champagne-Ardenne
Année : 2025-2026
Module : WR602 - Développement Web Avancé
