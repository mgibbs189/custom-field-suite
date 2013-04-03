<?php

class cfs_session
{
    public $session_id;
    public $session_data;
    public $expires = 3600;




    public function __construct() {
        if (isset($_COOKIE['cfs_session']) && $this->is_valid($_COOKIE['cfs_session'])) {
            $this->session_id = $_COOKIE['cfs_session'];
        }
        else {
            $this->session_id = md5(uniqid());
        }

        // Set or update the cookie
        setcookie('cfs_session',
            $this->session_id,
            time() + $this->expires,
            COOKIEPATH,
            COOKIE_DOMAIN
        );
    }




    public function get($key = null) {
        global $wpdb;

        $now = time();
        $output = array();
        $session_data = $wpdb->get_var("SELECT data FROM {$wpdb->prefix}cfs_sessions WHERE id = '$this->session_id' AND expires > '$now'");
        if (!empty($session_data)) {
            $session_data = unserialize($session_data);

            if (null != $key) {
                if (isset($session_data[$key])) {
                    $output = $session_data[$key];
                }
            }
            else {
                $output = $session_data;
            }
        }

        return $output;
    }




    public function set($key, $data) {
        global $wpdb;

        $session_data = $this->get();
        $session_data = array_merge($session_data, array($key => $data));
        $this->destroy();

        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$wpdb->prefix}cfs_sessions VALUES (%s, %s, %s)",
                $this->session_id, serialize($session_data), time() + $this->expires
            )
        );
    }




    public function destroy() {
        global $wpdb;

        $wpdb->query("DELETE FROM {$wpdb->prefix}cfs_sessions WHERE id = '$this->session_id' LIMIT 1");
    }




    public function cleanup() {
        global $wpdb;

        $now = time();
        $wpdb->query("DELETE FROM {$wpdb->prefix}cfs_sessions WHERE expires <= '$now'");
    }




    public function is_valid($session_id) {
        return preg_match("/^([a-f0-9]{32})$/", $session_id) ? true : false;
    }
}
