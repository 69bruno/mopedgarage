<?php
namespace bruno\mopedgarage\service;

/**
 * Data access and persistence for Mopedgarage bikes.
 */
class bike_manager
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

	public function get_table_name()
	{
		return $this->table_prefix . 'mopedgarage_bikes';
	}

	public function get_user_bikes($user_id)
	{
		$rows = [];
		$sql = 'SELECT id, user_id, brand, model, year, capacity, color, image, created_at, updated_at
			FROM ' . $this->get_table_name() . '
			WHERE user_id = ' . (int) $user_id . '
			ORDER BY id ASC';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$rows[] = $row;
		}
		$this->db->sql_freeresult($result);

		return $rows;
	}

	public function get_bikes_for_users(array $user_ids)
	{
		$user_ids = array_values(array_unique(array_map('intval', $user_ids)));
		$user_ids = array_filter($user_ids);
		if (empty($user_ids))
		{
			return [];
		}

		$data = [];
		$sql = 'SELECT id, user_id, brand, model, year, capacity, color, image
			FROM ' . $this->get_table_name() . '
			WHERE ' . $this->db->sql_in_set('user_id', $user_ids) . '
			ORDER BY user_id ASC, id ASC';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$user_id = (int) $row['user_id'];
			if (!isset($data[$user_id]))
			{
				$data[$user_id] = [];
			}
			$data[$user_id][] = $row;
		}
		$this->db->sql_freeresult($result);

		return $data;
	}

	/**
	 * Persist submitted rows without deleting and reinserting the whole user set.
	 *
	 * @param int   $user_id
	 * @param array $submitted_rows Normalised rows including optional existing id and slot_index.
	 *
	 * @return array
	 */
	public function save_user_bikes($user_id, array $submitted_rows)
	{
		$existing_rows = $this->get_user_bikes($user_id);
		$existing_by_id = [];
		$existing_images = [];

		foreach ($existing_rows as $row)
		{
			$row_id = (int) $row['id'];
			$existing_by_id[$row_id] = $row;
			if (!empty($row['image']))
			{
				$existing_images[] = basename((string) $row['image']);
			}
		}

		$keep_ids = [];
		$current_images = [];
		$saved_rows = [];
		$now = time();

		foreach ($submitted_rows as $row)
		{
			$sql_ary = [
				'user_id' => (int) $user_id,
				'brand' => (string) $row['brand'],
				'model' => (string) $row['model'],
				'year' => (int) $row['year'],
				'capacity' => (int) $row['capacity'],
				'color' => (string) $row['color'],
				'image' => (string) $row['image'],
				'updated_at' => $now,
			];

			if (!empty($row['image']))
			{
				$current_images[] = basename((string) $row['image']);
			}

			$row_id = isset($row['id']) ? (int) $row['id'] : 0;
			if ($row_id > 0 && isset($existing_by_id[$row_id]))
			{
				$sql = 'UPDATE ' . $this->get_table_name() . '
					SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
					WHERE id = ' . $row_id . '
						AND user_id = ' . (int) $user_id;
				$this->db->sql_query($sql);
				$keep_ids[] = $row_id;
				$saved_rows[] = [
					'slot_index' => isset($row['slot_index']) ? (int) $row['slot_index'] : -1,
					'id' => $row_id,
				];
				continue;
			}

			$sql_ary['created_at'] = $now;
			$sql = 'INSERT INTO ' . $this->get_table_name() . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
			$this->db->sql_query($sql);
			$new_id = (int) $this->db->sql_nextid();
			$keep_ids[] = $new_id;
			$saved_rows[] = [
				'slot_index' => isset($row['slot_index']) ? (int) $row['slot_index'] : -1,
				'id' => $new_id,
			];
		}

		$deleted_ids = [];
		foreach ($existing_by_id as $row_id => $row)
		{
			if (in_array($row_id, $keep_ids, true))
			{
				continue;
			}

			$deleted_ids[] = (int) $row_id;
			$sql = 'DELETE FROM ' . $this->get_table_name() . '
				WHERE id = ' . (int) $row_id . '
					AND user_id = ' . (int) $user_id;
			$this->db->sql_query($sql);
		}

		return [
			'removed_images' => array_values(array_diff($existing_images, $current_images)),
			'saved_rows' => $saved_rows,
			'deleted_ids' => $deleted_ids,
		];
	}
}
