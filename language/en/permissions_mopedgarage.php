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
	'ACL_CAT_MOPEDGARAGE' => 'Mopedgarage',

	'ACL_U_MOPEDGARAGE_VIEW' => 'Can view the public Mopedgarage',
	'ACL_U_MOPEDGARAGE_USE' => 'Can manage own Mopedgarage',

	'ACL_A_MOPEDGARAGE_MANAGE' => 'Can manage Mopedgarage settings',
]);
