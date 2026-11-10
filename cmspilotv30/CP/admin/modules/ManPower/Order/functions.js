Util.createCPObject('cpm.manPower.order');

cpm.manPower.order = {
    init: function(){
        $('.click-all-top .check-all').livequery('click', function(e){
            e.preventDefault();
            cpm.manPower.order.checkAllCol.call(this);
        });

        $('.click-all-top .uncheck-all').livequery('click', function(e){
            e.preventDefault();
            cpm.manPower.order.uncheckAllCol.call(this);
        });

        $('.m-manPower_order input[name=cst]').livequery('click', function (e){
            cst = $(this).val();
            if(cst == 1){
                $('.m-manPower_order .cst_value').show();
                $('.m-manPower_order .cstValueNew').show();
            } else {
                $('.m-manPower_order .cst_value').hide();
                $('.m-manPower_order .cstValueNew').hide();
            }
        });

        $('.m-manPower_order select[name=mode_of_payment]').livequery('change', function (e){
            mode_of_payment = $(this).val();
            if(mode_of_payment == 'Cheque'){
                $('.m-manPower_order .chequeNoDisplay').show();
            } else {
                $('.m-manPower_order .chequeNoDisplay').hide();
            }
        });

        $('.m-manPower_order input[name=vat]').livequery('click', function (e){
            vat = $(this).val();
            if(vat == 1){
                $('.m-manPower_order .vat_value').show();
                $('.m-manPower_order .vatValueNew').show();
            } else {
                $('.m-manPower_order .vat_value').hide();
                $('.m-manPower_order .vatValueNew').hide();
            }
        });
    },

    checkAllCol: function(e){
        var colPos = $(this).parent().index();
        $('.room-order-table tbody tr').each(function(rowIndex, trObj) {
            var checkbox = $(trObj).find('td:eq(' + colPos + ') input');
            checkbox.attr('checked', 'checked');
        });
        cpm.manPower.order.updateInvoiceAmount();
    },

    uncheckAllCol: function(e){
        var colPos = $(this).parent().index();
        $('.room-order-table tbody tr').each(function(rowIndex, trObj) {
            var checkbox = $(trObj).find('td:eq(' + colPos + ') input');
            checkbox.removeAttr('checked');
        });
        $('.invoiceForm #fld_invoice_amount').html(0);
    },

    reloadInvoicePortalDisplayClient: function(order_id){
        var url = 'index.php?_topRm=finance&module=manPower_order&_spAction=invoicePortalDisplayDetail&showHTML=0';
        $.get(url, {order_id: order_id}, function(html){
            $('#invoicePortalOuterClient').html(html);
            Util.hideProgressInd();
        });
    },

    reloadInvoicePortalDisplayCandidate: function(order_id){
        var url = 'index.php?_topRm=finance&module=manPower_order&_spAction=invoicePortalDisplayDetailCandidate&showHTML=0';
        $.get(url, {order_id: order_id}, function(html){
            $('#invoicePortalOuterCandidate').html(html);
            Util.hideProgressInd();
        });
    },

    reloadInvoicePortalDisplayReferral: function(order_id){
        var url = 'index.php?_topRm=finance&module=manPower_order&_spAction=referralInvoicePortalDisplayDetail&showHTML=0';
        $.get(url, {order_id: order_id}, function(html){
            $('#invoicePortalOuterReferral').html(html);
            Util.hideProgressInd();
        });
    },

    reloadReceiptPortalDisplayClient: function(order_id){
        var url = 'index.php?_topRm=finance&module=manPower_order&_spAction=receiptPortalDisplay&showHTML=0';
        $.get(url, {order_id: order_id}, function(html){
            $('#invoicePortalOuterClient').html(html);
            Util.hideProgressInd();
        });
    },

    reloadReceiptPortalDisplayCandidate: function(order_id){
        var url = 'index.php?_topRm=finance&module=manPower_order&_spAction=receiptPortalDisplayCandidate&showHTML=0';
        $.get(url, {order_id: order_id}, function(html){
            $('#invoicePortalOuterCandidate').html(html);
            Util.hideProgressInd();
        });
    },

    updateInvoiceAmount: function(){
        var amount = parseInt(0);
        $('.room-order-table tbody tr input[type=checkbox]:checked').each(function(){
            var parent = $(this).closest('tr');
            var valueObj = $('td.sellingPrice', parent);
            if(valueObj.text() != ''){
                var qtyObj = $(this).parents('tr').find('input[id=fld_qty]');
                var qty = (qtyObj.val() != '') ? parseInt(qtyObj.val()) : parseInt(0);

                amount += Math.round(parseFloat(valueObj.text()) * qty);
            }
        });
        $('.invoiceForm #fld_invoice_amount').html(amount);

        /* $('.room-order-table tbody tr input[name=qty]').livequery('change', function(){
            Util.showProgressInd();
            var parent = $(this).closest('tr');
            order_item_id = $(this).val();
            var checked    = $(this).attr('checked') ? 'checked' : '';
            var checkedVal = checked == 'checked' ? 1 : 0;

            var qtyObj = $(this).parents('tr').find('input[name=qty]');
            var qty = qtyObj.val();*/
            /*var priceObj = $('td.sellingPrice', parent);
            var price = priceObj.text();
            var valueObj = qty * price;
            if(valueObj != ''){
                amount += valueObj;
            }*/
         /*   var url = 'index.php?_topRm=finance&module=manPower_order&_spAction=populateInvoiceAmount&showHTML=0';
            $.get(url,{order_item_id: order_item_id ,checkedVal: checkedVal, qty: qty}, function(html){
                $('.invoiceForm input[id=fld_invoice_amount]').val(html);
                Util.hideProgressInd();
            });
        });*/
    }
}

