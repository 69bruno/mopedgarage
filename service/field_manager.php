<?php
namespace bruno\mopedgarage\service;

/**
 * Handles dynamic custom field definitions and stored values for bikes.
 */
class field_manager
{
    /** @var \phpbb\db\driver\driver_interface */
    protected $db;

    /** @var string */
    protected $table_prefix;

    public function __construct($db, $table_prefix)
    {
        $this->db = $db;
        $this->table_prefix = $table_prefix;
    }

    public function get_fields_table_name()
    {
        return $this->table_prefix . 'mopedgarage_fields';
    }

    public function get_values_table_name()
    {
        return $this->table_prefix . 'mopedgarage_field_values';
    }

    public function get_allowed_field_types()
    {
        return ['text', 'number', 'boolean', 'select'];
    }

    public function get_fields($active_only = false, $show_ucp = null, $show_profile = null)
    {
        $rows = [];
        $conditions = [];

        if ($active_only)
        {
            $conditions[] = 'field_active = 1';
        }

        if ($show_ucp !== null)
        {
            $conditions[] = 'field_show_ucp = ' . (int) $show_ucp;
        }

        if ($show_profile !== null)
        {
            $conditions[] = 'field_show_profile = ' . (int) $show_profile;
        }

        $sql = 'SELECT *
            FROM ' . $this->get_fields_table_name();

        if (!empty($conditions))
        {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY field_sort ASC, field_id ASC';

        $result = $this->db->sql_query($sql);
        while ($row = $this->db->sql_fetchrow($result))
        {
            $row['field_id'] = (int) $row['field_id'];
            $row['field_required'] = (int) $row['field_required'];
            $row['field_sort'] = (int) $row['field_sort'];
            $row['field_active'] = (int) $row['field_active'];
            $row['field_show_profile'] = (int) $row['field_show_profile'];
            $row['field_show_ucp'] = (int) $row['field_show_ucp'];
            $row['field_show_search'] = (int) ($row['field_show_search'] ?? 0);
            $row['parsed_options'] = $this->parse_field_options($row['field_options']);
            $rows[] = $row;
        }
        $this->db->sql_freeresult($result);

        return $rows;
    }

    public function get_field($field_id)
    {
        $sql = 'SELECT *
            FROM ' . $this->get_fields_table_name() . '
            WHERE field_id = ' . (int) $field_id;
        $result = $this->db->sql_query($sql);
        $row = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);

        if (!$row)
        {
            return null;
        }

        $row['field_id'] = (int) $row['field_id'];
        $row['field_required'] = (int) $row['field_required'];
        $row['field_sort'] = (int) $row['field_sort'];
        $row['field_active'] = (int) $row['field_active'];
        $row['field_show_profile'] = (int) $row['field_show_profile'];
        $row['field_show_ucp'] = (int) $row['field_show_ucp'];
        $row['field_show_search'] = (int) ($row['field_show_search'] ?? 0);
        $row['parsed_options'] = $this->parse_field_options($row['field_options']);

        return $row;
    }

    public function normalise_field_name($field_name)
    {
        $field_name = strtolower(trim((string) $field_name));
        $field_name = preg_replace('/[^a-z0-9_]+/', '_', $field_name);
        $field_name = trim((string) $field_name, '_');
        return mb_substr($field_name, 0, 50, 'UTF-8');
    }

    public function field_name_exists($field_name, $exclude_field_id = 0)
    {
        $field_name = $this->normalise_field_name($field_name);
        if ($field_name === '')
        {
            return false;
        }

        $sql = 'SELECT field_id
            FROM ' . $this->get_fields_table_name() . "
            WHERE field_name = '" . $this->db->sql_escape($field_name) . "'";

        if ($exclude_field_id > 0)
        {
            $sql .= ' AND field_id <> ' . (int) $exclude_field_id;
        }

        $result = $this->db->sql_query_limit($sql, 1);
        $row = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);

