<?php
/**
 * Generateur de placeholders SVG pour les annonces
 * Usage: placeholder.php?type=studio&seed=123
 */

// Configuration
$type = $_GET['type'] ?? 'default';
$seed = $_GET['seed'] ?? rand(1, 1000);
$width = $_GET['width'] ?? 800;
$height = $_GET['height'] ?? 600;

// Couleurs selon le type de logement
$colors = [
    'studio' => ['#667eea', '#764ba2'],
    'colocation' => ['#f093fb', '#f5576c'],
    'residence_etudiante' => ['#4facfe', '#00f2fe'],
    'chambre_habitant' => ['#43e97b', '#38f9d7'],
    'default' => ['#667eea', '#764ba2']
];

// Icones selon le type
$icons = [
    'studio' => '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline>',
    'colocation' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path>',
    'residence_etudiante' => '<rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="9" y1="3" x2="9" y2="21"></line><line x1="15" y1="3" x2="15" y2="21"></line><line x1="3" y1="9" x2="21" y2="9"></line><line x1="3" y1="15" x2="21" y2="15"></line>',
    'chambre_habitant' => '<path d="M20 9v11a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V9"></path><path d="M9 22V12h6v10M2 10.6L12 2l10 8.6"></path>',
    'default' => '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>'
];

// Titres selon le type
$titles = [
    'studio' => 'Studio',
    'colocation' => 'Colocation',
    'residence_etudiante' => 'Residence',
    'chambre_habitant' => 'Chambre',
    'default' => 'Logement'
];

$selectedColors = $colors[$type] ?? $colors['default'];
$selectedIcon = $icons[$type] ?? $icons['default'];
$selectedTitle = $titles[$type] ?? $titles['default'];

// Header pour le SVG
header('Content-Type: image/svg+xml');
header('Cache-Control: public, max-age=86400'); // Cache 24h

// Generation du SVG
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<svg width="<?= $width ?>" height="<?= $height ?>" xmlns="http://www.w3.org/2000/svg">
    <defs>
        <linearGradient id="grad<?= $seed ?>" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:<?= $selectedColors[0] ?>;stop-opacity:1" />
            <stop offset="100%" style="stop-color:<?= $selectedColors[1] ?>;stop-opacity:1" />
        </linearGradient>
        <pattern id="pattern<?= $seed ?>" x="0" y="0" width="40" height="40" patternUnits="userSpaceOnUse">
            <circle cx="20" cy="20" r="1.5" fill="rgba(255,255,255,0.1)"/>
        </pattern>
    </defs>

    <!-- Background avec gradient -->
    <rect width="<?= $width ?>" height="<?= $height ?>" fill="url(#grad<?= $seed ?>)"/>

    <!-- Pattern decoratif -->
    <rect width="<?= $width ?>" height="<?= $height ?>" fill="url(#pattern<?= $seed ?>)"/>

    <!-- Icone centrale -->
    <g transform="translate(<?= $width/2 - 60 ?>, <?= $height/2 - 80 ?>)">
        <svg width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.9)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <?= $selectedIcon ?>
        </svg>
    </g>

    <!-- Texte -->
    <text x="50%" y="<?= $height/2 + 60 ?>"
          font-family="Arial, sans-serif"
          font-size="28"
          font-weight="600"
          fill="rgba(255,255,255,0.95)"
          text-anchor="middle">
        <?= $selectedTitle ?>
    </text>

    <!-- Sous-texte -->
    <text x="50%" y="<?= $height/2 + 90 ?>"
          font-family="Arial, sans-serif"
          font-size="16"
          fill="rgba(255,255,255,0.7)"
          text-anchor="middle">
        Photo non disponible
    </text>
</svg>