$('.m-manPower_order .actionBtnsDetail #generateInvoice').livequery('click', function (e){
    var title = "Create Invoice";
    e.preventDefault();

    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            var msg = 'Invoice created successfully';
            Util.alert(msg, function(){
                Util.closeAllDialogs();
                window.location.reload(true);
            });
        }
    }
    Util.openFormInDialog.call(this, 'portalForm', title, 600, 500, expObj);
});

$('.m-manPower_order .emp_tax_invoice').livequery('click', function (e){
    var title = "Create Invoice";
    var order_id = $(this).attr('order_id');
    e.preventDefault();

    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            var msg = 'Invoice created successfully';
            Util.alert(msg, function(){
                Util.closeAllDialogs();
                cpm.manPower.order.reloadInvoicePortalDisplayCandidate(order_id);
            });
        }
    }
    Util.openFormInDialog.call(this, 'portalEmpTaxForm', title, 468, 618, expObj);
});

$('.m-manPower_order #generateSalesReturn').livequery('click', function (e){
    var title = "Create Sales Return";
    e.preventDefault();

    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            var msg = 'Sales Return created successfully';
            Util.alert(msg, function(){
                Util.closeAllDialogs();
                window.location.reload(true);
            });
        }
    }
    Util.openFormInDialog.call(this, 'portalForm', title, 600, 500, expObj);
});

$('.m-manPower_order #editInvoice').livequery('click', function (e){
    var title = "Edit Invoice";
    e.preventDefault();

    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            var msg = 'Invoice updated successfully';
            Util.alert(msg, function(){
                Util.closeAllDialogs();
                window.location.reload(true);
            });
        }
    }
    Util.openFormInDialog.call(this, 'portalForm', title, 600, 500, expObj);
});

$('.m-manPower_order #generateReceiptClient').livequery('click', function (e){
    var title = "Create Receipt";
    e.preventDefault();

    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            var msg = 'Receipt created successfully';
            Util.alert(msg, function(){
                Util.closeAllDialogs();
                window.location.reload(true);
            });
        }
    }
    Util.openFormInDialog.call(this, 'portalForm', title, 500, 360, expObj);
});

$('.m-manPower_order #generateReceiptCandidate').livequery('click', function (e){
    var title = "Create Receipt";
    e.preventDefault();

    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            var msg = 'Receipt created successfully';
            Util.alert(msg, function(){
                Util.closeAllDialogs();
                window.location.reload(true);
            });
        }
    }
    Util.openFormInDialog.call(this, 'portalForm', title, 500, 360, expObj);
});

$('.m-manPower_order #generateReceiptReferral').livequery('click', function (e){
    var title = "Create Receipt";
    e.preventDefault();

    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            var msg = 'Receipt created successfully';
            Util.alert(msg, function(){
                Util.closeAllDialogs();
                window.location.reload(true);
            });
        }
    }
    Util.openFormInDialog.call(this, 'portalForm', title, 500, 360, expObj);
});

