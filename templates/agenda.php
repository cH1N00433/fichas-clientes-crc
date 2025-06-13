<?php
if (!defined('ABSPATH')) exit;

wp_enqueue_script('fullcalendar-js', 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js', [], null, true);
wp_enqueue_script('fcw-agenda-js', plugin_dir_url(__DIR__) . 'assets/js/agenda.js', ['fullcalendar-js'], null, true);
wp_localize_script('fcw-agenda-js', 'fcw_turnos', [
    'ajaxurl' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('fcw_turnos_nonce')
]);

echo '<div class="wrap">';
echo '<h1>Agenda de Turnos</h1>';
echo '<div id="fcw-calendar"></div>';
echo '</div>';
