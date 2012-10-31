$(function() {
	CreateOrganisationDialog.created = function(data) {
		$("#user_organisation").addOption(data.id, data.name, false);		
	};

	$("#create_organisation_dialog_image").click(function() {
		$("#create_organisation_dialog").dialog("open");
	});

	$('.metadata .delete-button').bind('click', function() {
		$(this).closest('li').remove();
	});	

    $('#add-metadata').click(function() {
        var metadataList = $('#metadata-fields-list');

        var newWidget = metadataList.attr('data-prototype');
        newWidget = newWidget.replace(/\$\$name\$\$/g, metadataCount);
        metadataCount++;

		var deleteButton = $('<a class="btn-delete delete-button" href="javascript:;" title="' + i18n.label.delete_metadata + '">x</a>');
		deleteButton.bind('click', function() {
			$(this).closest('li').remove();
		});
		
        var newLi = $('<li></li>').html(newWidget).append(deleteButton);
        newLi.appendTo($('#metadata-fields-list'));

        return false;
    });
});