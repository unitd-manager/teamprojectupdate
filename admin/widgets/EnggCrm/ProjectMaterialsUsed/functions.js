$(function(){
    /* Adding 5 Materials in New window */
    $("a.addMultipleMaterials").livequery('click', function (e){
        var title = "Add Materials";
        var project_id = $(this).attr('project_id');
        var url = 'index.php?widget=enggCrm_projectMaterialsUsed&_spAction=addMultipleMaterials'
                + '&showHTML=0&project_id=' + project_id;
        var exp = {
            url: url
           ,callbackOnSuccess: function(){
                Util.closeAllDialogs();
                //Util.alert('Updated successfully..');
                var mgsalert = 'Materials added successfully!';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 5000,
                });
                //window.location.reload(true);
                projectMaterialsUsed.reloadMaterialUsedPortal(project_id);
            }
        };
        Util.openFormInDialog.call(this, 'addMultipleMaterialsForm', title, 900, 500, exp);
    });

    /* Adding row in new material */
    $("a.addMaterialRow").livequery('click', function (e){
        var url = 'index.php?widget=enggCrm_projectMaterialsUsed&_spAction=addMaterialRecord'
                + '&showHTML=0';

        $.get(url, '' ,function(html){
            $('#addMultipleMaterialsForm tr:last').after(html);
        });
    });

    $('.cancelMaterial').livequery('click', function (e){
        msg = "Are you sure you want to cancel the entry? You cannot undo this action!";
        if (!confirm(msg)){
            return false;
        } else {
            Util.showProgressInd();
            var url = 'index.php?widget=enggCrm_projectMaterialsUsed&_spAction=cancelMaterial&showHTML=0';
            var project_materials_id = $(this).attr('project_materials_id');
            $.get(url,{project_materials_id: project_materials_id}, function(html){
                alert ('Material Cancelled Succesfully');
                Util.hideProgressInd();
                window.location.reload(true);
            });
        }
    });

    $('.virescoFactory').livequery('click', function (e){
        Util.showProgressInd();
        var url = 'index.php?widget=enggCrm_projectMaterialsUsed&_spAction=updateVirescoFactory&showHTML=0';
        var project_materials_id = $(this).attr('project_materials_id');
        var checked    = $(this).attr('checked') ? 'checked' : '';
        var checkedVal = checked == 'checked' ? 1 : 0;
        $.get(url,{project_materials_id: project_materials_id, checkedVal:checkedVal}, function(html){
            Util.hideProgressInd();
        });
    });

    $(".addMultipleMaterialsForm input.materialTitleFull").livequery(projectMaterialsUsed.poProductTitle);
    $("#editForMaterialUsed input[name=title]").livequery(projectMaterialsUsed.poProductTitleEdit);

    $(".addMultipleMaterialsForm input.materialQuantity").livequery('change', function (e){
        var qty    = $(this).val();
        var parent = $(this).closest('tr');
        var stock  = $("input.materialStock", parent).val();

        if(parseFloat(qty) > parseFloat(stock)) {
            Util.alert("Please enter quantity less than or equal to "+stock);
            $("input.materialQuantity", parent).val(0);
        }
    });

    $("#editForMaterialUsed input[name=quantity]").livequery('change', function (e){
        var qty    = $(this).val();
        var stock  = $("#editForMaterialUsed input.materialStock").val();

        if(parseFloat(qty) > parseFloat(stock)) {
            Util.alert("Please enter quantity less than or equal to "+stock);
            $("#editForMaterialUsed input[name=quantity]").val(0);
        }
    });

    /* MATERIAL USED EDIT */
    $("a.editForMaterialUsed").livequery('click', function (e){
        var title = "Edit Material Used";
        var project_id = $("#record_id").val();

        e.preventDefault();
        var expObj = {
            validate: true,
            callbackOnSuccess: function(){
                Util.closeAllDialogs();
                //Util.alert('Updated successfully..');
                var mgsalert = 'Updated successfully!';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 5000,
                });
                //window.location.reload(true);
                projectMaterialsUsed.reloadMaterialUsedPortal(project_id);
            }
        }

        Util.openFormInDialog.call(this, 'editForMaterialUsed', title, 600, 350, expObj);
    });

    $("a.returnMaterial").livequery('click', function (e){
        var title = "Return To Stock";
        var project_id = $("#record_id").val();

        e.preventDefault();
        var expObj = {
            validate: true,
            callbackOnSuccess: function(){
                Util.closeAllDialogs();
                //Util.alert('Updated successfully..');
                var mgsalert = 'Updated successfully!';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 5000,
                });
                //window.location.reload(true);
                projectMaterialsUsed.reloadMaterialUsedPortal(project_id);
            }
        }

        Util.openFormInDialog.call(this, 'returnMaterialUsed', title, 350, 300, expObj);
    });

    $('.viewAllReturnedMaterialHistory').livequery('click', function (){
        Util.showProgressInd();
        var project_materials_id = $(this).attr('project_materials_id');

        if(project_materials_id != "") {
            var url = "index.php?widget=enggCrm_projectMaterialsUsed&_spAction=returnedMaterialHistory&project_materials_id="+project_materials_id+"&showHTML=0";
            var exp = {
                url: url
            };

            Util.openDialogForLink('Materials Returned History', 550, 300, 0, exp);
        } else {
            Util.hideProgressInd();
            Util.alert("There is no history records found!");
        }
    });

    $(".creationModificationMU").livequery('click', function (e){
        var project_materials_id = $(this).attr('project_materials_id');

        Util.showProgressInd();

        var url = "index.php?widget=enggCrm_projectMaterialsUsed&_spAction=creationModificationMU&project_materials_id="+project_materials_id+"&showHTML=0";
        var exp = {
            url: url
        };

        Util.openDialogForLink('Updated By',  500, 200, 0, exp);
    });

    $(".addMultipleMaterialsForm input.materialTitleFull").livequery('change', function(){
        var parent     = $(this).closest('tr');
        var product_id = $("input.product_id_hidden", parent).val();
        
        if(product_id == "") {
            $("input.materialTitleFull", parent).val("");
            $("td.productType", parent).html("");
            $("td.productStock", parent).html("");
            $("input.materialStock", parent).val(0);
        }
    });

    $("#editForMaterialUsed input[name=title]").livequery('change', function(){
        var product_id = $("#editForMaterialUsed input[name='product_id']").val();

        if(product_id == "") {
            $("#editForMaterialUsed input[name='title']").val("");
            $("#editForMaterialUsed input[name='product_id']").val("");
            $("#editForMaterialUsed input[name='materialStock']").val(0);
            $("#editForMaterialUsed .row_product_type .txt span.value").html("");
            $("#editForMaterialUsed .row_stock .txt span.value").html("");
        }
    });
});

