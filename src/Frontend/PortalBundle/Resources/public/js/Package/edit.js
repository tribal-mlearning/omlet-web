PackageEdit = {
    packagePredefinedMetadata: ["courseCode"],
    filePredefinedMetadata: ["deviceHeight", "deviceMinHeight", "deviceMaxHeight", "deviceWidth", "deviceMinWidth", "deviceMaxWidth", "deviceOS", "deviceOSMinVersion", "deviceOSMaxVersion"],
    regex: new RegExp("(.+)(=)(.+)"),
    
    init: function() {       
        PackageEdit.initializePublishStartDatetimePicker();
        PackageEdit.showOrHidePublishedFrom();
        PackageEdit.addPackageTagBehavior();
        PackageEdit.addFileDeleteBehavior();
        PackageEdit.addFileTagBehavior();
        PackageEdit.initializeCourseCategoriesTree();
        $('#add-file').click(PackageEdit.addFile);
        $("#package_published").change(PackageEdit.publishClicked);
    },
    
    initializePublishStartDatetimePicker: function() {
        $("#package_publishStart").datetimepicker({
            dateFormat: PackageEdit.i18n.format.date_format,
            timeFormat: PackageEdit.i18n.format.time_format
        });     
    },
    
    publishClicked: function() {
        PackageEdit.showOrHidePublishedFrom();
        
        if ($("#package_published").attr("checked")) {
            if ($.trim($("#package_publishStart").val()) == "") 
                $("#package_publishStart").datetimepicker('setDate', (new Date()));
        } else {
            $("#package_publishStart").val("");
        }
    },
    
    showOrHidePublishedFrom: function() {
        if ($("#package_published").attr("checked")) $("#publishStart_container").show();
        else $("#publishStart_container").hide();
    },
    
    addFile: function() {
        var widget = "<td width='40%'><input type='file' name='package[files][" + PackageEdit.fileCount + "][fileContent]' /></td>";

        var version = "<td width='10%'><input type='text' class='file_version' name='package[files][" + PackageEdit.fileCount + "][version]' /></td>";

        var metadata = "<td width='45%'><ul class='package_file_tag' file_id='" + PackageEdit.fileCount + "' metadata_id='0'></ul></td>";

        var deleteButton = "<td width='5%'><a class='btn-delete delete-button' href='javascript:;' title='" + PackageEdit.i18n.label.delete_file + "'>x</a></td>";

        var row = $("<tr></tr>").append(widget).append(version).append(metadata).append(deleteButton);

        $("#files-table").append(row);

        PackageEdit.fileCount++;

        PackageEdit.addFileDeleteBehavior();
        PackageEdit.addFileTagBehavior();

        return false;
    },

    addFileDeleteBehavior: function() {
        $(".files .delete-button").click(function() {
            if ($(this).attr("error_row")) $("#" + $(this).attr("error_row")).remove();

            $(this).closest("tr").remove();
        });
    },

    addFileTagBehavior: function() {
        $(".package_file_tag").tagit({
            tagSource: PackageEdit.filePredefinedMetadata, 
            triggerKeys: ["enter", "tab"],
            minLength: 0,
            tagsChanged: function(tagValue, action, element) {
                if (action == "added") {
                    if (PackageEdit.regex.test(tagValue)) {
                        var values = PackageEdit.regex.exec(tagValue);
                        var metadataName = $.trim(values[1]);
                        var metadataValue = $.trim(values[3]);

                        PackageEdit.insertFileMetadataHiddenFieldsIntoLiElement(metadataName, metadataValue, element);

                        PackageEdit.highlightFilePredefinedMetadatada(metadataName, element);
                    } else  {
                        $(element).remove();
                    }
                }
            }
        });
    },    

    insertFileMetadataHiddenFieldsIntoLiElement: function(metadataName, metadataValue, element) {
        var file = $(element).parent().attr("file_id");

        var metadataId = parseInt($(element).parent().attr("metadata_id"));

        var name = "<input type='hidden' name='package[files][$file_id][metadata][$metadata_id][name]' value='$value' />"
            .replace("$file_id", file)
            .replace("$metadata_id", metadataId)
            .replace("$value", metadataName);

        var value = "<input type='hidden' name='package[files][$file_id][metadata][$metadata_id][value]' value='$value' />"
            .replace("$file_id", file)
            .replace("$metadata_id", metadataId)
            .replace("$value", metadataValue);

        $(element).parent().attr("metadata_id", metadataId + 1);

        $(element)
            .append(name)
            .append(value);
    },
    
    highlightFilePredefinedMetadatada: function(metadata_name, element) {
        if ($.inArray(metadata_name, PackageEdit.filePredefinedMetadata) != -1)  
            $(element).addClass("highlighted_metadata");
    },
    
    addPackageTagBehavior: function() {
        $(".package_tag").tagit({
            tagSource: PackageEdit.packagePredefinedMetadata, 
            triggerKeys: ["enter", "tab"],
            minLength: 0,
            tagsChanged: function(tagValue, action, element) {
                if (action == "added") {
                    if (PackageEdit.regex.test(tagValue)) {
                        var values = PackageEdit.regex.exec(tagValue);
                        var metadataName = $.trim(values[1]);
                        var metadataValue = $.trim(values[3]);

                        PackageEdit.insertPackageMetadataHiddenFieldsIntoLiElement(metadataName, metadataValue, element);

                        PackageEdit.highlightPackagePredefinedMetadatada(metadataName, element);
                    } else {
                        $(element).remove();
                    }
                }
            }
        });
    },
    
    insertPackageMetadataHiddenFieldsIntoLiElement: function(metadataName, metadataValue, element) {        
        var name = "<input type='hidden' name='package[metadata][$metadata_id][name]' value='$value' />"
            .replace("$metadata_id", PackageEdit.metadataCount)
            .replace("$value", metadataName);

        var value = "<input type='hidden' name='package[metadata][$metadata_id][value]' value='$value' />"
            .replace("$metadata_id", PackageEdit.metadataCount)
            .replace("$value", metadataValue);

        PackageEdit.metadataCount++;

        $(element)
            .append(name)
            .append(value);
    },
    
    highlightPackagePredefinedMetadatada: function(metadata_name, element) {
        if ($.inArray(metadata_name, PackageEdit.packagePredefinedMetadata) != -1)  
            $(element).addClass("highlighted_metadata");
    },
    
    initializeCourseCategoriesTree: function() {
         $("#course_categories")
            .jstree({
                "core": { 
                    "animation": 100
                },
                "plugins": [ "themes", "json_data", "ui", "checkbox" ],
                "themes": {
                    "dots": false
                },
                "json_data": {
                    "ajax": {
                        "url": Routing.generate("course_category_retrieve")
                    }
                }
            })
            .bind("loaded.jstree", function (event, data) {
                $(this).jstree("open_node", $("#-1"));
                
                $.each(PackageEdit.selectedCategories, function(index, value) {
                    $("#course_categories").jstree("check_node", $("#" + value));
                });                
            });                    
        
        $("#edit_package_form").submit(PackageEdit.insertCategoriesHiddenInputs);
    },
    
    insertCategoriesHiddenInputs: function() {
        var categoriesCount = 0;
        $(".jstree-leaf").each(function(index, value) {
            if ($("#course_categories").jstree("is_checked", value))
                $("#edit_package_form").append("<input value='" + $(value).attr("id") + "' type='hidden' name='package[categories][" + categoriesCount++ + "]' />");
        });
    }
}

$(PackageEdit.init);