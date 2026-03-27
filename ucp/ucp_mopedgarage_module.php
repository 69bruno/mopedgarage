<?php
namespace bruno\mopedgarage\ucp;

/**
 * User control panel module for editing personal motorcycles.
 */
class ucp_mopedgarage_module
{
	public $u_action;
	public $tpl_name;
	public $page_title;

	public function main($id, $mode)
	{
		global $config, $phpbb_container, $request, $template, $user;

		/** @var \bruno\mopedgarage\service\bike_manager $bike_manager */
		$bike_manager = $phpbb_container->get('bruno.mopedgarage.manager');
		/** @var \bruno\mopedgarage\service\image_manager $image_manager */
		$image_manager = $phpbb_container->get('bruno.mopedgarage.image_manager');
		/** @var \bruno\mopedgarage\service\field_manager $field_manager */
		$field_manager = $phpbb_container->get('bruno.mopedgarage.field_manager');

		$this->tpl_name = 'ucp_mopedgarage_body';
		$this->page_title = 'UCP_MOPEDGARAGE_EDIT';

		$user->add_lang_ext('bruno/mopedgarage', 'ucp_mopedgarage');
		add_form_key('ucp_mopedgarage');

		$enable_year = (int) ($config['mopedgarage_enable_year'] ?? 1);
		$enable_capacity = (int) ($config['mopedgarage_enable_capacity'] ?? 1);
		$enable_color = (int) ($config['mopedgarage_enable_color'] ?? 1);
		$allow_multiple = (int) ($config['mopedgarage_allow_multiple'] ?? 1);
		$enable_images = (int) ($config['mopedgarage_enable_images'] ?? 1);
		$max_bikes = $allow_multiple ? (int) ($config['mopedgarage_max_bikes'] ?? 3) : 1;
		$max_filesize_kb = (int) ($config['mopedgarage_image_max_filesize_kb'] ?? 5120);
		$max_width = (int) ($config['mopedgarage_image_max_width'] ?? 2200);
		$max_height = (int) ($config['mopedgarage_image_max_height'] ?? 2200);

		$max_bikes = max(1, min(20, $max_bikes));
		$max_filesize_kb = max(128, min(20480, $max_filesize_kb));
		$max_width = max(320, min(6000, $max_width));
		$max_height = max(320, min(6000, $max_height));

		$custom_fields = $field_manager->get_fields(true, 1, null);
		$errors = [];

		if ($enable_images && !$image_manager->ensure_directories())
		{
			$errors[] = $user->lang('UCP_MOPEDGARAGE_UPLOAD_DIR_ERROR');
		}

		if ($request->is_set_post('submit'))
		{
			if (!check_form_key('ucp_mopedgarage'))
			{
				trigger_error('FORM_INVALID');
			}

			$existing_rows = $bike_manager->get_user_bikes((int) $user->data['user_id']);
			$existing_values = $field_manager->get_values_for_bike_ids(array_column($existing_rows, 'id'));
			$processing = [
				'uploaded_files' => [],
			];

			$submitted_rows = $this->build_submitted_rows(
				$request,
				$user,
				$image_manager,
				$custom_fields,
				$existing_rows,
				$existing_values,
				$max_bikes,
				$enable_year,
				$enable_capacity,
				$enable_color,
				$enable_images,
				$max_filesize_kb,
				$max_width,
				$max_height,
				$errors,
				$processing,
				$field_manager
			);

			if (empty($errors))
			{
				$save_result = $bike_manager->save_user_bikes((int) $user->data['user_id'], $submitted_rows);
				foreach ($save_result['removed_images'] as $filename)
				{
					$image_manager->delete_file($filename);
				}
				if (!empty($save_result['deleted_ids']))
				{
					$field_manager->delete_values_for_bike_ids($save_result['deleted_ids']);
				}
				foreach ($save_result['saved_rows'] as $saved_row)
				{
					$slot_index = (int) $saved_row['slot_index'];
					$bike_id = (int) $saved_row['id'];
					foreach ($submitted_rows as $submitted_row)
					{
						if ((int) $submitted_row['slot_index'] === $slot_index)
						{
							$field_manager->save_values_for_bike($bike_id, $submitted_row['custom_fields']);
							break;
						}
					}
				}

				meta_refresh(2, $this->u_action);
				trigger_error($user->lang('UCP_MOPEDGARAGE_SAVED') . '<br><br><a href="' . $this->u_action . '">' . $user->lang('RETURN_UCP') . '</a>');
			}

			foreach ($processing['uploaded_files'] as $filename)
			{
				$image_manager->delete_file($filename);
			}
		}

		$rows = $bike_manager->get_user_bikes((int) $user->data['user_id']);
		$values_by_bike = $field_manager->get_values_for_bike_ids(array_column($rows, 'id'));
		if (empty($rows))
		{
			$rows[] = ['id' => 0, 'brand' => '', 'model' => '', 'year' => '', 'capacity' => '', 'color' => '', 'image' => ''];
		}

		while (count($rows) < $max_bikes)
		{
			$rows[] = ['id' => 0, 'brand' => '', 'model' => '', 'year' => '', 'capacity' => '', 'color' => '', 'image' => ''];
		}

		foreach ($rows as $index => $row)
		{
			$image_name = !empty($row['image']) ? basename((string) $row['image']) : '';
			$image_urls = $image_manager->get_image_urls($image_name);
			$bike_id = (int) ($row['id'] ?? 0);
			$row_values = isset($values_by_bike[$bike_id]) ? $values_by_bike[$bike_id] : [];

			$template->assign_block_vars('bike_rows', [
				'ROW_NUM' => $index + 1,
				'SLOT_INDEX' => $index,
				'ID' => $bike_id,
				'BRAND' => $row['brand'],
				'MODEL' => $row['model'],
				'YEAR' => $row['year'],
				'CAPACITY' => $row['capacity'],
				'COLOR' => $row['color'],
				'IMAGE' => $image_name,
				'IMAGE_URL' => $image_urls['image_url'],
				'THUMB_URL' => $image_urls['thumb_url'],
			]);

			foreach ($custom_fields as $field)
			{
				$field_id = (int) $field['field_id'];
				$raw_value = array_key_exists($field_id, $row_values) ? (string) $row_values[$field_id] : (string) ($field['field_default_value'] ?? '');
				$display_value = $raw_value;
				if ($field['field_type'] === 'boolean')
				{
					$display_value = ($raw_value === '1') ? '1' : '0';
				}

				$template->assign_block_vars('bike_rows.custom_fields', [
					'FIELD_ID' => $field_id,
					'FIELD_NAME' => $field['field_name'],
					'FIELD_LABEL' => $field['field_label'],
					'FIELD_TYPE' => $field['field_type'],
					'FIELD_VALUE' => $display_value,
					'FIELD_DEFAULT_VALUE' => (string) ($field['field_default_value'] ?? ''),
					'FIELD_OPTIONS_RAW' => (string) ($field['field_options'] ?? ''),
					'S_REQUIRED' => !empty($field['field_required']),
					'S_TYPE_TEXT' => $field['field_type'] === 'text',
					'S_TYPE_NUMBER' => $field['field_type'] === 'number',
					'S_TYPE_BOOLEAN' => $field['field_type'] === 'boolean',
					'S_TYPE_SELECT' => $field['field_type'] === 'select',
					'S_BOOL_YES' => $display_value === '1',
					'S_BOOL_NO' => $display_value !== '1',
				]);

				foreach ($field['parsed_options'] as $option_value => $option_label)
				{
					$template->assign_block_vars('bike_rows.custom_fields.options', [
						'OPTION_VALUE' => $option_value,
						'OPTION_LABEL' => $option_label,
						'S_SELECTED' => ((string) $option_value === (string) $display_value),
					]);
				}
			}
		}

		$capabilities = $image_manager->get_capabilities();
		$template->assign_vars([
			'U_ACTION' => $this->u_action,
			'S_ENABLE_YEAR' => $enable_year,
			'S_ENABLE_CAPACITY' => $enable_capacity,
			'S_ENABLE_COLOR' => $enable_color,
			'S_ENABLE_IMAGES' => $enable_images,
			'S_HAS_CUSTOM_FIELDS' => !empty($custom_fields),
			'S_UPLOAD_DIR_OK' => !$enable_images || $image_manager->is_upload_dir_ready(),
			'S_GD_AVAILABLE' => $capabilities['gd'],
			'S_EXIF_AVAILABLE' => $capabilities['exif'],
			'S_WEBP_AVAILABLE' => $capabilities['webp'],
			'MOPEDGARAGE_IMAGE_MAX_SIZE' => $max_filesize_kb,
			'MOPEDGARAGE_IMAGE_MAX_WIDTH' => $max_width,
			'MOPEDGARAGE_IMAGE_MAX_HEIGHT' => $max_height,
			'MOPEDGARAGE_ERRORS' => !empty($errors) ? implode('<br>', $errors) : '',
		]);
	}