        return (bool) $row;
    }

    public function save_field(array $data, $field_id = 0)
    {
        $sql_ary = [
            'field_name' => $this->normalise_field_name($data['field_name'] ?? ''),
            'field_label' => mb_substr(trim((string) ($data['field_label'] ?? '')), 0, 255, 'UTF-8'),
            'field_type' => (string) ($data['field_type'] ?? 'text'),
            'field_required' => !empty($data['field_required']) ? 1 : 0,
            'field_options' => str_replace(["\r\n", "\r"], "\n", (string) ($data['field_options'] ?? '')),
            'field_default_value' => mb_substr((string) ($data['field_default_value'] ?? ''), 0, 255, 'UTF-8'),
            'field_sort' => (int) ($data['field_sort'] ?? 0),
            'field_active' => !empty($data['field_active']) ? 1 : 0,
            'field_show_profile' => !empty($data['field_show_profile']) ? 1 : 0,
            'field_show_ucp' => !empty($data['field_show_ucp']) ? 1 : 0,
            'field_show_search' => !empty($data['field_show_search']) ? 1 : 0,
        ];

        if ($field_id > 0)
        {
            $sql = 'UPDATE ' . $this->get_fields_table_name() . '
                SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
                WHERE field_id = ' . (int) $field_id;
            $this->db->sql_query($sql);
            return $field_id;
        }

        $sql_ary['created_at'] = time();
        $sql = 'INSERT INTO ' . $this->get_fields_table_name() . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
        $this->db->sql_query($sql);

        return (int) $this->db->sql_nextid();
    }

    public function delete_field($field_id)
    {
        $field_id = (int) $field_id;
        if ($field_id <= 0)
        {
            return;
        }

        $sql = 'DELETE FROM ' . $this->get_values_table_name() . '
            WHERE field_id = ' . $field_id;
        $this->db->sql_query($sql);

        $sql = 'DELETE FROM ' . $this->get_fields_table_name() . '
            WHERE field_id = ' . $field_id;
        $this->db->sql_query($sql);
    }

    public function get_values_for_bike_ids(array $bike_ids)
    {
        $bike_ids = array_values(array_unique(array_map('intval', $bike_ids)));
        $bike_ids = array_filter($bike_ids);
        if (empty($bike_ids))
        {
            return [];
        }

        $data = [];
        $sql = 'SELECT bike_id, field_id, field_value
            FROM ' . $this->get_values_table_name() . '
            WHERE ' . $this->db->sql_in_set('bike_id', $bike_ids);
        $result = $this->db->sql_query($sql);

        while ($row = $this->db->sql_fetchrow($result))
        {
            $bike_id = (int) $row['bike_id'];
            $field_id = (int) $row['field_id'];
            if (!isset($data[$bike_id]))
            {
                $data[$bike_id] = [];
            }
            $data[$bike_id][$field_id] = (string) $row['field_value'];
        }
        $this->db->sql_freeresult($result);

        return $data;
    }

    public function save_values_for_bike($bike_id, array $values)
    {
        $bike_id = (int) $bike_id;
        if ($bike_id <= 0)
        {
            return;
        }

        $sql = 'DELETE FROM ' . $this->get_values_table_name() . '
            WHERE bike_id = ' . $bike_id;
        $this->db->sql_query($sql);

        foreach ($values as $field_id => $value)
        {
            $field_id = (int) $field_id;
            $value = (string) $value;
            if ($field_id <= 0 || $value === '')
            {
                continue;
            }

            $sql_ary = [
                'bike_id' => $bike_id,
                'field_id' => $field_id,
                'field_value' => $value,
            ];
            $sql = 'INSERT INTO ' . $this->get_values_table_name() . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
            $this->db->sql_query($sql);
        }
    }

    public function delete_values_for_bike_ids(array $bike_ids)
    {
        $bike_ids = array_values(array_unique(array_map('intval', $bike_ids)));
        $bike_ids = array_filter($bike_ids);
        if (empty($bike_ids))
        {
            return;
        }

        $sql = 'DELETE FROM ' . $this->get_values_table_name() . '
            WHERE ' . $this->db->sql_in_set('bike_id', $bike_ids);
        $this->db->sql_query($sql);
    }

    public function parse_field_options($raw)
    {
        $raw = str_replace(["\r\n", "\r"], "\n", (string) $raw);
        $lines = array_filter(array_map('trim', explode("\n", $raw)), 'strlen');
        $options = [];

        foreach ($lines as $line)
        {
            $parts = explode('|', $line, 2);
            $value = trim((string) $parts[0]);
            $label = isset($parts[1]) ? trim((string) $parts[1]) : $value;
            if ($value === '')
            {
                continue;
            }

            $options[$value] = $label;
        }

        return $options;
    }

    public function get_display_value(array $field, $stored_value, $yes_label = 'Yes', $no_label = 'No')
    {
        $stored_value = (string) $stored_value;
        if ($stored_value === '')
        {
            return '';
        }

        switch ($field['field_type'])
        {
            case 'boolean':
                return ((string) $stored_value === '1') ? $yes_label : $no_label;

            case 'select':
                $options = isset($field['parsed_options']) ? $field['parsed_options'] : $this->parse_field_options($field['field_options'] ?? '');
                return isset($options[$stored_value]) ? $options[$stored_value] : $stored_value;
        }

        return $stored_value;
    }

    public function validate_field_definition(array $data, array &$errors, $field_id = 0)
    {
        $field_name = $this->normalise_field_name($data['field_name'] ?? '');
        $field_label = trim((string) ($data['field_label'] ?? ''));
        $field_type = (string) ($data['field_type'] ?? '');

        if ($field_name === '')
        {
            $errors[] = 'ACP_MOPEDGARAGE_FIELD_ERROR_NAME';
        }
        else if ($this->field_name_exists($field_name, (int) $field_id))
        {
            $errors[] = 'ACP_MOPEDGARAGE_FIELD_ERROR_NAME_EXISTS';
        }

        if ($field_label === '')
        {
            $errors[] = 'ACP_MOPEDGARAGE_FIELD_ERROR_LABEL';
        }

        if (!in_array($field_type, $this->get_allowed_field_types(), true))
        {
            $errors[] = 'ACP_MOPEDGARAGE_FIELD_ERROR_TYPE';
        }

        if ($field_type === 'select' && empty($this->parse_field_options($data['field_options'] ?? '')))
        {
            $errors[] = 'ACP_MOPEDGARAGE_FIELD_ERROR_OPTIONS';
        }
    }

    public function validate_submitted_value(array $field, $raw_value, array &$errors, $lang_callback = null)
    {
        $label = isset($field['field_label']) ? (string) $field['field_label'] : '';
        $raw_value = is_string($raw_value) ? trim($raw_value) : (string) $raw_value;
        $type = isset($field['field_type']) ? (string) $field['field_type'] : 'text';

        if ($type === 'boolean')
        {
            $raw_value = ($raw_value === '1') ? '1' : '0';
        }

        if (!empty($field['field_required']))
        {
            $is_empty = ($type === 'boolean') ? false : ($raw_value === '');
            if ($is_empty)
            {
                $errors[] = $lang_callback ? $lang_callback('UCP_MOPEDGARAGE_CUSTOM_REQUIRED', $label) : ($label . ' erforderlich');
                return '';
            }
        }

        if ($raw_value === '' && $type !== 'boolean')
        {
            return '';
        }

        switch ($type)
        {
            case 'number':
                if (!preg_match('/^-?\d+(?:[\.,]\d+)?$/', $raw_value))
                {
                    $errors[] = $lang_callback ? $lang_callback('UCP_MOPEDGARAGE_CUSTOM_INVALID_NUMBER', $label) : ($label . ' Zahl ungültig');
                    return '';
                }
                $raw_value = str_replace(',', '.', $raw_value);
                return mb_substr($raw_value, 0, 255, 'UTF-8');

            case 'select':
                $options = isset($field['parsed_options']) ? $field['parsed_options'] : $this->parse_field_options($field['field_options'] ?? '');
                if (!isset($options[$raw_value]))
                {
                    $errors[] = $lang_callback ? $lang_callback('UCP_MOPEDGARAGE_CUSTOM_INVALID_SELECT', $label) : ($label . ' Auswahl ungültig');
                    return '';
                }
                return mb_substr($raw_value, 0, 255, 'UTF-8');

            case 'boolean':
                return ($raw_value === '1') ? '1' : '0';

            case 'text':
            default:
                return mb_substr($raw_value, 0, 255, 'UTF-8');
        }
    }
}
