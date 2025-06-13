<?php
/*
Plugin Name: Ficha Tratamiento CRC Completo
Description: Plugin integral para fichas clínicas, técnicos, y administración en WooCommerce.
Version: 1.3
Author: CRC Dev Team
*/

defined('ABSPATH') or die('No script kiddies please!');

// ==================== ACTIVACIÓN ====================
register_activation_hook(__FILE__, 'ftcrc_activar_plugin');
function ftcrc_activar_plugin() {
    global $wpdb;
    $tabla = $wpdb->prefix . 'fichas_tratamiento';
    $charset_collate = $wpdb->get_charset_collate();
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $sql = "CREATE TABLE $tabla (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        nombre_cliente VARCHAR(255),
        tecnico_nombre VARCHAR(255),
        legajo VARCHAR(100),
        fecha_inicio DATE,
        numero_mes VARCHAR(10),
        asistencia_semana TEXT,
        observaciones TEXT,
        productos TEXT,
        info_cliente TEXT,
        alta_frecuencia TINYINT,
        masaje_completo TINYINT,
        afinamiento_sectorizado VARCHAR(255),
        estado_fibra VARCHAR(50),
        escamas_abiertas TINYINT,
        hidratacion TEXT,
        fotos_tomadas TINYINT,
        sesiones TEXT,
        fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id)
    ) $charset_collate;";
    dbDelta($sql);
    add_role('tecnico_nivel_1', 'Técnico Nivel 1', []);
    add_role('tecnico_nivel_2', 'Técnico Nivel 2', []);
    flush_rewrite_rules();
}

// ==================== CREAR FICHA AUTOMÁTICA ====================
add_action('user_register', 'ftcrc_crear_ficha_para_cliente');
function ftcrc_crear_ficha_para_cliente($user_id) {
    $user = get_userdata($user_id);
    if (in_array('customer', (array) $user->roles)) {
        global $wpdb;
        $tabla = $wpdb->prefix . 'fichas_tratamiento';
        $existe = $wpdb->get_var($wpdb->prepare("SELECT id FROM $tabla WHERE user_id = %d", $user_id));
        if (!$existe) {
            $wpdb->insert($tabla, [
                'user_id' => $user_id,
                'nombre_cliente' => $user->display_name,
                'fecha_creacion' => current_time('mysql'),
            ]);
        }
    }
}

// ==================== ENDPOINT CLIENTE ====================
add_action('init', function() {
    add_rewrite_endpoint('historia-clinica', EP_ROOT | EP_PAGES);
    add_rewrite_endpoint('gestionar-fichas', EP_ROOT | EP_PAGES);
});
add_filter('woocommerce_account_menu_items', function($items) {
    $user = wp_get_current_user();
    $items['historia-clinica'] = 'Historia Clínica';
    if (in_array('tecnico_nivel_1', $user->roles) || in_array('tecnico_nivel_2', $user->roles)) {
        $items['gestionar-fichas'] = 'Gestionar Fichas';
    }
    return $items;
});
add_action('woocommerce_account_historia-clinica_endpoint', function() {
    $user_id = get_current_user_id();
    global $wpdb;
    $tabla = $wpdb->prefix . 'fichas_tratamiento';
    $ficha = $wpdb->get_row($wpdb->prepare("SELECT * FROM $tabla WHERE user_id = %d", $user_id), ARRAY_A);
    if (!$ficha) { echo '<p>Ficha no disponible.</p>'; return; }
    echo '<h2>Ficha de Tratamiento</h2><ul>';
    foreach ($ficha as $campo => $valor) {
        if ($campo === 'id' || $campo === 'user_id' || $campo === 'fecha_creacion') continue;
        echo '<li><strong>' . esc_html(ucwords(str_replace("_", " ", $campo))) . ':</strong> ' . esc_html($valor) . '</li>';
    }
    echo '</ul>';
});

