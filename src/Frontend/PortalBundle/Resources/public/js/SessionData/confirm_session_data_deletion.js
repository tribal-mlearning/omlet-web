ConfirmSessionDataDeletion = {	
    init: function() {
        $("#confirm_session_data_deletion_dialog").dialog({
            autoOpen: false,
            resizable: false,
            modal: true,
            width: 440,
            height: 220,
            buttons: [
                {
                    text: ConfirmSessionDataDeletion.i18n.confirm,
                    "class": 'btn100 left',
                    click: function() {
                        $(this).dialog("close");
                        ConfirmSessionDataDeletion.confirmCallback();
                    }
                },
                {
                    text: ConfirmSessionDataDeletion.i18n.exportToCsv,
                    "class": 'btn150 left',
                    click: function() {
                        ConfirmSessionDataDeletion.exportCallback();
                    }
                },
                {
                    text: ConfirmSessionDataDeletion.i18n.cancel,
                    "class": 'btn100 left',
                    click: function() {
                        $(this).dialog("close");
                    }
                }
            ]
        });
    },
    
    open: function(text, confirmCallback, exportCallback) {
        ConfirmSessionDataDeletion.confirmCallback = confirmCallback;
        ConfirmSessionDataDeletion.exportCallback = exportCallback;
        $("#confirm_session_data_deletion_dialog p").html(text);
        $("#confirm_session_data_deletion_dialog").dialog("open");
    }
};

$(ConfirmSessionDataDeletion.init);