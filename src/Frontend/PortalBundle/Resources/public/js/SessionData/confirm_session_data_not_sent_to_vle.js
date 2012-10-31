ConfirmSessionDataNotSentToVle = {	
    init: function() {
        $("#session_data_not_sync_with_vle_warning_dialog").dialog({
            autoOpen: false,
            resizable: false,
            modal: true,
            width: 440,
            height: 180,
            buttons: [
                {
                    text: ConfirmSessionDataNotSentToVle.i18n.confirm,
                    "class": 'default-button',
                    click: function() {
                        $(this).dialog("close");
                        ConfirmSessionDataNotSentToVle.confirmCallback();
                    }
                },
                {
                    text: ConfirmSessionDataNotSentToVle.i18n.cancel,
                    "class": 'default-button',
                    click: function() {
                        $(this).dialog("close");
                    }
                }
            ]
        });
    },
    
    open: function(confirmCallback) {
        ConfirmSessionDataNotSentToVle.confirmCallback = confirmCallback;                                
        $("#session_data_not_sync_with_vle_warning_dialog").dialog("open");
    }
};

$(ConfirmSessionDataNotSentToVle.init);