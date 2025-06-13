<?php
if (!defined('ABSPATH')) exit;

$current_user = wp_get_current_user();


        if (isset($_GET['sub']) && $_GET['sub'] === 'agenda') {
            include plugin_dir_path(__DIR__) . 'templates/agenda.php';
            return;
        }

echo '<div class="wrap">';
echo '<h1>Panel Técnico</h1>';
echo '<p>Bienvenido, ' . esc_html($current_user->display_name) . '.</p>';
echo '<ul>';
echo '<li><a href="#">📋 Buscar Fichas</a></li>';
echo '<li><a href="#">📚 Ver Historial</a></li>';
echo '<li><a href="#">📝 Editar Ficha</a></li>';
echo '<li><a href="?page=fcw_tecnico_panel&sub=agenda">📅 Agenda de Turnos</a></li>';
echo '<li><a href="#">🔗 Conectar Google Calendar</a></li>';
echo '</ul>';
echo '</div>';
