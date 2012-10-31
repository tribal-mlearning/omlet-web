MobileUserIndex = {
    init : function() {
        $(".delete_image").each(function(index, component) {
            $(component).click(function() {                
                MobileUserIndex.verifyIfUserHasSessionData($(component).attr("user_id"));
            })
        });        
    },   
    
    verifyIfUserHasSessionData : function(userId) {
        if (!confirm(MobileUserIndex.i18n.confirmDelete)) return;

        MobileUserIndex.deleteUser(userId);
    },
    
    deleteUser : function(userId) {
        window.location.href = Routing.generate("mobile_user_delete", { "id": userId });
    },
    
    exportToCSV : function(userId) {
        window.location.href = Routing.generate("mobile_user_session_data_export", { "id": userId });
    }    
}

$(MobileUserIndex.init);