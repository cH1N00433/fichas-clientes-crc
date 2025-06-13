<?php
defined('ABSPATH') || exit;

// class-fcw-google-calendar.php

use Google_Client;
use Google_Service_Calendar;

class FCW_Google_Calendar {

    public static function init() {
        add_action('admin_menu', [__CLASS__, 'add_calendar_settings']);
    }

    public static function add_calendar_settings() {
        if (!current_user_can('tecnico_nivel_1') && !current_user_can('tecnico_nivel_2')) return;

        add_submenu_page(
            'fcw_tecnico_panel',
            __('Conectar Google Calendar', 'ficha-cliente-woo'),
            __('Google Calendar', 'ficha-cliente-woo'),
            'read',
            'fcw_google_calendar',
            [__CLASS__, 'render_settings_page']
        );
    }

    public static function render_settings_page() {
        echo '<div class="wrap"><h1>Google Calendar</h1>';
        echo '<p>Aquí irá la integración con OAuth2 para permitir la conexión a Google Calendar.</p>';
        echo '</div>';
    }

    // Funciones futuras: obtener token, guardar en user_meta, crear eventos, refresh tokens, etc.
}


    public static function get_google_client() {
        require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';

        $client = new Google_Client();
        $client->setClientId('TU_CLIENT_ID');
        $client->setClientSecret('TU_CLIENT_SECRET');
        $client->setRedirectUri(admin_url('admin.php?page=fcw_google_calendar'));
        $client->addScope(Google_Service_Calendar::CALENDAR);
        $client->setAccessType('offline');
        $client->setPrompt('consent');

        return $client;
    }

    public static function render_settings_page() {
        echo '<div class="wrap"><h1>Google Calendar</h1>';

        $user_id = get_current_user_id();
        $client = self::get_google_client();

        if (isset($_GET['code'])) {
            $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
            if (!isset($token['error'])) {
                update_user_meta($user_id, 'fcw_google_token', $token);
                echo '<p><strong>¡Autorización completada!</strong></p>';
            } else {
                echo '<p>Error al obtener el token.</p>';
            }
        }

        $stored_token = get_user_meta($user_id, 'fcw_google_token', true);

        if ($stored_token) {
            echo '<p>Cuenta conectada correctamente.</p>';
        } else {
            $auth_url = $client->createAuthUrl();
            echo '<a class="button button-primary" href="' . esc_url($auth_url) . '">Conectar con Google Calendar</a>';
        }

        echo '</div>';
    }
