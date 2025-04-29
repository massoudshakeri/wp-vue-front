<?php
if (!defined('ABSPATH')) exit;

function wp_vue_front_suppliers_admin_page() {
    // Get supplier entries from the database or wherever they are stored
    $raw_entries = array(
        array(
            'id' => 1,
            'name' => 'ABC Electronics',
            'contact' => 'John Smith',
            'email' => 'john@abcelectronics.com',
            'phone' => '123-456-7890',
            'address' => '123 Main St, Anytown, USA'
        ),
        array(
            'id' => 2,
            'name' => 'XYZ Manufacturing',
            'contact' => 'Sarah Johnson',
            'email' => 'sarah@xyzmanufacturing.com',
            'phone' => '234-567-8901',
            'address' => '456 Industrial Park, Somewhere, USA'
        ),
        array(
            'id' => 3,
            'name' => 'Global Supplies Inc',
            'contact' => 'Michael Brown',
            'email' => 'michael@globalsupplies.com',
            'phone' => '345-678-9012',
            'address' => '789 Business Ave, Anywhere, USA'
        )
    );

    $entries = array_map(function($entry) {
        $entry['nonce'] = get_suppliers_nonce($entry['id']);
        return $entry;
    }, $raw_entries);

    ?>
    <div
        data-vue-component="suppliers-view"
        data-vue-props='<?= esc_attr(wp_json_encode(["suppliers" => $entries])) ?>'>
    </div>
    <?php
}
function get_suppliers_nonce($id) {
    return wp_create_nonce('wp_vue_front_suppliers_' . $id);
}