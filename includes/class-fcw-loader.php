<?php
defined('ABSPATH') || exit;

// class-fcw-loader.php

class FCW_Loader {

    public static function init() {
        add_action('init', [__CLASS__, 'add_endpoints']);
        add_filter('woocommerce_account_menu_items', [__CLASS__, 'add_account_menu_item']);
        add_action('woocommerce_account_historia-clinica_endpoint', [__CLASS__, 'render_historia_clinica']);
    }

    public static function add_endpoints() {
        add_rewrite_endpoint('historia-clinica', EP_ROOT | EP_PAGES);
    }

    public static function add_account_menu_item($items) {
        $items['historia-clinica'] = __('Historia Clínica', 'ficha-cliente-woo');
        return $items;
    }

    public static function render_historia_clinica() {
        include plugin_dir_path(__DIR__) . 'templates/cliente-ficha.php';
    }
}


        // Menú para técnicos en el admin
        add_action('admin_menu', [__CLASS__, 'register_tecnico_menu']);
        add_action('admin_init', [__CLASS__, 'restrict_admin_access']);
    }

    public static function register_tecnico_menu() {
        if (!current_user_can('tecnico_nivel_1') && !current_user_can('tecnico_nivel_2')) return;

        add_menu_page(
            __('Panel Técnico', 'ficha-cliente-woo'),
            __('Panel Técnico', 'ficha-cliente-woo'),
            'read',
            'fcw_tecnico_panel',
            [__CLASS__, 'render_tecnico_panel'],
            'dashicons-id',
            6
        );
    }

    public static function render_tecnico_panel() {
        include plugin_dir_path(__DIR__) . 'templates/tecnico-panel.php';
    }

    public static function restrict_admin_access() {
        if (defined('DOING_AJAX') && DOING_AJAX) return;
        if (current_user_can('tecnico_nivel_1') || current_user_can('tecnico_nivel_2')) {
            global $pagenow;
            if ($pagenow === 'index.php' || !is_admin()) return;
            $allowed_pages = ['admin.php'];
            if (!in_array($pagenow, $allowed_pages)) {
                wp_redirect(admin_url('admin.php?page=fcw_tecnico_panel'));
                exit;
            }
        }
