<?php
if (!defined('IN_PHPBB'))
{
    exit;
}

if (empty($lang) || !is_array($lang))
{
    $lang = [];
}

$lang = array_merge($lang, [
    'ACP_MOPEDGARAGE' => 'Mopedgarage',
    'ACP_MOPEDGARAGE_SETTINGS' => 'Mopedgarage settings',
    'ACP_MOPEDGARAGE_FIELDS' => 'Custom fields',
]);
