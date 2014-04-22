<?php

/**
 * Description of linkedin
 *
 * @copyright (c) 2011 University of Geneva
 * @license GNU General Public License - http://www.gnu.org/copyleft/gpl.html
 * @author Laurent Opprecht, Johann Luthi
 */
class linkedin
{
    const CODE = 'code';
    const STATE = 'linkedin_state';

    public static function get_contents($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CAPATH, $capath = realpath(dirname(__FILE__) . '/../ca'));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $res = curl_exec($ch);
        //$err = curl_error($ch);
        curl_close($ch);
        return $res;
    }

    /**
     * Returns the default redirect url. That is the current url with no parameters.
     * @return string 
     */
    public static function get_redirect_url()
    {
        $host = $_SERVER['HTTP_HOST'];
        $protocol = $_SERVER['SERVER_PROTOCOL'] == 'HTTPS/1.1' ? 'https://' : 'http://';
        $uri = $_SERVER['REQUEST_URI'];
        $uri = reset(explode('?', $uri));
        return $protocol . $host . $uri;
    }

    public static function get_dialog_url($app_id, $redirect_url, $state)
    {
        $redirect_url = urlencode($redirect_url);
        $result = "https://www.linkedin.com/uas/oauth2/authorization?response_type=code&client_id=$app_id&scope=rw_nus%20r_fullprofile%20r_emailaddress%20r_network&state=$state&redirect_uri=$redirect_url";
        return $result;
    }

    public static function get_token_url($app_id, $app_secret, $redirect_url, $code)
    {
        $redirect_url = urlencode($redirect_url);
		$result = "https://www.linkedin.com/uas/oauth2/accessToken?grant_type=authorization_code&code=$code&redirect_uri=$redirect_url&client_id=$app_id&client_secret=$app_secret";
        return $result;
    }

    public static function get_token($app_id, $redirect_url, $app_secret, $code)
    {
        $url = self::get_token_url($app_id, $redirect_url, $app_secret, $code);
        $response = self::get_contents($url);
        $params = null;
        parse_str($response, $params);
        return $params['access_token'];
    }

    private $app_id;
    private $app_secret;
    private $id;
    private $token;

    public function __construct($app_id, $app_secret, $id, $token)
    {
        $this->app_id = $app_id;
        $this->app_secret = $app_secret;
        $this->id = $id;
        $this->token = $token;
    }

    public function is_authenticated()
    {
        return!empty($this->token);
    }

    public function is_authenticating()
    {
        return!$this->is_authenticated() && isset($_GET['code']);
    }

    public function authenticate($redirect_url='')
    {
        $app_id = $this->app_id;
        $app_secret = $this->app_secret;

        $code = isset($_REQUEST[self::CODE]) ? $_REQUEST[self::CODE] : false;
        $redirect_url = $redirect_url ? $redirect_url : self::get_redirect_url();
        if (empty($code))
        {
            $state = $_SESSION[self::STATE] = md5(uniqid(rand(), TRUE)); //CSRF protection
            $dialog_url = self::get_dialog_url($app_id, $redirect_url, $state);
            echo "<script> top.location.href='" . $dialog_url . "'</script>";
            die;
        }

        $session_state = isset($_SESSION[self::STATE]) ? $_SESSION[self::STATE] : false;
        $request_state = isset($_REQUEST['state']) ? $_REQUEST['state'] : false;

        if ($request_state == $session_state)
        {
            $token_url = self::get_token_url($app_id, $app_secret, $redirect_url, $code);
            // ajout de la fonction json_decode()
			$params =  json_decode(self::get_contents($token_url));
            $result = isset($params->access_token) ? $params->access_token : false;
        }
        else
        {
            $result = false;
        }
        return $this->token = $result;
    }

    public function get_user($values,$array)
    {
        $token = $this->token;
        if (empty($token))
        {
            return false;
        }
        $url = "https://api.linkedin.com/v1/people/~:(";
        foreach ($array as $field){
            $url .= $field.',';
        }
        foreach ($values as $field){
            $url .= $field.',';
        }
        $url .= ")?oauth2_access_token=$token";
        $result = get_object_vars(simplexml_load_string(self::get_contents($url)));
        return $result;
    }
    
    public function wall_post($comment = '', $title = '', $description='', $url = '', $img = '', $visibility = '')
    {
        $token = $this->token;
        if (empty($token))
        {
            return false;
        }
		$data = "<?xml version='1.0' encoding='UTF-8'?>".
				"<share>".
					"<comment>$comment</comment>";
					if (!empty($url)) {
					$data .= "<content>".
								 "<title>$title</title>".
								 "<description>$description</description>".
								 "<submitted-url>$url</submitted-url>".
								 "<submitted-image-url>$img</submitted-image-url>".
							 "</content>";
					}
		$data .=	"<visibility>".
						"<code>$visibility</code>".
					"</visibility>".
				"</share>";

		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_URL, "https://api.linkedin.com/v1/people/~/shares?oauth2_access_token=$token");
        curl_setopt($ch, CURLOPT_CAPATH, $capath = realpath(dirname(__FILE__) . '/../ca'));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "$data");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $res = curl_exec($ch);
        //$err = curl_error($ch); 
        curl_close($ch);
        return strpos('error', $res) ? false : true;
    }

}
	
?>