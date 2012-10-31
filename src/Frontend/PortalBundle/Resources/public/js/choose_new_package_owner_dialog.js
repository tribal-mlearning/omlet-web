ChooseNewPackageOwnerDialog = {	
    init: function() {
        $("#choose_new_package_owner_dialog").dialog({
            autoOpen: false,
            resizable: false,
            modal: true,
            width: 440,
            height: 225,
            buttons: [
                {
                    text: ChooseNewPackageOwnerDialog.i18n ? ChooseNewPackageOwnerDialog.i18n.confirm : "Confirm",
                    "class": 'btn100',
                    click: function() {                        
                        $(this).dialog("close");
                        ChooseNewPackageOwnerDialog.confirmCallback();
                    }
                },
                {
                    text: ChooseNewPackageOwnerDialog.i18n ? ChooseNewPackageOwnerDialog.i18n.cancel : "Cancel",
                    "class": 'btn100',
                    click: function() {
                        $(this).dialog("close");
                    }
                }
            ]
        });
    },  

    open: function(users, confirmCallback) {
        ChooseNewPackageOwnerDialog.confirmCallback = confirmCallback;
        
        $("#new_user").removeOption(/./);

        $(users).each(function(index, user) {
            $("#new_user").addOption(user.id, user.username, false);
        });

        $("#choose_new_package_owner_dialog").dialog("open");
    },
    
    newUser : function() {
        return $("#new_user").val();
    }
};

$(ChooseNewPackageOwnerDialog.init);