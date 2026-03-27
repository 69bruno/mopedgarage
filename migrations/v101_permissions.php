<?php
namespace bruno\mopedgarage\migrations;

class v101_permissions extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\bruno\mopedgarage\migrations\v100'];
	}

	public function update_data()
	{
		return [
			['permission.add', ['u_mopedgarage_view']],
			['permission.add', ['u_mopedgarage_use']],
			['permission.add', ['a_mopedgarage_manage']],

			['permission.permission_set', [
				'REGISTERED',
				['u_mopedgarage_view', 'u_mopedgarage_use'],
				'group',
			]],
			['permission.permission_set', [
				'ADMINISTRATORS',
				['u_mopedgarage_view', 'u_mopedgarage_use', 'a_mopedgarage_manage'],
				'group',
			]],
		];
	}

	public function revert_data()
	{
		return [
			['permission.remove', ['u_mopedgarage_view']],
			['permission.remove', ['u_mopedgarage_use']],
			['permission.remove', ['a_mopedgarage_manage']],
		];
	}
}