	/**
	 * Normalise submitted rows for targeted insert/update/delete persistence.
	 */
	protected function build_submitted_rows(
		$request,
		$user,
		$image_manager,
		array $custom_fields,
		array $existing_rows,
		array $existing_values,
		$max_bikes,
		$enable_year,
		$enable_capacity,
		$enable_color,
		$enable_images,
		$max_filesize_kb,
		$max_width,
		$max_height,
		array &$errors,
		array &$processing,
		$field_manager
	) {
		$existing_by_index = array_values($existing_rows);
		$row_ids = $request->variable('bike_id', [0]);
		$brands = $request->variable('brand', [''], true);
		$models = $request->variable('model', [''], true);
		$years = $request->variable('year', [0]);
		$capacities = $request->variable('capacity', [0]);
		$colors = $request->variable('color', [''], true);
		$existing_images = $request->variable('existing_image', [''], true);
		$delete_entries = $request->variable('delete_entry', [0]);

		$submitted_rows = [];

		for ($i = 0; $i < $max_bikes; $i++)
		{
			$brand = isset($brands[$i]) ? trim((string) $brands[$i]) : '';
			$model = isset($models[$i]) ? trim((string) $models[$i]) : '';
			$year = isset($years[$i]) ? (int) $years[$i] : 0;
			$capacity = isset($capacities[$i]) ? (int) $capacities[$i] : 0;
			$color = isset($colors[$i]) ? trim((string) $colors[$i]) : '';
			$row_id = isset($row_ids[$i]) ? (int) $row_ids[$i] : 0;
			$stored_image = isset($existing_images[$i]) ? basename((string) $existing_images[$i]) : '';
			$delete_flag = (int) $request->variable('bike_image_' . $i . '_delete', 0);
			$remove_image = ($delete_flag === 1);
			$delete_entry = !empty($delete_entries[$i]);

			if (empty($stored_image) && !empty($existing_by_index[$i]['image']))
			{
				$stored_image = basename((string) $existing_by_index[$i]['image']);
			}

			$year = ($year >= 0 && $year <= 9999) ? $year : 0;
			$capacity = max(0, $capacity);
			$row_has_text = ($brand !== '' || $model !== '' || $year > 0 || $capacity > 0 || $color !== '');
			$image = $remove_image ? '' : $stored_image;
			$custom_values = [];
			$raw_custom_values = [];
			$custom_has_input = false;
			$existing_custom_has_input = false;

			foreach ($custom_fields as $field)
			{
				$field_id = (int) $field['field_id'];
				$raw_value = $request->variable('custom_field_' . $i . '_' . $field_id, '', true);
				$raw_custom_values[$field_id] = $raw_value;
				if ($field['field_type'] === 'boolean')
				{
					if ($raw_value === '1')
					{
						$custom_has_input = true;
					}
				}
				else if ($raw_value !== '')
				{
					$custom_has_input = true;
				}

				if ($row_id > 0 && !empty($existing_values[$row_id][$field_id]))
				{
					$existing_custom_has_input = true;
				}
			}

			$row_is_active = $row_has_text || $image !== '' || $row_id > 0 || $custom_has_input || $existing_custom_has_input;

			foreach ($custom_fields as $field)
			{
				$field_id = (int) $field['field_id'];
				$raw_value = isset($raw_custom_values[$field_id]) ? $raw_custom_values[$field_id] : '';

				if (!$row_is_active && $raw_value === '' && $field['field_type'] !== 'boolean')
				{
					continue;
				}

				$validated_value = $field_manager->validate_submitted_value(
					$field,
					$raw_value,
					$errors,
					[$user, 'lang']
				);
				if ($validated_value !== '' || $field['field_type'] === 'boolean')
				{
					$custom_values[$field_id] = $validated_value;
				}
				if ($validated_value !== '' || ($field['field_type'] === 'boolean' && $validated_value === '1'))
				{
					$row_has_text = true;
				}
			}

			if ($enable_images)
			{
				$upload_result = $image_manager->handle_uploaded_image(
					$request->file('bike_image_' . $i),
					(int) $user->data['user_id'],
					$i,
					$max_filesize_kb,
					$max_width,
					$max_height,
					$user
				);

				if (!empty($upload_result['error']))
				{
					$errors[] = $upload_result['error'];
				}
				else if (!empty($upload_result['filename']))
				{
					$image = $upload_result['filename'];
					$processing['uploaded_files'][] = $upload_result['filename'];
				}
			}
			else
			{
				$image = '';
			}

			if ($delete_entry)
			{
				continue;
			}

			if (!$row_has_text && $image === '')
			{
				continue;
			}

			$submitted_rows[] = [
				'id' => $row_id,
				'slot_index' => $i,
				'brand' => mb_substr($brand, 0, 100, 'UTF-8'),
				'model' => mb_substr($model, 0, 100, 'UTF-8'),
				'year' => $enable_year ? $year : 0,
				'capacity' => $enable_capacity ? $capacity : 0,
				'color' => $enable_color ? mb_substr($color, 0, 50, 'UTF-8') : '',
				'image' => mb_substr($image, 0, 255, 'UTF-8'),
				'custom_fields' => $custom_values,
			];
		}

		return $submitted_rows;
	}
}
