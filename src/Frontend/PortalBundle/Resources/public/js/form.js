$(document).ready(function() {
	setTimeout(function() {
		$("#form_read_icdoc_1").bind('click',function(){
			$(".box-questionnaire").find(".button").addClass("submit-button");
		});
	
		$("#form_read_icdoc_0").bind('click',function(){
			$(".box-questionnaire").find(".button").removeClass("submit-button").css("cursor","normal");
		});
	
		if ($("#form_read_icdoc_1").is(":checked")){
			$(".box-questionnaire").find(".button").addClass("submit-button");
		}
	
		jQuery('.submit-button').live('click', function() {
	 		jQuery(this).closest('form').submit();
		});
	
	
		jQuery('input').keypress(function(ev) {
			if (ev.which == 13) {
				jQuery(this).closest('form').submit();
			}
		});
	}, 0);

});

var validateForm = function(button) {
	var form = $(button).closest('form');
	
	var error = false;
	form.find(':input').each(function() {
		var $this = $(this);
		
		$this.removeClass('error');
		
		if ($this.hasClass('required')) {
			if ($this.val() == '') {
				$this.addClass('error');
				error = true;
			}
		}
	});
	
	if (!error) {
		form.submit();
	}
};
