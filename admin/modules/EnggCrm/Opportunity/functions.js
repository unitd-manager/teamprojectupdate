Util.createCPObject('cpm.enggCrm.opportunity');

cpm.enggCrm.opportunity = {
    init : function(){
        //initialize tabs
        $('#tabs').tabs();

        $('#tabs ul.ui-tabs-nav li:last').livequery(function() {
            $(this).css('border-right', '1px solid #D3D3D3');
        });


         $("a.addMultipleOpportunity").livequery('click', function (e){
        var title = "Add Renewal";
        var opportunity_id = $(this).attr('opportunity_id');
        var url = 'index.php?module=enggCrm_opportunity&_spAction=addMultipleMaterials'
                + '&showHTML=0&opportunity_id=' + opportunity_id;
        var exp = {
            url: url
           ,callbackOnSuccess: function(){
                Util.closeAllDialogs();
                //Util.alert('Updated successfully..');
                var mgsalert = 'Renewal added successfully!';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 5000,
                });
                //window.location.reload(true);
                    cpm.enggCrm.opportunity.reloadRenewalPortal(opportunity_id);
            }
        };
        Util.openFormInDialog.call(this, 'addMultipleOpportunityForm', title, 1200, 500, exp);
    });
        
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
                    Util.closeAllDialogs();
                    var mgsalert = 'Line Items created successfully!';
                    var n = noty({
                        text: mgsalert,
                        type: 'confirm',
                        dismissQueue: true,
                        layout: 'topCenter',
                        theme: 'defaultTheme',
                        timeout: 5000,
                    });
                    cpm.enggCrm.opportunity.reloadQuotePortal(opportunity_id);
                }
            };
            Util.openFormInDialog.call(this, 'addMultipleLineItemForm', title, 900, 500, exp);
        });

        $(".viewQuoteLog").livequery('click', function (e){
            var opportunity_id = $(this).attr('opportunity_id');

            Util.showProgressInd();

            var url = "index.php?module=enggCrm_opportunity&_spAction=viewQuoteLog&opportunity_id="+opportunity_id+"&showHTML=0";
            var exp = {
                url: url
            };

            Util.openDialogForLink('Quote History',  900, 500, 0, exp);
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
            var descriptionObj = $(this).closest('tr').find('.lineItemDescription');
            var titleObj = $(this).closest('tr').find('.lineItemTitle');
            var quantityObj = $(this).closest('tr').find('.lineItemQuantity');
            var unitObj = $(this).closest('tr').find('.lineItemUnit');
            var amountObj = $(this).closest('tr').find('.lineItemAmount');
            var totalCostObj = $(this).closest('tr').find('.totalCost');
            var remarksObj = $(this).closest('tr').find('.lineItemRemarks');
            var unitPriceObj   = $(this).closest('tr').find('.lineItemUnitPrice');

            descriptionObj.val('');
            titleObj.val('');
            quantityObj.val('');
            unitObj.val('');
            amountObj.val('');
            totalCostObj.html('');
            remarksObj.val('');
            unitPriceObj.val('');

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
                    Util.alert(msg);
                }
            }
        });

        $('.m-enggCrm_opportunity a.deleteLineItem').livequery('click', function (e){
            msg = "Do you like to delete the Quote Line Item?";
            if (!confirm(msg)){
                return false;
            }
            else{
                Util.showProgressInd();
                var quote_items_id = $(this).attr('quote_items_id');
                var opportunity_id = $("#record_id").val();
                var url = 'index.php?module=enggCrm_opportunity&_spAction=deleteLineItem&showHTML=0&quote_items_id=' + quote_items_id;
                $.get(url, {quote_items_id: quote_items_id}, function(html){
                    Util.hideProgressInd();
                    alert ('Add Line Item Deleted Succesfully');
                    cpm.enggCrm.opportunity.reloadQuotePortal(opportunity_id);
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
                var quote_id       = $(this).attr('quote_id');
                var opportunity_id = $("#record_id").val();
                var url = 'index.php?module=enggCrm_opportunity&_spAction=deleteAddQuote&showHTML=0&quote_id=' + quote_id;
                $.get(url, {quote_id: quote_id}, function(html){
                    Util.hideProgressInd();
                    alert ('Quote Deleted Succesfully');
                    cpm.enggCrm.opportunity.reloadQuotePortal(opportunity_id);
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
                        Util.showProgressInd();
                        var url = 'index.php?module=enggCrm_opportunity&_spAction=duplicateQuote&showHTML=0&add_line_item=1';
                        $.get(url, {opportunity_id: opportunity_id, quote_id: quote_id}, function(html){
                            Util.closeAllDialogs();
                            var mgsalert2 = 'Quote & Line Items duplicated Succesfully';
                            var n = noty({
                                text: mgsalert2,
                                type: 'confirm',
                                dismissQueue: true,
                                layout: 'topCenter',
                                theme: 'defaultTheme',
                                timeout: 5000,
                            });
                            cpm.enggCrm.opportunity.reloadQuotePortal(opportunity_id);
                        });
                    },
                    "Without Item":  function() {
                        Util.showProgressInd();
                        var url = 'index.php?module=enggCrm_opportunity&_spAction=duplicateQuote&showHTML=0&add_line_item=0';
                        $.get(url, {opportunity_id: opportunity_id, quote_id: quote_id}, function(html){
                            Util.closeAllDialogs();
                            var mgsalert2 = 'Quote duplicated Succesfully';
                            var n = noty({
                                text: mgsalert2,
                                type: 'confirm',
                                dismissQueue: true,
                                layout: 'topCenter',
                                theme: 'defaultTheme',
                                timeout: 5000,
                            });
                            cpm.enggCrm.opportunity.reloadQuotePortal(opportunity_id);
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
                    Util.closeAllDialogs();
                    var mgsalert2 = 'Quote Record Created Successfully';
                    var n = noty({
                        text: mgsalert2,
                        type: 'confirm',
                        dismissQueue: true,
                        layout: 'topCenter',
                        theme: 'defaultTheme',
                        timeout: 5000,
                    });

                    cpm.enggCrm.opportunity.reloadQuotePortal(opportunity_id);
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
            var opportunity_id = $("#record_id").val();

            e.preventDefault();
            var expObj = {
                validate: true,
                callbackOnSuccess: function(){
                    Util.closeAllDialogs();
                    var mgsalert = 'Updated Successfully!';
                    var n = noty({
                        text: mgsalert,
                        type: 'confirm',
                        dismissQueue: true,
                        layout: 'topCenter',
                        theme: 'defaultTheme',
                        timeout: 5000,
                    });
                    cpm.enggCrm.opportunity.reloadQuotePortal(opportunity_id);
                }
            }
            Util.openFormInDialog.call(this, 'editForLineItem', title, 600, 350, expObj);
        });

        /* Quote Edit Portal */
        $("a.editForQuote").livequery('click', function (e){
            var title = "Edit Quote";
            var opportunity_id = $('#record_id').val();

            e.preventDefault();
            var expObj = {
                validate: true,
                callbackOnSuccess: function(quote_status){
                    Util.closeAllDialogs();
                    var mgsalert = 'Updated Quote Successfully!';
                    var n = noty({
                        text: mgsalert,
                        type: 'confirm',
                        dismissQueue: true,
                        layout: 'topCenter',
                        theme: 'defaultTheme',
                        timeout: 5000,
                    });
                    cpm.enggCrm.opportunity.reloadQuotePortal(opportunity_id);

                    if(quote_status == "Awarded") {
                        opportunityCostingSummary.reloadCostingSummaryDisplay(opportunity_id);
                    }
                }
            }
            Util.openFormInDialog.call(this, 'editForQuote', title, 800, 500, expObj);
        });

        $("a.newContactLink").livequery('click', function (e){
            //alert(urlNew);
            var company_id = $('select[name=company_id]').val();
            var url = $(this).attr('link');
            var urlNew = 'index.php?_spAction=new&lnkRoom=enggCrm_contactLink&showHTML=0&company_id=' + company_id;

            $(this).attr('link', urlNew);
            

        });

        $('select#fld_company_id').change(function() {
            var company_id = $(this).val();

            var url = 'index.php?module=enggCrm_contact&_spAction=contactByCompanyJSON&showHTML=0';
            $.get(url, {company_id: company_id}, function (data) {
                $('#fld_contact_id').cp_loadSelect(data);
            }, 'json');

            var url = $('a.newContactLink').attr('link');
            var urlNew = 'index.php?_spAction=new&lnkRoom=enggCrm_contactLink&showHTML=0&company_id=' + company_id;
            $('a.newContactLink').attr('link', urlNew);
        });

        $('.m-enggCrm_opportunity #editForQuote input[name=drawing_nos]').livequery('click', function(){
            var drawing_nos = $(this).val();

            if(drawing_nos == 1) {
                $("#editForQuote .drawingQuoteFields").removeClass('displayNone');
                $("#editForQuote .defaultQuoteFields").addClass('displayNone');
            } else {
                $("#editForQuote .drawingQuoteFields").addClass('displayNone');
                $("#editForQuote .defaultQuoteFields").removeClass('displayNone');
            }
        });

        $(".m-enggCrm_opportunity a.clearDrawingLineItem").livequery('click', function (e){
            var drawingNumberObj   = $(this).closest('tr').find('.drawingNumber');
            var drawingTitleObj    = $(this).closest('tr').find('.drawingTitle');
            var drawingRevisionObj = $(this).closest('tr').find('.drawingRevision');

            drawingNumberObj.val('');
            drawingTitleObj.val('');
            drawingRevisionObj.val('');
        });

        /* Adding row in new Line Item */
        $(".m-enggCrm_opportunity #addMultipleLineItemForm a.addDrawingRow").livequery('click', function (e){
            var project_id = $(this).attr('project_id');
            var url = 'index.php?module=enggCrm_opportunity&_spAction=addLineDrawingItemRecord'
                    + '&showHTML=0&project_id=' + project_id;

            $.get(url, '' ,function(html){
                $('#addMultipleLineItemForm tr:last').after(html);
            });
        });
    },

     reloadRenewalPortal: function(opportunity_id){
        var url = 'index.php?module=enggCrm_opportunity&_spAction=ProjectMaintenanacePortal&showHTML=0';
        Util.showProgressInd();
        $.get(url, {opportunity_id: opportunity_id}, function(html){
            Util.hideProgressInd();
            $('#addContractPortalView').html(html);
        });
    },

    reloadQuotePortal: function(opportunity_id){
        var url = 'index.php?module=enggCrm_opportunity&_spAction=addQuoteFormListView&showHTML=0';
        Util.showProgressInd();
        $.get(url, {opportunity_id: opportunity_id}, function(html){
            Util.hideProgressInd();
            $('#addLineItemPortalView').html(html);
        });
    },
}

cpm.enggCrm.opportunity.loadContactsByCompany = function(){
    var url = 'index.php?module=enggCrm_contact&_spAction=contactByCompanyJSON&showHTML=0';
    var company_id = $('select[name=company_id]').val();
    $.get(url, {company_id: company_id}, function (data) {
        $('#fld_contact_id').cp_loadSelect(data);
    }, 'json');
}

cpm.enggCrm.opportunity.loadCompany = function(){
    var url = 'index.php?module=enggCrm_opportunity&_spAction=newCompanyJSON&showHTML=0';
    $.get(url, function (data) {
        $('#fld_company_id').cp_loadSelect(data);
    }, 'json');
}

cpm.enggCrm.opportunity.afterNewCompany = function(data){
    Util.closeAllDialogs();
    cpm.enggCrm.opportunity.loadCompany();
    var mgsalert = 'New company successfully created!';
    var n = noty({
        text: mgsalert,
        type: 'confirm',
        dismissQueue: true,
        layout: 'topCenter',
        theme: 'defaultTheme',
        timeout: 5000,
    });
}

cpm.enggCrm.opportunity.afterNewContact = function(){
    Util.closeAllDialogs();
    cpm.enggCrm.opportunity.loadContactsByCompany();
    var mgsalert = 'New contact successfully created!';
    var n = noty({
        text: mgsalert,
        type: 'confirm',
        dismissQueue: true,
        layout: 'topCenter',
        theme: 'defaultTheme',
        timeout: 5000,
    });
}