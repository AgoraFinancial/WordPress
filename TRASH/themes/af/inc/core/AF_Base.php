<?php
/**
 * Theme globals
 */

class AF_Base {

    // OpenX
    public $openx;
    public $openxZones;

    public function __construct() {
        // Setup OpenX if plugin enabled
        if (class_exists('Agora_profiles_openx')) {
            $this->openx = new Agora_profiles_openx();
        }
        $this->af_setup_openx();
    }

    // Get parent theme directory
    public function af_get_parent_theme_dir() {
        $path = PARENT_PATH_URI;
        return $path;
    }

    // Get child theme directory - if parent only, then set $child_theme_dir to $theme_dir
    public function af_get_child_theme_dir() {
        if (PARENT_PATH === CHILD_PATH) {
            $path = PARENT_PATH_URI;
        } else {
            $path = CHILD_PATH_URI;
        }
        return $path;
    }

    // Setup OpenX hooks
    public function af_setup_openx() {
        add_action('wp_ajax_openxZone', array($this, 'af_openxZone'));
        add_action('wp_ajax_nopriv_openxZone', array($this, 'af_openxZone'));
        add_action('init', array($this, 'af_setMiloCookie'));
        add_action('wp_ajax_listNameHasEmail', array($this, 'af_listNameHasEmail'));
        add_action('wp_ajax_nopriv_listNameHasEmail', array($this, 'af_listNameHasEmail'));
    }

    // Openx zone init
    public function af_openxZone() {

        global $af_auth;
        $params = isset($_POST['params']) ? $_POST['params'] : '';

        if ($params === '') {
            wp_die();
        }

        $params = '';
        $userPubs = $af_auth->get_current_user_active_pubs();
        $pubs = (is_array($userPubs) && count($userPubs)) ? implode(",", $userPubs) : '';

        if ($pubs) {
            $params .= ($params) ? '&' : '';
            $params .= "pubs=$pubs";
        }
        foreach ($_POST['params'] as $key => $value) {
            if (!$value || $key == 'callback') {
                continue;
            }
            if ($key == 'r') {
                if (!$value) {
                    continue;
                }
                $value = substr($value,5);
                $value = str_replace('_', ',', $value);
                $params .= $params ? '&' : '';
                $params .= $key . '=' . $value;
                continue;
            }
            if ($key == 'zone') {
                $pubs = (isset($_POST['params']['r']) && preg_match("/^milo_/", $_POST['params']['r'])) ? substr($_POST['params']['r'], 4) : $_POST['params']['r'];
                $value = apply_filters( 'af_custom_openx', $value, $pubs ); // Filter to change value of adzone cookie
                if ('in_content_ad' == $value) {
                    $value = $pubs ? OPENX_INCONTENT_COOKIE : OPENX_INCONTENT;
                } elseif ('sidebar_ad' == $value) {
                    $value = $pubs ? OPENX_SIDEBAR_COOKIE : OPENX_SIDEBAR;
                } elseif ('sidebar_ad_2' == $value) {
                    $value = $pubs ? OPENX_SIDEBAR_2_COOKIE : OPENX_SIDEBAR_2;
                } elseif ('large_ad' == $value) {
                    $value = $pubs ? OPENX_LARGE_COOKIE : OPENX_LARGE;
                } elseif ('bottom_free_ad' == $value) {
                    $value = $pubs ? OPENX_BOTTOM_FREE_COOKIE : OPENX_BOTTOM_FREE;
                } elseif ('bottom_frontend_ad' == $value) {
                    $value = $pubs ? OPENX_BOTTOM_FRONTEND_COOKIE : OPENX_BOTTOM_FRONTEND;
                } elseif ('exit_popup_ad' == $value) {
                    $value = $pubs ? OPENX_EXIT_COOKIE : OPENX_EXIT;
                } elseif ('related_article_ad' == $value) {
                    $value = $pubs ? OPENX_RELATED_COOKIE : OPENX_RELATED;
                } elseif ('trending_article_ads' == $value) {
                    $value = $pubs ? OPENX_TRENDING_COOKIE : OPENX_TRENDING;
                }
                $key = 'zoneid';
            } elseif ($key == 'lysegs') {
                $value = urldecode($value);
                $ar_vals = explode(',', $value);
                $value = '';
                $is_first = 1;
                $ar_find = array('"', '}', '{');
                $ar_replace = array('', '', '');
                foreach ($ar_vals as $lyseg) {
                    if (!strpos($lyseg, ':')) {
                        continue;
                    }
                    list($send_key, $val) = explode(":", $lyseg, 2);
                    $send_key = str_replace($ar_find, $ar_replace, stripslashes($send_key));
                    if (!preg_match("/^has_\w{3}$/", $send_key)) {
                        continue;
                    }
                    if (!$is_first) {
                        $value .= ',';
                    }
                    $is_first = 0;
                    $value .= $send_key;
                }
                if (!$value) {
                    continue;
                }
            }
            $params .= $params ? '&' : '';
            $params .= $key . '=' . $value;
        }

        if ($params === '') {
            wp_die();
        }

        $url = 'http://ads.agorafinancial.com/www/delivery/ajs.php';

        // get the js contents from openx
        $content = file_get_contents($url . '?' . $params);
        if ($content == '0') {
            error_log($url . '?' . $params . "\n");
            $content = '';
        }

        /**
         * clean up with regex
         */
        $preg_content_find = array(
            "/var(.*?)= '';/", // leading var definition
            '/OX_(.*?) \+= "/', // concatinated string of 'OX_xxxxx' var
            '/<"\+"/', // concatinated opening tags
            '/\\\n"\;/', // trailing newline and semicolin
            '/document.write\((.*?)\)\;/' // last document write
        );
        $preg_content_replace = array(
            '',
            '',
            '<',
            '',
            ''
        );
        $str_content_find = array(
            'http:',
            'https://ads.agorafinancial.com/www/delivery/',
            'http://ads.agorafinancial.com/www/delivery/lg.php',
            'http://ads.agorafinancial.com/www/delivery/ck.php'
        );
        $str_content_replace = array(
            'https:',
            'http://ads.agorafinancial.com/www/delivery/',
            'https://ads.agorafinancial.com/www/delivery/lg.php',
            'https://ads.agorafinancial.com/www/delivery/ck.php'
        );

        $content = preg_replace($preg_content_find, $preg_content_replace, $content);
        $content = stripslashes($content);
        $content = str_replace($str_content_find, $str_content_replace, $content);

        echo $content;

        wp_die();
    }