$('.room-order-table input.orderItemId, .room-order-table input.invoiceItemId, .room-order-table tbody tr input[id=fld_qty]').livequery('change', function (e){
    Util.showProgressInd();

    var parent = $(this).closest('tr');
    var qtyBalance = $('td.qtyBalance', parent).text();
    var qty = $('input[id=fld_qty]', parent).val();
    var cbObj = $('input.orderItemId', parent);
    var checked = cbObj.is(":checked") ? true : false;
    var cbObj1 = $('input.invoiceItemId', parent);
    var checked1 = cbObj1.is(":checked") ? true : false;
    var qty = (qty != '') ? parseInt(qty) : parseInt(0);

    if((qty == 0 && checked) || (qty == 0 && checked1)){
        Util.alert('Please enter the qty')
    } else if(qty > qtyBalance && checked){
        Util.alert('The qty should not be more than than the balance qty')
    } else {
        cpm.manPower.order.updateInvoiceAmount();
    }

    Util.hideProgressInd();
});

$('.m-manPower_order input.invoiceCode').livequery('click', function (e){
    Util.showProgressInd();
    invoice_code = $(this).val();
    var checked    = $(this).attr('checked') ? 'checked' : '';
    var checkedVal = checked == 'checked' ? 1 : 0;

    var url = 'index.php?_topRm=finance&module=manPower_order&_spAction=populateReceiptAmount&showHTML=0';
    $.get(url,{invoice_code: invoice_code ,checkedVal: checkedVal}, function(html){
        $('input[id=fld_amount]').val(html);
        Util.hideProgressInd();
    });
});

$('.m-manPower_order input#fld_hrs_Client').livequery('change', function(){
    var amount              = parseInt(0);
    var netAmount           = parseInt(0);
    var candidate_amount    = parseInt(0);
    var ss_amount           = parseInt(0);
    var med_amount          = parseInt(0);
    var commission_amount   = parseInt(0);
    var state_amount        = parseInt(0);
    var hrs                 = $('#fld_hrs_Client').val();
    var hrly_rate           = $('#fld_hourly_Rate_client').val();
    var hrly_rate_candidate = $('#fld_hourly_Rate_candidate').val();
    var commission          = $('#fld_commission_percentage_value').val();
    var fed_amount          = $('#fld_fed').val();
    var fld_soc             = $('#fld_soc').val();
    var fld_med             = $('#fld_med').val();
    var fld_state           = $('#fld_state').val();
    var fld_deductions      = $('#fld_deductions').val();
    var work_state          = $('#fld_work_state span').html();

    amount           += (parseFloat(hrs * hrly_rate));
    candidate_amount += (parseFloat(hrs * hrly_rate_candidate));

    if(commission !=''){
       commission_amount += (parseFloat(((amount - candidate_amount)*commission)/100));
       commission_amount  = commission_amount.toFixed(2);
       $('#fld_commission_amount').val(commission_amount);
    }

    if(work_state == 'Illinois'){
       state_amount = (parseFloat(candidate_amount * 0.0375));
    }

    ss_amount  = (parseFloat(candidate_amount * 0.062));
    med_amount = (parseFloat(candidate_amount * 0.0145));

    netAmount += (parseFloat(candidate_amount - fed_amount - ss_amount - med_amount - fld_state - fld_deductions));
    
    med_amount       = med_amount.toFixed(2);
    netAmount        = netAmount.toFixed(2);
    ss_amount        = ss_amount.toFixed(2);
    state_amount     = state_amount.toFixed(2);
    amount           = amount.toFixed(2);
    candidate_amount = candidate_amount.toFixed(2);

    $('#fld_invoice_Amount').val(amount);
    $('#fld_invoice_Amount_Candidate').val(candidate_amount);
    $('#fld_soc').val(ss_amount);
    $('#fld_med').val(med_amount);
    $('#fld_state').val(state_amount);
    $('#fld_net').html(netAmount);
});

$('.m-manPower_order input#fld_invoice_Amount_Candidate').livequery('change', function(){
    var netAmount           = parseInt(0);
    var commission_amount   = parseInt(0);
    var candidate_amount    = $('#fld_invoice_Amount_Candidate').val();
    var fed_amount          = $('#fld_fed').val();
    var fld_soc             = $('#fld_soc').val();
    var fld_med             = $('#fld_med').val();
    var fld_state           = $('#fld_state').val();
    var amount              = $('#fld_invoice_Amount').val();
    var commission          = $('#fld_commission_percentage_value').val();
    //var fld_FUTA          = $('#fld_FUTA').val();
    //var fld_SUTA          = $('#fld_SUTA').val();
    var fld_deductions      = $('#fld_deductions').val();

    if(commission !=''){
       commission_amount += (parseFloat(((amount - candidate_amount)*commission)/100));
       commission_amount  = commission_amount.toFixed(2);
       $('#fld_commission_amount').val(commission_amount);
    }
    //fld_FUTA - fld_SUTA - 
    netAmount += (parseFloat(candidate_amount - fed_amount - fld_soc - fld_med - fld_state - fld_deductions));
    netAmount  = netAmount.toFixed(2);
    $('#fld_net').html(netAmount);
});

