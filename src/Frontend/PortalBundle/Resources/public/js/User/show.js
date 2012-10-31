UserShow = {
    init : function() {
        $("#delete_button").click(function() {
            UserIndex.verifyIfUserHasPackages($(this).attr("user_id"));
        });
    }	
}

$(UserShow.init);