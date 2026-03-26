<?php
namespace bruno\mopedgarage\migrations;

class v103_ucp_modules extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\bruno\mopedgarage\migrations\v102_acp_modules'];
	}

	public function update_data()
	{
		return [
			['module.add', [
				'ucp',
				'UCP_PROFILE',
				[
					'module_basename'	=> '\bruno\mopedgarage\ucp\ucp_mopedgarage_module',
					'module_langname'	=> 'UCP_MOPEDGARAGE_EDIT',
					'module_mode'		=> 'edit',
					'module_auth'		=> 'acl_u_mopedgarage_use',
					'module_enabled'	=> 1,
					'module_display'	=> 1,
				],
			]],
		];
	}
}
