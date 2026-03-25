<?php
namespace bruno\mopedgarage\migrations;

class v_1_2_6_admin_user_permissions extends \phpbb\db\migration\migration
{
    static public function depends_on()
    {
        return [
            '\bruno\mopedgarage\migrations\v_1_2_5_permission_roles',
        ];
    }

    public function effectively_installed()
    {
        return false;
    }

    public function update_data()
    {
        return [
            ['permission.permission_set', ['ADMINISTRATORS', 'u_mopedgarage_view', 'group']],
            ['permission.permission_set', ['ADMINISTRATORS', 'u_mopedgarage_use', 'group']],
            ['permission.permission_set', ['ADMINISTRATORS', 'a_mopedgarage_manage', 'group']],
        ];
    }

    public function revert_data()
    {
        return [
            ['permission.permission_unset', ['ADMINISTRATORS', 'u_mopedgarage_view', 'group']],
            ['permission.permission_unset', ['ADMINISTRATORS', 'u_mopedgarage_use', 'group']],
            ['permission.permission_unset', ['ADMINISTRATORS', 'a_mopedgarage_manage', 'group']],
        ];
    }
}
