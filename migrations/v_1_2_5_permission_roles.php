<?php
namespace bruno\mopedgarage\migrations;

class v_1_2_5_permission_roles extends \phpbb\db\migration\migration
{
    static public function depends_on()
    {
        return [
            '\bruno\mopedgarage\migrations\v_1_2_1_default_permissions',
        ];
    }

    public function effectively_installed()
    {
        return false;
    }

    public function update_data()
    {
        return [
            // Benutzerrollen
            ['permission.permission_set', ['ROLE_USER_FULL', 'u_mopedgarage_view']],
            ['permission.permission_set', ['ROLE_USER_FULL', 'u_mopedgarage_use']],

            // Optional: Standardnutzer dürfen ansehen
            ['permission.permission_set', ['ROLE_USER_STANDARD', 'u_mopedgarage_view']],

            // Adminrollen
            ['permission.permission_set', ['ROLE_ADMIN_FULL', 'a_mopedgarage_manage']],
        ];
    }

    public function revert_data()
    {
        return [
            ['permission.permission_unset', ['ROLE_USER_FULL', 'u_mopedgarage_view']],
            ['permission.permission_unset', ['ROLE_USER_FULL', 'u_mopedgarage_use']],
            ['permission.permission_unset', ['ROLE_USER_STANDARD', 'u_mopedgarage_view']],
            ['permission.permission_unset', ['ROLE_ADMIN_FULL', 'a_mopedgarage_manage']],
        ];
    }
}
