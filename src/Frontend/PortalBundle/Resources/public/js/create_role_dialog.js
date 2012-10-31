CreateRoleDialog = {
    init: function() {
        $("#create_role_dialog").dialog({
            autoOpen: false,
            resizable: true ,
            modal: true,
            width: 440,
            height: 250,
            buttons: [
                {
                    text: CreateRoleDialog.i18n ? CreateRoleDialog.i18n.confirm : "Confirm",
                    "class": 'btn100',
                    click: function() {
                        $.post(Routing.generate('role_create'), { "name": $("#role_name").val(), "alias": $("#role_alias").val() }, function(data) {
                            if (data.status != 'success') return;

                            var newRoleAlias = ($("#role_alias").val() == '') ? $("#role_name").val().toUpperCase() : $("#role_alias").val();
                            addRole(data.id, newRoleAlias);

                            $("#role_name").val("");

                            if (CreateRoleDialog.created == null) return;

                            CreateRoleDialog.created(data);
                        });

                        $(this).dialog("close");
                    }
                },
                {
                    text: CreateRoleDialog.i18n ? CreateRoleDialog.i18n.cancel : "Cancel",
                    "class": 'btn100',
                    click: function() {
                        $(this).find('#role_name').val('');
                        $(this).dialog("close");
                    }
                }
            ]
        });
    }
};

$(CreateRoleDialog.init);