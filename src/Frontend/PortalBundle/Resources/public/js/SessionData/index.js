SessionDataIndex = {        
    init : function() {
        SessionDataIndex.selectedIds = [];
        $("#filter_session_data_tr select").change(SessionDataIndex.filterBySpecificColumns);
        $("#select_all_table_session_data").click(SessionDataIndex.selectAllTableRecords);        
        $(".select_single_session_data").live("change", SessionDataIndex.selectSingleRecord);
        $("#delete_session_data_button").click(SessionDataIndex.showDeleteConfirmation);
        $("#export_session_data_button").click(SessionDataIndex.exportToCSV);
        $(".select_single_session_data").shiftcheckbox();
    },
    
    filterBySpecificColumns : function() {         
        var columnOfSelectElement = $(this).parent();
        var columnOfSelectElementIndex = $("#filter_session_data_tr th").index(columnOfSelectElement);
        SessionDataIndex.table.fnFilter($(this).val(), columnOfSelectElementIndex);
    },
    
    selectAllTableRecords : function() {          
        if ($(this).is(':checked')) {
            $(".select_single_session_data").attr("checked", "checked");
            SessionDataIndex.selectedIds = SessionDataIndex.ids;
        } else {
            $(".select_single_session_data").removeAttr("checked");
            SessionDataIndex.selectedIds = [];
        }
    },
    
    selectSingleRecord : function() {
        if ($(this).is(":checked")) {
            if ($(SessionDataIndex.selectedIds).index($(this).attr("session_data_id")) == -1) {
                SessionDataIndex.selectedIds.push($(this).attr("session_data_id"));
            }
        } else {
            var index = $(SessionDataIndex.selectedIds).index($(this).attr("session_data_id"));
            SessionDataIndex.selectedIds.splice(index, 1);
            $("#select_all_table_session_data").removeAttr("checked");
        }                                
    },
    
    exportToCSV : function() {
        if (SessionDataIndex.selectedIds.length > 0) {
            var export_url = Routing.generate("session_data_export", { "ids[]" : SessionDataIndex.selectedIds });
            window.location.href = export_url;
        } else {
            alert(SessionDataIndex.i18n.cannotExportMessage);
        }
        return false;
    },
    
    showDeleteConfirmation : function() {
        if (SessionDataIndex.selectedIds.length > 0) {
            var text = SessionDataIndex.i18n.confirmDeletionMessage.replace("{records}", SessionDataIndex.selectedIds.length);
            ConfirmSessionDataDeletion.open(text, SessionDataIndex.verifyVleSyncStatus, SessionDataIndex.exportToCSV);
        } else {
            alert(SessionDataIndex.i18n.cannotDeleteMessage);
        }
        return false;
    },
    
    verifyVleSyncStatus : function() {
        $.get(Routing.generate("session_data_verify_vle_sync_status"), { "ids" : SessionDataIndex.selectedIds }, function(data) {
            if (data == "true") {
                ConfirmSessionDataNotSentToVle.open(SessionDataIndex.deleteSessionData);
            } else if (data == "false") {
                SessionDataIndex.deleteSessionData();
            } else {
                alert(SessionDataIndex.i18n.vleSyncStatusErrorMessage);
            }
        });
    },
    
    deleteSessionData : function() {
        window.location.href = Routing.generate("session_data_delete", { "ids[]" : SessionDataIndex.selectedIds });
    }
}

$(SessionDataIndex.init);