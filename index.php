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
require(dirname(dirname(dirname(__FILE__))) . '/artefact/linkedin/listobjet.php');
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
    $usr        = $linkedin->get_user($values,$array);
    $profile = $usr->id;
    $token = PluginArtefactlinkedin::set_user_access_token($token, $profile);
    if ($token){ $SESSION->add_ok_msg(get_string('youhavebeenloggedin', 'artefact.linkedin')); }
    else { $SESSION->add_error_msg(get_string('loginerror', 'artefact.linkedin')); }

        if (isset($_GET['sendlinkedinnotification'])) {
            $linkedin->wall_post($comment = $_REQUEST['linkedin_share_comment'],
                $title = $_REQUEST['linkedin_share_title'], 
                $description= $_REQUEST['linkedin_share_description'], 
                $url = $_REQUEST['linkedin_share_url'], 
                $img = $_REQUEST['linkedin_share_url_img'], 
                $visibility = $_REQUEST['linkedin_share_visibility']);
        }
}

if (isset($_GET['linkedinlogout']))
{
    $token = PluginArtefactlinkedin::delete_user_access_token();
    $SESSION->add_ok_msg(get_string('youhavebeenloggedout', 'artefact.linkedin'));
}

$loggedin = $linkedin->is_authenticated();
if ($loggedin && empty($usr))
{
    $usr = $linkedin->get_user($values,$array);
}

function xml2array ( $xmlObject, $out = array () )
{
    foreach ( (array) $xmlObject as $index => $node )
        $out[$index] = ( is_object ( $node ) ) ? xml2array ( $node ) : $node;

    return $out;
}

$smarty = smarty();
if (!empty($usr))
{
    // https://developer.linkedin.com/documents/profile-fields#fullprofile
    $usr['skills']              = xml2array($usr['skills']);
    $usr['skills']              = xml2array($usr['skills']['skill']);
    $usr['positions']           = xml2array($usr['positions']);
    $usr['positions']           = xml2array($usr['positions']['position']);
    $usr['educations']          = xml2array($usr['educations']);
    $usr['educations']          = xml2array($usr['educations']['education']);
    $usr['languages']           = xml2array($usr['languages']);
    $usr['languages']           = xml2array($usr['languages']['language']);
    $usr['publications']       = xml2array($usr['publications']);
    $usr['publications']       = xml2array($usr['publications']['publication']);
    $usr['patents']       = xml2array($usr['patents']);
    $usr['patents']       = xml2array($usr['patents']['patent']);
    $usr['certifications']       = xml2array($usr['certifications']);
    $usr['certifications']       = xml2array($usr['certifications']['certification']);
    $usr['courses']       = xml2array($usr['courses']);
    $usr['courses']       = xml2array($usr['courses']['course']);
    $usr['date-of-birth']       = xml2array($usr['date-of-birth']);
    $smarty->assign('usr', $usr);
}

$send = False;
switch ($_GET['action']){
    case ('send'):
        $send = True;
    break;
    case ('logout'):
        $logout = True;
    break;
    default:
    break;
}
$smarty->assign('send', $send);
$smarty->assign('logout', $logout); 
$smarty->assign('PAGEHEADING', hsc(get_string('linkedin', 'artefact.linkedin')));
$smarty->assign('loggedin', $loggedin);
$smarty->assign('message', $message);
$smarty->assign('SUBPAGENAV', PluginArtefactLinkedin::submenu_items());
$smarty->display('artefact:linkedin:index.tpl');