var projectMaterialsUsed = {
    poProductTitle: function() {
        var titleObj = this;
        $(titleObj).autocomplete({
            source: function(request, response) {
                $.ajax({
                  url: 'index.php?widget=enggCrm_projectMaterialsUsed&_spAction=searchProductTitle&showHTML=0',
                  dataType: "json",
                  data: request,                    
                  success: function (data) {
                    if (data.length == 0) {
                        var parent = titleObj.closest('tr');
                        $("input[name='product_id[]']", parent).val("");                        
                        response("");
                    } else {
                      response(data);
                    }

                  }
                });
            },

            minLength : 1,
            electFirst: true,
            autoFocus: true,
            select: function(event, ui) {
                var selectedObj  = ui.item;
                var product_id   = selectedObj.id;
                var category     = selectedObj.category;
                var product_type = selectedObj.product_type;
                var stock        = selectedObj.stock;
                var parent       = titleObj.closest('tr');
                
                if(stock == "" || stock == null || stock == "NaN") {
                    stock = parseFloat(0);
                }

                $("input[name='product_id[]']", parent).val(product_id);
                $("td.productType", parent).html(product_type);
                $("td.productStock", parent).html(stock);
                $("input.materialStock", parent).val(stock);
            }
        });
    },

    poProductTitleEdit: function() {
        var titleObj = this;
        $(titleObj).autocomplete({
            source: function(request, response) {
                $.ajax({
                  url: 'index.php?widget=enggCrm_projectMaterialsUsed&_spAction=searchProductTitle&showHTML=0',
                  dataType: "json",
                  data: request,                    
                  success: function (data) {
                    if (data.length == 0) {
                        $("input[name='product_id']").val("");                       
                        response("");
                    } else {
                      response(data);
                    }

                  }
                });
            },

            minLength : 1,
            electFirst: true,
            autoFocus: true,
            select: function(event, ui) {
                var selectedObj  = ui.item;
                var product_id   = selectedObj.id;
                var product_type = selectedObj.product_type;
                var stock        = selectedObj.stock;

                if(stock == "" || stock == null || stock == "NaN") {
                    stock = parseFloat(0);
                }

                $("input[name='product_id']").val(product_id);
                $("input[name='materialStock']").val(stock);
                $(".row_product_type .txt span.value").html(product_type);
                $(".row_stock .txt span.value").html(stock);
            }
        });
    },

    reloadMaterialUsedPortal: function(project_id){
        var url = 'index.php?widget=enggCrm_projectMaterialsUsed&_spAction=projectMaterialPortal&showHTML=0';
        Util.showProgressInd();
        $.get(url, {project_id: project_id}, function(html){
            Util.hideProgressInd();
            $('#materialLinkPortal').html(html);
        });
    },
}