var deliveryOrderChecked = [];
var matReqProductChecked = [];
$(function(){
    $(".creationModificationPo").livequery('click', function (e){
        var po_product_id = $(this).attr('po_product_id');

        Util.showProgressInd();

        var url = "index.php?widget=enggCrm_projectPurchaseOrder&_spAction=creationModificationPo&po_product_id="+po_product_id+"&showHTML=0";
        var exp = {
            url: url
        };

        Util.openDialogForLink('Updated By',  500, 200, 0, exp);
    });

    $(".creationModificationMR").livequery('click', function (e){
        var materials_request_line_items_id = $(this).attr('materials_request_line_items_id');

        Util.showProgressInd();

        var url = "index.php?widget=enggCrm_projectPurchaseOrder&_spAction=creationModificationMR&materials_request_line_items_id="+materials_request_line_items_id+"&showHTML=0";
        var exp = {
            url: url
        };

        Util.openDialogForLink('Updated By',  500, 200, 0, exp);
    });

    $("#materialPurchasedTransfer a.addTransferProjectRow").livequery('click', function (e){
        var url = 'index.php?widget=enggCrm_projectPurchaseOrder&_spAction=addTransferProjectRowRecord'
                + '&showHTML=0';

        $.get(url, '' ,function(html){
            $('.materialPurchasedTransfer tr:last').after(html);
        });
    });

    $("#editForPoLineItem [name='item_title']").livequery(projectPurchaseOrder.poProductTitleEdit);
        
    /* Adding 5 Purchase Order rows in New window */
    $("a.addMultiplePurchaseOrder").livequery('click', function (e){
        var title = "Add Purchase Order";
        var project_id = $(this).attr('project_id');
        var url = 'index.php?widget=enggCrm_projectPurchaseOrder&_spAction=addMultiplePurchaseOrder'
                + '&showHTML=0&project_id=' + project_id;
        var exp = {
            url: url
           ,callbackOnSuccess: function(){
                Util.closeAllDialogs();
                //Util.alert('Updated successfully..');
                var mgsalert = 'Purchase order created successfully!';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 5000,
                });
                //window.location.reload(true);
                projectPurchaseOrder.reloadPoLineItem(project_id);
            }
        };
        Util.openFormInDialog.call(this, 'addMultiplePurchaseOrderForm', title, 900, 500, exp);
    });

    /* Adding 5 Purchase Order rows in New window */
    $("a.addMaterialsRequest").livequery('click', function (e){
        var title = "Add Materials Request";
        var project_id = $(this).attr('project_id');
        var url = 'index.php?widget=enggCrm_projectPurchaseOrder&_spAction=addMultipleMaterialRequest'
                + '&showHTML=0&project_id=' + project_id;
        var exp = {
            url: url
           ,callbackOnSuccess: function(){
                Util.closeAllDialogs();
                var mgsalert = 'Materials request created successfully!';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 5000,
                });
                projectPurchaseOrder.reloadMaterialRequestPortal(project_id);
            }
        };
        Util.openFormInDialog.call(this, 'addMultipleMaterialRequestForm', title, 900, 500, exp);
    });

    $('#addMultipleMaterialRequestForm .addNewSupplierPopup').livequery('click', function (e){
        var title = "Add Supplier";
        e.preventDefault();

        var expObj = {
            validate: true
           ,callbackOnSuccess: function(){
                var supplierObj = $("#addMultipleMaterialRequestForm .poSupplier");
                var url = $('#scopeRootAlias').val() + 'index.php?widget=enggCrm_projectPurchaseOrder&_spAction=supplierByJSON&showHTML=0';
                $.ajax({
                    type: "GET",
                    url: url,
                    async: false,
                    dataType: 'json',
                    success: function(json){
                        supplierObj.empty();
                        $.each(json, function() {
                            supplierObj.append(new Option(this.caption, this.value));
                        });
                    }
                });

                $('#dialog1').dialog('close');
                $('#dialog1').dialog('destroy');
                $('#dialog1').remove();
            }
        }

        Util.openFormInDialog.call(this, 'AddNewSupplierPortalForm', title, 530, 532, expObj);
    });

    $('#editMultipleMaterialRequestForm .addNewSupplierPopup').livequery('click', function (e){
        var title = "Add Supplier";
        e.preventDefault();

        var expObj = {
            validate: true
           ,callbackOnSuccess: function(){
                var supplierObj = $("#editMultipleMaterialRequestForm .poSupplier");
                var url = $('#scopeRootAlias').val() + 'index.php?widget=enggCrm_projectPurchaseOrder&_spAction=supplierByJSON&showHTML=0';
                $.ajax({
                    type: "GET",
                    url: url,
                    async: false,
                    dataType: 'json',
                    success: function(json){
                        supplierObj.empty();
                        $.each(json, function() {
                            supplierObj.append(new Option(this.caption, this.value));
                        });
                    }
                });

                $('#dialog1').dialog('close');
                $('#dialog1').dialog('destroy');
                $('#dialog1').remove();
            }
        }

        Util.openFormInDialog.call(this, 'AddNewSupplierPortalForm', title, 530, 532, expObj);
    });

    /* Adding row in new Purchase Order */
    $("#addMultipleMaterialRequestForm a.addSingleMRRow").livequery('click', function (e){
        var url = 'index.php?widget=enggCrm_projectPurchaseOrder&_spAction=addSingleMaterialsRequestRecord'
                + '&showHTML=0';

        $.get(url, '' ,function(html){
            $('#addMultipleMaterialRequestForm tr:last').after(html);
        });
    });

    /* Adding row in new Purchase Order */
    $("#editMultipleMaterialRequestForm a.addSingleMRRow").livequery('click', function (e){
        var url = 'index.php?widget=enggCrm_projectPurchaseOrder&_spAction=addSingleMaterialsRequestRecord'
                + '&showHTML=0';

        $.get(url, '' ,function(html){
            $('#editMultipleMaterialRequestForm tr:last').after(html);
        });
    });

    $("#addMultipleMaterialRequestForm .poQuantity").livequery('change', function (e){
        var quantity = $(this).val();
        var amountObj = $(this).closest('tr').find('.poAmount');
        var amount = amountObj.val();
        var totalCostObj = $(this).closest('tr').find('.totalCost');

        if (quantity > 0 && amount > 0) {
            var total_cost = quantity * amount;
            var total_cost_formatted = parseFloat(total_cost).toFixed(3);
            totalCostObj.html(total_cost_formatted);
        }
    });

    $("#addMultipleMaterialRequestForm .poAmount").livequery('change', function (e){
        var amount = $(this).val();
        var quantityObj = $(this).closest('tr').find('.poQuantity');
        var quantity = quantityObj.val();
        var totalCostObj = $(this).closest('tr').find('.totalCost');

        if (quantity > 0 && amount > 0) {
            var total_cost = quantity * amount;
            var total_cost_formatted = parseFloat(total_cost).toFixed(3);
            totalCostObj.html(total_cost_formatted);
        }
    });

    $("#editMultipleMaterialRequestForm .poQuantity").livequery('change', function (e){
        var quantity = $(this).val();
        var amountObj = $(this).closest('tr').find('.poAmount');
        var amount = amountObj.val();
        var totalCostObj = $(this).closest('tr').find('.totalCost');

        if (quantity > 0 && amount > 0) {
            var total_cost = quantity * amount;
            var total_cost_formatted = parseFloat(total_cost).toFixed(3);
            totalCostObj.html(total_cost_formatted);
        }
    });

    $("#editMultipleMaterialRequestForm .poAmount").livequery('change', function (e){
        var amount = $(this).val();
        var quantityObj = $(this).closest('tr').find('.poQuantity');
        var quantity = quantityObj.val();
        var totalCostObj = $(this).closest('tr').find('.totalCost');

        if (quantity > 0 && amount > 0) {
            var total_cost = quantity * amount;
            var total_cost_formatted = parseFloat(total_cost).toFixed(3);
            totalCostObj.html(total_cost_formatted);
        }
    });

    /* Clear data in Purchase Order */
    $("a.clearMR").livequery('click', function (e){
        var supplierObj    = $(this).closest('tr').find('.poSupplier');
        var quantityObj    = $(this).closest('tr').find('.poQuantity');
        var unitObj        = $(this).closest('tr').find('.poUnit');
        var brandObj       = $(this).closest('tr').find('.poBrand');
        var amountObj      = $(this).closest('tr').find('.poAmount');
        var totalCostObj   = $(this).closest('tr').find('.totalCost');
        var descriptionObj = $(this).closest('tr').find('.poDescription');

        supplierObj.val('');
        quantityObj.val('');
        brandObj.val('');
        unitObj.val('');
        amountObj.val('');
        totalCostObj.html('');
        descriptionObj.val('');
    });

    /* Purchase Order Edit Portal */
    $("a.editForMaterialsRequest").livequery('click', function (e){
        e.preventDefault();
        var project_id = $("#record_id").val();
        var exp = {
            callbackOnSuccess: function(){
                var msg = 'Updated materials request successfully';
                Util.alert(msg, function(){
                    Util.closeAllDialogs();
                    projectPurchaseOrder.reloadMaterialRequestPortal(project_id);
                });
            }
        }

        Util.openFormInDialog.call(this, 'editForMaterialsRequestForm', 'Edit Materials Request', 900, 500, exp);
    });

    /* Adding 5 Purchase Order rows in New window */
    $("a.editMRMultipleLineItem").livequery('click', function (e){
        var title = "Edit Materials Request";
        var materials_request_id = $(this).attr('materials_request_id');
        var project_id = $("#record_id").val();
        var url = 'index.php?widget=enggCrm_projectPurchaseOrder&_spAction=editMRMultipleLineItem'
                + '&showHTML=0&materials_request_id=' + materials_request_id+'&project_id=' + project_id;
        var exp = {
            url: url
           ,callbackOnSuccess: function(){
                Util.closeAllDialogs();
                //Util.alert('Updated successfully..');
                var mgsalert = 'Materials request Updated successfully!';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 5000,
                });
                //window.location.reload(true);
                projectPurchaseOrder.reloadPoLineItem(project_id);
            }
        };
        Util.openFormInDialog.call(this, 'editMultipleMaterialRequestForm', title, 900, 500, exp);
    });

    $("#addMultiplePurchaseOrderForm .poQuantity").livequery('change', function (e){
        var quantity = $(this).val();
        var amountObj = $(this).closest('tr').find('.poAmount');
        var amount = amountObj.val();
        var totalCostObj = $(this).closest('tr').find('.totalCost');

        if (quantity > 0 && amount > 0) {
            var total_cost = quantity * amount;
            var total_cost_formatted = parseFloat(total_cost).toFixed(3);
            totalCostObj.html(total_cost_formatted);
        }
    });

    $("#addMultiplePurchaseOrderForm .poAmount").livequery('change', function (e){
        var amount = $(this).val();
        var quantityObj = $(this).closest('tr').find('.poQuantity');
        var quantity = quantityObj.val();
        var totalCostObj = $(this).closest('tr').find('.totalCost');

        if (quantity > 0 && amount > 0) {
            var total_cost = quantity * amount;
            var total_cost_formatted = parseFloat(total_cost).toFixed(3);
            totalCostObj.html(total_cost_formatted);
        }
    });

    $("#editPoMultipleLineItem .poQuantity").livequery('change', function (e){
        var quantity = $(this).val();
        var amountObj = $(this).closest('tr').find('.poAmount');
        var amount = amountObj.val();
        var totalCostObj = $(this).closest('tr').find('.totalCost');

        if (quantity > 0 && amount > 0) {
            var total_cost = quantity * amount;
            var total_cost_formatted = parseFloat(total_cost).toFixed(3);
            totalCostObj.html(total_cost_formatted);
        }
    });

    $("#editPoMultipleLineItem .poAmount").livequery('change', function (e){
        var amount = $(this).val();
        var quantityObj = $(this).closest('tr').find('.poQuantity');
        var quantity = quantityObj.val();
        var totalCostObj = $(this).closest('tr').find('.totalCost');

        if (quantity > 0 && amount > 0) {
            var total_cost = quantity * amount;
            var total_cost_formatted = parseFloat(total_cost).toFixed(3);
            totalCostObj.html(total_cost_formatted);
        }
    });

    /* Adding row in new Purchase Order */
    $("#addMultiplePurchaseOrderForm a.addSinglePoRow").livequery('click', function (e){
        var url = 'index.php?widget=enggCrm_projectPurchaseOrder&_spAction=addSinglePurchaseOrderRecord'
                + '&showHTML=0';

        $.get(url, '' ,function(html){
            $('#addMultiplePurchaseOrderForm tr:last').after(html);
        });
    });

    $("#editPoMultipleLineItem a.addSinglePoRow").livequery('click', function (e){
        var url = 'index.php?widget=enggCrm_projectPurchaseOrder&_spAction=addSinglePurchaseOrderRecord'
                + '&showHTML=0';

        $.get(url, '' ,function(html){
            $('#editPoMultipleLineItem tr:last').after(html);
        });
    });

    /* Clear data in Purchase Order */
    $("a.clearPo").livequery('click', function (e){
        var supplierObj = $(this).closest('tr').find('.poSupplier');
        var supplierId = supplierObj.val();
        var titleObj = $(this).closest('tr').find('.poTitle');
        var quantityObj = $(this).closest('tr').find('.poQuantity');
        var unitObj = $(this).closest('tr').find('.poUnit');
        var amountObj = $(this).closest('tr').find('.poAmount');
        var descriptionObj = $(this).closest('tr').find('.poDescription');
        var totalCostObj = $(this).closest('tr').find('.totalCost');

        supplierObj.val('');
        titleObj.val('');
        quantityObj.val('');
        unitObj.val('');
        amountObj.val('');
        totalCostObj.html('');
        descriptionObj.val('');
    });

    /* Purchase Order Edit Portal */
    $("a.editForPo").livequery('click', function (e){
        e.preventDefault();
        var project_id = $("#record_id").val();
        var exp = {
            callbackOnSuccess: function(){
                var msg = 'Updated purchase order successfully';
                Util.alert(msg, function(){
                    Util.closeAllDialogs();
                    //window.location.reload(true);
                    projectPurchaseOrder.reloadPoLineItem(project_id);
                });
            }
        }
        Util.openFormInDialog.call(this, 'editForPoForm', 'Edit Purchase Order', 900, 500, exp);
    });

    $(".transferPo").livequery('click', function (e){
        var po_product_id = $(this).attr('po_product_id');
        var product_id = $(this).attr('product_id');
        var project_id = $('#record_id').val();

        Util.showProgressInd();

        var url = "index.php?widget=enggCrm_projectPurchaseOrder&_spAction=transferToOtherPO&po_product_id="+po_product_id+"&product_id="+product_id+"&project_id="+project_id+"&showHTML=0";
        var exp = {
            url: url
        };

        Util.openDialogForLink('Transfer To Other Project',  780, 400, 0, exp);
    });

    $('#po_productTable td.quantity input[name=quantity]').livequery('change', function (e){
        var qty           = $(this).val();
        var parent        = $(this).closest('tr');
        var project_id    = $("#record_id").val();
        var total_qty     = $("input[name='total_qty']").val();
        var to_project_id = $("select[name='to_project_id']", parent).val();
        var product_id    = $("input[name='product_id']").val();

        var qtyInput = parseInt(0);
        $('#po_productTable td.quantity input[name=quantity]').each(function(){
            var enteredQty = $(this).val();
            qtyInput += parseInt(enteredQty);
        });

        if(parseFloat(qtyInput) > parseFloat(total_qty)) {
            Util.alert('The quantity should not be more than the total quantity');
            $('input[name=quantity]', parent).val("");
        } else {
            Util.showProgressInd();
            var url = 'index.php?widget=enggCrm_projectPurchaseOrder&_spAction=createStockTransfer&showHTML=0';
            $.get(url, {project_id: project_id, to_project_id:to_project_id, product_id:product_id, qty: qty}, function(html){
                $("input[name='stock_transfer_id']").val(html);
                projectPurchaseOrder.reloadMaterialTransferredPortal(project_id);
                Util.hideProgressInd();
            });
        }
    });

	$("select[name='to_project_id']").livequery('change', function (e){
        var to_project_id = $(this).val();
        var parent     = $(this).closest('tr');
        var project_id = $("#record_id").val();
        var product_id = $("input[name='product_id']").val();

        //var url = 'index.php?widget=enggCrm_projectPurchaseOrder&_spAction=createStockTransfer&showHTML=0';
        //$.get(url, {project_id: project_id, to_project_id:to_project_id, product_id:product_id}, function(html){
            //$("input[name='stock_transfer_id']").val(html);
            if(to_project_id != "") {
                $("input[name='quantity']", parent).removeAttr("disabled");
            }
        //}); 
    });

		$("input[name='project_title']")
    .livequery(projectPurchaseOrder.projectTitle);

    $("input[name='client_name']")
    .livequery(projectPurchaseOrder.clientName);

    $('.cancelPoItem').livequery('click', function (e){
        msg = "Are you sure you want to cancel the entry? You cannot undo this action!";
        if (!confirm(msg)){
            return false;
        } else {
            Util.showProgressInd();
            var url = 'index.php?widget=enggCrm_projectPurchaseOrder&_spAction=cancelPoItem&showHTML=0';
            var po_product_id = $(this).attr('po_product_id');
            $.get(url,{po_product_id: po_product_id}, function(html){
                alert ('Item Cancelled Succesfully');
                Util.hideProgressInd();
                window.location.reload(true);
            });
        }
    });

    /* Add Product */
    $('.addNewProductProductPopup').livequery('click', function (e){
        var title = "Add New Materials / Tools";
        e.preventDefault();

        var expObj = {
            validate: true
           ,callbackOnSuccess: function(){
                /*var msg = 'Product Added Successfully!';
                Util.alert(msg, function(){*/
                    $('#dialog1').dialog('close');
                    $('#dialog1').dialog('destroy');
                    $('#dialog1').remove();
                //});
            }
        }
        Util.openFormInDialog.call(this, 'NewProductPortalForm', title, 530, 332, expObj);
    });

 	$("input[name='product_title[]']").livequery(projectPurchaseOrder.poProductTitle);

    /* EDIT PO LINE ITEM EDIT */
    $("a.editForPoLineItem").livequery('click', function (e){
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
                projectPurchaseOrder.reloadPoLineItem(project_id);
            }
        }

        Util.openFormInDialog.call(this, 'editForPoLineItem', title, 600, 350, expObj);
    });

    $("a.editPoMultipleLineItem").livequery('click', function (e){
        var title      = "Edit PO Line Items";
        var project_id = $("#record_id").val();

        e.preventDefault();
        var expObj = {
            validate: true,
            callbackOnSuccess: function(){
                Util.closeAllDialogs();
                var mgsalert = 'Updated successfully!';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 5000,
                });
                projectPurchaseOrder.reloadPoLineItem(project_id);
            }
        }

        Util.openFormInDialog.call(this, 'editPoMultipleLineItem', title, 900, 500, expObj);
    });

    $('.qtyAllDelivered').livequery('click', function(e){
        if (deliveryOrderChecked.length == 0){
            Util.alert('Please select atleast one product!');
        }else{
            if (!confirm("Do you want to add all qty to stock?")){
                return;
            }
            
            var project_id = $(this).attr('project_id');
            var url = "index.php?widget=enggCrm_projectPurchaseOrder&_spAction=UpdateQtyDelivered";
            Util.showProgressInd();
            $.get(url, {deliveryOrderChecked:deliveryOrderChecked} ,function(html){
                projectPurchaseOrder.reloadPoLineItem(project_id);
                var colPo = $('.click-all-top .uncheck-all').parent().index();
                $('.room-poProduct-table tbody tr').each(function(rowIndex, trObj) {
                    var checkbox = $(trObj).find('td:eq(' + colPo + ') input');
                    checkbox.removeAttr('checked');

                    var is_checked = checkbox.is(':checked');
                    if(is_checked == false){
                        var index = deliveryOrderChecked.indexOf(checkbox.val());
                        deliveryOrderChecked.splice(index, 1);
                    }
                });

                deliveryOrderChecked = [];
                Util.hideProgressInd();

                var mgsalert = 'All Quantity Delivered and Added to The Stock!';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 5000,
                });
                
            });
        }
    });

    $('.deliveryOrderId').livequery('click', function(e){
        var deliveryOrderId  = $(this).val();
        var is_checked   = $(this).is(':checked');
        if(is_checked == true){
            deliveryOrderChecked.push(deliveryOrderId);
        }else{
            var index = deliveryOrderChecked.indexOf(deliveryOrderId);
            deliveryOrderChecked.splice(index, 1);
        }
    });

    $('.deliveryOrder').livequery('click', function(e){
        if (deliveryOrderChecked.length == 0){
            Util.alert('Please select atleast one product!');
        }else{
            if (!confirm("Do you want to create delivery order?")){
                return;
            }
            var project_id = $(this).attr('project_id');
            var url = "index.php?widget=enggCrm_projectDeliveryOrder&_spAction=createDeliveryOrder";
            Util.showProgressInd();
            $.get(url, {deliveryOrderChecked:deliveryOrderChecked, project_id:project_id} ,function(html){
                projectPurchaseOrder.reloadDeliveryOrderLink(project_id);

                deliveryOrderChecked = [];
                Util.hideProgressInd();
                var mgsalert = 'Delivery order created successfully! Please check the delivery order tab';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 5000,
                });
                //window.location.reload(true);
                projectPurchaseOrder.reloadPoLineItem(project_id);
            });
        }
    });

    $('.click-all-top .check-all').livequery('click', function(e){
        e.preventDefault();
        projectPurchaseOrder.checkAllCol.call(this);
    });

    $('.click-all-top .uncheck-all').livequery('click', function(e){
        e.preventDefault();
        projectPurchaseOrder.uncheckAllCol.call(this);
    });

    $('.requestMaterialsForApproval').livequery('click', function(e){
        if (matReqProductChecked.length == 0){
            Util.alert('Please select atleast one product!');
        }else{
            if (!confirm("Do you want to request for approval?")){
                return;
            }

            var project_id = $(this).attr('project_id');
            var url = "index.php?widget=enggCrm_projectPurchaseOrder&_spAction=updateMaterialSupplierConfirmStatus";
            Util.showProgressInd();
            $.get(url, {matReqProductChecked:matReqProductChecked, project_id:project_id}, function(html){
                projectPurchaseOrder.reloadMaterialRequestPortal(project_id);

                matReqProductChecked = [];
                Util.hideProgressInd();
                var mgsalert = 'Status updated successfully!';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 5000,
                });
            });
        }
    });

    $('.approveMaterialRequest').livequery('click', function(e){
        if (matReqProductChecked.length == 0){
            Util.alert('Please select atleast one product!');
        }else{
            if (!confirm("Do you want to approve all checked materials?")){
                return;
            }
            
            var project_id = $(this).attr('project_id');
            var url = "index.php?widget=enggCrm_projectPurchaseOrder&_spAction=approveMaterialRequestByAdmin";
            Util.showProgressInd();
            $.get(url, {matReqProductChecked:matReqProductChecked, project_id:project_id}, function(html){
                projectPurchaseOrder.reloadPoLineItem(project_id);
                projectPurchaseOrder.reloadMaterialRequestPortal(project_id);

                matReqProductChecked = [];
                Util.hideProgressInd();
                var mgsalert = 'Materials approved successfully!';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 5000,
                });
            });
        }
    });

    $("input[name='product_title[]']").livequery('change', function(){
        var parent     = $(this).closest('tr');
        var product_id = $("input.product_id_hidden", parent).val();
        
        if(product_id == "") {
            $("input.poProductTitle", parent).val("");
            $("select[name='category[]']", parent).val("");
            $("td.productType", parent).html("");
        }
    });

    $("input.materailRequestId").live('click', function() {
        var is_checked = $(this).is(':checked');

        if(is_checked == true){
            matReqProductChecked.push($(this).val());
        } else {
            var index = matReqProductChecked.indexOf($(this).val());
            matReqProductChecked.splice(index, 1);
        }
    });
});