    // Set milo cookie if incoming via query string
    public function af_setMiloCookie() {
        if (!(isset($_GET['r']) && $_GET['r'])) {
            return;
        }
        if (isset($_GET['r'])) {
            $milo = $_GET['r'];
            $cur_cookie = '';
            $new_cookie = '';
            if (isset($_COOKIE['r'])) {
                $cur_cookie = $_COOKIE['r'];
            }
            // These pubs are legacy, should add filters to individual sites going forward
            $ar_pubs = array(
                'TPD' => 'Technology Profits Daily',
                'RUD' => 'Rude Awakening',
                'RUN' => 'The Rundown',
                'MWW' => 'Mike Wealth Watch',
                'DR' => 'Daily Reckoning',
                'DRH' => 'The Daily Edge',
                'RLR' => 'Rich Life Road Map',
                'WSD' => 'Penny Stock Millionaire',
                'RDD' => 'Rich Dad Poor Dad Daily',
                'BRU' => 'Brian Rose Uncensored',
                'RLM' => 'Rich Life Roadmap',
                'DPF' => 'Daily Proof',
            );
            $ar_pubs = apply_filters('af_free_newsletters', $ar_pubs);
            if (strpos($milo, '_')) {
                $ar_milo = explode('_', $milo);
            } else {
                $ar_milo = array();
                $ar_milo[] = $milo;
            }
            if (strpos($cur_cookie, '_')) {
                $ar_cookie = explode('_', $cur_cookie);
            } else {
                $ar_cookie = array();
                $ar_cookie[] = $cur_cookie;
            }
            foreach ($ar_pubs as $pub => $desc) {
                if (in_array($pub, $ar_milo) || in_array($pub, $ar_cookie)) {
                    $new_cookie .= '_' . $pub;
                }
            }
            $new_cookie = trim($new_cookie, '_');
            setcookie('r', $new_cookie, time() + (365 * 24 * 60 * 60), '/', COOKIE_DOMAIN, false);
        }
    }

    /**
     * Verify from ajax request if incoming email is on advantage list
     *
     * @return bool
     */
    public function af_listNameHasEmail() {
        // Assign data.
        $email = (isset($_POST['email']) && $_POST['email']) ? $_POST['email'] : '' ;
        $listname = (isset($_POST['listname']) && $_POST['listname']) ? $_POST['listname'] : '' ;

        // Return if either are empty.
        if (!$email || !$listname) {
            echo 'false';
            exit;
        }

        $results = $this->af_getEmailLists($email, $listname);

        echo json_encode($results);
        wp_die();
    }


    /**
     * Make call out to wiggum to get email lists
     *
     * @param  string $email
     * @param  string $listname
     * @return bool|null
     */
    public function af_getEmailLists($email, $listname) {
        if (!$email || !$listname) {
            return 'Failed';
        }

        // Request vars
        $arg = array('method' => 'GET', 'timeout' => 120, 'Content-Type' => 'application/json');

        //Get emails lists from wiggum
        $response = wp_remote_request('https://wiggum.agorafinancial.com/api/middleware/adv/list/signup/emailaddress/' . $email, $arg);

        // Verify on success
        if (is_wp_error($response)) {
            $results = 'Error Found ( ' . $response->get_error_message() . ' )';
        } else {
            $results = $this->af_hasListName($response['body'], $listname);
        }
        return $results;
    }

    /**
     * Crosscheck listname to wiggum response
     *
     * @param  array $response
     * @param  string $listname
     * @return bool
     */
    public function af_hasListName($response, $listname) {
        $has_listname = false;

        if (!$response || !$listname) {
            return $has_listname;
        }

        $response = json_decode($response);

        // Cycle through all results and check if they have list
        foreach ($response as $object) {
            // return $response;
            if ($listname != $object->listCode) {
                continue;
            } else {
                $has_listname = true;
            }
        }
        return $has_listname;
    }
}
