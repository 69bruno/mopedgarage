<?php
if (!defined('IN_PHPBB')) {
    exit;
}

if (empty($lang) || !is_array($lang)) {
    $lang = [];
}

$lang = array_merge($lang, [
    'MOPEDGARAGE'                         => 'Mopedgarage',
    'MOPEDGARAGE_GALLERY'                 => 'Galerie',
    'MOPEDGARAGE_GALLERY_EXPLAIN'         => 'Hier findest du die Mopeds und Motorräder der Community in einer übersichtlichen Galerie.',
    'MOPEDGARAGE_NO_ENTRIES'              => 'Noch keine Fahrzeuge vorhanden.',
    'MOPEDGARAGE_NO_ENTRIES_EXPLAIN'      => 'Sobald Mitglieder Fahrzeuge eintragen, erscheinen sie hier automatisch.',
    'MOPEDGARAGE_SHOW_PROFILE'            => 'Profil ansehen',
    'MOPEDGARAGE_SHOW_OWNER'              => 'Zum Besitzerprofil',
    'MOPEDGARAGE_OWNER'                   => 'Besitzer',
    'MOPEDGARAGE_BRAND'                   => 'Marke',
    'MOPEDGARAGE_MODEL'                   => 'Modell',
    'MOPEDGARAGE_YEAR'                    => 'Baujahr',
    'MOPEDGARAGE_COLOR'                   => 'Farbe',
    'MOPEDGARAGE_DISPLACEMENT'            => 'Hubraum',
    'MOPEDGARAGE_DISPLACEMENT_SHORT'      => 'ccm',
    'MOPEDGARAGE_MORE_IMAGES'             => 'Weitere Bilder',
    'MOPEDGARAGE_PREVIOUS'                => 'Zurück',
    'MOPEDGARAGE_NEXT'                    => 'Weiter',
    'MOPEDGARAGE_OPEN_IMAGE'              => 'Bild vergrößern',
    'MOPEDGARAGE_IMAGE_OF'                => 'Bild von %s',
    'MOPEDGARAGE_UNKNOWN'                 => 'Unbekannt',
    'MOPEDGARAGE_FILTER_ALL'              => 'Alle',
    'MOPEDGARAGE_EMPTY_TITLE'             => 'Noch nichts zu sehen',
    'MOPEDGARAGE_LIGHTBOX_CLOSE'          => 'Schließen',
    'ACP_MOPEDGARAGE_FIELDS' => 'Zusatzfelder',
]);
