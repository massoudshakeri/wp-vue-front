<?php
if (!defined('ABSPATH')) exit;

function wp_vue_front_products_admin_page() {
    // Get accounting products from the database or wherever they are stored
$raw_products = array(
    array(
        'id' => 1,
        'name' => 'Product 1',
        'price' => 100.00,
        'stock' => 10,
        'description' => 'This is the first product'
    ),
    array(  
        'id' => 2,
        'name' => 'Product 2',
        'price' => 150.00,
        'stock' => 5,
        'description' => 'This is the second product'
    ),
    array(
        'id' => 3,
        'name' => 'Product 3',
        'price' => 200.00,
        'stock' => 0,
        'description' => 'This is the third product'
    )
);
$products = array_map(function($entry) {
    $entry['nonce'] = get_products_nonce($entry['id']);
    return $entry;
}, $raw_products);

?>
    <div
        data-vue-component="products-view"
        data-vue-props='<?= esc_attr(wp_json_encode(["products" => $products])) ?>'>
    </div>
<?php
}
function get_products_nonce($id) {
    return wp_create_nonce('wp_vue_front_products_' . $id);
}