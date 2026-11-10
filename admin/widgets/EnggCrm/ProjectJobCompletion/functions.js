$(function(){

    $('a.editForDoJob').livequery('click', function(e){
        var title = "Edit Job Completion Display";
        var project_id = $('#record_id').val();

        e.preventDefault();
        var expObj = {
            validate: true,
            callbackOnSuccess: function(){
                Util.closeAllDialogs();
                //Util.alert('Updated successfully..');
                var mgsalert = 'Updated Job successfully!';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 5000,
                });
                projectJobCompletion.reloadQuotePortal(project_id);
            }
        }

        Util.openFormInDialog.call(this, 'editForDoNote', title, 900, 500, expObj);
     });

    $('#addJobCompletion').livequery('click', function(){

        var project_id = $("#record_id").val();
        msg = "Do you like to Add Job Completion?";

        if (!confirm(msg)){
            return false;
        }
        else{
            Util.showProgressInd();
            var project_id = $(this).attr('project_id');

            var url = 'index.php?widget=enggCrm_projectJobCompletion&_spAction=addJobFormSubmit&showHTML=0&id=' + project_id;

            $.get(url, {project_id: project_id}, function(html){
                //alert('Quote Record Created Successfully');
                var mgsalert = 'Delivery order record created successfully!';
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
                projectJobCompletion.reloadQuotePortal(project_id);
            });
            //Util.hideProgressInd();
        }
    });

    


    $(".creationModificationQuote1").livequery('click', function (e){
        var delivery_note_id = $(this).attr('delivery_note_id');

        Util.showProgressInd();

        var url = "index.php?widget=enggCrm_projectJobCompletion&_spAction=creationModificationQuote&delivery_note_id="+delivery_note_id+"&showHTML=0";
        var exp = {
            url: url
        };

        Util.openDialogForLink('Updated By',  500, 200, 0, exp);
    });

     /* Adding row in new Line Item */

    $("#addMultipleJobLineItemForm a.addRow").livequery('click', function (e){

        var project_id = $(this).attr('project_id');
        var url = 'index.php?widget=enggCrm_projectJobCompletion&_spAction=addLineItemRecord'
                + '&showHTML=0&project_id=' + project_id;

        $.get(url, '' ,function(html){

            $('#addMultipleJobLineItemForm tr:last').after(html);

        });
    });

    /* Adding row in new Line Item */

    $("#addMultipleJobLineItemForm a.addDrawingRow").livequery('click', function (e){

        var project_id = $(this).attr('project_id');
        var url = 'index.php?widget=enggCrm_projectJobCompletion&_spAction=addLineDrawingItemRecord'
                + '&showHTML=0&project_id=' + project_id;

        $.get(url, '' ,function(html){

            $('#addMultipleJobLineItemForm tr:last').after(html);
        });
    });

   

   

    /*$('a.editForQuote').livequery('click', function (e){
        var title = "Edit Quote Display";
        var project_id = $('#record_id').val();

        e.preventDefault();
        var expObj = {
            validate: true,
            callbackOnSuccess: function(data){
                Util.closeAllDialogs();
                var mgsalert = 'Updated quote successfully!';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 5000,
                });

                

                projectJobCompletion.reloadQuotePortal(project_id);
            }
        }
        Util.openFormInDialog.call(this, 'editForQuote', title, 800, 500, expObj);
    });

    /* ADD LINE ITEM EDIT */

   
    $('#updateDeliveryReport').livequery('click', function(){
        var project_id = $("#record_id").val();

        Util.showProgressInd();
        var project_id = $(this).attr('project_id');
        var delivery_order_id = $(this).attr('delivery_order_id');

        var url = 'index.php?widget=enggCrm_projectJobCompletion&_spAction=updateDeliveryOrderSubmit&showHTML=0&id=' + project_id;

        $.get(url, {project_id: project_id, delivery_order_id:delivery_order_id}, function(html){
            //alert('Quote Record Created Successfully');
            var mgsalert = 'Delivery report updated successfully!';
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
            projectJobCompletion.reloadQuotePortal(project_id);
        });
        //Util.hideProgressInd();
    });

    $('a.deleteDoLineItem').livequery('click', function (e){

        var project_id = $("#record_id").val();
        var delivery_order_id = $(this).attr('delivery_order_id');
        //var category = $(this).attr('category');

        msg = "Do you like to delete the Quote Line Item?";
        if (!confirm(msg)){
            return false;
        }
        else{
            Util.showProgressInd();
            var delivery_order_hist_id = $(this).attr('delivery_order_hist_id');

            var url = 'index.php?widget=enggCrm_projectJobCompletion&_spAction=deleteJobLineItem&showHTML=0&delivery_order_hist_id=' + delivery_order_hist_id;

            $.get(url, {delivery_order_hist_id: delivery_order_hist_id}, function(html){
                Util.hideProgressInd();
                var mgsalert = 'Add line item deleted succesfully!';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 5000,
                });
                projectJobCompletion.reloadQuotePortal(project_id);
            });
        }
    });


    $("#addMultipleJobLineItemForm .lineItemQuantity").livequery('change', function (e){

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

        var total_amount = 0;
        var total_Price = document.getElementsByClassName('lineItemAmount');
        for (var i = 0; i < total_Price.length; ++i) {
            if (!isNaN(parseInt(total_Price[i].value)) ){
                total_amount += parseInt(total_Price[i].value);
            }
        }

        if(total_amount == "NaN") {
            total_amount = parseInt(0);
        }

        $('.quoteLineItemsOverallTotal .quoteLineItemsOverallTotalAmount').html(total_amount.toFixed(3));
    });


    $("#addMultipleJobLineItemForm .lineItemUnitPrice").livequery('change', function (e){

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

        var total_amount = 0;
        var total_Price = document.getElementsByClassName('lineItemAmount');
        for (var i = 0; i < total_Price.length; ++i) {
            if (!isNaN(parseInt(total_Price[i].value)) ){
                total_amount += parseInt(total_Price[i].value);
            }
        }

        if(total_amount == "NaN") {
            total_amount = parseInt(0);
        }

        $('.quoteLineItemsOverallTotal .quoteLineItemsOverallTotalAmount').html(total_amount.toFixed(3));
    });
    // $("input[name='title[]']").livequery(projectJobCompletion.ProductTitle);


    $("#addMultipleJobLineItemForm .lineItemAmount").livequery('change', function (e){

        var total_amount = 0;
        var total_Price = document.getElementsByClassName('lineItemAmount');
        for (var i = 0; i < total_Price.length; ++i) {
            if (!isNaN(parseInt(total_Price[i].value)) ){
                total_amount += parseInt(total_Price[i].value);
            }
        }

        if(total_amount == "NaN") {
            total_amount = parseInt(0);
        }

        $('.quoteLineItemsOverallTotal .quoteLineItemsOverallTotalAmount').html(total_amount.toFixed(3));
    });

    $("a.clearLineItem").livequery('click', function (e){
        var titleObj       = $(this).closest('tr').find('.lineItemTitle');

        var qtyObj    = $(this).closest('tr').find('.lineItemQuantity');

        var unitObj        = $(this).closest('tr').find('.lineItemUnit');
        var amountObj      = $(this).closest('tr').find('.lineItemAmount');
        //var totalCostObj   = $(this).closest('tr').find('.totalCost');
        var unitPriceObj   = $(this).closest('tr').find('.lineItemUnitPrice');
        var descriptionObj = $(this).closest('tr').find('.lineItemDescription');
        //var remarksObj   = $(this).closest('tr').find('.lineItemRemarks');
        //var scaffoldCodeObj = $(this).closest('tr').find('.lineItemScaffoldCode');
        //var erectionObj = $(this).closest('tr').find('.lineItemErection');
        //var dismantleObj = $(this).closest('tr').find('.lineItemDismantle');

        titleObj.val('');
        qtyObj.val('');
        unitObj.val('');
        amountObj.val('');
        //totalCostObj.html('');
        descriptionObj.val('');
        unitPriceObj.val('');
        //remarksObj.val('');
        //scaffoldCodeObj.val('');
        //erectionObj.val('');
        //dismantleObj.val('');

        var total_amount = 0;
        var total_Price = document.getElementsByClassName('lineItemAmount');
        for (var i = 0; i < total_Price.length; ++i) {
            if (!isNaN(parseInt(total_Price[i].value)) ){
                total_amount += parseInt(total_Price[i].value);
            }
        }

        if(total_amount == "NaN") {
            total_amount = parseInt(0);
        }

        $('.quoteLineItemsOverallTotal .quoteLineItemsOverallTotalAmount').html(total_amount.toFixed(3));
    });

    $("a.clearDrawingLineItem").livequery('click', function (e){
        var drawingNumberObj   = $(this).closest('tr').find('.drawingNumber');
        var drawingTitleObj    = $(this).closest('tr').find('.drawingTitle');
        var drawingRevisionObj = $(this).closest('tr').find('.drawingRevision');

        drawingNumberObj.val('');
        drawingTitleObj.val('');
        drawingRevisionObj.val('');
    });
});

var projectJobCompletion = {
  

    reloadQuotePortal: function(project_id){
        var url = 'index.php?widget=enggCrm_projectJobCompletion&_spAction=JobCompletionPortal&showHTML=0';

        Util.showProgressInd();
        $.get(url, {project_id: project_id}, function(html){
            Util.hideProgressInd();
            $('#JobCompletionPortal').html(html);
        });
    },
    ProductTitle: function() {
        var titleObj = this;
        $(titleObj).autocomplete({
            source: function(request, response) {
                $.ajax({
                  url: 'index.php?widget=enggCrm_projectJobCompletion&_spAction=searchTitle&showHTML=0',
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
                //var model = selectedObj.model;
                var nomenclature = selectedObj.nomenclature;
                var manufacture = selectedObj.manufacture;
                var serial_no = selectedObj.serial_no;
                var parent       = $(this).closest('tr');

                $("input[name='product_id[]']", parent).val(product_id);
                //$("td.model", parent).html(model); 
                $("td.manufacture", parent).html(manufacture);   
                $("td.serial_no", parent).html(serial_no);              }
        });
    },

}