$('.m-manPower_order input#fld_hourly_Rate_client').livequery('change', function(){
    var amount              = parseInt(0);
    var hrs                 = $('#fld_hrs_Client').val();
    var hrly_rate           = $('#fld_hourly_Rate_client').val();
    //alert(hrly_rate);

    amount += (parseFloat(hrs * hrly_rate));
    amount  = amount.toFixed(2);
    $('#fld_invoice_Amount').val(amount);
});

$('.m-manPower_order input#fld_hourly_Rate_candidate').livequery('change', function(){
    var candidate_amount    = parseInt(0);
    var netAmount           = parseInt(0);
    var commission_amount   = parseInt(0);
    var amount              = parseInt(0);
    var hrs                 = $('#fld_hrs_Client').val();
    var hrly_rate_candidate = $('#fld_hourly_Rate_candidate').val();
    var hrly_rate           = $('#fld_hourly_Rate_client').val();
    var fed_amount          = $('#fld_fed').val();
    var fld_soc             = $('#fld_soc').val();
    var fld_med             = $('#fld_med').val();
    var fld_state           = $('#fld_state').val();
    var commission          = $('#fld_commission_percentage_value').val();
    //var fld_FUTA          = $('#fld_FUTA').val();
    //var fld_SUTA          = $('#fld_SUTA').val();
    var fld_deductions      = $('#fld_deductions').val();

    amount           += (parseFloat(hrs * hrly_rate));
    candidate_amount += (parseFloat(hrs * hrly_rate_candidate));

    netAmount += (parseFloat(candidate_amount - fed_amount - fld_soc - fld_med - fld_state - fld_deductions));

    if(commission !=''){
       commission_amount += (parseFloat(((amount - candidate_amount)*commission)/100));
       $('#fld_commission_amount').val(commission_amount);
    }

    netAmount        = netAmount.toFixed(2);
    amount           = amount.toFixed(2);
    candidate_amount = candidate_amount.toFixed(2);
    $('#fld_invoice_Amount_Candidate').val(candidate_amount);
    $('#fld_net').html(netAmount);
});

$('.m-manPower_order input#fld_fed').livequery('change', function(){
    var netAmount        = parseInt(0);
    var candidate_amount = $('#fld_invoice_Amount_Candidate').val();
    var fed_amount       = $('#fld_fed').val();
    var fld_soc          = $('#fld_soc').val();
    var fld_med          = $('#fld_med').val();
    var fld_state        = $('#fld_state').val();
    //var fld_FUTA         = $('#fld_FUTA').val();
    //var fld_SUTA         = $('#fld_SUTA').val();
    var fld_deductions   = $('#fld_deductions').val();

    netAmount += (parseFloat(candidate_amount - fed_amount - fld_soc - fld_med - fld_state - fld_deductions));
    msg = 'Please enter fed amount less than amount for candidate!';

    if(netAmount <= 0){
        Util.alert(msg, function(){
        $('#fld_fed').val(0);
        $('#fld_fed').focus().select();
        });
    }
    else{
        if(candidate_amount != ''){
            netAmount  = netAmount.toFixed(2);
            $('#fld_net').html(netAmount);
        }
    }
});

$('.m-manPower_order input#fld_soc').livequery('change', function(){
    var netAmount        = parseInt(0);
    var candidate_amount = $('#fld_invoice_Amount_Candidate').val();
    var fed_amount       = $('#fld_fed').val();
    var fld_soc          = $('#fld_soc').val();
    var fld_med          = $('#fld_med').val();
    var fld_state        = $('#fld_state').val();
    //var fld_FUTA         = $('#fld_FUTA').val();
    //var fld_SUTA         = $('#fld_SUTA').val();
    var fld_deductions   = $('#fld_deductions').val();

    netAmount += (parseFloat(candidate_amount - fed_amount - fld_soc - fld_med - fld_state - fld_deductions));
    msg = 'Please enter soc amount less than amount for candidate!';

    if(netAmount <= 0){
        Util.alert(msg, function(){
        $('#fld_soc').val(0);
        $('#fld_soc').focus().select();
        });
    }
    else{
        if(candidate_amount != ''){
            netAmount  = netAmount.toFixed(2);
            $('#fld_net').html(netAmount);
        }
    }
});

