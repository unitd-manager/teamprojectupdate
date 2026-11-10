Util.createCPObject('cpm.enggCrm.opportunity');

cpm.enggCrm.opportunity.init = function(){
    $("a.addMultipleLineItem").livequery('click', function (e){
        var title = "Add Line Item";
        var opportunity_id = $(this).attr('opportunity_id');
        var quote_id = $(this).attr('quote_id');
        var category = $(this).attr('category');
        var url = 'index.php?module=enggCrm_opportunity&_spAction=addMultipleLineItem'
                + '&showHTML=0&opportunity_id=' + opportunity_id + '&quote_id=' + quote_id + '&category=' + category;
        var exp = {
            url: url
           ,callbackOnSuccess: function(){
                var msg = 'Line Items created successfully123';
                Util.alert(msg, function(){
                    Util.closeAllDialogs();
                    window.location.reload(true);
                });
            }
        };
        Util.openFormInDialog.call(this, 'addMultipleLineItemForm', title, 1100, 500, exp);
    });

    /* Adding row in new Line Item */
    $("a.addRow").livequery('click', function (e){
        var url = 'index.php?module=enggCrm_opportunity&_spAction=addLineItemRecord'
                + '&showHTML=0';

        $.get(url, '' ,function(html){
            $('#addMultipleLineItemForm tr:last').after(html);
        });
    });

    $("#addMultipleLineItemForm .lineItemQuantity").livequery('change', function (e){
        var quantity = $(this).val();
        var amountObj = $(this).closest('tr').find('.lineItemAmount');
        var amount = amountObj.val();
        var totalCostObj = $(this).closest('tr').find('.totalCost');

        if (quantity > 0 && amount > 0) {
            var total_cost = quantity * amount;
            var total_cost_formatted = (total_cost).toFixed(2);
            totalCostObj.html(total_cost_formatted);
        }
    });

    $("#addMultipleLineItemForm .lineItemAmount").livequery('change', function (e){
        var amount = $(this).val();
        var quantityObj = $(this).closest('tr').find('.lineItemQuantity');
        var quantity = quantityObj.val();
        var totalCostObj = $(this).closest('tr').find('.totalCost');

        if (quantity > 0 && amount > 0) {
            var total_cost = quantity * amount;
            var total_cost_formatted = (total_cost).toFixed(2);
            totalCostObj.html(total_cost_formatted);
        }
    });

    $("a.clearLineItem").livequery('click', function (e){
        var descriptionObj = $(this).closest('tr').find('.lineItemDescription');
        var titleObj = $(this).closest('tr').find('.lineItemTitle');
        var quantityObj = $(this).closest('tr').find('.lineItemQuantity');
        var unitObj = $(this).closest('tr').find('.lineItemUnit');
        var amountObj = $(this).closest('tr').find('.lineItemAmount');
        var totalCostObj = $(this).closest('tr').find('.totalCost');
        var remarksObj = $(this).closest('tr').find('.lineItemRemarks');

        descriptionObj.val('');
        titleObj.val('');
        quantityObj.val('');
        unitObj.val('');
        amountObj.val('');
        totalCostObj.html('');
        remarksObj.val('');
    });

    $('.convertOppToProject').livequery('click', function (e){
        msg = "Do you like to convert to Project?";
        if (!confirm(msg)){
            return false;
        }
        else{

            Util.showProgressInd();
            var opportunity_id  = $('#record_id').val();
            var statusConfirmed = $(this).attr('statusConfirmed');

            if(statusConfirmed == 'Yes') {
                var url = 'index.php?_topRm=project&module=enggCrm_project&_spAction=convertToProject&showHTML=0' +
                        '&opportunity_id=' + opportunity_id;
                $.get(url, {opportunity_id: opportunity_id}, function (html) {
                    Util.hideProgressInd();
                    Util.alert('Converted Project Succesfully')
                    var convertUrl = "index.php?_topRm=project&module=enggCrm_opportunity&_action=edit&opportunity_id=" + opportunity_id;
                    document.location = convertUrl;
                    //window.location.reload(true);
                });
            } else {
                var msg = 'The opportunity should have a confirmed quote before it is getting converetd to project';
                Util.alert(msg)

            }
        }
    });

    $('select#fld_company_id').change(function() {
        var company_id = $(this).val();

        var url = 'index.php?module=enggCrm_contact&_spAction=contactByCompanyJSON&showHTML=0';
        $.get(url, {company_id: company_id}, function (data) {
            $('#fld_contact_id').cp_loadSelect(data);
        }, 'json');
    });


    $('.m-enggCrm_opportunity a.deleteLineItem').livequery('click', function (e){
        msg = "Do you like to delete the Quote Line Item?";
        if (!confirm(msg)){
            return false;
        }
        else{
            Util.showProgressInd();
            var quote_items_id = $(this).attr('quote_items_id');
            var url = 'index.php?module=enggCrm_opportunity&_spAction=deleteLineItem&showHTML=0&quote_items_id=' + quote_items_id;
            $.get(url, {quote_items_id: quote_items_id}, function(html){
                Util.hideProgressInd();
                alert ('Add Line Item Deleted Succesfully');
                window.location.reload(true);
            });
        }
    });

    $('.m-enggCrm_opportunity a.deleteAddQuote').livequery('click', function (e){
        msg = "Do you like to delete the Quote?";

        if (!confirm(msg)){
            return false;
        }
        else{
            Util.showProgressInd();
            var quote_id = $(this).attr('quote_id');
            var url = 'index.php?module=enggCrm_opportunity&_spAction=deleteAddQuote&showHTML=0&quote_id=' + quote_id;
            $.get(url, {quote_id: quote_id}, function(html){
                Util.hideProgressInd();
                alert ('Quote Deleted Succesfully');
                window.location.reload(true);
            });
        }
    });

    $('.m-enggCrm_opportunity a.duplicateQuote').livequery('click', function (e){
        var opportunity_id = $(this).attr('opportunity_id');
        var quote_id = $(this).attr('quote_id');

        e.preventDefault();
        var dialog = $('<div>Do you like to duplicate the quote?</div>').dialog({
            buttons: {
                "With Item": function() {
                    var url = 'index.php?module=enggCrm_opportunity&_spAction=duplicateQuote&showHTML=0&add_line_item=1';
                    $.get(url, {opportunity_id: opportunity_id, quote_id: quote_id}, function(html){
                        Util.hideProgressInd();
                        alert ('Quote & Line Items duplicated Succesfully');
                        Util.closeAllDialogs();
                        window.location.reload(true);
                    });
                },
                "Without Item":  function() {
                    var url = 'index.php?module=enggCrm_opportunity&_spAction=duplicateQuote&showHTML=0&add_line_item=0';
                    $.get(url, {opportunity_id: opportunity_id, quote_id: quote_id}, function(html){
                        Util.hideProgressInd();
                        alert ('Quote duplicated Succesfully');
                        window.location.reload(true);
                    });
                },
                "Cancel":  function() {
                    dialog.dialog('close');
                }
            }
        });
    });

    $('.quoteLayoutShow').livequery('click', function (e){

        var link_text = $(this).html();
        var parent = $(this).closest('.quoteDetailRow');

        if(link_text == 'View Line Items'){
            $('.quoteLayoutShow', parent).text('Hide');
        }
        else{
            $('.quoteLayoutShow', parent).text('View Line Items');
        }

        $('.showAddLineRow', parent).slideToggle();
    });

    $('#addQuote').livequery('click', function(){
        msg = "Do you like to Add Quote?";

        if (!confirm(msg)){
            return false;
        }
        else{
            Util.showProgressInd();
            var opportunity_id = $(this).attr('opportunity_id');
            var category       = $(this).attr('category');
            var url = 'index.php?module=enggCrm_opportunity&_spAction=addQuoteFormSubmit&showHTML=0&id=' + opportunity_id;
            $.get(url, {opportunity_id: opportunity_id, category: category}, function(html){
                    window.location.reload(true);

                    Util.closeAllDialogs();
                    var mgsalert2='Quote Record Created Successfully';
                    var n = noty({
                        text: mgsalert2,
                        type: 'confirm',
                        dismissQueue: true,
                        layout: 'topCenter',
                        theme: 'defaultTheme',
                        timeout: 5000,
                    });
            });
            //Util.hideProgressInd();
        }
    });

    $('a.addLineItem').livequery('click', function (e){
        var title    = "Add Line Item";
        var quote_id        = $(this).attr('quote_id');
        var opportunity_id  = $(this).attr('opportunity_id');

        e.preventDefault();
        var expObj = {
            validate: true
           ,callbackOnSuccess: function(){
                Util.closeAllDialogs();
                alert('Line Item Created Successfully');
                reloadAddLineItemList.reloadAddLineItemListobj(quote_id, opportunity_id);
            }
        }

        Util.openFormInDialog.call(this, 'portalForm', title, 400, 300, expObj);
    });

    /* ADD LINE ITEM EDIT */
    $("a.editForLineItem").livequery('click', function (e){
        var title = "Edit Display";

        e.preventDefault();
        var expObj = {
            validate: true,
            callbackOnSuccess: function(){
                Util.closeAllDialogs();
                Util.alert('Updated successfully..');
                window.location.reload(true);
            }
        }
        Util.openFormInDialog.call(this, 'editForLineItem', title, 577, 427, expObj);
    });

    /* Quote Edit Portal */
    $("a.editForQuote").livequery('click', function (e){
        var title = "Edit Quote";

        e.preventDefault();
        var expObj = {
            validate: true,
            callbackOnSuccess: function(){
                Util.closeAllDialogs();
                Util.alert('Updated Quote successfully..');
                window.location.reload(true);
            }
        }
        Util.openFormInDialog.call(this, 'editForQuote', title, 1100, 500, expObj);
    });
}

