<?php adverts_flash( $adverts_flash ) ?>

<form action="" method="post" class="adverts-form adverts-form-aligned">
    <fieldset>

        <?php foreach($form->get_fields( array( "type" => array( "adverts_field_hidden" ) ) ) as $field): ?>
        <?php call_user_func( adverts_field_get_renderer($field), $field) ?>
        <?php endforeach; ?>

        <!--SimplyWorld -->

        <?php if ( is_user_logged_in() ) : ?>
        <?php foreach($form->get_fields() as $field): ?>

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
                <?php if(adverts_field_has_validator($field, "is_required")): ?>
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

            <div class="adverts-control-group adverts-field-account adverts-field-name-_adverts_account ">

                <div class="adverts-form-input-group adverts-form-input-group-checkbox adverts-field-rows-1"><div><label for="_adverts_account_1"><a href="http://ds2.systemethic.it/wp-login.php?action=register">Registration</a> or <a href="http://ds2.systemethic.it/wp-login.php?redirect_to=http%3A%2F%2Fds2.systemethic.it%2Fadverts%2Fadd%2F">Sign In</a></label></div></div>

            </div>

        <?php endif; ?>
        <!--SimplyWorld -->

    </fieldset>
</form>