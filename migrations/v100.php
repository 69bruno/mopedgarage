<?php
namespace bruno\mopedgarage\migrations;

class v100 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['mopedgarage_version'])
			&& version_compare($this->config['mopedgarage_version'], '1.0.0', '>=');
	}

	public static function depends_on()
	{
		return ['\phpbb\db\migration\data\v330\v330'];
	}

	public function update_schema()
	{
		return [
			'add_tables' => [
				$this->table_prefix . 'mopedgarage_bikes' => [
					'COLUMNS' => [
						'id'			=> ['UINT', null, 'auto_increment'],
						'user_id'		=> ['UINT', 0],
						'brand'			=> ['VCHAR:100', ''],
						'model'			=> ['VCHAR:100', ''],
						'year'			=> ['UINT:4', 0],
						'capacity'		=> ['UINT', 0],
						'color'			=> ['VCHAR:50', ''],
						'image'			=> ['VCHAR:255', ''],
						'created_at'	=> ['UINT', 0],
						'updated_at'	=> ['UINT', 0],
					],
					'PRIMARY_KEY'	=> 'id',
					'KEYS' => [
						'user_id'			=> ['INDEX', 'user_id'],
						'brand'				=> ['INDEX', 'brand'],
						'model'				=> ['INDEX', 'model'],
						'year'				=> ['INDEX', 'year'],
						'capacity'			=> ['INDEX', 'capacity'],
						'user_brand_model'	=> ['INDEX', ['user_id', 'brand', 'model']],
					],
				],

				$this->table_prefix . 'mopedgarage_fields' => [
					'COLUMNS' => [
						'field_id'				=> ['UINT', null, 'auto_increment'],
						'field_name'			=> ['VCHAR:50', ''],
						'field_label'			=> ['VCHAR:255', ''],
						'field_type'			=> ['VCHAR:20', 'text'],
						'field_required'		=> ['BOOL', 0],
						'field_options'			=> ['MTEXT_UNI', ''],
						'field_default_value'	=> ['VCHAR:255', ''],
						'field_sort'			=> ['UINT', 0],
						'field_active'			=> ['BOOL', 1],
						'field_show_profile'	=> ['BOOL', 1],
						'field_show_ucp'		=> ['BOOL', 1],
						'field_show_search'		=> ['BOOL', 0],
						'created_at'			=> ['UINT', 0],
					],
					'PRIMARY_KEY'	=> 'field_id',
					'KEYS' => [
						'field_name'	=> ['UNIQUE', 'field_name'],
						'field_sort'	=> ['INDEX', 'field_sort'],
					],
				],

				$this->table_prefix . 'mopedgarage_field_values' => [
					'COLUMNS' => [
						'bike_id'		=> ['UINT', 0],
						'field_id'		=> ['UINT', 0],
						'field_value'	=> ['VCHAR:255', ''],
					],
					'PRIMARY_KEY'	=> ['bike_id', 'field_id'],
					'KEYS' => [
						'field_id'	=> ['INDEX', 'field_id'],
					],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_tables' => [
				$this->table_prefix . 'mopedgarage_field_values',
				$this->table_prefix . 'mopedgarage_fields',
				$this->table_prefix . 'mopedgarage_bikes',
			],
		];
	}

	public function update_data()
	{
		return [
			['config.add', ['mopedgarage_enable_year', 1]],
			['config.add', ['mopedgarage_enable_capacity', 1]],
			['config.add', ['mopedgarage_enable_color', 1]],
			['config.add', ['mopedgarage_allow_multiple', 1]],
			['config.add', ['mopedgarage_max_bikes', 3]],
			['config.add', ['mopedgarage_enable_images', 1]],
			['config.add', ['mopedgarage_image_max_filesize_kb', 5120]],
			['config.add', ['mopedgarage_image_max_width', 2200]],
			['config.add', ['mopedgarage_image_max_height', 2200]],
			['config.add', ['mopedgarage_enable_gallery', 1]],
			['config.add', ['mopedgarage_lightbox_global', 1]],
			['config.add', ['mopedgarage_mobile_card_scale', 'compact']],

			['permission.add', ['u_mopedgarage_view']],
			['permission.add', ['u_mopedgarage_use']],
			['permission.add', ['a_mopedgarage_manage']],

			['permission.permission_set', ['REGISTERED', ['u_mopedgarage_view', 'u_mopedgarage_use'], 'group']],
			['permission.permission_set', ['ADMINISTRATORS', ['u_mopedgarage_view', 'u_mopedgarage_use', 'a_mopedgarage_manage'], 'group']],

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

			['config.add', ['mopedgarage_version', '1.0.0']],
		];
	}

	public function revert_data()
	{
		return [
			['config.remove', ['mopedgarage_enable_year']],
			['config.remove', ['mopedgarage_enable_capacity']],
			['config.remove', ['mopedgarage_enable_color']],
			['config.remove', ['mopedgarage_allow_multiple']],
			['config.remove', ['mopedgarage_max_bikes']],
			['config.remove', ['mopedgarage_enable_images']],
			['config.remove', ['mopedgarage_image_max_filesize_kb']],
			['config.remove', ['mopedgarage_image_max_width']],
			['config.remove', ['mopedgarage_image_max_height']],
			['config.remove', ['mopedgarage_enable_gallery']],
			['config.remove', ['mopedgarage_lightbox_global']],
			['config.remove', ['mopedgarage_mobile_card_scale']],
			['config.remove', ['mopedgarage_version']],

			['permission.remove', ['u_mopedgarage_view']],
			['permission.remove', ['u_mopedgarage_use']],
			['permission.remove', ['a_mopedgarage_manage']],
		];
	}
}