$('.m-manPower_order input#fld_med').livequery('change', function(){
    var netAmount        = parseInt(0);
    var candidate_amount = $('#fld_invoice_Amount_Candidate').val();
    var fed_amount       = $('#fld_fed').val();
    var fld_soc          = $('#fld_soc').val();
    var fld_med          = $('#fld_med').val();
    var fld_state        = $('#fld_state').val();
    //var fld_FUTA         = $('#fld_FUTA').val();
    //var fld_SUTA         = $('#fld_SUTA').val();
    var fld_deductions   = $('#fld_deductions').val();

    netAmount += (parseFloat(candidate_amount - fed_amount - fld_soc - fld_med - fld_state - fld_deductions));
    msg = 'Please enter med amount less than amount for candidate!';

    if(netAmount <= 0){
        Util.alert(msg, function(){
        $('#fld_med').val(0);
        $('#fld_med').focus().select();
        });
    }
    else{
        if(candidate_amount != ''){
            netAmount  = netAmount.toFixed(2);
            $('#fld_net').html(netAmount);
        }
    }
});

$('.m-manPower_order input#fld_state').livequery('change', function(){
    var netAmount        = parseInt(0);
    var candidate_amount = $('#fld_invoice_Amount_Candidate').val();
    var fed_amount       = $('#fld_fed').val();
    var fld_soc          = $('#fld_soc').val();
    var fld_med          = $('#fld_med').val();
    var fld_state        = $('#fld_state').val();
    //var fld_FUTA         = $('#fld_FUTA').val();
    //var fld_SUTA         = $('#fld_SUTA').val();
    var fld_deductions   = $('#fld_deductions').val();

    netAmount += (parseFloat(candidate_amount - fed_amount - fld_soc - fld_med - fld_state - fld_deductions));
    msg = 'Please enter state amount less than amount for candidate!';

    if(netAmount <= 0){
        Util.alert(msg, function(){
        $('#fld_state').val(0);
        $('#fld_state').focus().select();
        });
    }
    else{
        if(candidate_amount != ''){
            netAmount  = netAmount.toFixed(2);
            $('#fld_net').html(netAmount);
        }
    }
});

$('.m-manPower_order input#fld_FUTA').livequery('change', function(){
    var UCS_Tax          = parseInt(0);
    var UCS_Cost         = parseInt(0);
    var candidate_amount = $('#tax_invoiceamount').val();
    var fed_amount       = $('#Fed1').val();
    var fld_soc          = $('#soc1').val();
    var fld_med          = $('#med1').val();
    var fld_state        = $('#state1').val();
    var fld_FUTA         = $('#fld_FUTA').val();
    var fld_SUTA         = $('#fld_SUTA').val();
    var UCS_fed_tax      = $('#UCS_fed1').val();

    UCS_Tax   +=  Number(UCS_fed_tax) + Number(fld_state) + Number(fld_FUTA) + Number(fld_SUTA);
    UCS_Cost  +=  Number(fld_soc) + Number(fld_med) + Number(fld_FUTA) + Number(fld_SUTA);

    $('#fld_ucs_tax').html('$'+UCS_Tax.toFixed(2));
    $('#fld_ucs_cost').html('$'+UCS_Cost.toFixed(2));
});

$('.m-manPower_order input#fld_SUTA').livequery('change', function(){
    var UCS_Tax          = parseInt(0);
    var UCS_Cost         = parseInt(0);
    var candidate_amount = $('#tax_invoiceamount').val();
    var fed_amount       = $('#Fed1').val();
    var fld_soc          = $('#soc1').val();
    var fld_med          = $('#med1').val();
    var fld_state        = $('#state1').val();
    var fld_FUTA         = $('#fld_FUTA').val();
    var fld_SUTA         = $('#fld_SUTA').val();
    var UCS_fed_tax      = $('#UCS_fed1').val();

    UCS_Tax   +=  Number(UCS_fed_tax) + Number(fld_state) + Number(fld_FUTA) + Number(fld_SUTA);
    UCS_Cost  +=  Number(fld_soc) + Number(fld_med) + Number(fld_FUTA) + Number(fld_SUTA);

    $('#fld_ucs_tax').html('$'+UCS_Tax.toFixed(2));
    $('#fld_ucs_cost').html('$'+UCS_Cost.toFixed(2));
});

