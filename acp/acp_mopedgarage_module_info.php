<?php
namespace bruno\mopedgarage\acp;

class acp_mopedgarage_module_info
{
	public function module()
	{
		return [
			'filename' => '\bruno\mopedgarage\acp\acp_mopedgarage_module',
			'title' => 'ACP_MOPEDGARAGE',
			'modes' => [
				'settings' => [
					'title' => 'ACP_MOPEDGARAGE_SETTINGS',
					'auth' => 'acl_a_mopedgarage_manage',
					'cat' => ['ACP_MOPEDGARAGE'],
				],
				'fields' => [
					'title' => 'ACP_MOPEDGARAGE_FIELDS',
					'auth' => 'acl_a_mopedgarage_manage',
					'cat' => ['ACP_MOPEDGARAGE'],
				],
			],
		];
	}
}
