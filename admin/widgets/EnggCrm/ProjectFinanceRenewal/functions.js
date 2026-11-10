$(function(){

	  $('#addFinanceProjectsRenewal').livequery('click', function(){

        var renewal_id = $("#record_id").val();
        msg = "Do you like to Add Finance Order?";

        if (!confirm(msg)){
            return false;
        }
        else{
            Util.showProgressInd();
            var renewal_id = $(this).attr('renewal_id');
                        var quote_id = $(this).attr('quote_id');


            var url = 'index.php?widget=enggCrm_projectFinanceRenewal&_spAction=GenerateOrderRecords&showHTML=0&renewal_id=' + renewal_id + '&quote_id=' + quote_id;

            $.get(url, {renewal_id: renewal_id,quote_id: quote_id}, function(html){
                //alert('Quote Record Created Successfully');
                var mgsalert = 'Finance order record created successfully!';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 5000,
                });
                //$('#addQuoteProject').hide();
                window.location.reload(true);
                //projectFinance.reloadQuotePortal(renewal_id);
            });
            //Util.hideProgressInd();
        }
    });

    $('.m-enggCrm_renewal .cancelReceipt2').livequery('click', function (e){
        msg = "Do you like to cancel the Receipt?";
        if (!confirm(msg)){
            return false;
        }
        else {
            var url = 'index.php?_topRm=finance&module=enggCrm_order&_spAction=cancelReceipt&showHTML=0';
            Util.showProgressInd();
            var receipt_id = $(this).attr('receipt_id');
            $.get(url,{receipt_id: receipt_id}, function(html){
                alert ('Receipt Cancelled Succesfully');
                Util.hideProgressInd();
                window.location.reload(true);
            });
        }
    });


    $('.m-enggCrm_renewal .cancelInvoice2').livequery('click', function (e){
        var invoice_status = $(this).attr('invoice_status');
    
        if (invoice_status != 'Paid') {
            msg = "Do you want to cancel the Invoice?";
            if (!confirm(msg)){
                return false;
            }
            else {
                var url = 'index.php?_topRm=finance&module=enggCrm_order&_spAction=cancelInvoice&showHTML=0';
                Util.showProgressInd();
                var invoice_code = $(this).attr('invoice_code');
                var invoice_id = $(this).attr('invoice_id');
                $.get(url,{invoice_code: invoice_code, invoice_id:invoice_id}, function(html){
    
                    /* Checking for one or more receipt for the invoice */
                    if (html == 'Cannot cancel') {
                        Util.alert ('Cancel the related receipts and then proceed canceling the invoice');
                        Util.hideProgressInd();
                    } else {
                        alert ('Invoice Cancelled Succesfully');
                        Util.hideProgressInd();
                        window.location.reload(true);
                    }
                });
            }
        } else {
            msg = "Please cancel the receipt and then try canceling the Invoice";
            if (!confirm(msg)){
                return false;
            } else {
                return false;
            }
        }
    });
    $("#editInvoicePortalForm .invoiceQuantity").livequery('change', function (e){
        var quantity = parseFloat($(this).val());
        var unitPrice = parseFloat($(this).closest('tr').find('.invoiceUnitPrice').val());
        var totalCostObj = $(this).closest('tr').find('.invoiceAmount');
        
        if (!isNaN(quantity) && !isNaN(unitPrice) && quantity >= 0 && unitPrice >= 0) {
            var totalCost = quantity * unitPrice;
            var totalCostFormatted = totalCost.toFixed(2); // Ensure two decimal places
            totalCostObj.val(totalCostFormatted); // Assuming invoiceAmount is an input field
        }
    });
    
    $("#editInvoicePortalForm .invoiceUnitPrice").livequery('change', function (e){
        var unitPrice = parseFloat($(this).val());
        var quantity = parseFloat($(this).closest('tr').find('.invoiceQuantity').val());
        var totalCostObj = $(this).closest('tr').find('.invoiceAmount');
        
        if (!isNaN(quantity) && !isNaN(unitPrice) && quantity >= 0 && unitPrice >= 0) {
            var totalCost = quantity * unitPrice;
            var totalCostFormatted = totalCost.toFixed(2); // Ensure two decimal places
            totalCostObj.val(totalCostFormatted); // Assuming invoiceAmount is an input field
        }
    });
    

    $('.m-enggCrm_renewal .editInvoice2').livequery('click', function (e){
        var title    = "Edit Invoice2";
        var order_id = $(this).attr('order_id');
        
        e.preventDefault();

        var expObj = {
            validate: true
           ,callbackOnSuccess: function(){
                var msg = 'Invoice updated successfully';
                Util.alert(msg, function(){
                    Util.closeAllDialogs();
                    projectFinanceRenewal.reloadInvoicePortalDisplay(order_id);
                    // window.location.reload(true);
                });
            }
        }
        Util.openFormInDialog.call(this, 'editInvoicePortalForm', title, 700, 500, expObj);
    });

    $('.m-enggCrm_renewal input.invoiceCode').livequery('click', function (e){
        Util.showProgressInd();
        invoice_code = $(this).val();
        var checked    = $(this).attr('checked') ? 'checked' : '';
        var checkedVal = checked == 'checked' ? 1 : 0;
        var order_id   = $(this).attr('order_id');

        var url = 'index.php?_topRm=finance&module=enggCrm_receipt&_spAction=populateReceiptAmount&showHTML=0';
        $.get(url,{invoice_code: invoice_code ,checkedVal: checkedVal, order_id: order_id}, function(html){
            $('input[id=fld_amount]').val(html);
            Util.hideProgressInd();
        });
    });

        /* Adding multiple rows in invoice */
        $(".m-enggCrm_renewal a.generateInvoiceRenewal").livequery('click', function (e){
            var title = "Generate Invoice1";
            var order_id    = $(this).attr('order_id');
            var record_type = $(this).attr('record_type');
            var url = 'index.php?module=enggCrm_invoice&_spAction=generateInvoiceForm'
                    + '&showHTML=0&order_id='+order_id+'&record_type='+record_type;
            var exp = {
                url: url
               ,callbackOnSuccess: function(){
                    Util.closeAllDialogs();

                    var mgsalert = 'Invoice created successfully!';
                    var n = noty({
                        text: mgsalert,
                        type: 'confirm',
                        dismissQueue: true,
                        layout: 'topCenter',
                        theme: 'defaultTheme',
                        timeout: 5000,
                    });
                    
                    projectFinanceRenewal.reloadInvoicePortalDisplay(order_id);
                }
            };

            Util.openFormInDialog.call(this, 'generateInvoiceForm', title, 900, 530, exp);
        });

        $("a.generateReceiptRenewal").livequery('click', function (e){
            var title = "Generate Receipt";
            e.preventDefault();

            $("select[name='mode_of_payment']").livequery('change', function (e){
                projectFinanceRenewal.populatePaymentMode.call(this);
            });

            var order_id = $(this).attr('order_id');
            var url = 'index.php?widget=enggCrm_projectFinanceRenewal&_spAction=generateReceiptFormRenewal'
                    + '&showHTML=0&order_id=' + order_id;
            var exp = {
                url: url
               ,callbackOnSuccess: function(){
                    Util.closeAllDialogs();

                    var mgsalert = 'Receipt created successfully!';
                    var n = noty({
                        text: mgsalert,
                        type: 'confirm',
                        dismissQueue: true,
                        layout: 'topCenter',
                        theme: 'defaultTheme',
                        timeout: 5000,
                    });

                    projectFinanceRenewal.reloadReceiptPortalDisplay(order_id);
                    projectFinanceRenewal.reloadInvoicePortalDisplay(order_id);
                }
            };

            Util.openFormInDialog.call(this, 'portalForm', title, 700, 500, exp);
        });

        /* Adding row in invoice */
        $("#generateInvoiceForm a.addRow").livequery('click', function (e){
            var url = 'index.php?module=enggCrm_invoice&_spAction=addInvoiceItemRecord'
                    + '&showHTML=0';

            $.get(url, '', function(html){
                $('#generateInvoiceForm table.thinlist tr:last').after(html);
            });
        });

        $("#generateInvoiceForm .invoiceItemQuantity").livequery('change', function (e){
            //projectFinanceRenewal.triggerCalcForQuantity.call(this);
            var discount     = $("#generateInvoiceForm input[name=discount]").val();
            var quantity     = $(this).val();
            var amountObj    = $(this).closest('tr').find('.invoiceItemUnitPrice');
            var amount       = amountObj.val();
            var totalCostObj = $(this).closest('tr').find('.invoiceItemAmount');

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
            var total_Price = document.getElementsByClassName('invoiceItemAmount');
            for (var i = 0; i < total_Price.length; ++i) {
                if (!isNaN(parseInt(total_Price[i].value)) ){
                    total_amount += parseInt(total_Price[i].value);
                }
            }

            if(total_amount == "NaN") {
                total_amount = parseInt(0);
            }

            if(parseFloat(discount) > 0) {
                total_amount = parseFloat(total_amount) - parseFloat(discount);
            }

            $('.totalInvoiceAmountLabel .totalInvoiceAmount').html(total_amount.toFixed(3));
        });

        $("#generateInvoiceForm .invoiceItemAmount").livequery('change', function (e){
            var total_amount = 0;
            var total_Price = document.getElementsByClassName('invoiceItemAmount');
            for (var i = 0; i < total_Price.length; ++i) {
                if (!isNaN(parseInt(total_Price[i].value)) ){
                    total_amount += parseInt(total_Price[i].value);
                }
            }

            if(total_amount == "NaN") {
                total_amount = parseInt(0);
            }

            var discount = $("#generateInvoiceForm input[name=discount]").val();
            if(parseFloat(discount) > 0) {
                total_amount = parseFloat(total_amount) - parseFloat(discount);
            }

            $('.totalInvoiceAmountLabel .totalInvoiceAmount').html(total_amount.toFixed(3));
        });

        $("#generateInvoiceForm .invoiceItemUnitPrice").livequery('change', function (e){
            var amount       = $(this).val();
            var quantityObj  = $(this).closest('tr').find('.invoiceItemQuantity');
            var quantity     = quantityObj.val();
            var totalCostObj = $(this).closest('tr').find('.invoiceItemAmount');

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
            var total_Price = document.getElementsByClassName('invoiceItemAmount');
            for (var i = 0; i < total_Price.length; ++i) {
                if (!isNaN(parseInt(total_Price[i].value)) ){
                    total_amount += parseInt(total_Price[i].value);
                }
            }

            if(total_amount == "NaN") {
                total_amount = parseInt(0);
            }

            var discount = $("#generateInvoiceForm input[name=discount]").val();
            if(parseFloat(discount) > 0) {
                total_amount = parseFloat(total_amount) - parseFloat(discount);
            }

            $('.totalInvoiceAmountLabel .totalInvoiceAmount').html(total_amount.toFixed(3));
        });

        $("a.clearInvoiceItemEdit").livequery('click', function (e){
            var titleObj       = $(this).closest('tr').find('#title');
            var descriptionObj = $(this).closest('tr').find('#description');

            titleObj.val('');
            descriptionObj.val('');
        });

        $("a.clearInvoiceItem").livequery('click', function (e){
            var titleObj       = $(this).closest('tr').find('.invoiceItemTitleFull');
            var quantityObj    = $(this).closest('tr').find('.invoiceItemQuantity');
            var unitObj        = $(this).closest('tr').find('.invoiceItemUnit');
            var amountObj      = $(this).closest('tr').find('.invoiceItemAmount');
            var unitPriceObj   = $(this).closest('tr').find('.invoiceItemUnitPrice');
            //var totalCostObj   = $(this).closest('tr').find('.totalCost');
            var descriptionObj = $(this).closest('tr').find('.invoiceItemDescription');
            //var remarksObj = $(this).closest('tr').find('.invoiceItemRemarks');

            titleObj.val('');
            quantityObj.val('');
            unitObj.val('');
            amountObj.val('');
            unitPriceObj.val('');
            descriptionObj.val('');
            //remarksObj.val('');

            var total_Price  = document.getElementsByClassName('invoiceItemAmount');
            var total_amount = 0;
            for (var i = 0; i < total_Price.length; ++i) {
                if (!isNaN(parseInt(total_Price[i].value)) ){
                    total_amount += parseInt(total_Price[i].value);
                }
            }

            if(total_amount == "NaN") {
                total_amount = parseInt(0);
            }

            var discount = $("#generateInvoiceForm input[name=discount]").val();
            if(parseFloat(discount) > 0) {
                total_amount = parseFloat(total_amount) - parseFloat(discount);
            }

            $('.totalInvoiceAmountLabel .totalInvoiceAmount').html(total_amount.toFixed(3));
        });

         $('.showHideCancelledInvoice1').livequery('click', function (e){
            var link_text = $(this).html();

            if(link_text == '(+) Click to Hide Cancelled Invoice(s)'){
                $('.showHideCancelledInvoice1').text('(-) Click to View Cancelled Invoice(s)');
            }
            else{
                $('.showHideCancelledInvoice1').text('(+) Click to Hide Cancelled Invoice(s)');
            }

            $('.cancelledInvoiceTableOrder').slideToggle();
        });


      

          $('.cancelInvoice').livequery('click', function (e){
            var invoice_status = $(this).attr('invoice_status');
            var order_id = $(this).attr('order_id');

            if (invoice_status != 'Paid') {
                msg = "Do you want to cancel the Invoice?";
                if (!confirm(msg)){
                    return false;
                }
                else {
                    var url = 'index.php?_topRm=finance&module=enggCrm_order&_spAction=cancelInvoice&showHTML=0';
                    Util.showProgressInd();
                    var invoice_code = $(this).attr('invoice_code');
                    var invoice_id = $(this).attr('invoice_id');
                    $.get(url,{invoice_code: invoice_code, invoice_id:invoice_id}, function(html){

                        /* Checking for one or more receipt for the invoice */
                        if (html == 'Cannot cancel') {
                            Util.alert ('Cancel the related receipts and then proceed canceling the invoice');
                            Util.hideProgressInd();
                        } else {
                            alert ('Invoice Cancelled Succesfully');
                            Util.hideProgressInd();
                            projectFinanceRenewal.reloadInvoicePortalDisplay(order_id);
                        }
                    });
                }
            } else {
                msg = "Please cancel the receipt and then try canceling the Invoice";
                if (!confirm(msg)){
                    return false;
                } else {
                    return false;
                }
            }
        });

        $('.cancelReceipt').livequery('click', function (e){
            var order_id = $(this).attr('order_id');
            var msg = "Do you like to cancel the Receipt?";
            if (!confirm(msg)){
                return false;
            }
            else {
                var url = 'index.php?_topRm=finance&module=enggCrm_order&_spAction=cancelReceipt&showHTML=0';
                Util.showProgressInd();
                var receipt_id = $(this).attr('receipt_id');
                $.get(url,{receipt_id: receipt_id}, function(html){
                    alert ('Receipt Cancelled Succesfully');
                    Util.hideProgressInd();
                    projectFinanceRenewal.reloadReceiptPortalDisplay(order_id);
                    projectFinanceRenewal.reloadInvoicePortalDisplay(order_id);
                });
            }
        });


         

        $('.addMoreDetailsInvoiceRow').livequery('click', function (e){
            var link_text = $(this).html();

            if(link_text == '(+) Add More Details'){
                $('.addMoreDetailsInvoiceRow').text('(-) Hide More Details');
            }
            else{
                $('.addMoreDetailsInvoiceRow').text('(+) Add More Details');
            }

            $('.hideMoreInvoiceDetails').slideToggle();
        });


});

var projectFinanceRenewal = {

	 reloadQuotePortal: function(renewal_id){
        var url = 'index.php?widget=enggCrm_projectFinanceRenewal&_spAction=InvoiceReceiptPortalDetails&showHTML=0';

        Util.showProgressInd();
        $.get(url, {renewal_id: renewal_id}, function(html){
            Util.hideProgressInd();
            $('#invoiceReceiptPortalDisplayDiv').html(html);
        });
    },

   reloadInvoicePortalDisplay: function(order_id){
    var url = 'index.php?_topRm=finance&module=enggCrm_order&_spAction=invoicePortalDisplay&showHTML=0';
    Util.showProgressInd();
    $.get(url,{order_id: order_id}, function(html){
        $('.invoicePortalDisplayDiv').html(html);
        Util.hideProgressInd();
    });
},
    
reloadReceiptPortalDisplay: function(order_id){
    var url = 'index.php?_topRm=finance&module=enggCrm_order&_spAction=receiptPortalDisplay&showHTML=0';
    Util.showProgressInd();
    $.get(url,{order_id: order_id}, function(html){
        $('.receiptPortalDisplayDiv').html(html);
        Util.hideProgressInd();
    });
}

}