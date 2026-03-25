<?php
namespace bruno\mopedgarage\event;

use bruno\mopedgarage\service\bike_manager;
use bruno\mopedgarage\service\field_manager;
use bruno\mopedgarage\service\image_manager;
use phpbb\auth\auth;
use phpbb\config\config;
use phpbb\controller\helper;
use phpbb\event\data;
use phpbb\language\language;
use phpbb\request\request_interface;
use phpbb\template\template;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{
    /** @var auth */
    protected $auth;

    /** @var template */
    protected $template;

    /** @var language */
    protected $language;

    /** @var config */
    protected $config;

    /** @var request_interface */
    protected $request;

    /** @var helper */
    protected $helper;

    /** @var bike_manager */
    protected $bike_manager;

    /** @var field_manager */
    protected $field_manager;

    /** @var image_manager */
    protected $image_manager;

    public function __construct(
        auth $auth,
        template $template,
        language $language,
        config $config,
        request_interface $request,
        helper $helper,
        bike_manager $bike_manager,
        field_manager $field_manager,
        image_manager $image_manager
    ) {
        $this->auth = $auth;
        $this->template = $template;
        $this->language = $language;
        $this->config = $config;
        $this->request = $request;
        $this->helper = $helper;
        $this->bike_manager = $bike_manager;
        $this->field_manager = $field_manager;
        $this->image_manager = $image_manager;
    }

    public static function getSubscribedEvents()
    {
        return [
            'core.user_setup'                 => 'on_user_setup',
            'core.page_header'               => 'on_page_header',
            'core.permissions'               => 'add_permissions',
            'core.viewtopic_modify_post_row' => 'on_viewtopic_modify_post_row',
            'core.memberlist_view_profile'   => 'on_memberlist_view_profile',
        ];
    }

    public function on_user_setup(data $event)
    {
        $lang_set_ext = $event['lang_set_ext'];

        foreach (['common', 'acp_mopedgarage', 'ucp_mopedgarage', 'mopedgarage_search', 'permissions_mopedgarage'] as $lang_set)
        {
            $lang_set_ext[] = [
                'ext_name' => 'bruno/mopedgarage',
                'lang_set' => $lang_set,
            ];
        }

        $event['lang_set_ext'] = $lang_set_ext;
    }

    public function on_page_header()
    {
        $gallery_url = $this->helper->route('mopedgarage_gallery');

        $this->template->assign_vars([
            'U_MOPEDGARAGE_SEARCH'     => $gallery_url,
            'U_MOPEDGARAGE_GALLERY'    => $gallery_url,
            'MOPEDGARAGE_GALLERY_URL'  => $gallery_url,
            'MOPEDGARAGE_ENABLE_GALLERY' => true,
            'MOPEDGARAGE_LIGHTBOX'     => !empty($this->config['mopedgarage_lightbox_global']),
        ]);
    }

    public function add_permissions(data $event)
    {
        $categories = $event['categories'];
        $permissions = $event['permissions'];

        $categories['mopedgarage'] = 'ACL_CAT_MOPEDGARAGE';

        $permissions['u_mopedgarage_view'] = [
            'lang' => 'ACL_U_MOPEDGARAGE_VIEW',
            'cat'  => 'mopedgarage',
        ];

        $permissions['u_mopedgarage_use'] = [
            'lang' => 'ACL_U_MOPEDGARAGE_USE',
            'cat'  => 'mopedgarage',
        ];

        $permissions['a_mopedgarage_manage'] = [
            'lang' => 'ACL_A_MOPEDGARAGE_MANAGE',
            'cat'  => 'mopedgarage',
        ];

        $event['categories'] = $categories;
        $event['permissions'] = $permissions;
    }

    public function on_viewtopic_modify_post_row(data $event)
    {
        if (!$this->auth->acl_get('u_mopedgarage_view'))
        {
            return;
        }

        $row = $event['row'];
        $user_id = !empty($row['user_id']) ? (int) $row['user_id'] : 0;
        if ($user_id <= 0)
        {
            return;
        }

        $bikes = $this->bike_manager->get_user_bikes($user_id);
        if (empty($bikes))
        {
            return;
        }

        $template_bikes = $this->build_bike_template_rows($bikes, false);
        if (empty($template_bikes))
        {
            return;
        }

        $post_row = $event['post_row'];
        $post_row['S_MOPEDGARAGE_POST'] = true;
        $post_row['MOPEDGARAGE_POST_BIKES'] = $template_bikes;
        $post_row['MOPEDGARAGE_POST_BIKE_COUNT'] = count($template_bikes);
        $event['post_row'] = $post_row;
    }

    public function on_memberlist_view_profile(data $event)
    {
        if (!$this->auth->acl_get('u_mopedgarage_view'))
        {
            return;
        }

        $member = $event['member'];
        $user_id = !empty($member['user_id']) ? (int) $member['user_id'] : 0;
        if ($user_id <= 0)
        {
            return;
        }

        $bikes = $this->bike_manager->get_user_bikes($user_id);
        if (empty($bikes))
        {
            return;
        }

        $template_bikes = $this->build_bike_template_rows($bikes, true);
        if (empty($template_bikes))
        {
            return;
        }

        $this->template->assign_var('S_MOPEDGARAGE_PROFILE', true);

        foreach ($template_bikes as $bike)
        {
            $this->template->assign_block_vars('mopedgarage_bikes', [
                'TITLE'           => $bike['TITLE'],
                'BRAND'           => $bike['BRAND'],
                'MODEL'           => $bike['MODEL'],
                'YEAR'            => $bike['YEAR'],
                'CAPACITY'        => $bike['CAPACITY'],
                'COLOR'           => $bike['COLOR'],
                'IMAGE_URL'       => $bike['IMAGE_URL'],
                'THUMB_URL'       => $bike['THUMB_URL'],
                'S_HAS_IMAGE'     => $bike['S_HAS_IMAGE'],
                'S_SHOW_YEAR'     => $bike['S_SHOW_YEAR'],
                'S_SHOW_CAPACITY' => $bike['S_SHOW_CAPACITY'],
                'S_SHOW_COLOR'    => $bike['S_SHOW_COLOR'],
            ]);

            foreach ($bike['custom_fields'] as $field)
            {
                $this->template->assign_block_vars('mopedgarage_bikes.custom_fields', [
                    'FIELD_LABEL' => $field['FIELD_LABEL'],
                    'FIELD_VALUE' => $field['FIELD_VALUE'],
                ]);
            }
        }
    }

    protected function build_bike_template_rows(array $bikes, $show_profile_fields)
    {
        $bike_ids = [];
        foreach ($bikes as $bike)
        {
            if (!empty($bike['id']))
            {
                $bike_ids[] = (int) $bike['id'];
            }
        }

        $profile_fields = $this->field_manager->get_fields(true, null, $show_profile_fields ? 1 : null);
        $values_by_bike = !empty($bike_ids) ? $this->field_manager->get_values_for_bike_ids($bike_ids) : [];
        $rows = [];

        foreach ($bikes as $bike)
        {
            $bike_id = !empty($bike['id']) ? (int) $bike['id'] : 0;
            $title = trim((string) $bike['brand'] . ' ' . (string) $bike['model']);
            if ($title === '')
            {
                $title = 'Fahrzeug';
            }

            $image_urls = $this->image_manager->get_image_urls((string) $bike['image']);
            $stored_values = isset($values_by_bike[$bike_id]) ? $values_by_bike[$bike_id] : [];
            $custom_fields = [];

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
                    $this->language->lang('YES'),
                    $this->language->lang('NO')
                );

                if ($display_value === '')
                {
                    continue;
                }

                $custom_fields[] = [
                    'FIELD_LABEL' => (string) $field['field_label'],
                    'FIELD_VALUE' => $display_value,
                ];
            }

            $rows[] = [
                'TITLE'           => $title,
                'BRAND'           => (string) $bike['brand'],
                'MODEL'           => (string) $bike['model'],
                'YEAR'            => !empty($bike['year']) ? (int) $bike['year'] : 0,
                'CAPACITY'        => !empty($bike['capacity']) ? (int) $bike['capacity'] : 0,
                'COLOR'           => (string) ($bike['color'] ?? ''),
                'IMAGE_URL'       => !empty($image_urls['image_url']) ? $image_urls['image_url'] : '',
                'THUMB_URL'       => !empty($image_urls['thumb_url']) ? $image_urls['thumb_url'] : '',
                'S_HAS_IMAGE'     => !empty($image_urls['has_image']),
                'S_SHOW_YEAR'     => !empty($bike['year']) && !empty($this->config['mopedgarage_enable_year']),
                'S_SHOW_CAPACITY' => !empty($bike['capacity']) && !empty($this->config['mopedgarage_enable_capacity']),
                'S_SHOW_COLOR'    => !empty($bike['color']) && !empty($this->config['mopedgarage_enable_color']),
                'custom_fields'   => $custom_fields,
            ];
        }

        return $rows;
    }
}
