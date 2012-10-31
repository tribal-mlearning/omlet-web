PackageNew = {
    packagePredefinedMetadata: ["courseCode"],
    regex: new RegExp("(.+)(=)(.+)"),
    
    init: function() {
        PackageNew.addPackageTagBehavior();
        PackageNew.initializeCourseCategoriesTree();        
    },
    
    insertCategoriesHiddenInputs: function() {            
        var categoriesCount = 0;
        $(".jstree-leaf").each(function(index, value) {
            if ($("#course_categories").jstree("is_checked", value))
                $("#new_package_form").append("<input value='" + $(value).attr("id") + "' type='hidden' name='package[categories][" + categoriesCount++ + "]' />");
        });
    },
    
    addPackageTagBehavior: function() {
        $(".package_tag").tagit({
            tagSource: PackageNew.packagePredefinedMetadata, 
            triggerKeys: ["enter", "tab"],
            minLength: 0,
            tagsChanged: function(tagValue, action, element) {
                if (action == "added") {
                    if (PackageNew.regex.test(tagValue)) {
                        var values = PackageNew.regex.exec(tagValue);
                        var metadataName = $.trim(values[1]);
                        var metadataValue = $.trim(values[3]);

                        PackageNew.insertPackageMetadataHiddenFieldsIntoLiElement(metadataName, metadataValue, element);

                        PackageNew.highlightPackagePredefinedMetadatada(metadataName, element);
                    } else {
                        $(element).remove();
                    }
                }
            }
        });
    },
    
    insertPackageMetadataHiddenFieldsIntoLiElement: function(metadataName, metadataValue, element) {        
        var name = "<input type='hidden' name='package[metadata][$metadata_id][name]' value='$value' />"
            .replace("$metadata_id", PackageNew.metadataCount)
            .replace("$value", metadataName);

        var value = "<input type='hidden' name='package[metadata][$metadata_id][value]' value='$value' />"
            .replace("$metadata_id", PackageNew.metadataCount)
            .replace("$value", metadataValue);

        PackageNew.metadataCount++;

        $(element)
            .append(name)
            .append(value);
    },
    
    highlightPackagePredefinedMetadatada: function(metadata_name, element) {
        if ($.inArray(metadata_name, PackageNew.packagePredefinedMetadata) != -1)  
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
                
                $.each(PackageNew.selectedCategories, function(index, value) {
                    $("#course_categories").jstree("check_node", $("#" + value));
                });                
            });
        
        $("#new_package_form").submit(PackageNew.insertCategoriesHiddenInputs);
    }
}

$(PackageNew.init);