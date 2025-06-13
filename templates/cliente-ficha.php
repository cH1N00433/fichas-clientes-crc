<?php
if (!defined('ABSPATH')) exit;

$current_user_id = get_current_user_id();
global $wpdb;

$table_name = $wpdb->prefix . 'fichas_cliente';
$ficha = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE user_id = %d", $current_user_id));

if (!$ficha) {
    echo '<p>No hay ficha médica registrada.</p>';
    return;
}

$data = json_decode($ficha->json_datos, true);

echo '<h2>Historia Clínica</h2>';
echo '<ul>';
echo '<li><strong>Peso:</strong> ' . esc_html($data['peso'] ?? '') . ' kg</li>';
echo '<li><strong>Altura:</strong> ' . esc_html($data['altura'] ?? '') . ' cm</li>';
echo '<li><strong>Diagnóstico:</strong> ' . esc_html($data['diagnostico'] ?? '') . '</li>';
echo '<li><strong>Tratamientos:</strong> <pre>' . esc_html($data['tratamientos'] ?? '') . '</pre></li>';
echo '<li><strong>Observaciones:</strong> <pre>' . esc_html($data['observaciones'] ?? '') . '</pre></li>';
if (!empty($data['sesiones'])) {
    echo '<li><strong>Sesiones:</strong><ul>';
    foreach ($data['sesiones'] as $sesion) {
        echo '<li>' . esc_html($sesion) . '</li>';
    }
    echo '</ul></li>';
}
echo '</ul>';
?>
