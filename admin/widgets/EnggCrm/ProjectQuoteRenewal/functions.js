$(function(){
    $('#addQuoteProject').livequery('click', function(){
        var renewal_id = $("#record_id").val();
        var opportunity_id = $(this).attr('opportunity_id');
        var category = $(this).attr('category');
        msg = "Do you like to Add Quote?";

        if (!confirm(msg)){
            return false;
        }
        else{
            Util.showProgressInd();
            var renewal_id = $(this).attr('renewal_id');
            var url = 'index.php?widget=enggCrm_projectQuoteRenewal&_spAction=addQuoteFormSubmit&showHTML=0&id=' + renewal_id;
            $.get(url, {renewal_id: renewal_id}, function(html){
                var mgsalert = 'Quote record created successfully!';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 5000,
                });
                
                // var returnVal = html.split("_");

                // if(returnVal[1] != "" && (returnVal[1] != undefined || returnVal[1] != "NaN")) {
                //     var title    = "Edit Quote Display";
                //     var quote_id = returnVal[1];
                //     var urlQuoteEdit = "index.php?_topRm=project&widget=enggCrm_projectQuoteRenewal&_spAction=editForQuote&renewal_id="+renewal_id+"&quote_id="+quote_id+"&showHTML=0";
                    
                //     var expObj = {
                //         validate: true,
                //         url: urlQuoteEdit,
                //         callbackOnSuccess: function(data){
                //             Util.closeAllDialogs();
                //             var mgsalert = 'Updated quote successfully!';
                //             var n = noty({
                //                 text: mgsalert,
                //                 type: 'confirm',
                //                 dismissQueue: true,
                //                 layout: 'topCenter',
                //                 theme: 'defaultTheme',
                //                 timeout: 5000,
                //             });

                //             if(data.returnUrl == "Awarded") {
                //                 cpm.enggCrm.project.reloadInvoiceReceiptMainPortalDisplay(renewal_id);
                //             }

                //             projectQuoteRenewal.reloadQuotePortal(renewal_id);
                //         }
                //     }
                    
                //     Util.openFormInDialog.call(this, 'editForRenewal', title, 800, 500, expObj);
                // }

                // if(returnVal[0] == 1) {
                //     cpm.enggCrm.project.reloadClaimPortal(renewal_id);
                // }

                projectQuoteRenewal.reloadQuotePortal(renewal_id);
            });
        }
    });

    $(".viewQuoteLog1").livequery('click', function (e){
        var renewal_id = $(this).attr('renewal_id');

        Util.showProgressInd();

        var url = "index.php?widget=enggCrm_projectQuoteRenewal&_spAction=viewQuoteLog1&renewal_id="+renewal_id+"&showHTML=0";
        var exp = {
            url: url
        };

        Util.openDialogForLink('Quote History',  900, 500, 0, exp);
    });

    $('#editForQuote input[name=drawing_nos]').livequery('click', function(){
        var drawing_nos = $(this).val();

        if(drawing_nos == 1) {
            $("#editForQuote .drawingQuoteFields").removeClass('displayNone');
            $("#editForQuote .defaultQuoteFields").addClass('displayNone');
        } else {
            $("#editForQuote .drawingQuoteFields").addClass('displayNone');
            $("#editForQuote .defaultQuoteFields").removeClass('displayNone');
        }
    });

    $(".creationModificationQuote3").livequery('click', function (e){
        var quote_id = $(this).attr('quote_id');

        Util.showProgressInd();

        var url = "index.php?widget=enggCrm_projectQuoteRenewal&_spAction=creationModificationQuote3&quote_id="+quote_id+"&showHTML=0";
        var exp = {
            url: url
        };

        Util.openDialogForLink('Updated By',  500, 200, 0, exp);
    });

     /* Adding row in new Line Item */
    $("#addMultipleLineItemForm a.addRow").livequery('click', function (e){
        var renewal_id = $(this).attr('renewal_id');
        var url = 'index.php?widget=enggCrm_projectQuoteRenewal&_spAction=addLineItemRecord'
                + '&showHTML=0&renewal_id=' + renewal_id;

        $.get(url, '' ,function(html){
            $('#addMultipleLineItemForm tr:last').after(html);
        });
    });

    /* Adding row in new Line Item */
    $("#addMultipleLineItemForm a.addDrawingRow").livequery('click', function (e){
        var renewal_id = $(this).attr('renewal_id');
        var url = 'index.php?widget=enggCrm_projectQuoteRenewal&_spAction=addLineDrawingItemRecord'
                + '&showHTML=0&renewal_id=' + renewal_id;

        $.get(url, '' ,function(html){
            $('#addMultipleLineItemForm tr:last').after(html);
        });
    });

    $("a.addMultipleLineItemRenewal").livequery('click', function (e){
        var title = "Add Line Item";
        var renewal_id = $(this).attr('renewal_id');
        var opportunity_id = $(this).attr('opportunity_id');
        var quote_id = $(this).attr('quote_id');
        var url = 'index.php?widget=enggCrm_projectQuoteRenewal&_spAction=addMultipleLineItem'
                + '&showHTML=0&renewal_id=' + renewal_id + '&quote_id=' + quote_id;
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
                projectQuoteRenewal.reloadQuotePortal(renewal_id);
            }
        };

        Util.openFormInDialog.call(this, 'addMultipleLineItemForm', title, 900, 500, exp);
    });

    $('.m-enggCrm_renewal .quoteLayoutShow').livequery('click', function (e){
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

    $('a.editForRenewal').livequery('click', function (e){
        var title = "Edit Quote Display";
        var renewal_id = $('#record_id').val();

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
                    cpm.enggCrm.project.reloadInvoiceReceiptMainPortalDisplay(renewal_id);
                }

                projectQuoteRenewal.reloadQuotePortal(renewal_id);
            }
        }
        Util.openFormInDialog.call(this, 'editForRenewal', title, 800, 500, expObj);
    });

    /* ADD LINE ITEM EDIT */
    $("a.editForLineItem").livequery('click', function (e){
        var title = "Edit Display";
        var renewal_id = $("#record_id").val();
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
                projectQuoteRenewal.reloadQuotePortal(renewal_id);
            }
        }

        Util.openFormInDialog.call(this, 'editForLineItem', title, 600, 350, expObj);
    });

    $('a.deleteLineItem').livequery('click', function (e){
        var renewal_id = $("#record_id").val();
        var opportunity_id = $(this).attr('opportunity_id');
        var category = $(this).attr('category');

        msg = "Do you like to delete the Quote Line Item?";
        if (!confirm(msg)){
            return false;
        }
        else{
            Util.showProgressInd();
            var quote_items_id = $(this).attr('quote_items_id');
            var url = 'index.php?widget=enggCrm_projectQuoteRenewal&_spAction=deleteLineItem&showHTML=0&quote_items_id=' + quote_items_id;
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
                projectQuoteRenewal.reloadQuotePortal(renewal_id);
            });
        }
    });

    $("#addMultipleLineItemForm .lineItemQuantity").livequery('change', function (e){
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

    $("#addMultipleLineItemForm .lineItemUnitPrice").livequery('change', function (e){
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

    $("#addMultipleLineItemForm .lineItemAmount").livequery('change', function (e){
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

    $("a.clearDrawingLineItem").livequery('click', function (e){
        var drawingNumberObj   = $(this).closest('tr').find('.drawingNumber');
        var drawingTitleObj    = $(this).closest('tr').find('.drawingTitle');
        var drawingRevisionObj = $(this).closest('tr').find('.drawingRevision');

        drawingNumberObj.val('');
        drawingTitleObj.val('');
        drawingRevisionObj.val('');
    });
});

var projectQuoteRenewal = {
    reloadQuotePortal: function(renewal_id){
        var url = 'index.php?widget=enggCrm_projectQuoteRenewal&_spAction=addQuoteFormListView&showHTML=0';
        Util.showProgressInd();
        $.get(url, {renewal_id: renewal_id}, function(html){
            Util.hideProgressInd();
            $('#quoteLinkPortal').html(html);
        });
    },
}