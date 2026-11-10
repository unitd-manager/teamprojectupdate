$(function(){
    $('a.editForWorkOrder').livequery('click', projectWorkOrder.workOrderEditInProject);

    $("a.addMultipleWOItem").livequery('click', function (e){
        var title = "Add Line Item";
        var project_id = $(this).attr('project_id');
        var sub_con_work_order_id = $(this).attr('sub_con_work_order_id');
        var url = 'index.php?widget=enggCrm_projectWorkOrder&_spAction=addMultipleWOItem'
                + '&showHTML=0&project_id=' + project_id + '&sub_con_work_order_id=' + sub_con_work_order_id;
        var exp = {
            url: url
           ,callbackOnSuccess: function(){
                Util.closeAllDialogs();
                //Util.alert('Updated successfully..');
                var mgsalert = 'Line items created successfully!';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 5000,
                });
                //window.location.reload(true);
                projectWorkOrder.reloadWOPortal(project_id);
            }
        };

        Util.openFormInDialog.call(this, 'addMultipleWOItemForm', title, 700, 500, exp);
    });

    $("#addMultipleWOItemForm a.addWORow").livequery('click', function (e){
        var project_id = $(this).attr('project_id');
        var url = 'index.php?widget=enggCrm_projectWorkOrder&_spAction=addWOLineItemRecord'
                + '&showHTML=0&project_id=' + project_id;

        $.get(url, '' ,function(html){
            $('#addMultipleWOItemForm tr:last').after(html);
        });
    });

    $('#addWOProject').livequery('click', function(){
        var project_id = $("#record_id").val();
        msg = "Do you like to Add Work Order?";

        if (!confirm(msg)){
            return false;
        }
        else{
            Util.showProgressInd();
            var project_id = $(this).attr('project_id');
            var url = 'index.php?widget=enggCrm_projectWorkOrder&_spAction=addWOFormSubmit&showHTML=0&id=' + project_id;
            $.get(url, {project_id: project_id}, function(html){
                //alert('Quote Record Created Successfully');
                var mgsalert = 'Work order record created successfully!';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 5000,
                });
                //$('#addQuoteProject').hide();
                //window.location.reload(true);
                projectWorkOrder.reloadWOPortal(project_id);
            });
            //Util.hideProgressInd();
        }
    });

    $("#addMultipleWOItemForm .lineItemQuantity").livequery('change', function (e){
        var quantity     = $(this).val();
        var amountObj    = $(this).closest('tr').find('.lineItemUnitPrice');
        var amount       = amountObj.val();
        var totalCostObj = $(this).closest('tr').find('.lineItemAmount');

        if (quantity > 0 && amount > 0) {
            var total_cost = quantity * amount;
            var total_cost_formatted = parseFloat(total_cost).toFixed(3);
            totalCostObj.val(total_cost_formatted);
        }

        if (quantity == "" && amount > 0) {
            var total_cost = amount;
            var total_cost_formatted = parseFloat(total_cost).toFixed(3);
            totalCostObj.val(total_cost_formatted);
        }
    });

    $("#addMultipleWOItemForm .lineItemUnitPrice").livequery('change', function (e){
        var amount       = $(this).val();
        var quantityObj  = $(this).closest('tr').find('.lineItemQuantity');
        var quantity     = quantityObj.val();
        var totalCostObj = $(this).closest('tr').find('.lineItemAmount');

        if (quantity > 0 && amount > 0) {
            var total_cost = quantity * amount;
            var total_cost_formatted = parseFloat(total_cost).toFixed(3);
            totalCostObj.val(total_cost_formatted);
        }

        if (quantity == "" && amount > 0) {
            var total_cost = amount;
            var total_cost_formatted = parseFloat(total_cost).toFixed(3);
            totalCostObj.val(total_cost_formatted);
        }

    });

    $('.m-enggCrm_project a.deleteWOLineItem').livequery('click', function (e){
        var project_id = $("#record_id").val();

        msg = "Do you like to delete the Work order Line Item?";
        if (!confirm(msg)){
            return false;
        }
        else{
            Util.showProgressInd();
            var work_order_line_items_id = $(this).attr('work_order_line_items_id');
            var url = 'index.php?widget=enggCrm_projectWorkOrder&_spAction=deleteWOLineItem&showHTML=0&work_order_line_items_id=' + work_order_line_items_id;
            $.get(url, {work_order_line_items_id: work_order_line_items_id}, function(html){
                Util.hideProgressInd();
                //alert ('Add Line Item Deleted Succesfully');
                var mgsalert = 'Line item deleted succesfully!';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 5000,
                });
                projectWorkOrder.reloadWOPortal(project_id);
            });
        }
    });

    $("a.editForWOLineItem").livequery('click', function (e){
        var title = "Edit Display";
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
                projectWorkOrder.reloadWOPortal(project_id);
            }
        }

        Util.openFormInDialog.call(this, 'editForWOLineItem', title, 600, 350, expObj);
    });
});

var projectWorkOrder = {
    workOrderEditInProject: function(e){
        var title = "Edit Work Order Display";
        var project_id = $('#record_id').val();

        e.preventDefault();
        var expObj = {
            validate: true,
            callbackOnSuccess: function(){
                Util.closeAllDialogs();
                //Util.alert('Updated successfully..');
                var mgsalert = 'Updated work order successfully!';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 5000,
                });
                //window.location.reload(true);
                projectWorkOrder.reloadWOPortal(project_id);
            }
        }
        Util.openFormInDialog.call(this, 'editForQuote', title, 900, 500, expObj);
    },

    reloadWOPortal: function(project_id){
        var url = 'index.php?widget=enggCrm_projectWorkOrder&_spAction=workOrderListView&showHTML=0';
        Util.showProgressInd();
        $.get(url, {project_id: project_id}, function(html){
            Util.hideProgressInd();
            $('#workOrderLinkPortal').html(html);
        });
    },
}