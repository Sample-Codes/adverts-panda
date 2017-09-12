<?php include apply_filters( "adverts_template_load", ADVERTS_PATH . 'templates/single.php' ); ?>

<link rel='stylesheet' href='plugins/wpadverts/assets/css/simply.css' type='text/css' media='all'/>
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

//SimplyWorld

$children = get_children( array( 'post_parent' => $post_id ) );
$thumb_id = get_post_thumbnail_id( $post_id );
$images = array();

if( empty( $children ) ) {
    //return $img;
}

if( isset( $children[$thumb_id] ) ) {
    $images[$thumb_id] = $children[$thumb_id];
    unset($children[$thumb_id]);
}

$images += $children;
$img = $images;
$images = adverts_sort_images($images, $post_id);
$countimg = count($images);

$tmp = get_option( 'pccf_options', $pccf_defaults );
$db_search_string = $tmp['txtar_keywords'];
$keywords = array_map( 'trim', explode( ',', $db_search_string ) ); // explode and trim whitespace
$keywords = array_unique( $keywords ); // get rid of duplicates in the keywords textbox

$bad_string = str_replace($keywords,'***', $post_content);

$countstar = substr_count($bad_string,'***');

$pregurl = preg_match_all('@((https?://)?([-\\w]+\\.[-\\w\\.]+)+\\w(:\\d+)?(/([-\\w/_\\.]*(\\?\\S+)?)?)*)@', $post_content, $pregurl);
$left_time_to_msg = ceil((3600 - $time_passed)/60);

    if ($countimg > 5) {
        echo '<p style="color: red">Загружено больше чем 5 изображений, пожалуйста вернитесь в редактирование объявления и удалите лишние.</p>';
    }

    if (preg_match_all('@((https?://)?([-\\w]+\\.[-\\w\\.]+)+\\w(:\\d+)?(/([-\\w/_\\.]*(\\?\\S+)?)?)*)@', $post_content, $a)) {
        echo '<p style="color: red">Запрещён ввод URL ссылок.</p>';
    }

    if ($time_passed < 3600) {
        echo '<div class="simply-info"><strong>Внимание!</strong><br>';
        echo 'До нового сообщения осталось '. $left_time_to_msg .' минут.</div>';
    }





//echo '<pre>'; print_r($last_post_data);  echo '</pre>';
//echo '<pre>'; print_r($new_post_data);  echo '</pre>';
//echo '<pre>'; print_r($time_passed);  echo '</pre>';
//echo '<pre>'; print_r($time_per_day);  echo '</pre>';
//echo '<pre>'; var_dump($countstar);  echo '</pre>';
//echo '<pre>'; print_r(get_posts($post_id));  echo '</pre>';
//        die;

//SimplyWorld
?>

<form action="" method="post" style="display:inline">
    <input type="hidden" name="_adverts_action" value="" />
    <input type="hidden" name="_post_id" id="_post_id" value="<?php esc_attr_e($post_id) ?>" />
    <input type="submit" value="<?php _e("Edit Listing", "adverts") ?>" style="font-size:1.2em" class="adverts-cancel-unload" />
</form>

<form action="" method="post" id="sub_post" style="display:inline">
    <input type="hidden" name="_adverts_action" value="save" />
    <input type="hidden" name="_post_id" id="_post_id" value="<?php esc_attr_e($post_id) ?>" />
    <input type="submit" <?= ($time_passed > 3600 && $time_per_day > 86400 ? false : 'disabled')
                              || $countimg > 5 ? 'disabled' : false
                              || $countstar > 0 ? 'disabled' : false
                              || $pregurl == true ? 'disabled' : false ?> value="<?php _e("Publish Listing", "adverts") ?>" style="font-size:1.2em" class="adverts-cancel-unload" />
</form>

<style>
    .simply-info {
        color: #000a0e;
        background-color: rgba(210, 8, 8, 0.33);
        border-color: #bce8f1;
        padding: 8px 35px 8px 14px;
        margin-bottom: 20px;
        text-shadow: 0 1px 0 rgba(255,255,255,.5);
        border: 1px solid #fbeed5;
        -webkit-border-radius: 4px;
    }
</style>

<script>

</script>