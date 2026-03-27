<?php
namespace bruno\mopedgarage\acp;

/**
 * ACP settings screen and custom field management for the Mopedgarage extension.
 */
class acp_mopedgarage_module
{
    public $u_action;
    public $tpl_name;
    public $page_title;

    public function main($id, $mode)
    {
        global $config, $phpbb_container, $request, $template, $user;

        $user->add_lang_ext('bruno/mopedgarage', 'acp_mopedgarage');

        if ($mode === 'fields')
        {
            $field_manager = $phpbb_container->get('bruno.mopedgarage.field_manager');

            $this->tpl_name = 'acp_mopedgarage_fields';
            $this->page_title = 'ACP_MOPEDGARAGE_FIELDS';

            add_form_key('acp_mopedgarage_fields');

            $field_id = (int) $request->variable('field_id', 0);
            $action = (string) $request->variable('action', '');
            if ($action === 'edit' && $field_id <= 0)
            {
                $field_id = (int) $request->variable('id', 0);
            }

            $field_data = [
                'field_name' => '',
                'field_label' => '',
                'field_type' => 'text',
                'field_required' => 0,
                'field_options' => '',
                'field_default_value' => '',
                'field_sort' => 0,
                'field_active' => 1,
                'field_show_profile' => 1,
                'field_show_ucp' => 1,
                'field_show_search' => 0,
            ];
            $errors = [];

            if ($field_id > 0)
            {
                $existing = $field_manager->get_field($field_id);
                if ($existing)
                {
                    $field_data = array_merge($field_data, $existing);
                }
            }

            if ($request->is_set_post('save'))
            {
                if (!check_form_key('acp_mopedgarage_fields'))
                {
                    trigger_error('FORM_INVALID');
                }

                $field_data = [
                    'field_name' => $request->variable('field_name', '', true),
                    'field_label' => $request->variable('field_label', '', true),
                    'field_type' => $request->variable('field_type', 'text'),
                    'field_required' => (int) $request->variable('field_required', 0),
                    'field_options' => $request->variable('field_options', '', true),
                    'field_default_value' => $request->variable('field_default_value', '', true),
                    'field_sort' => (int) $request->variable('field_sort', 0),
                    'field_active' => (int) $request->variable('field_active', 1),
                    'field_show_profile' => (int) $request->variable('field_show_profile', 1),
                    'field_show_ucp' => (int) $request->variable('field_show_ucp', 1),
                    'field_show_search' => 0,
                ];

                $field_manager->validate_field_definition($field_data, $errors, $field_id);

                if (empty($errors))
                {
                    $field_manager->save_field($field_data, $field_id);
                    trigger_error($user->lang('CONFIG_UPDATED') . adm_back_link($this->u_action));
                }
            }
            else if ($request->is_set_post('delete') && $field_id > 0)
            {
                if (!check_form_key('acp_mopedgarage_fields'))
                {
                    trigger_error('FORM_INVALID');
                }

                $field_manager->delete_field($field_id);
                trigger_error($user->lang('CONFIG_UPDATED') . adm_back_link($this->u_action));
            }

            $fields = $field_manager->get_fields();
            foreach ($fields as $field)
            {
                $template->assign_block_vars('field_rows', [
                    'FIELD_NAME' => $field['field_name'],
                    'FIELD_LABEL' => $field['field_label'],
                    'FIELD_TYPE' => $user->lang('ACP_MOPEDGARAGE_TYPE_' . strtoupper($field['field_type'])),
                    'FIELD_SORT' => (int) $field['field_sort'],
                    'S_ACTIVE' => !empty($field['field_active']),
                    'S_REQUIRED' => !empty($field['field_required']),
                    'S_SHOW_PROFILE' => !empty($field['field_show_profile']),
                    'U_EDIT' => $this->u_action . '&amp;action=edit&amp;field_id=' . (int) $field['field_id'],
                ]);
            }

            $field_data['field_name'] = $field_manager->normalise_field_name($field_data['field_name'] ?? '');

            $template->assign_vars([
                'U_ACTION' => $this->u_action,
                'U_MOPEDGARAGE_SETTINGS' => append_sid('index.php', 'i=acp_mopedgarage_module&mode=settings'),
                'FIELD_ERRORS' => !empty($errors) ? implode('<br>', array_map([$user, 'lang'], $errors)) : '',
                'FIELD_ID' => $field_id,
                'FIELD_NAME' => $field_data['field_name'],
                'FIELD_LABEL' => $field_data['field_label'],
                'FIELD_DEFAULT_VALUE' => $field_data['field_default_value'],
                'FIELD_SORT' => (int) $field_data['field_sort'],
                'FIELD_OPTIONS' => $field_data['field_options'],
                'S_EDIT_MODE' => $field_id > 0,
                'S_TYPE_TEXT' => $field_data['field_type'] === 'text',
                'S_TYPE_NUMBER' => $field_data['field_type'] === 'number',
                'S_TYPE_BOOLEAN' => $field_data['field_type'] === 'boolean',
                'S_TYPE_SELECT' => $field_data['field_type'] === 'select',
                'S_FIELD_REQUIRED' => !empty($field_data['field_required']),
                'S_FIELD_ACTIVE' => !isset($field_data['field_active']) || !empty($field_data['field_active']),
                'S_FIELD_SHOW_PROFILE' => !isset($field_data['field_show_profile']) || !empty($field_data['field_show_profile']),
                'S_FIELD_SHOW_UCP' => !isset($field_data['field_show_ucp']) || !empty($field_data['field_show_ucp']),
            ]);

            return;
        }

        $image_manager = $phpbb_container->get('bruno.mopedgarage.image_manager');

        $this->tpl_name = 'acp_mopedgarage_body';
        $this->page_title = 'ACP_MOPEDGARAGE_SETTINGS';

        add_form_key('acp_mopedgarage');

        if ($request->is_set_post('submit'))
        {
            if (!check_form_key('acp_mopedgarage'))
            {
                trigger_error('FORM_INVALID');
            }

            $enable_year = (int) $request->variable('mopedgarage_enable_year', 0);
            $enable_capacity = (int) $request->variable('mopedgarage_enable_capacity', 0);
            $enable_color = (int) $request->variable('mopedgarage_enable_color', 0);
            $allow_multiple = (int) $request->variable('mopedgarage_allow_multiple', 0);
            $max_bikes = max(1, min(20, (int) $request->variable('mopedgarage_max_bikes', 1)));
            $enable_images = (int) $request->variable('mopedgarage_enable_images', 1);
            $max_filesize_kb = max(128, min(20480, (int) $request->variable('mopedgarage_image_max_filesize_kb', 5120)));
            $max_width = max(320, min(6000, (int) $request->variable('mopedgarage_image_max_width', 2200)));
            $max_height = max(320, min(6000, (int) $request->variable('mopedgarage_image_max_height', 2200)));

            set_config('mopedgarage_enable_year', $enable_year);
            set_config('mopedgarage_enable_capacity', $enable_capacity);
            set_config('mopedgarage_enable_color', $enable_color);
            set_config('mopedgarage_allow_multiple', $allow_multiple);
            set_config('mopedgarage_max_bikes', $max_bikes);
            set_config('mopedgarage_enable_images', $enable_images);
            set_config('mopedgarage_image_max_filesize_kb', $max_filesize_kb);
            set_config('mopedgarage_image_max_width', $max_width);
            set_config('mopedgarage_image_max_height', $max_height);

            trigger_error($user->lang('CONFIG_UPDATED') . adm_back_link($this->u_action));
        }

        $capabilities = $image_manager->get_capabilities();

        $template->assign_vars([
            'U_ACTION' => $this->u_action,
            'MOPEDGARAGE_ENABLE_YEAR' => (int) ($config['mopedgarage_enable_year'] ?? 1),
            'MOPEDGARAGE_ENABLE_CAPACITY' => (int) ($config['mopedgarage_enable_capacity'] ?? 1),
            'MOPEDGARAGE_ENABLE_COLOR' => (int) ($config['mopedgarage_enable_color'] ?? 1),
            'MOPEDGARAGE_ALLOW_MULTIPLE' => (int) ($config['mopedgarage_allow_multiple'] ?? 1),
            'MOPEDGARAGE_MAX_BIKES' => (int) ($config['mopedgarage_max_bikes'] ?? 3),
            'MOPEDGARAGE_ENABLE_IMAGES' => (int) ($config['mopedgarage_enable_images'] ?? 1),
            'MOPEDGARAGE_IMAGE_MAX_FILESIZE_KB' => (int) ($config['mopedgarage_image_max_filesize_kb'] ?? 5120),
            'MOPEDGARAGE_IMAGE_MAX_WIDTH' => (int) ($config['mopedgarage_image_max_width'] ?? 2200),
            'MOPEDGARAGE_IMAGE_MAX_HEIGHT' => (int) ($config['mopedgarage_image_max_height'] ?? 2200),
            'S_MOPEDGARAGE_GD_AVAILABLE' => $capabilities['gd'],
            'S_MOPEDGARAGE_EXIF_AVAILABLE' => $capabilities['exif'],
            'S_MOPEDGARAGE_WEBP_AVAILABLE' => $capabilities['webp'],
            'S_MOPEDGARAGE_UPLOAD_DIR_OK' => $image_manager->is_upload_dir_ready(),
            'U_MOPEDGARAGE_FIELDS' => append_sid('index.php', 'i=acp_mopedgarage_module&mode=fields'),
        ]);
    }
}