$('.m-manPower_order input#fld_deductions').livequery('change', function(){
    var netAmount        = parseInt(0);
    var candidate_amount = $('#fld_invoice_Amount_Candidate').val();
    var fed_amount       = $('#fld_fed').val();
    var fld_soc          = $('#fld_soc').val();
    var fld_med          = $('#fld_med').val();
    var fld_state        = $('#fld_state').val();
    //var fld_FUTA       = $('#fld_FUTA').val();
    //var fld_SUTA       = $('#fld_SUTA').val();
    var fld_deductions   = $('#fld_deductions').val();

    netAmount += (parseFloat(candidate_amount - fed_amount - fld_soc - fld_med - fld_state - fld_deductions));
    msg = 'Please enter deductions amount less than amount for candidate!';

    if(netAmount <= 0){
        Util.alert(msg, function(){
        $('#fld_deductions').val(0);
        $('#fld_deductions').focus().select();
        });
    }
    else{
        if(candidate_amount != ''){
            netAmount    = netAmount.toFixed(2);
            $('#fld_net').html(netAmount);
        }
    }
});

$('.invoiceTypeCheckBox input[type=checkbox]').livequery('click',function(){
        var invoiceType = $(this).val();
        var cboxInvoice = $(this);

        if (!cboxInvoice.attr('checked')){
            if(invoiceType == 'Client'){

                $('#fld_hourly_Rate_client').val('');
                $('#fld_invoice_Amount').val('');
                $('#fld_client_invoice_terms').val('');
                $('#fld_client_invoice_notes').val('');
                $('.clientDisplay').addClass("clientNoDisplay");

            }else if(invoiceType == 'Candidate'){

                $('#fld_hourly_Rate_candidate').val('');
                $('#fld_invoice_Amount_Candidate').val('');
                $('#fld_candidate_invoice_terms').val('');
                $('#fld_candidate_invoice_notes').val('');
                $('#fld_fed').val('');
                $('#fld_soc').val('');
                $('#fld_med').val('');
                $('#fld_state').val('');
                $('#fld_FUTA').val('');
                $('#fld_SUTA').val('');
                $('#fld_deductions').val('');
                $('#fld_net').html('');
                $('.candidateDisplay').addClass("candidateNoDisplay");

            }else{

                $('#fld_commission_percentage_value').val('');
                $('#fld_commission_amount').val('');
                $('#fld_referral_invoice_terms').val('');
                $('#fld_referral_invoice_notes').val('');
                $('.referralDisplay').addClass("referralNoDisplay");
            }
        }
        else{
            if(invoiceType == 'Client'){
                var client_hr_rate = $('#fld_client_hourly_rate span').html();

                $('.clientDisplay').removeClass("clientNoDisplay");
                $('#fld_hourly_Rate_client').val(client_hr_rate);

            }else if(invoiceType == 'Candidate'){
                var candidate_hr_rate = $('#fld_candidate_hourly_rate span').html();

                $('.candidateDisplay').removeClass("candidateNoDisplay");
                $('#fld_hourly_Rate_candidate').val(candidate_hr_rate);
            }else{
                $('.referralDisplay').removeClass("referralNoDisplay");
            }
        }
});

$('.m-manPower_order input#fld_commission_percentage_value').livequery('change', function(){
    var commission_amount   = parseInt(0);
    var commission_percent  = $('#fld_commission_percentage_value').val();
    var order_percent       = $('#fld_commission_percentage').val();
    var hrs                 = $('#fld_hrs_Client').val();
    var amount              = $('#fld_invoice_Amount').val();
    var candidate_amount    = $('#fld_invoice_Amount_Candidate').val();

    if(hrs != ''){
        if(amount == '' || candidate_amount == ''){
            Util.alert('Please populate chargable hours');
            $('#fld_commission_percentage_value').val(order_percent);
        }else if(amount != '' || candidate_amount != ''){
            commission_amount += Math.round(parseFloat(((amount - candidate_amount)*commission_percent)/100));
            $('#fld_commission_amount').val(commission_amount);
        }
    }
    if(hrs == ''){
        Util.alert('Please populate Chargable Hours!');
        $('#fld_commission_percentage_value').val(order_percent);
    }

});

