<?php
if (!defined('ABSPATH')) {
    exit;
}

class Reservas_Google_Calendar {
    private $api_key;
    private $client_id;
    private $client_secret;
    private $redirect_uri;
    private $access_token;
    private $refresh_token;

    public function __construct() {
        $this->init_settings();
        add_action('admin_init', array($this, 'handle_oauth_callback'));
    }

    private function init_settings() {
        $this->api_key = get_option('reservas_google_api_key');
        $this->client_id = get_option('reservas_google_client_id');
        $this->client_secret = get_option('reservas_google_client_secret');
        $this->redirect_uri = admin_url('admin.php?page=reservas-config&google_oauth=1');
        $this->access_token = get_option('reservas_google_access_token');
        $this->refresh_token = get_option('reservas_google_refresh_token');
    }

    public function handle_oauth_callback() {
        if (isset($_GET['google_oauth']) && isset($_GET['code'])) {
            $code = sanitize_text_field($_GET['code']);
            $this->exchange_code_for_token($code);
        }
    }

    private function exchange_code_for_token($code) {
        $token_url = 'https://oauth2.googleapis.com/token';
        $args = array(
            'body' => array(
                'code' => $code,
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'redirect_uri' => $this->redirect_uri,
                'grant_type' => 'authorization_code'
            )
        );

        $response = wp_remote_post($token_url, $args);

        if (!is_wp_error($response)) {
            $body = json_decode(wp_remote_retrieve_body($response), true);
            if (isset($body['access_token']) && isset($body['refresh_token'])) {
                update_option('reservas_google_access_token', $body['access_token']);
                update_option('reservas_google_refresh_token', $body['refresh_token']);
                $this->access_token = $body['access_token'];
                $this->refresh_token = $body['refresh_token'];
            }
        }
    }

    public function create_calendar_for_cabana($cabana_id, $cabana_nombre) {
        if (!$this->access_token) {
            return false;
        }

        $calendar_name = 'Reservas - ' . $cabana_nombre;
        $calendar_url = 'https://www.googleapis.com/calendar/v3/calendars';
        
        $args = array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->access_token,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode(array(
                'summary' => $calendar_name,
                'timeZone' => 'America/Santiago'
            ))
        );

        $response = wp_remote_post($calendar_url, $args);

        if (!is_wp_error($response)) {
            $body = json_decode(wp_remote_retrieve_body($response), true);
            if (isset($body['id'])) {
                global $wpdb;
                $table_name = $wpdb->prefix . 'reservas_cabanas';
                $wpdb->update(
                    $table_name,
                    array('google_calendar_id' => $body['id']),
                    array('id' => $cabana_id),
                    array('%s'),
                    array('%d')
                );
                return $body['id'];
            }
        }

        return false;
    }

    public function add_event_to_calendar($calendar_id, $event_data) {
        if (!$this->access_token) {
            return false;
        }

        $event_url = "https://www.googleapis.com/calendar/v3/calendars/{$calendar_id}/events";
        
        $args = array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->access_token,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($event_data)
        );

        $response = wp_remote_post($event_url, $args);

        if (!is_wp_error($response)) {
            $body = json_decode(wp_remote_retrieve_body($response), true);
            return isset($body['id']) ? $body['id'] : false;
        }

        return false;
    }

    public function get_auth_url() {
        $auth_url = 'https://accounts.google.com/o/oauth2/v2/auth';
        $params = array(
            'client_id' => $this->client_id,
            'redirect_uri' => $this->redirect_uri,
            'response_type' => 'code',
            'scope' => 'https://www.googleapis.com/auth/calendar',
            'access_type' => 'offline',
            'prompt' => 'consent'
        );

        return $auth_url . '?' . http_build_query($params);
    }

    public function refresh_access_token() {
        if (!$this->refresh_token) {
            return false;
        }

        $token_url = 'https://oauth2.googleapis.com/token';
        $args = array(
            'body' => array(
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'refresh_token' => $this->refresh_token,
                'grant_type' => 'refresh_token'
            )
        );

        $response = wp_remote_post($token_url, $args);

        if (!is_wp_error($response)) {
            $body = json_decode(wp_remote_retrieve_body($response), true);
            if (isset($body['access_token'])) {
                update_option('reservas_google_access_token', $body['access_token']);
                $this->access_token = $body['access_token'];
                return true;
            }
        }

        return false;
    }
} 