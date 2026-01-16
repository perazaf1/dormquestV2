# Configuration de l'envoi d'emails - DormQuest

## Configuration pour le développement local (XAMPP)

En développement local, la fonction `mail()` de PHP ne fonctionnera pas par défaut. Le système affichera directement le lien de réinitialisation à l'écran.

## Configuration pour la production

### Option 1 : Utiliser un serveur SMTP (Recommandé)

Pour un envoi d'emails fiable en production, utilisez un service SMTP comme :
- **Gmail SMTP** (gratuit pour faible volume)
- **SendGrid** (gratuit jusqu'à 100 emails/jour)
- **Mailgun** (gratuit jusqu'à 5000 emails/mois)
- **Amazon SES** (très bon rapport qualité/prix)

#### Installation de PHPMailer (recommandé)

```bash
composer require phpmailer/phpmailer
```

#### Configuration dans config.php

Ajoutez ces constantes :

```php
// Configuration SMTP
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'votre-email@gmail.com');
define('SMTP_PASSWORD', 'votre-mot-de-passe-app');
define('SMTP_ENCRYPTION', 'tls'); // ou 'ssl'
```

### Option 2 : Configurer sendmail (XAMPP Windows)

1. Téléchargez **sendmail** pour Windows
2. Modifiez `C:\xampp\php\php.ini` :
   ```ini
   [mail function]
   sendmail_path = "C:\xampp\sendmail\sendmail.exe -t"
   ```

3. Configurez `C:\xampp\sendmail\sendmail.ini` :
   ```ini
   smtp_server=smtp.gmail.com
   smtp_port=587
   auth_username=votre-email@gmail.com
   auth_password=votre-mot-de-passe-app
   force_sender=votre-email@gmail.com
   ```

### Option 3 : Service d'emailing (Production)

Pour un site en production, utilisez des services dédiés :
- **SendGrid** : Jusqu'à 100 emails/jour gratuits
- **Mailjet** : Jusqu'à 200 emails/jour gratuits
- **Brevo (ex-Sendinblue)** : Jusqu'à 300 emails/jour gratuits

## Test de l'envoi d'emails

Créez un fichier `test-email.php` à la racine :

```php
<?php
$to = "votre-email@exemple.com";
$subject = "Test email DormQuest";
$message = "Ceci est un email de test.";
$headers = "From: noreply@dormquest.com\r\n";
$headers .= "Content-type: text/html; charset=UTF-8\r\n";

if (mail($to, $subject, $message, $headers)) {
    echo "✅ Email envoyé avec succès !";
} else {
    echo "❌ Échec de l'envoi.";
}
?>
```

## 📝 Notes importantes

- En développement local (localhost), le lien de réinitialisation s'affiche directement
- En production, assurez-vous que `SITE_URL` dans `config.php` correspond à votre domaine
- Les tokens expirent après 1 heure
- Les anciens tokens sont automatiquement supprimés lors d'une nouvelle demande

## 🔒 Sécurité

- Les tokens sont hashés en SHA-256 avant stockage en base de données
- Les liens expirent après 1 heure
- Un utilisateur ne peut avoir qu'un seul token actif à la fois
- Le token est supprimé après utilisation

## 🚀 Fonctionnalités implémentées

✅ Page de demande de réinitialisation (`mot-de-passe-oublie.php`)
✅ Page de réinitialisation avec token (`reinitialiser-mdp.php`)
✅ Table `password_resets` en base de données
✅ Validation du token et expiration
✅ Email HTML responsive
✅ Affichage du lien en mode développement local
✅ Suppression automatique des tokens après utilisation
