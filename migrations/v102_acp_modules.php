<?php
namespace bruno\mopedgarage\migrations;

class v102_acp_modules extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\bruno\mopedgarage\migrations\v101_permissions'];
	}

	public function update_data()
	{
		return [
			['module.add', [
				'acp',
				'ACP_CAT_DOT_MODS',
				[
					'module_langname'	=> 'ACP_MOPEDGARAGE',
					'module_enabled'	=> 1,
					'module_display'	=> 1,
				],
			]],

			['module.add', [
				'acp',
				'ACP_MOPEDGARAGE',
				[
					'module_basename'	=> '\bruno\mopedgarage\acp\acp_mopedgarage_module',
					'module_langname'	=> 'ACP_MOPEDGARAGE_SETTINGS',
					'module_mode'		=> 'settings',
					'module_auth'		=> 'acl_a_mopedgarage_manage',
					'module_enabled'	=> 1,
					'module_display'	=> 1,
				],
			]],

			['module.add', [
				'acp',
				'ACP_MOPEDGARAGE',
				[
					'module_basename'	=> '\bruno\mopedgarage\acp\acp_mopedgarage_module',
					'module_langname'	=> 'ACP_MOPEDGARAGE_FIELDS',
					'module_mode'		=> 'fields',
					'module_auth'		=> 'acl_a_mopedgarage_manage',
					'module_enabled'	=> 1,
					'module_display'	=> 1,
				],
			]],
		];
	}
}