var projectPurchaseOrder = {
    reloadPoLineItem: function(project_id){
        var url = 'index.php?widget=enggCrm_projectPurchaseOrder&_spAction=purchaseOrderPortal&showHTML=0';
        Util.showProgressInd();
        $.get(url, {project_id: project_id}, function(html){
            Util.hideProgressInd();
            $('#poLinkPortal').html(html);
        });
    },

    poProductTitle: function() {
        var titleObj = this;
        $(titleObj).autocomplete({
            source: function(request, response) {
                $.ajax({
                  url: 'index.php?widget=enggCrm_projectPurchaseOrder&_spAction=searchProductTitle&showHTML=0',
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
                var parent       = $(this).closest('tr');

                $("input[name='product_id[]']", parent).val(product_id);
                $("select[name='category[]']", parent).val(category);
                $("td.productType", parent).html(product_type);
            }
        });
    },

    poProductTitleEdit: function() {
        var titleObj = this;
        $(titleObj).autocomplete({
             source : 'index.php?widget=enggCrm_projectPurchaseOrder&_spAction=searchProductTitle&showHTML=0'
            ,minLength : 1
            ,selectFirst: true
            ,autoFocus: true
            ,select: function(event, ui) {
                var selectedObj = ui.item;
                var product_id  = selectedObj.id;

                $("#editForPoLineItem input[name='product_id']").val(product_id);
            }
        });
    },

    projectTitle: function() {
        var titleObj = this;
        $(titleObj).autocomplete({
             source : 'index.php?widget=enggCrm_projectPurchaseOrder&_spAction=searchProjectTitle&showHTML=0'
            ,minLength : 2
            ,selectFirst: true
            ,autoFocus: true
            ,select: function(event, ui) {
                var selectedObj    = ui.item;
                var to_project_id     = selectedObj.id;
                var company_name   = selectedObj.company_name;
                var parent         = $(this).closest('tr');
                var project_id = $("#record_id").val();
                var product_id = $("input[name='product_id']").val();

                $("input[name='to_project_id']", parent).val(to_project_id);
                $(".clientName", parent).html(company_name);
                //$(this).after("<input type='hidden' name='product_id[]' value=" + product_id + ">");
                var url = 'index.php?widget=enggCrm_projectPurchaseOrder&_spAction=createStockTransfer&showHTML=0';
                $.get(url, {project_id: project_id, to_project_id:to_project_id, product_id:product_id}, function(html){
                    $("input[name='stock_transfer_id']", parent).val(html);
                }); 
            }
        });
    },

    clientName: function() {
        var titleObj = this;
        $(titleObj).autocomplete({
             source : 'index.php?widget=enggCrm_projectPurchaseOrder&_spAction=searchClientName&showHTML=0'
            ,minLength : 2
            ,selectFirst: true
            ,autoFocus: true
            ,select: function(event, ui) {
                var selectedObj   = ui.item;
                //var to_project_id = selectedObj.id;
                var company_id  = selectedObj.id;
                var parent        = $(this).closest('tr');
                var project_id    = $("#record_id").val();
                var product_id    = $("input[name='product_id']").val();

                $("input[name='company_id']", parent).val(company_id);
                /*var url = 'index.php?widget=enggCrm_projectPurchaseOrder&_spAction=createStockTransfer&showHTML=0';
                $.get(url, {project_id: project_id, to_project_id:to_project_id, product_id:product_id}, function(html){
                    $("input[name='stock_transfer_id']", parent).val(html);
                });*/ 
                var url = 'index.php?widget=enggCrm_projectPurchaseOrder&_spAction=projectByCompanyJSON&showHTML=0';
                $.get(url, {company_id: company_id}, function (data) {
                    $("select[name='to_project_id']", parent).cp_loadSelect(data);
                }, 'json');
            }
        });
    },

    reloadMaterialTransferredPortal: function(project_id){
        var url = 'index.php?widget=enggCrm_projectMaterialTransferred&_spAction=materialTransferredPortal&showHTML=0';
        Util.showProgressInd();
        $.get(url, {project_id: project_id}, function(html){
            Util.hideProgressInd();
            $('#materialTransferLinkPortal').html(html);
        });
    },

    reloadMaterialRequestPortal: function(project_id){
        var url = 'index.php?widget=enggCrm_projectPurchaseOrder&_spAction=materialRequesPortal&showHTML=0';
        Util.showProgressInd();
        $.get(url, {project_id: project_id}, function(html){
            Util.hideProgressInd();
            $('#materialRequestPortal').html(html);
        });
    },

    reloadDeliveryOrderLink: function(project_id){
        var url = 'index.php?widget=enggCrm_projectDeliveryOrder&_spAction=deliveryOrderPortal&showHTML=0';
        Util.showProgressInd();
        $.get(url, {project_id: project_id}, function(html){
            Util.hideProgressInd();
            $('#deliveryOrderPortal').html(html);
        });
    },

    checkAllCol: function(e){
        var parent = $(this).closest(".requestItemsRow");
        var colPo  = $(this).parent().index();
        $('tr', parent).each(function(rowIndex, trObj) {
            var checkbox = $(trObj).find('td:eq(' + colPo + ') input');
            checkbox.attr('checked', 'checked');
            
            var is_checked   = checkbox.is(':checked');
            if(is_checked == true){
                matReqProductChecked.push(checkbox.val());
            }
        });
    },

    uncheckAllCol: function(e){
        var parent = $(this).closest(".requestItemsRow");
        var colPo  = $(this).parent().index();
        $('tr', parent).each(function(rowIndex, trObj) {
            var checkbox = $(trObj).find('td:eq(' + colPo + ') input');
            checkbox.removeAttr('checked');

            var is_checked = checkbox.is(':checked');
            if(is_checked == false){
                var index = matReqProductChecked.indexOf(checkbox.val());
                matReqProductChecked.splice(index, 1);
            }
        });
    },
}