<?php
if (!defined('ABSPATH')) exit;

function wp_vue_front_team_admin_page() {
// Get accounting members from the database or wherever they are stored
$raw_members = array(
    array(
        'id' => 1,
        'name' => 'John Doe',
        'email' => 'john.doe@example.com',
        'phone' => '123-456-7890'
    ),
    array(
        'id' => 2,
        'name' => 'Jane Smith',
        'email' => 'jane.smith@example.com',
        'phone' => '123-456-7890'
    ),
    array(  
        'id' => 3,  
        'name' => 'Jim Beam',  
        'email' => 'jim.beam@example.com',  
        'phone' => '123-456-7890'  
    )
);
$members = array_map(function($entry) {
    $entry['nonce'] = get_team_nonce($entry['id']);
    return $entry;
}, $raw_members);

?>
    <div
            data-vue-component="team-view"
        data-vue-props='<?= esc_attr(wp_json_encode(["members" => $members])) ?>'>
    </div>
<?php
}
function get_team_nonce($id) {
    return wp_create_nonce('wp_vue_front_team_' . $id);
}