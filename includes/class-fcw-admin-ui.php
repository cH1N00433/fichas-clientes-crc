<?php
defined('ABSPATH') || exit;

// class-fcw-admin-ui.php

class FCW_Admin_UI {

    public static function init() {
        add_action('show_user_profile', [__CLASS__, 'extra_user_profile_fields']);
        add_action('edit_user_profile', [__CLASS__, 'extra_user_profile_fields']);
        add_action('personal_options_update', [__CLASS__, 'save_extra_user_profile_fields']);
        add_action('edit_user_profile_update', [__CLASS__, 'save_extra_user_profile_fields']);
    }

    public static function extra_user_profile_fields($user) {
        if (!current_user_can('administrator')) return;

        $tecnico_id = get_user_meta($user->ID, 'fcw_tecnico_id', true);
        $nivel_tecnico = get_user_meta($user->ID, 'fcw_nivel_tecnico', true);

        $tecnicos = get_users([
            'role__in' => ['tecnico_nivel_1', 'tecnico_nivel_2'],
            'fields' => ['ID', 'display_name']
        ]);
        ?>
        <h3>Ficha Clínica - Asignación de Técnico</h3>
        <table class="form-table">
            <tr>
                <th><label for="fcw_tecnico_id">Técnico Asignado</label></th>
                <td>
                    <select name="fcw_tecnico_id" id="fcw_tecnico_id">
                        <option value="">— Ninguno —</option>
                        <?php foreach ($tecnicos as $tecnico) : ?>
                            <option value="<?php echo esc_attr($tecnico->ID); ?>" <?php selected($tecnico_id, $tecnico->ID); ?>>
                                <?php echo esc_html($tecnico->display_name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="fcw_nivel_tecnico">Nivel de Técnico</label></th>
                <td>
                    <select name="fcw_nivel_tecnico" id="fcw_nivel_tecnico">
                        <option value="1" <?php selected($nivel_tecnico, '1'); ?>>Nivel 1</option>
                        <option value="2" <?php selected($nivel_tecnico, '2'); ?>>Nivel 2</option>
                    </select>
                </td>
            </tr>
        </table>
        <?php
    }

    public static function save_extra_user_profile_fields($user_id) {
        if (!current_user_can('administrator')) return;

        update_user_meta($user_id, 'fcw_tecnico_id', sanitize_text_field($_POST['fcw_tecnico_id']));
        update_user_meta($user_id, 'fcw_nivel_tecnico', sanitize_text_field($_POST['fcw_nivel_tecnico']));
    }
}
