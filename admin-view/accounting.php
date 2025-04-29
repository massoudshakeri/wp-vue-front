<?php
if (!defined('ABSPATH')) exit;

function wp_vue_front_accounting_admin_page() {
    // Get accounting entries from the database or wherever they are stored
    $raw_entries = array(
        array(
            'id' => 1,
            'date' => '2025-04-26',
            'description' => 'Sample Entry 1',
            'amount' => 100.00,
            'type' => 'income',
            'isEditing' => false
        ),
        array(
            'id' => 2,
            'date' => '2025-04-26',
            'description' => 'Sample--- Entry 2',
            'amount' => 150.00,
            'type' => 'expense',
            'isEditing' => false
        )
    );
    $entries = array_map(function($entry) {
        $entry['nonce'] = get_accounting_nonce($entry['id']);
        return $entry;
    }, $raw_entries);
?>
    <div
        data-vue-component="accounting-view"
        data-vue-props='<?= esc_attr(wp_json_encode(["entries" => $entries])) ?>'>
    </div>
<?php
}
function get_accounting_nonce($id) {
    return wp_create_nonce('wp_vue_front_accounting_' . $id);
}