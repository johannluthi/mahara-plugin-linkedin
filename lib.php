<?php

/**
 *
 * @package    mahara
 * @subpackage artefact-linkedin
 * @author     laurent.opprecht@gmail.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2011 University of Geneva http://www.unige.ch/
 *
 */
defined('INTERNAL') || die();

require_once(dirname(__FILE__) . '/lib/linkedin.class.php');

class PluginArtefactlinkedin extends PluginArtefact
{
    const TABLE_NAME = 'usr_security_token_2';
    const APPLICATION_NAME = 'linkedin';

    private static $linkedin = null;

    /**
     *
     * @return linkedin
     */
    public static function linkedin()
    {
        if (!is_null(self::$linkedin))
        {
            return self::$linkedin;
        }
        $access = self::get_user_access();
        $app_secret = ArtefactTypelinkedin::get_application_secret();
        $app_id = ArtefactTypelinkedin::get_application_id();
        
        if (empty($app_secret) || empty($app_id))
        {
            return self::$linkedin = false;
        }        
        $id = $access ? $access->profile : false;
        $token = $access ? $access->token : false;
        return self::$linkedin = new linkedin($app_id, $app_secret, $id, $token);
    }

    public static function submenu_items() {
        $tabs = array(
            'index' => array(
                'page'  => 'index',
                'url'   => 'artefact/linkedin',
                'title' => get_string('introduction', 'artefact.linkedin'),
            ),
            'profil' => array(
                'page'  => 'profil',
                'url'   => 'artefact/linkedin/index.php?action=send',
                'title' => get_string('post', 'artefact.linkedin'),
            ),
            'logout' => array(
                'page'  => 'logout',
                'url'   => 'artefact/linkedin/index.php?action=logout',
                'title' => get_string('logout', 'artefact.linkedin'),
            ),
        );
        if (defined('RESUME_SUBPAGE') && isset($tabs[RESUME_SUBPAGE])) {
            $tabs[RESUME_SUBPAGE]['selected'] = true;
        }
        return $tabs;
    }
    
    public static function get_user_access($user_id = false)
    {
        global $USER;
        $user_id = $user_id ? $user_id : $USER->get('id');


        $record = get_record(self::TABLE_NAME, 'usr', $user_id, 'app', self::APPLICATION_NAME);
        return $record;
    }

    public static function get_user_access_token($user_id = false)
    {
        global $USER;
        $user_id = $user_id ? $user_id : $USER->get('id');


        $record = get_record(self::TABLE_NAME, 'usr', $user_id, 'app', self::APPLICATION_NAME);
        return $record ? $record->token : false;
    }

    public static function set_user_access_token($value, $profile, $user_id = false)
    {
        global $USER;
        $user_id = $user_id ? $user_id : $USER->get('id');
        delete_records(self::TABLE_NAME, 'usr', $user_id, 'app', self::APPLICATION_NAME);
        if (empty($value))
        {
            return false;
        }

        $data['usr'] = $user_id;
        $data['app'] = self::APPLICATION_NAME;
        $data['token'] = $value;
        $data['profile'] = $profile;
        $data['salt'] = md5(uniqid($prefix)); //@todo
        $data['ctime'] = db_format_timestamp(time());
        insert_record(self::TABLE_NAME, $data);
        return $value;
    }

    public static function delete_user_access_token($value, $user_id = false)
    {
        global $USER;
        $user_id = $user_id ? $user_id : $USER->get('id');
        delete_records(self::TABLE_NAME, 'usr', $user_id, 'app', self::APPLICATION_NAME);
    }

    public static function get_headers()
    {
        $headers = array();
        return $headers;
    }

    public static function get_artefact_types()
    {
        return array('linkedin');
    }

    public static function get_block_types()
    {
        return array();
    }

    public static function get_plugin_name()
    {
        return 'linkedin';
    }

    public static function menu_items()
    {
        return array(
            'content/linkedin' => array(
                'path' => 'content/linkedin',
                'url' => 'artefact/linkedin/index.php',
                'title' => get_string('linkedin', 'artefact.linkedin'),
                'weight' => 100,
            )
        );
    }

    public static function get_event_subscriptions()
    {
        return array();
    }

    public static function get_activity_types()
    {
        return array();
    }

    public static function postinst($prevversion)
    {

        return true;
    }

    public static function view_export_extra_artefacts($viewids)
    {
        $artefacts = array();
        return $artefacts;
    }

    public static function artefact_export_extra_artefacts($artefactids)
    {
        $artefacts = array();
        return $artefacts;
    }

}

class ArtefactTypelinkedin extends ArtefactType
{
    const APPLICATION_ID = 'application_id';
    const APPLICATION_SECRET = 'application_secret';

    public static function get_application_id()
    {
        return self::def(self::APPLICATION_ID);
    }

    public static function get_application_secret()
    {
        return self::def(self::APPLICATION_SECRET);
    }

    public function __construct($id = 0, $data = null)
    {
        parent::__construct($id, $data);
    }

    public static function is_singular()
    {
        return false;
    }

    public static function get_icon($options=null)
    {
        global $THEME;
        return $THEME->get_url('images/thumb.gif', false, 'artefact/linkedin');
    }

    public static function get_links($id)
    {
        return array(
            '_default' => get_config('wwwroot') . 'artefact/linkedin/view.php?id=' . $id,
        );
    }

    public function can_have_attachments()
    {
        return false;
    }

    public function render_self()
    {
        return clean_html($this->get('description'));
    }

    public function exportable()
    {
        return false;
    }

    public function get_view_url($viewid, $showcomment=true)
    {
        return '';
    }

    public function viewable_in($viewid)
    {
        return false;
    }

    public static function has_config()
    {
        return true;
    }

    public static function get_config_options()
    {
        $elements = array(
            self::APPLICATION_ID => array(
                'type' => 'text',
                'size' => 50,
                'title' => self::get_string(self::APPLICATION_ID),
                'rules' => array(
                    'required' => true,
                ),
                'defaultvalue' => self::def(self::APPLICATION_ID),
                'help' => false,
            ),
            self::APPLICATION_SECRET => array(
                'type' => 'text',
                'size' => 50,
                'title' => self::get_string(self::APPLICATION_SECRET),
                'rules' => array(
                    'required' => true,
                ),
                'defaultvalue' => self::def(self::APPLICATION_SECRET),
                'help' => false,
                ));

        return array(
            'elements' => $elements,
            'renderer' => 'table'
        );
    }

    protected static function def($field_name, $default_value = '')
    {
        $result = get_config_plugin('artefact', 'linkedin', $field_name);
        $result = is_null($result) ? $default_value : $result;
        return $result;
    }

    public static function get_string($identifier, $section='artefact.linkedin')
    {
        return get_string($identifier, $section);
    }

    public static function save_config_options($values)
    {
        foreach (array(self::APPLICATION_SECRET, self::APPLICATION_ID) as $settingname)
        {
            set_config_plugin('artefact', 'linkedin', $settingname, $values[$settingname]);
        }
    }

}