// ==================== ENDPOINT TÉCNICO ====================
add_action('woocommerce_account_gestionar-fichas_endpoint', function() {
    $user = wp_get_current_user();
    if (!in_array('tecnico_nivel_1', $user->roles) && !in_array('tecnico_nivel_2', $user->roles)) {
        echo '<p>No tienes permisos.</p>'; return;
    }

    echo '<h2>Gestionar Fichas de Clientes</h2>';
    echo '<form method="POST"><select name="cliente_id">';
    $clientes = get_users(['role' => 'customer']);
    foreach ($clientes as $cliente) {
        $asignado = get_user_meta($cliente->ID, 'ftcrc_tecnico_asignado', true);
        if ($asignado == $user->ID) {
            echo '<option value="' . $cliente->ID . '">' . esc_html($cliente->display_name) . '</option>';
        }
    }
    echo '</select><button type="submit">Cargar Ficha</button></form>';

    if (isset($_POST['cliente_id'])) {
        global $wpdb;
        $tabla = $wpdb->prefix . 'fichas_tratamiento';
        $cid = intval($_POST['cliente_id']);
        $asignado = get_user_meta($cid, 'ftcrc_tecnico_asignado', true);
        if ($asignado != $user->ID) { echo '<p>No autorizado.</p>'; return; }
        $ficha = $wpdb->get_row($wpdb->prepare("SELECT * FROM $tabla WHERE user_id = %d", $cid), ARRAY_A);
        if (!$ficha) { echo '<p>Ficha no encontrada.</p>'; return; }

        echo '<form method="POST"><input type="hidden" name="ficha_id" value="' . esc_attr($ficha['id']) . '">';
        echo '<p><label>Observaciones:<br><textarea name="observaciones">' . esc_textarea($ficha['observaciones']) . '</textarea></label></p>';
        echo '<p><label>Estado Fibra:<input type="text" name="estado_fibra" value="' . esc_attr($ficha['estado_fibra']) . '"></label></p>';
        echo '<button type="submit" name="guardar_ficha">Guardar Cambios</button></form>';
    }

    if (isset($_POST['guardar_ficha'])) {
        $fid = intval($_POST['ficha_id']);
        global $wpdb;
        $wpdb->update($wpdb->prefix . 'fichas_tratamiento', [
            'observaciones' => sanitize_text_field($_POST['observaciones']),
            'estado_fibra' => sanitize_text_field($_POST['estado_fibra']),
        ], ['id' => $fid]);
        echo '<p>Ficha actualizada.</p>';
    }
});

// ==================== ADMIN: CAMPO EN PERFIL Y TABLA ====================
add_action('show_user_profile', 'ftcrc_campo_tecnico_asignado');
add_action('edit_user_profile', 'ftcrc_campo_tecnico_asignado');
function ftcrc_campo_tecnico_asignado($user) {
    if (!current_user_can('administrator')) return;
    $tecnicos = get_users(['role__in' => ['tecnico_nivel_1', 'tecnico_nivel_2']]);
    $asignado = get_user_meta($user->ID, 'ftcrc_tecnico_asignado', true);
    echo '<h3>Técnico Asignado</h3><select name="ftcrc_tecnico_asignado"><option value="">-- Ninguno --</option>';
    foreach ($tecnicos as $tec) {
        $selected = $asignado == $tec->ID ? 'selected' : '';
        echo "<option value='{$tec->ID}' $selected>{$tec->display_name}</option>";
    }
    echo '</select>';
}
add_action('personal_options_update', 'ftcrc_guardar_tecnico_asignado');
add_action('edit_user_profile_update', 'ftcrc_guardar_tecnico_asignado');
function ftcrc_guardar_tecnico_asignado($user_id) {
    if (isset($_POST['ftcrc_tecnico_asignado'])) {
        update_user_meta($user_id, 'ftcrc_tecnico_asignado', intval($_POST['ftcrc_tecnico_asignado']));
    }
}

add_action('admin_menu', function() {
    add_menu_page('Asignar Técnicos', 'Asignar Técnicos', 'manage_options', 'asignar-tecnicos', 'ftcrc_admin_asignaciones');
});
function ftcrc_admin_asignaciones() {
    if (!current_user_can('manage_options')) return;
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cliente_id'], $_POST['tecnico_id'])) {
        update_user_meta(intval($_POST['cliente_id']), 'ftcrc_tecnico_asignado', intval($_POST['tecnico_id']));
        echo '<div class="updated"><p>Asignación actualizada.</p></div>';
    }

    $clientes = get_users(['role' => 'customer']);
    $tecnicos = get_users(['role__in' => ['tecnico_nivel_1', 'tecnico_nivel_2']]);

    echo '<div class="wrap"><h1>Asignación de Técnicos a Clientes</h1><table class="widefat"><thead><tr><th>Cliente</th><th>Técnico Asignado</th><th>Acción</th></tr></thead><tbody>';
    foreach ($clientes as $cliente) {
        $asignado = get_user_meta($cliente->ID, 'ftcrc_tecnico_asignado', true);
        echo '<tr><form method="POST">';
        echo '<td>' . esc_html($cliente->display_name) . '</td><td><select name="tecnico_id">';
        foreach ($tecnicos as $tec) {
            $selected = $asignado == $tec->ID ? 'selected' : '';
            echo "<option value='{$tec->ID}' $selected>{$tec->display_name}</option>";
        }
        echo '</select></td>';
        echo '<td><input type="hidden" name="cliente_id" value="' . esc_attr($cliente->ID) . '"><button class="button button-primary" type="submit">Asignar</button></td>';
        echo '</form></tr>';
    }
    echo '</tbody></table></div>';
}
?>
