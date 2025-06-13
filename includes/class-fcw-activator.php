<?php
defined('ABSPATH') || exit;

// class-fcw-activator.php

class FCW_Activator {

    public static function activate() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $table_fichas = $wpdb->prefix . 'fichas_cliente';
        $sql_fichas = "CREATE TABLE IF NOT EXISTS $table_fichas (
            id INT NOT NULL AUTO_INCREMENT,
            user_id INT NOT NULL,
            tecnico_id INT NOT NULL,
            nivel_tecnico TINYINT NOT NULL,
            fecha_creacion DATETIME NOT NULL,
            fecha_ultima_mod DATETIME NOT NULL,
            json_datos LONGTEXT,
            PRIMARY KEY (id)
        ) $charset_collate;";

        $table_turnos = $wpdb->prefix . 'fcw_turnos';
        $sql_turnos = "CREATE TABLE IF NOT EXISTS $table_turnos (
            id INT NOT NULL AUTO_INCREMENT,
            cliente_id INT NOT NULL,
            tecnico_id INT NOT NULL,
            fecha_turno DATETIME NOT NULL,
            nota TEXT,
            notificado BOOLEAN DEFAULT FALSE,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_fichas);
        dbDelta($sql_turnos);
    }
}
