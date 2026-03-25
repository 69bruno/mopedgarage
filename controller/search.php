<?php
namespace bruno\mopedgarage\controller;

/**
 * Public gallery and filter page for Mopedgarage entries.
 */
class search
{
    protected $db;
    protected $template;
    protected $user;
    protected $request;
    protected $helper;
    protected $config;
    protected $manager;
    protected $image_manager;
    protected $field_manager;

    public function __construct($db, $template, $user, $request, $helper, $config, $manager, $image_manager, $field_manager)
    {
        $this->db = $db;
        $this->template = $template;
        $this->user = $user;
        $this->request = $request;
        $this->helper = $helper;
        $this->config = $config;
        $this->manager = $manager;
        $this->image_manager = $image_manager;
        $this->field_manager = $field_manager;
    }

    public function handle()
    {
        $this->user->add_lang_ext('bruno/mopedgarage', 'ucp_mopedgarage');
        $this->user->add_lang_ext('bruno/mopedgarage', 'mopedgarage_search');

        $brand = trim((string) $this->request->variable('brand', '', true));
        $model = trim((string) $this->request->variable('model', '', true));
        $capacity = (int) $this->request->variable('capacity', 0);
        $year = (int) $this->request->variable('year', 0);
        $with_image = (int) $this->request->variable('with_image', 0);

        $submitted = ($this->request->variable('search', '', true) !== '')
            || ($brand !== '')
            || ($model !== '')
            || ($capacity > 0)
            || ($year > 0)
            || ($with_image === 1);

        $conditions = [];
        if ($brand !== '')
        {
            $conditions[] = "LOWER(b.brand) LIKE '" . $this->db->sql_escape('%' . utf8_strtolower($brand) . '%') . "'";
        }

        if ($model !== '')
        {
            $conditions[] = "LOWER(b.model) LIKE '" . $this->db->sql_escape('%' . utf8_strtolower($model) . '%') . "'";
        }

        if ($capacity > 0)
        {
            $conditions[] = 'b.capacity = ' . (int) $capacity;
        }

        if ($year > 0)
        {
            $conditions[] = 'b.year = ' . (int) $year;
        }

        if ($with_image)
        {
            $conditions[] = "b.image <> ''";
        }

        $error = '';
        $has_results = false;
        $result_count = 0;

        if ($submitted && empty($conditions))
        {
            $error = $this->user->lang('MOPEDGARAGE_SEARCH_ENTER_CRITERIA');
        }
        else
        {
            $sql = 'SELECT u.user_id, u.username, u.user_colour, b.id, b.brand, b.model, b.year, b.capacity, b.color, b.image, b.updated_at, b.created_at
                FROM ' . USERS_TABLE . ' u
                INNER JOIN ' . $this->manager->get_table_name() . ' b
                    ON b.user_id = u.user_id';

            if (!empty($conditions))
            {
                $sql .= ' WHERE ' . implode(' AND ', $conditions);
            }

            $sql .= ' ORDER BY b.updated_at DESC, b.created_at DESC, u.username_clean ASC, b.brand ASC, b.model ASC';

            $result = $this->db->sql_query_limit($sql, 200);

            $rows = [];
            $bike_ids = [];

            while ($row = $this->db->sql_fetchrow($result))
            {
                $rows[] = $row;
                if (!empty($row['id']))
                {
                    $bike_ids[] = (int) $row['id'];
                }
            }
            $this->db->sql_freeresult($result);

            $profile_fields = $this->field_manager->get_fields(true, null, 1);
            $values_by_bike = $this->field_manager->get_values_for_bike_ids($bike_ids);

            foreach ($rows as $row)
            {
                $has_results = true;
                $result_count++;

                $title = trim($row['brand'] . ' ' . $row['model']);
                if ($title === '')
                {
                    $title = $this->user->lang('UCP_MOPEDGARAGE_BIKE');
                }

                $image_urls = $this->image_manager->get_image_urls($row['image']);

                $this->template->assign_block_vars('search_results', [
                    'USERNAME_FULL' => get_username_string('full', (int) $row['user_id'], $row['username'], $row['user_colour']),
                    'PROFILE_URL' => append_sid('memberlist.php', 'mode=viewprofile&u=' . (int) $row['user_id']),
                    'TITLE' => $title,
                    'BRAND' => $row['brand'],
                    'MODEL' => $row['model'],
                    'YEAR' => (int) $row['year'],
                    'CAPACITY' => (int) $row['capacity'],
                    'COLOR' => $row['color'],
                    'IMAGE_URL' => $image_urls['image_url'],
                    'THUMB_URL' => $image_urls['thumb_url'],
                    'S_HAS_IMAGE' => $image_urls['has_image'],
                    'S_SHOW_YEAR' => !empty($this->config['mopedgarage_enable_year']) && !empty($row['year']),
                    'S_SHOW_CAPACITY' => !empty($this->config['mopedgarage_enable_capacity']) && !empty($row['capacity']),
                    'S_SHOW_COLOR' => !empty($this->config['mopedgarage_enable_color']) && $row['color'] !== '',
                ]);

                $bike_id = (int) ($row['id'] ?? 0);
                $stored_values = isset($values_by_bike[$bike_id]) ? $values_by_bike[$bike_id] : [];

                foreach ($profile_fields as $field)
                {
                    $field_id = (int) $field['field_id'];
                    $stored_value = isset($stored_values[$field_id]) ? (string) $stored_values[$field_id] : '';
                    if ($stored_value === '')
                    {
                        continue;
                    }

                    $display_value = $this->field_manager->get_display_value(
                        $field,
                        $stored_value,
                        $this->user->lang('YES'),
                        $this->user->lang('NO')
                    );

                    if ($display_value === '')
                    {
                        continue;
                    }

                    $this->template->assign_block_vars('search_results.custom_fields', [
                        'FIELD_LABEL' => $field['field_label'],
                        'FIELD_VALUE' => $display_value,
                    ]);
                }
            }
        }

        $gallery_url = $this->helper->route('mopedgarage_gallery');

        $this->template->assign_vars([
            'MOPEDGARAGE_SEARCH_BRAND' => $brand,
            'MOPEDGARAGE_SEARCH_MODEL' => $model,
            'MOPEDGARAGE_SEARCH_CAPACITY' => $capacity > 0 ? $capacity : '',
            'MOPEDGARAGE_SEARCH_YEAR' => $year > 0 ? $year : '',
            'S_MOPEDGARAGE_SEARCH_WITH_IMAGE' => (bool) $with_image,
            'MOPEDGARAGE_SEARCH_ERROR' => $error,
            'S_MOPEDGARAGE_SEARCH_SUBMITTED' => $submitted,
            'S_MOPEDGARAGE_SEARCH_RESULTS' => $has_results,
            'S_MOPEDGARAGE_GALLERY_OVERVIEW' => !$submitted,
            'MOPEDGARAGE_SEARCH_COUNT' => $result_count,
            'U_MOPEDGARAGE_SEARCH' => $gallery_url,
            'U_MOPEDGARAGE_GALLERY' => $gallery_url,
        ]);

        return $this->helper->render('mopedgarage_search_body.html', $this->user->lang('MOPEDGARAGE_GALLERY_TITLE'));
    }
}
