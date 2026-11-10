$(function(){
    $('.m-enggCrm_project #addQuoteProject').livequery('click', function(){
        var project_id = $("#record_id").val();
        var opportunity_id = $(this).attr('opportunity_id');
        var category = $(this).attr('category');
        msg = "Do you like to Add Quote?";

        if (!confirm(msg)){
            return false;
        }
        else{
            Util.showProgressInd();
            var project_id = $(this).attr('project_id');
            var url = 'index.php?widget=enggCrm_projectQuote&_spAction=addQuoteFormSubmit&showHTML=0&id=' + project_id;
            $.get(url, {project_id: project_id}, function(html){
                var mgsalert = 'Quote record created successfully!';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 5000,
                });
                
                var returnVal = html.split("_");

                if(returnVal[1] != "" && (returnVal[1] != undefined || returnVal[1] != "NaN")) {
                    var title    = "Edit Quote Display";
                    var quote_id = returnVal[1];
                    var urlQuoteEdit = "index.php?_topRm=project&widget=enggCrm_projectQuote&_spAction=editForQuote&project_id="+project_id+"&quote_id="+quote_id+"&showHTML=0";
                    
                    var expObj = {
                        validate: true,
                        url: urlQuoteEdit,
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

                            if(data.returnUrl == "Awarded") {
                                cpm.enggCrm.project.reloadInvoiceReceiptMainPortalDisplay(project_id);
                            }

                            projectQuote.reloadQuotePortal(project_id);
                        }
                    }
                    
                    Util.openFormInDialog.call(this, 'editForQuote', title, 800, 500, expObj);
                }

                if(returnVal[0] == 1) {
                    cpm.enggCrm.project.reloadClaimPortal(project_id);
                }

                projectQuote.reloadQuotePortal(project_id);
            });
        }
    });

    $(".m-enggCrm_project .viewQuoteLog").livequery('click', function (e){
        var project_id = $(this).attr('project_id');

        Util.showProgressInd();

        var url = "index.php?widget=enggCrm_projectQuote&_spAction=viewQuoteLog&project_id="+project_id+"&showHTML=0";
        var exp = {
            url: url
        };

        Util.openDialogForLink('Quote History',  900, 500, 0, exp);
    });

    $('.m-enggCrm_project #editForQuote input[name=drawing_nos]').livequery('click', function(){
        var drawing_nos = $(this).val();

        if(drawing_nos == 1) {
            $("#editForQuote .drawingQuoteFields").removeClass('displayNone');
            $("#editForQuote .defaultQuoteFields").addClass('displayNone');
        } else {
            $("#editForQuote .drawingQuoteFields").addClass('displayNone');
            $("#editForQuote .defaultQuoteFields").removeClass('displayNone');
        }
    });

    $(".m-enggCrm_project .creationModificationQuote").livequery('click', function (e){
        var quote_id = $(this).attr('quote_id');

        Util.showProgressInd();

        var url = "index.php?widget=enggCrm_projectQuote&_spAction=creationModificationQuote&quote_id="+quote_id+"&showHTML=0";
        var exp = {
            url: url
        };

        Util.openDialogForLink('Updated By',  500, 200, 0, exp);
    });

     /* Adding row in new Line Item */
    $(".m-enggCrm_project #addMultipleLineItemForm a.addRow").livequery('click', function (e){
        var project_id = $(this).attr('project_id');
        var url = 'index.php?widget=enggCrm_projectQuote&_spAction=addLineItemRecord'
                + '&showHTML=0&project_id=' + project_id;

        $.get(url, '' ,function(html){
            $('#addMultipleLineItemForm tr:last').after(html);
        });
    });

    /* Adding row in new Line Item */
    $(".m-enggCrm_project #addMultipleLineItemForm a.addDrawingRow").livequery('click', function (e){
        var project_id = $(this).attr('project_id');
        var url = 'index.php?widget=enggCrm_projectQuote&_spAction=addLineDrawingItemRecord'
                + '&showHTML=0&project_id=' + project_id;

        $.get(url, '' ,function(html){
            $('#addMultipleLineItemForm tr:last').after(html);
        });
    });

    $(".m-enggCrm_project a.addMultipleLineItem").livequery('click', function (e){
        var title = "Add Line Item";
        var project_id = $(this).attr('project_id');
        var opportunity_id = $(this).attr('opportunity_id');
        var quote_id = $(this).attr('quote_id');
        var url = 'index.php?widget=enggCrm_projectQuote&_spAction=addMultipleLineItem'
                + '&showHTML=0&project_id=' + project_id + '&quote_id=' + quote_id;
        var exp = {
            url: url
           ,callbackOnSuccess: function(){
                Util.closeAllDialogs();
                var mgsalert = 'Line items created successfully!';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 5000,
                });
                projectQuote.reloadQuotePortal(project_id);
            }
        };

        Util.openFormInDialog.call(this, 'addMultipleLineItemForm', title, 900, 500, exp);
    });

    $('.m-enggCrm_project .quoteLayoutShow').livequery('click', function (e){
        var link_text = $(this).html();
        var parent = $(this).closest('.quoteDetailRow');

        if(link_text == 'View Line Items'){
            $('.quoteLayoutShow', parent).text('Hide Line Items');
        }
        else{
            $('.quoteLayoutShow', parent).text('View Line Items');
        }

        $('.showAddLineRow', parent).slideToggle();
    });

    $('.m-enggCrm_project a.editForQuote').livequery('click', function (e){
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

                if(data.returnUrl == "Awarded") {
                    cpm.enggCrm.project.reloadInvoiceReceiptMainPortalDisplay(project_id);
                }

                projectQuote.reloadQuotePortal(project_id);
            }
        }
        Util.openFormInDialog.call(this, 'editForQuote', title, 800, 500, expObj);
    });

    /* ADD LINE ITEM EDIT */
    $(".m-enggCrm_project a.editForLineItem").livequery('click', function (e){
        var title = "Edit Display";
        var project_id = $("#record_id").val();
        var opportunity_id = $(this).attr('opportunity_id');
        var category = $(this).attr('category');

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
                projectQuote.reloadQuotePortal(project_id);
            }
        }

        Util.openFormInDialog.call(this, 'editForLineItem', title, 600, 350, expObj);
    });

    $('.m-enggCrm_project a.deleteLineItem').livequery('click', function (e){
        var project_id = $("#record_id").val();
        var opportunity_id = $(this).attr('opportunity_id');
        var category = $(this).attr('category');

        msg = "Do you like to delete the Quote Line Item?";
        if (!confirm(msg)){
            return false;
        }
        else{
            Util.showProgressInd();
            var quote_items_id = $(this).attr('quote_items_id');
            var url = 'index.php?widget=enggCrm_projectQuote&_spAction=deleteLineItem&showHTML=0&quote_items_id=' + quote_items_id;
            $.get(url, {quote_items_id: quote_items_id}, function(html){
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
                projectQuote.reloadQuotePortal(project_id);
            });
        }
    });

    $(".m-enggCrm_project #addMultipleLineItemForm .lineItemQuantity").livequery('change', function (e){
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

    $(".m-enggCrm_project #addMultipleLineItemForm .lineItemUnitPrice").livequery('change', function (e){
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

    $(".m-enggCrm_project #addMultipleLineItemForm .lineItemAmount").livequery('change', function (e){
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

    $(".m-enggCrm_project a.clearLineItem").livequery('click', function (e){
        var titleObj       = $(this).closest('tr').find('.lineItemTitle');
        var quantityObj    = $(this).closest('tr').find('.lineItemQuantity');
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
        quantityObj.val('');
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

    $(".m-enggCrm_project a.clearDrawingLineItem").livequery('click', function (e){
        var drawingNumberObj   = $(this).closest('tr').find('.drawingNumber');
        var drawingTitleObj    = $(this).closest('tr').find('.drawingTitle');
        var drawingRevisionObj = $(this).closest('tr').find('.drawingRevision');

        drawingNumberObj.val('');
        drawingTitleObj.val('');
        drawingRevisionObj.val('');
    });
});

var projectQuote = {
    reloadQuotePortal: function(project_id){
        var url = 'index.php?widget=enggCrm_projectQuote&_spAction=addQuoteFormListView&showHTML=0';
        Util.showProgressInd();
        $.get(url, {project_id: project_id}, function(html){
            Util.hideProgressInd();
            $('#quoteLinkPortal').html(html);
        });
    },
}