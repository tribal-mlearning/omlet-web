MobileUserShow = {
    init : function() {
        $("#delete_button").click(function() {
            MobileUserIndex.verifyIfUserHasSessionData($(this).attr("user_id"));
        });
    }
}

$(MobileUserShow.init);