UserIndex = {
    init : function() {
        $(".delete_image").each(function(index, component) {
            $(component).click(function() {
                UserIndex.verifyIfUserHasPackages($(component).attr("user_id"));
            })
        });
    },
    
    verifyIfUserHasPackages : function(userId) {                        
        if (!confirm(UserIndex.i18n.confirmDelete)) return;
        
        $.get(Routing.generate("user_has_packages", {"id": userId}), function(data) {
            if (data == false)
                UserIndex.deleteUser(userId);
            else
                ChooseNewPackageOwnerDialog.open(data, function() { UserIndex.updateAndDeleteUser(userId) });
        });
    },
    
    deleteUser : function(userId) {
        window.location.href = Routing.generate("user_delete", {"id": userId });
    },
    
    updateAndDeleteUser : function(userId) {
        window.location.href = Routing.generate("user_update_and_delete", {"id": userId, "new_id": ChooseNewPackageOwnerDialog.newUser() });
    }
}

$(UserIndex.init);