$('.m-manPower_order input#fld_invoice_start_date').livequery('change', function(){
     var start_date_candidate = $('#start_date_candidate').val();
     var end_date_candidate   = $('#end_date_candidate').val();
     var start_date_client    = $('#start_date_client').val();
     var end_date_client      = $('#end_date_client').val();
     var start_date           = $('#fld_invoice_start_date').val();
     var end_date             = $('#fld_invoice_end_date').val();
     var count_client         = $('#count_client').val();
     var count_candidate      = $('#count_candidate').val();

     var date = start_date.split("-");
     var newdate = new Date(date);

     newdate.setDate(newdate.getDate() + 14);

     var dd = ("0" + newdate.getDate()).slice(-2);
     var mm = ("0" + (newdate.getMonth() + 1)).slice(-2)
     var y  = newdate.getFullYear();

     var endDate = y + '-'+ mm + '-' + dd;

     $('#fld_invoice_end_date').val(endDate);

     if(count_client > 0){
         if(start_date_client >= start_date && start_date_client <= end_date){
            $('.m-manPower_order .chequeNoDisplay').show();
         }
         else if(end_date_client >= start_date && end_date_client <= end_date){
            $('.m-manPower_order .chequeNoDisplay').show();
         }
         else{
            $('.m-manPower_order .chequeNoDisplay').hide();
         }
     }

     if(count_candidate > 0){
         if(start_date_candidate >= start_date && start_date_candidate <= end_date){
            $('.m-manPower_order .chequeNoDisplay').show();
         }
         else if(end_date_candidate >= start_date && end_date_candidate <= end_date){
            $('.m-manPower_order .chequeNoDisplay').show();
         }
         else{
            $('.m-manPower_order .chequeNoDisplay').hide();
         }
     }
});

$('.m-manPower_order input#fld_invoice_end_date').livequery('change', function(){
     var start_date_candidate = $('#start_date_candidate').val();
     var end_date_candidate   = $('#end_date_candidate').val();
     var start_date_client    = $('#start_date_client').val();
     var end_date_client      = $('#end_date_client').val();
     var start_date           = $('#fld_invoice_start_date').val();
     var end_date             = $('#fld_invoice_end_date').val();
     var count_client         = $('#count_client').val();
     var count_candidate      = $('#count_candidate').val();

     if(count_client > 0){
         if(start_date_client >= start_date && start_date_client <= end_date){
            $('.m-manPower_order .chequeNoDisplay').show();
         }
         else if(end_date_client >= start_date && end_date_client <= end_date){
            $('.m-manPower_order .chequeNoDisplay').show();
         }
         else{
            $('.m-manPower_order .chequeNoDisplay').hide();
         }
     }

     if(count_candidate > 0){
         if(start_date_candidate >= start_date && start_date_candidate <= end_date){
            $('.m-manPower_order .chequeNoDisplay').show();
         }
         else if(end_date_candidate >= start_date && end_date_candidate <= end_date){
            $('.m-manPower_order .chequeNoDisplay').show();
         }
         else{
            $('.m-manPower_order .chequeNoDisplay').hide();
         }
     }
});

