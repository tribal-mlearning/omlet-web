ChangePasswordDialog = {	
	init: function() {
		$("#change_password_dialog").dialog({
			autoOpen: false,
			resizable: false,
			modal: true,
			width: 450,
			height: 400,
			buttons: [
		        {
		            text: ChangePasswordDialog.i18n ? ChangePasswordDialog.i18n.confirm : "Confirm",
		            "class": 'btn100',
		            click: function() {
		            	var url = Routing.generate("user_update_password");
		            	var parameters = { 
	        					"current": $("#current").val(), 
	                            "new": $("#new").val(),
	        					"verification": $("#verification").val()
                        };

	                    $.post(url, parameters, function(data) {
	                    	if (data == "true") {
	                    		$("#change_password_dialog").dialog("close");
	                    	} else {
	                    		$("#error_message").html(data.replace(/"/g, ''));
	                    		$("#change_password_error_message").fadeIn();
	                    	}
	                    });	                   
		            }
		        },
		        {
		            text: ChangePasswordDialog.i18n ? ChangePasswordDialog.i18n.cancel : "Cancel",
		            "class": 'btn100',
		            click: function() {
		                $(this).dialog("close");
		            }
		        }
			]
		});
	},

	open_dialog: function() {
        $("#current").val("");
        $("#new").val("");
        $("#verification").val("");
        $("#change_password_error_message").hide();
        $("#change_password_dialog").dialog("open");
	}
};

$(ChangePasswordDialog.init);