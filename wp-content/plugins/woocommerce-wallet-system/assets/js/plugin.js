$wk=jQuery.noConflict();
(function($wk){
	$wk(document).on('click', "#wallet-checkout-payment", function(){
		if(this.checked){
			check = 1;
		}else{
			check = 0;
		}
		$wk.ajax({
			url : wallet_ajax.ajaxurl,
			type : 'POST',
			data : {
				action : 'ajax_wallet_check',
				'check': check,
				"nonce":wallet_ajax.nonce
			},
			success : function(response){
				$wk( document.body ).trigger( 'update_checkout' );
			}
		});
	    $wk( document.body ).trigger( 'update_checkout' );
	});
})($wk);