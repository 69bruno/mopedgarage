<?php
namespace bruno\mopedgarage\migrations;

class v_1_2_0_permissions extends \phpbb\db\migration\migration
{
    public static function depends_on()
    {
        return [
            '\\bruno\\mopedgarage\\migrations\\v_1_1_0_custom_fields',
        ];
    }

    public function effectively_installed()
    {
        return isset($this->config['mopedgarage_version'])
            && version_compare($this->config['mopedgarage_version'], '1.2.0', '>=');
    }

    public function update_data()
    {
        return [
            ['permission.add', ['u_mopedgarage_view']],
            ['permission.add', ['u_mopedgarage_use']],
            ['permission.add', ['a_mopedgarage_manage']],
            ['custom', [[$this, 'update_module_auths']]],
            ['config.update', ['mopedgarage_version', '1.2.0']],
        ];
    }

    public function revert_data()
    {
        return [
            ['custom', [[$this, 'revert_module_auths']]],
            ['permission.remove', ['u_mopedgarage_view']],
            ['permission.remove', ['u_mopedgarage_use']],
            ['permission.remove', ['a_mopedgarage_manage']],
        ];
    }

    public function update_module_auths()
    {
        $sql = 'UPDATE ' . MODULES_TABLE . "
            SET module_auth = 'acl_u_mopedgarage_use'
            WHERE module_basename = '" . $this->db->sql_escape('\\bruno\\mopedgarage\\ucp\\ucp_mopedgarage_module') . "'";
        $this->sql_query($sql);

        $sql = 'UPDATE ' . MODULES_TABLE . "
            SET module_auth = 'acl_a_mopedgarage_manage'
            WHERE module_basename = '" . $this->db->sql_escape('\\bruno\\mopedgarage\\acp\\acp_mopedgarage_module') . "'";
        $this->sql_query($sql);
    }

    public function revert_module_auths()
    {
        $sql = 'UPDATE ' . MODULES_TABLE . "
            SET module_auth = 'acl_u_'
            WHERE module_basename = '" . $this->db->sql_escape('\\bruno\\mopedgarage\\ucp\\ucp_mopedgarage_module') . "'";
        $this->sql_query($sql);

        $sql = 'UPDATE ' . MODULES_TABLE . "
            SET module_auth = 'acl_a_board'
            WHERE module_basename = '" . $this->db->sql_escape('\\bruno\\mopedgarage\\acp\\acp_mopedgarage_module') . "'";
        $this->sql_query($sql);
    }
}
