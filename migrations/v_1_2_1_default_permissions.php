<?php
namespace bruno\mopedgarage\migrations;

class v_1_2_1_default_permissions extends \phpbb\db\migration\migration
{
    public static function depends_on()
    {
        return [
            '\bruno\mopedgarage\migrations\v_1_2_0_permissions',
        ];
    }

    public function effectively_installed()
    {
        return isset($this->config['mopedgarage_version'])
            && version_compare($this->config['mopedgarage_version'], '1.2.1', '>=');
    }

    public function update_data()
    {
        return [
            ['permission.permission_set', ['REGISTERED', ['u_mopedgarage_view', 'u_mopedgarage_use'], 'group']],
            ['permission.permission_set', ['ADMINISTRATORS', 'a_mopedgarage_manage', 'group']],
            ['config.update', ['mopedgarage_version', '1.2.1']],
        ];
    }

    public function revert_data()
    {
        return [
            ['permission.permission_unset', ['REGISTERED', ['u_mopedgarage_view', 'u_mopedgarage_use'], 'group']],
            ['permission.permission_unset', ['ADMINISTRATORS', 'a_mopedgarage_manage', 'group']],
            ['config.update', ['mopedgarage_version', '1.2.0']],
        ];
    }
}
