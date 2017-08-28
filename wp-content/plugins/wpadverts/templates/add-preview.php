<?php include apply_filters( "adverts_template_load", ADVERTS_PATH . 'templates/single.php' ); ?>

<hr/>

<!-- SimplyWorld  получаем айди юзера-->
<?php $user = wp_get_current_user();
$args = array(
    'author'        =>  $user->ID,
    'numberposts' => 3,
    'orderby'       =>  'post_date',
    'post_type'     =>  'advert',
    'order'         =>  'DESC',
);

$all_user_posts = get_posts( $args );

$last_post_data = $all_user_posts[0]->post_date;
$new_post_data = current_time('mysql');
$time_passed = strtotime($new_post_data)-strtotime($last_post_data);
$time_per_day = strtotime($new_post_data) - strtotime($all_user_posts[2]->post_date);

//echo '<pre>'; print_r($last_post_data);  echo '</pre>';
//echo '<pre>'; print_r($new_post_data);  echo '</pre>';
//echo '<pre>'; print_r($time_passed);  echo '</pre>';
//echo '<pre>'; print_r($all_user_posts);  echo '</pre>';
//        die;
?>
<form action="" method="post" style="display:inline">
    <input type="hidden" name="_adverts_action" value="" />
    <input type="hidden" name="_post_id" id="_post_id" value="<?php esc_attr_e($post_id) ?>" />
    <input type="submit" value="<?php _e("Edit Listing", "adverts") ?>" style="font-size:1.2em" class="adverts-cancel-unload" />
</form>

<form action="" method="post" style="display:inline">
    <input type="hidden" name="_adverts_action" value="save" />
    <input type="hidden" name="_post_id" id="_post_id" value="<?php esc_attr_e($post_id) ?>" />
    <input type="submit" <?= $time_passed > 3600 || $time_per_day > 86400 ? false : 'disabled' ?> value="<?php _e("Publish Listing", "adverts") ?>" style="font-size:1.2em" class="adverts-cancel-unload" />
</form>