<?php

class cfs_session
{
    public $session_id;
    public $session_data;
    public $expires = 14400; // 4 hours




    public function __construct() {
        if (isset($_POST['cfs']['session_id']) && $this->is_valid($_POST['cfs']['session_id'])) {
            $this->session_id = $_POST['cfs']['session_id'];
        }
        else {
            $this->session_id = md5(uniqid());
        }
    }




    public function get() {
        global $wpdb;

        $now = time();
        $output = array();
        $session_data = $wpdb->get_var("SELECT data FROM {$wpdb->prefix}cfs_sessions WHERE id = '$this->session_id' AND expires > '$now'");
        if (!empty($session_data)) {
            $output = unserialize($session_data);
        }

        return $output;
    }




    public function set($session_data) {
        global $wpdb;

        $wpdb->query("DELETE FROM {$wpdb->prefix}cfs_sessions WHERE id = '$this->session_id' LIMIT 1");

        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$wpdb->prefix}cfs_sessions VALUES (%s, %s, %s)",
                $this->session_id, serialize($session_data), time() + $this->expires
            )
        );
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
