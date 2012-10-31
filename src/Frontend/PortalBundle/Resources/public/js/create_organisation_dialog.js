CreateOrganisationDialog = {
    init: function() {
        $("#create_organisation_dialog").dialog({
            autoOpen: false,
            resizable: false,
            modal: true,
            width: 440,
            height: 190,
            buttons: [
            {
                text: CreateOrganisationDialog.i18n ? CreateOrganisationDialog.i18n.confirm : "Confirm",
                "class": 'btn100',
                click: function() {
                    $.post(Routing.generate('organisation_create'), { "name": $("#organisation_name").val() }, function(data) {
                        if (data == null) return;

                        $("#organisation_name").val("");

                        if (CreateOrganisationDialog.created == null) return;

                        CreateOrganisationDialog.created(data);
                    });

                    $(this).dialog("close");
                }
            },	
            {
                text: CreateOrganisationDialog.i18n ? CreateOrganisationDialog.i18n.cancel : "Cancel",
                "class": 'btn100',
                click: function() {
                    $(this).dialog("close");
                }
            }
            ]
        });
    }
};

$(CreateOrganisationDialog.init);