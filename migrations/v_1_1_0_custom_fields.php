<?php
namespace bruno\mopedgarage\migrations;

class v_1_1_0_custom_fields extends \phpbb\db\migration\migration
{
    public function effectively_installed()
    {
        return isset($this->config['mopedgarage_version'])
            && version_compare($this->config['mopedgarage_version'], '1.1.0', '>=');
    }

    static public function depends_on()
    {
        return ['\bruno\mopedgarage\migrations\v100'];
    }

    public function update_schema()
    {
        return [
            'add_tables' => [
                $this->table_prefix . 'mopedgarage_fields' => [
                    'COLUMNS' => [
                        'field_id' => ['UINT', null, 'auto_increment'],
                        'field_name' => ['VCHAR:50', ''],
                        'field_label' => ['VCHAR:255', ''],
                        'field_type' => ['VCHAR:20', 'text'],
                        'field_required' => ['BOOL', 0],
                        'field_options' => ['TEXT_UNI', ''],
                        'field_default_value' => ['VCHAR:255', ''],
                        'field_sort' => ['INT:11', 0],
                        'field_active' => ['BOOL', 1],
                        'field_show_profile' => ['BOOL', 1],
                        'field_show_ucp' => ['BOOL', 1],
                        'field_show_search' => ['BOOL', 0],
                        'created_at' => ['UINT', 0],
                    ],
                    'PRIMARY_KEY' => 'field_id',
                    'KEYS' => [
                        'field_name' => ['UNIQUE', 'field_name'],
                        'field_sort' => ['INDEX', 'field_sort'],
                        'field_active' => ['INDEX', 'field_active'],
                    ],
                ],
                $this->table_prefix . 'mopedgarage_field_values' => [
                    'COLUMNS' => [
                        'value_id' => ['UINT', null, 'auto_increment'],
                        'bike_id' => ['UINT', 0],
                        'field_id' => ['UINT', 0],
                        'field_value' => ['TEXT_UNI', ''],
                    ],
                    'PRIMARY_KEY' => 'value_id',
                    'KEYS' => [
                        'bike_field' => ['UNIQUE', ['bike_id', 'field_id']],
                        'bike_id' => ['INDEX', 'bike_id'],
                        'field_id' => ['INDEX', 'field_id'],
                    ],
                ],
            ],
        ];
    }

    public function revert_schema()
    {
        return [
            'drop_tables' => [
                $this->table_prefix . 'mopedgarage_fields',
                $this->table_prefix . 'mopedgarage_field_values',
            ],
        ];
    }

    public function update_data()
    {
        return [
            ['module.add', ['acp', 'ACP_MOPEDGARAGE', [
                'module_basename' => '\\bruno\\mopedgarage\\acp\\acp_mopedgarage_module',
                'module_langname' => 'ACP_MOPEDGARAGE_FIELDS',
                'module_mode' => 'fields',
                'module_auth' => 'acl_a_board',
                'module_enabled' => 1,
                'module_display' => 1,
            ]]],
            ['config.update', ['mopedgarage_version', '1.1.0']],
        ];
    }
}
