jQuery(function($) {

    /**
     * Enable AJAX tab switching in [adverts_add] shortcode third step
     */
    $(".adverts-tab-link").click(function(e) {
        e.preventDefault();
        $(".adverts-tab-link").removeClass("current");
        $(".adverts-tab-content").css("opacity", 0.5);

        $(this).addClass("current");

        var data = {
            action: "adext_payments_render",
            gateway: $(this).data("tab"),
            page_id: $(".adverts-payment-data").data("page-id"),
            listing_id: $(".adverts-payment-data").data("listing-id"),
            no_money: $(".adverts-payment-data").data("no_money"),
            object_id: $(".adverts-payment-data").data("object-id")
        };

        $.ajax({
            url: adverts_frontend_lang.ajaxurl,
            context: this,
            type: "post",
            dataType: "json",
            data: data,
            success: function(response) {
                $(".adverts-tab-content").css("opacity", 1).html(response.html);
            }
        });

    });

    /**
     * Place order in [adverts_add] shortcode third step
     */
    $(".adext-payments-place-order").click(function(e) {
        e.preventDefault();
        $(".adverts-tab-content").css("opacity", 0.5);

        var data = {
            action: "adext_payments_render",
            gateway: $(".adverts-tab-link.current").data("tab"),
            page_id: $(".adverts-payment-data").data("page-id"),
            listing_id: $(".adverts-payment-data").data("listing-id"),
            object_id: $(".adverts-payment-data").data("object-id"),
            no_money: $(".adverts-payment-data").data("no_money"),
            form: $(".adverts-tab-content form").serializeArray()
        };

        $.ajax({
            url: adverts_frontend_lang.ajaxurl,
            context: this,
            type: "post",
            dataType: "json",
            data: data,
            success: function(response) {
                // alert('123');
                if(response.no_money == 'no_money') {
                    // alert('no_money');
                    $('.adverts-tab-content').css("opacity", 1).html('<br><div class="simply-info" style="color: #000a0e; ' +
                        'background-color: rgba(210, 8, 8, 0.33); padding: 8px 35px 8px 14px; margin-bottom: 20px;' +
                        'text-shadow: 0 1px 0 rgba(255,255,255,.5); border: 1px solid #fbeed5; -webkit-border-radius: 4px;">' +
                        '<strong>Внимание! На Вашем счёте не достаточно средств!!!</strong><br>' +
                        '<a href="http://develop.panda-code.com/my-account/wallet/">Пожалуйста пополните свой счёт</a>');
                    $('.adext-payments-place-order').fadeOut();
                } else if (response.no_money == '') {
                    $(".adverts-tab-content").css("opacity", 1).html(response.html);

                    if (response.result == 1) {
                        $(".adext-payments-place-order").fadeOut();
                        $("ul.adverts-tabs li").unbind("click").css("cursor", "default");
                    }

                    if (response.execute == "click") {
                        $(response.execute_id).click();
                    } else if (response.execute == "submit") {
                        $(response.execute_id).submit();
                    }
                }


            }
        });
    });

    $(".adverts-tab-link.current").click();
});
