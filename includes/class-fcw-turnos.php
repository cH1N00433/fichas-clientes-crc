<?php
defined('ABSPATH') || exit;

// class-fcw-turnos.php

class FCW_Turnos {

    public static function init() {
        add_action('wp_ajax_fcw_get_turnos', [__CLASS__, 'get_turnos']);
        add_action('wp_ajax_fcw_save_turno', [__CLASS__, 'save_turno']);
        add_action('wp_ajax_fcw_delete_turno', [__CLASS__, 'delete_turno']);
    }

    public static function get_turnos() {
        check_ajax_referer('fcw_turnos_nonce', 'nonce');
        if (!current_user_can('tecnico_nivel_1') && !current_user_can('tecnico_nivel_2')) {
            wp_send_json_error('No autorizado');
        }

        global $wpdb;
        $user_id = get_current_user_id();
        $table = $wpdb->prefix . 'fcw_turnos';

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE tecnico_id = %d ORDER BY fecha_turno ASC",
            $user_id
        ));

        wp_send_json_success($results);
    }

    public static function save_turno() {
        check_ajax_referer('fcw_turnos_nonce', 'nonce');
        if (!current_user_can('tecnico_nivel_1') && !current_user_can('tecnico_nivel_2')) {
            wp_send_json_error('No autorizado');
        }

        global $wpdb;
        $table = $wpdb->prefix . 'fcw_turnos';

        $data = [
            'cliente_id' => intval($_POST['cliente_id']),
            'tecnico_id' => get_current_user_id(),
            'fecha_turno' => sanitize_text_field($_POST['fecha_turno']),
            'nota' => sanitize_textarea_field($_POST['nota']),
            'notificado' => false
        ];

        if (!empty($_POST['id'])) {
            $wpdb->update($table, $data, ['id' => intval($_POST['id'])]);
        } else {
            $wpdb->insert($table, $data);
        }

        
        // Enviar notificaciones por correo
        $cliente = get_user_by('id', $data['cliente_id']);
        $tecnico = wp_get_current_user();

        if ($cliente && $tecnico) {
            wp_mail(
                $cliente->user_email,
                'Confirmación de Turno',
                "Estimado/a {$cliente->display_name},\n\nSu turno ha sido programado para el día {$data['fecha_turno']}.\n\nSaludos."
            );

            wp_mail(
                $tecnico->user_email,
                'Nuevo Turno Asignado',
                "Has agendado un turno con {$cliente->display_name} para el día {$data['fecha_turno']}."
            );
        }

        
        // Crear evento en Google Calendar
        $token = get_user_meta($data['tecnico_id'], 'fcw_google_token', true);
        if ($token && isset($token['access_token'])) {
            require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';

            $client = new Google_Client();
            $client->setAccessToken($token);
            if ($client->isAccessTokenExpired()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                update_user_meta($data['tecnico_id'], 'fcw_google_token', $client->getAccessToken());
            }

            $service = new Google_Service_Calendar($client);

            $event = new Google_Service_Calendar_Event([
                'summary' => 'Turno con cliente ID ' . $data['cliente_id'],
                'description' => $data['nota'],
                'start' => ['dateTime' => date(DATE_RFC3339, strtotime($data['fecha_turno'])), 'timeZone' => 'America/Argentina/Buenos_Aires'],
                'end' => ['dateTime' => date(DATE_RFC3339, strtotime($data['fecha_turno'] . ' +1 hour')), 'timeZone' => 'America/Argentina/Buenos_Aires'],
            ]);

            try {
                $service->events->insert('primary', $event);
            } catch (Exception $e) {
                error_log('Google Calendar error: ' . $e->getMessage());
            }
        }

        wp_send_json_success();
    }

    public static function delete_turno() {
        check_ajax_referer('fcw_turnos_nonce', 'nonce');
        if (!current_user_can('tecnico_nivel_1') && !current_user_can('tecnico_nivel_2')) {
            wp_send_json_error('No autorizado');
        }

        global $wpdb;
        $table = $wpdb->prefix . 'fcw_turnos';
        $wpdb->delete($table, ['id' => intval($_POST['id'])]);

        
        // Enviar notificaciones por correo
        $cliente = get_user_by('id', $data['cliente_id']);
        $tecnico = wp_get_current_user();

        if ($cliente && $tecnico) {
            wp_mail(
                $cliente->user_email,
                'Confirmación de Turno',
                "Estimado/a {$cliente->display_name},\n\nSu turno ha sido programado para el día {$data['fecha_turno']}.\n\nSaludos."
            );

            wp_mail(
                $tecnico->user_email,
                'Nuevo Turno Asignado',
                "Has agendado un turno con {$cliente->display_name} para el día {$data['fecha_turno']}."
            );
        }

        
        // Crear evento en Google Calendar
        $token = get_user_meta($data['tecnico_id'], 'fcw_google_token', true);
        if ($token && isset($token['access_token'])) {
            require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';

            $client = new Google_Client();
            $client->setAccessToken($token);
            if ($client->isAccessTokenExpired()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                update_user_meta($data['tecnico_id'], 'fcw_google_token', $client->getAccessToken());
            }

            $service = new Google_Service_Calendar($client);

            $event = new Google_Service_Calendar_Event([
                'summary' => 'Turno con cliente ID ' . $data['cliente_id'],
                'description' => $data['nota'],
                'start' => ['dateTime' => date(DATE_RFC3339, strtotime($data['fecha_turno'])), 'timeZone' => 'America/Argentina/Buenos_Aires'],
                'end' => ['dateTime' => date(DATE_RFC3339, strtotime($data['fecha_turno'] . ' +1 hour')), 'timeZone' => 'America/Argentina/Buenos_Aires'],
            ]);

            try {
                $service->events->insert('primary', $event);
            } catch (Exception $e) {
                error_log('Google Calendar error: ' . $e->getMessage());
            }
        }

        wp_send_json_success();
    }
}
