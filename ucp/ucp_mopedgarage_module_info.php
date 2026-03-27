<?php
namespace bruno\mopedgarage\ucp;

class ucp_mopedgarage_module_info
{
	public function module()
	{
		return [
			'filename' => '\bruno\mopedgarage\ucp\ucp_mopedgarage_module',
			'title' => 'UCP_MOPEDGARAGE_TITLE',
			'modes' => [
				'edit' => [
					'title' => 'UCP_MOPEDGARAGE_EDIT',
					'auth' => 'acl_u_mopedgarage_use',
					'cat' => ['UCP_PROFILE'],
				],
			],
		];
	}
}
