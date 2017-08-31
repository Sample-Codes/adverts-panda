<?php adverts_flash( $adverts_flash ) ?>

<form action="" method="post" class="adverts-form adverts-form-aligned">
    <fieldset>

        <?php foreach($form->get_fields( array( "type" => array( "adverts_field_hidden" ) ) ) as $field): ?>
        <?php call_user_func( adverts_field_get_renderer($field), $field) ?>
        <?php endforeach; ?>



        <!-- SimplyWorld -->

        <?php if ( is_user_logged_in() ) : ?>
        <?php foreach($form->get_fields() as $field): ?>
                <!-- SimplyWorld -->
                <?php if($field["label"] == 'Информация об объявлении') {
                    $field['label'] = '';
                }?>
                <?php if($field["label"] == 'Объявление') {
                    $field['label'] = '';
                }?>
                <!-- SimplyWorld -->

        <div class="adverts-control-group <?php echo esc_attr( str_replace("_", "-", $field["type"] ) . " adverts-field-name-" . $field["name"] ) ?> <?php if(adverts_field_has_errors($field)): ?>adverts-field-error<?php endif; ?>">

            <?php if($field["type"] == "adverts_field_header"): ?>

            <div class="adverts-field-header">

                <span class="adverts-field-header-title"><?php echo esc_html($field["label"]) ?></span>

                <?php if( isset( $field["description"] ) ): ?>
                <span class="adverts-field-header-description"><?php echo esc_html( $field["description"] ) ?></span>
                <?php endif; ?>
            </div>
            <?php else: ?>


            <label for="<?php esc_attr_e($field["name"]) ?>">
                <?php esc_html_e($field["label"]) ?>
                <?php if(adverts_field_has_validator($field, "is_required") && !empty($field["label"])): ?>
                <span class="adverts-form-required">*</span>
                <?php endif; ?>
            </label>

            <?php call_user_func( adverts_field_get_renderer($field), $field) ?>

            <?php endif; ?>

            <?php if(adverts_field_has_errors($field)): ?>
            <ul class="adverts-field-error-list">
                <?php foreach($field["error"] as $k => $v): ?>
                <li><?php echo esc_html($v) ?></li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>


        </div>
        <?php endforeach; ?>

            <div  style="border-top:2px solid silver; padding: 1em 0 1em 0">
                <input type="submit" name="submit" value="<?php _e("Preview", "adverts") ?>" style="font-size:1.2em" class="adverts-cancel-unload" />
            </div>

            <!--SimplyWorld -->
        <?php else:?>

                <div class="adverts-flash-messages adverts-flash-error">
                    <div class="adverts-flash-single">
                        <span class="adverts-flash-message-icon adverts-icon-lock"></span>
                        <span class="adverts-flash-message-text adverts-flash-padding">Только зарегистрированные пользователи могут открыть эту страницу.
                            <a href="http://ds2.systemethic.it/wp-login.php?redirect_to=http%3A%2F%2Fds2.systemethic.it%2Fadverts%2Fmanage%2F">Войдите в сайт</a> или
                            <a href="http://ds2.systemethic.it/wp-login.php?action=register">Зарегистрируйтесь</a>.
                        </span>
                    </div>
                </div>

        <?php endif; ?>
        <!--SimplyWorld -->

    </fieldset>
</form>
<style>
    #post-7 > div > form > fieldset > div:nth-child(15) > div{
        display: none;
    }
</style>