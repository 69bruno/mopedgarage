<?php
if (!defined('IN_PHPBB')) { exit; }

if (empty($lang) || !is_array($lang)) {
	$lang = [];
}

$lang = array_merge($lang, [
	'ACL_CAT_MOPEDGARAGE'      => 'Mopedgarage',
	'ACL_U_MOPEDGARAGE_VIEW'   => 'Kann Mopedgaragen ansehen',
	'ACL_U_MOPEDGARAGE_USE'    => 'Kann eigene Mopedgarage verwalten',
	'ACL_A_MOPEDGARAGE_MANAGE' => 'Kann Mopedgarage administrieren',
]);
