<?php
namespace bruno\mopedgarage\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{
    protected $auth;
    protected $template;
    protected $language;
    protected $config;
    protected $request;
    protected $helper;

    public function __construct($auth, $template, $language, $config, $request, $helper)
    {
        $this->auth = $auth;
        $this->template = $template;
        $this->language = $language;
        $this->config = $config;
        $this->request = $request;
        $this->helper = $helper;
    }

    public static function getSubscribedEvents()
    {
        return [
            'core.user_setup' => 'on_user_setup',
            'core.page_header' => 'on_page_header',
            'core.permissions' => 'add_permissions',
        ];
    }

    public function on_user_setup($event)
    {
        $lang_set_ext = $event['lang_set_ext'];
        $lang_set_ext[] = [
            'ext_name' => 'bruno/mopedgarage',
            'lang_set' => 'common',
        ];
        $lang_set_ext[] = [
            'ext_name' => 'bruno/mopedgarage',
            'lang_set' => 'ucp_mopedgarage',
        ];
        $lang_set_ext[] = [
            'ext_name' => 'bruno/mopedgarage',
            'lang_set' => 'acp_mopedgarage',
        ];
        $lang_set_ext[] = [
            'ext_name' => 'bruno/mopedgarage',
            'lang_set' => 'mopedgarage_search',
        ];
        $lang_set_ext[] = [
            'ext_name' => 'bruno/mopedgarage',
            'lang_set' => 'permissions_mopedgarage',
        ];
        $event['lang_set_ext'] = $lang_set_ext;
    }

    public function on_page_header()
    {
        $gallery_enabled = !empty($this->config['mopedgarage_enable_gallery']);
        $can_view = method_exists($this->auth, 'acl_get') ? $this->auth->acl_get('u_mopedgarage_view') : true;
        $gallery_url = ($gallery_enabled && $can_view) ? $this->helper->route('mopedgarage_gallery') : '';

        $this->template->assign_vars([
            'U_MOPEDGARAGE_SEARCH' => $gallery_url,
            'U_MOPEDGARAGE_GALLERY' => $gallery_url,
            'MOPEDGARAGE_GALLERY_URL' => $gallery_url,
            'MOPEDGARAGE_ENABLE_GALLERY' => $gallery_enabled,
            'MOPEDGARAGE_LIGHTBOX' => !empty($this->config['mopedgarage_lightbox_global']),
        ]);
    }

    public function add_permissions($event)
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
}