$('.cancelInvoice').livequery('click', function (e){
    var invoice_status = $(this).attr('invoice_status');

    if (invoice_status != 'Paid') {
        msg = "Do you like to cancel the Invoice?";
        if (!confirm(msg)){
            return false;
        }
        else {
            var url = 'index.php?_topRm=finance&module=manPower_order&_spAction=cancelInvoice&showHTML=0';
            Util.showProgressInd();
            var invoice_code = $(this).attr('invoice_code');
            var invoice_id = $(this).attr('invoice_id');
            var order_id = $(this).attr('order_id');
            var type = $(this).attr('type');
            $.get(url,{invoice_code: invoice_code, invoice_id:invoice_id, order_id:order_id}, function(html){

                /* Checking for one or more receipt for the invoice */
                if (html == 'Cannot cancel') {
                    Util.alert ('Cancel the related receipts and then proceed canceling the invoice');
                    Util.hideProgressInd();
                } else {
                    alert ('Invoice Cancelled Succesfully');
                    Util.hideProgressInd();
                    //window.location.reload(true);
                    if(type == 'Client'){
                        cpm.manPower.order.reloadInvoicePortalDisplayClient(order_id);
                    } else if(type == 'Candidate' || type == 'Employer Tax'){
                        cpm.manPower.order.reloadInvoicePortalDisplayCandidate(order_id);
                    }else{
                        cpm.manPower.order.reloadInvoicePortalDisplayReferral(order_id);
                    }
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
    msg = "Do you like to cancel the Receipt?";
    if (!confirm(msg)){
        return false;
    }
    else {
        var url = 'index.php?_topRm=finance&module=manPower_order&_spAction=cancelReceipt&showHTML=0';
        Util.showProgressInd();
        var receipt_code = $(this).attr('receipt_code');
        var order_id     = $(this).attr('order_id');
        var type = $(this).attr('type');
        $.get(url,{receipt_code: receipt_code, order_id:order_id}, function(html){
            alert ('Receipt Cancelled Succesfully');
            Util.hideProgressInd();
            /*if(type == 'Client'){
                cpm.manPower.order.reloadReceiptPortalDisplayClient(order_id);
            } else {
                cpm.manPower.order.reloadReceiptPortalDisplayCandidate(order_id);
            }*/
            window.location.reload(true);
        });
    }
});


$('.cancelTaxReceipt').livequery('click', function (e){
    msg = "Do you like to cancel the Tax Receipt?";
    if (!confirm(msg)){
        return false;
    }
    else {
        var url = 'index.php?_topRm=finance&module=manPower_order&_spAction=cancelTaxReceipt&showHTML=0';
        Util.showProgressInd();
        var receipt_code = $(this).attr('receipt_code');
        var order_id     = $(this).attr('order_id');
        var invoice_id   = $(this).attr('invoice_id');
        var type = $(this).attr('type');
        $.get(url,{receipt_code: receipt_code, order_id:order_id, invoice_id:invoice_id}, function(html){
            alert ('Receipt Cancelled Succesfully');
            Util.hideProgressInd();
            cpm.manPower.order.reloadInvoicePortalDisplayCandidate(order_id);
            Util.closeAllDialogs();

        });
    }
});



$('.employerTaxShow').livequery('click', function (e){
    
    var link_text = $(this).html();
    var parent = $(this).closest('.slideTaxRow');

    if(link_text == 'View'){
        $('.employerTaxShow', parent).text('Hide');
    }
    else{
        $('.employerTaxShow', parent).text('View');
    }

    $('.showemptaxrow', parent).slideToggle();
});


$('.m-manPower_order .invoice_detail_link').livequery('click', function (e){
    e.preventDefault();
    var order_id   = $(this).attr('order_id');
    var invoice_id = $(this).attr('invoice_id');
    var url = "index.php?module=manPower_order&_spAction=generateInvoiceFormDetail&invoice_id="+invoice_id+"&order_id="+order_id+"&showHTML=0";
    var exp = {
        url: url
    };

    Util.openDialogForLink('Invoice Details',  563, 420, 0, exp);
});


$('.m-manPower_order .emp_tax_detail_link').livequery('click', function (e){
    e.preventDefault();
    var order_id   = $(this).attr('order_id');
    var invoice_id = $(this).attr('invoice_id');
    var url = "index.php?module=manPower_order&_spAction=generateEmpTaxFormDetail&invoice_id="+invoice_id+"&order_id="+order_id+"&showHTML=0";
    var exp = {
        url: url
    };

    Util.openDialogForLink('Tax Details',  563, 391, 0, exp);
});


$('.m-manPower_order .makePayment').livequery('click', function (e){
    var title = "Create Receipt";
    var order_id = $(this).attr('order_id');
    e.preventDefault();

    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            var msg = 'Receipt created successfully';
            Util.alert(msg, function(){
                Util.closeAllDialogs();
                cpm.manPower.order.reloadInvoicePortalDisplayCandidate(order_id);
            });
        }
    }
    Util.openFormInDialog.call(this, 'receiptFormEmployerTax', title, 440, 340, expObj);
});

$('.m-manPower_order .receipt_detail_link').livequery('click', function (e){
    e.preventDefault();
    var order_id   = $(this).attr('order_id');
    var invoice_id = $(this).attr('invoice_id');
    var url = "index.php?module=manPower_order&_spAction=employerTaxFormDetail&invoice_id="+invoice_id+"&order_id="+order_id+"&showHTML=0";
    var exp = {
        url: url
    };

    Util.openDialogForLink('Receipt Details',  380, 260, 0, exp);
});

$('.m-manPower_order .editReceiptCandidate').livequery('click', function (e){
    var title = "Edit Receipt";
    e.preventDefault();

    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            var msg = 'Receipt Updated Successfully';
            Util.alert(msg, function(){
                Util.closeAllDialogs();
                window.location.reload(true);
            });
        }
    }
    Util.openFormInDialog.call(this, 'portalForm', title, 500, 420, expObj);
});

$('.m-manPower_order select[name=mode_of_payment_edit]').livequery('change', function (e){
    mode_of_payment = $(this).val();
    if(mode_of_payment == 'Cheque'){
        $('.m-manPower_order .editReceiptCheque').removeClass('chequeNoDisplay');
    } else {
        $('.m-manPower_order .editReceiptCheque').addClass('chequeNoDisplay');
    }
});