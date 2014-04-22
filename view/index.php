<?php

/**
 * @copyright (c) 2011 University of Geneva
 * @license GNU General Public License - http://www.gnu.org/copyleft/gpl.html
 * @author Laurent Opprecht, Johann Luthi
 */
define('INTERNAL', 1);
define('MENUITEM', 'content/linkedin');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'linkedin');
define('SECTION_PAGE', 'index');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
// Safely calls require, returning false if LoadError occurs.
safe_require('artefact', 'linkedin');
define('TITLE', get_string('linkedin', 'artefact.linkedin'));

$linkedin = PluginArtefactlinkedin::linkedin();
if (empty($linkedin))
{
    $message = get_string('mustconfigureplugin', 'artefact.linkedin');
    $smarty = smarty();
    $smarty->assign('PAGEHEADING', hsc(get_string('linkedin', 'artefact.linkedin')));
    $smarty->assign('loggedin', false);
    $smarty->assign('message', $message);
    $smarty->display('artefact:linkedin:index.tpl');
    return;
}
$message = '';
if (isset($_GET['linkedinlogin']) || $linkedin->is_authenticating())
{
    $token = $linkedin->authenticate();
    $usr = $linkedin->get_user();
    $profile = $usr->id;
    $token = PluginArtefactlinkedin::set_user_access_token($token, $profile);
    if ($token)
    {
        $SESSION->add_ok_msg(get_string('youhavebeenloggedin', 'artefact.linkedin'));
    }
    else
    {
        $SESSION->add_error_msg(get_string('loginerror', 'artefact.linkedin'));
    }
}

if (isset($_GET['sendlinkedinnotification']))
{
    $linkedin->wall_post($comment = $_REQUEST['linkedin_share_comment'],
						 $title = $_REQUEST['linkedin_share_title'], 
						 $description= $_REQUEST['linkedin_share_description'], 
						 $url = $_REQUEST['linkedin_share_url'], 
						 $img = $_REQUEST['linkedin_share_url_img'], 
						 $visibility = $_REQUEST['linkedin_share_visibility']);
}

if (isset($_GET['linkedinlogout']))
{
    $token = PluginArtefactlinkedin::delete_user_access_token();
    $SESSION->add_ok_msg(get_string('youhavebeenloggedout', 'artefact.linkedin'));
}

$loggedin = $linkedin->is_authenticated();
if ($loggedin && empty($usr))
{
    $usr = $linkedin->get_user();
}

$smarty = smarty();
if (!empty($usr))
{
    $smarty->assign('usr', $usr);
}

$smarty->assign('PAGEHEADING', hsc(get_string('linkedin', 'artefact.linkedin')));
$smarty->assign('loggedin', $loggedin);
    $smarty->assign('message', $message);
$smarty->display('artefact:linkedin:index.tpl');
?>