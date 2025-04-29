<?php
if (!defined('ABSPATH')) {
    exit;
}

function wp_vue_front_handle_team() {
    // Extract the base nonce from the submitted nonce (remove the ID)
    $id = sanitize_text_field($_POST['id']);
    $action_string = 'wp_vue_front_team_' . $id;
    if (!wp_verify_nonce($_POST['nonce'], $action_string)) {
        wp_send_json_error('Invalid nonce');
        wp_die();
    }
    error_log('=== Team request received ===');
    error_log('POST data: ' . print_r($_POST, true));
    error_log('Request method: ' . $_SERVER['REQUEST_METHOD']);
    error_log('Request URI: ' . $_SERVER['REQUEST_URI']);
    error_log('==================================');
    
    // Handle accounting related POST requests here
    $response = array(
        'success' => true,
        'message' => 'Team request processed'
    );
    
    wp_send_json($response);
}
