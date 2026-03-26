<?php
namespace bruno\mopedgarage\migrations;

class v_1_2_2_modules_safe extends \phpbb\db\migration\migration
{
    protected function mg_module_exists($module_class, $module_langname, $parent_langname = null)
    {
        $sql = 'SELECT m.module_id
            FROM ' . MODULES_TABLE . ' m';

        if ($parent_langname !== null)
        {
            $sql .= ' LEFT JOIN ' . MODULES_TABLE . ' p
                ON p.module_id = m.parent_id';
        }

        $sql .= " WHERE m.module_class = '" . $this->db->sql_escape($module_class) . "'
            AND m.module_langname = '" . $this->db->sql_escape($module_langname) . "'";

        if ($parent_langname !== null)
        {
            $sql .= " AND p.module_langname = '" . $this->db->sql_escape($parent_langname) . "'";
        }

        $result = $this->db->sql_query_limit($sql, 1);
        $row = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);

        return (bool) $row;
    }

    public function effectively_installed()
    {
        return $this->mg_module_exists('acp', 'ACP_MOPEDGARAGE')
            && $this->mg_module_exists('acp', 'ACP_MOPEDGARAGE_SETTINGS', 'ACP_MOPEDGARAGE')
            && $this->mg_module_exists('acp', 'ACP_MOPEDGARAGE_FIELDS', 'ACP_MOPEDGARAGE')
            && $this->mg_module_exists('ucp', 'UCP_MOPEDGARAGE_EDIT');
    }

    public function update_data()
    {
        $data = [];

        if (!$this->mg_module_exists('acp', 'ACP_MOPEDGARAGE'))
        {
            $data[] = ['module.add', [
                'acp',
                0,
                [
                    'module_basename' => '\bruno\mopedgarage\acp\main_module',
                    'module_langname' => 'ACP_MOPEDGARAGE',
                    'module_mode'     => '',
                    'module_auth'     => 'ext_bruno/mopedgarage && acl_a_mopedgarage_manage',
                ],
            ]];
        }

        if (!$this->mg_module_exists('acp', 'ACP_MOPEDGARAGE_SETTINGS', 'ACP_MOPEDGARAGE'))
        {
            $data[] = ['module.add', [
                'acp',
                'ACP_MOPEDGARAGE',
                [
                    'module_basename' => '\bruno\mopedgarage\acp\main_module',
                    'module_langname' => 'ACP_MOPEDGARAGE_SETTINGS',
                    'module_mode'     => 'settings',
                    'module_auth'     => 'ext_bruno/mopedgarage && acl_a_mopedgarage_manage',
                ],
            ]];
        }

        if (!$this->mg_module_exists('acp', 'ACP_MOPEDGARAGE_FIELDS', 'ACP_MOPEDGARAGE'))
        {
            $data[] = ['module.add', [
                'acp',
                'ACP_MOPEDGARAGE',
                [
                    'module_basename' => '\bruno\mopedgarage\acp\main_module',
                    'module_langname' => 'ACP_MOPEDGARAGE_FIELDS',
                    'module_mode'     => 'fields',
                    'module_auth'     => 'ext_bruno/mopedgarage && acl_a_mopedgarage_manage',
                ],
            ]];
        }

        if (!$this->mg_module_exists('ucp', 'UCP_MOPEDGARAGE_EDIT'))
        {
            $data[] = ['module.add', [
                'ucp',
                0,
                [
                    'module_basename' => '\bruno\mopedgarage\ucp\main_module',
                    'module_langname' => 'UCP_MOPEDGARAGE_EDIT',
                    'module_mode'     => 'edit',
                    'module_auth'     => 'ext_bruno/mopedgarage && acl_u_mopedgarage_use',
                ],
            ]];
        }

        return $data;
    }
}