var reloadAddLineItemList = {
    reloadAddLineItemListobj: function(quote_id, opportunity_id){
         var url = 'index.php?&module=enggCrm_opportunity&_spAction=addQuoteFormListView&quote_id='+ quote_id +'&opportunity_id='+ opportunity_id +'&showHTML=0';
         $.get(url, {opportunity_id: opportunity_id, quote_id: quote_id}, function(html){
            $('#addLineItemPortalView').html(html);
            Util.hideProgressInd();
        });
    }
}

cpm.enggCrm.opportunity.loadContactsByCompany = function(){
    var url = 'index.php?module=enggCrm_contact&_spAction=contactByCompanyJSON&showHTML=0';
    var company_id = $('select[name=company_id]').val();
    $.get(url, {company_id: company_id}, function (data) {
        $('#fld_contact_id').cp_loadSelect(data);
    }, 'json');
}

cpm.enggCrm.opportunity.afterNewCompany = function(){
    Util.closeAllDialogs();
    Util.alert('New company successfully created.', function(){
        window.location.reload(true);
        $('#fld_company_name').focus();
        $('#fld_company_name').select();
    });
}

cpm.enggCrm.opportunity.afterNewContact = function(){
    Util.closeAllDialogs();
    Util.alert('New contact successfully created.', function(){
        cpm.enggCrm.opportunity.loadContactsByCompany();
    });
}