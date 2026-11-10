Util.createCPObject('cpm.pos.pos');

cpm.pos.pos = {
    init: function(){
        $('#pos form#posSearch').submit(function(e) {
            e.preventDefault();
            sku_no = $('#pos #fld_sku').val();
            sku_qty = $('#pos #fld_qty').val();

            var url = 'index.php?module=pos_pos&_spAction=insertOrderItem&showHTML=0';

            Util.showProgressInd();
            $.get(url, {sku_no: sku_no, sku_qty: sku_qty}, function(html){
                $('#pos #showOrderItem').html(html);
                $('#pos #fld_sku').val('');
                cpm.pos.pos.reloadOrderItems();
                cpm.pos.pos.updateTotalValues();

            });
        });

        /*$('#pos #fld_sku').change(function() {
            sku_no = $('#pos #fld_sku').val();
            var url = 'index.php?module=pos_pos&_spAction=warningMessage&showHTML=0&sku_no=' + sku_no;
            expObj = {
                url: url
               ,callbackOnSuccess: function(){
                    $('#dialog1').dialog('close');
                    $('#dialog1').dialog('destroy');
                    $('#dialog1').remove();
                    $('#pos form#posSearch').submit();
               }
            }
            Util.openFormInDialog.call(this, 'warningMessageForm', 'Warning Message', 600, 300, expObj);
        });*/

        $('#pos #fld_sku').change(function() {
            $('#pos form#posSearch').submit();
        });

        $('#pos .deleteOrderItem').livequery('click', function(){
            var order_item_id = $(this).attr('order_item_id');
            Util.showProgressInd();
            var url = "index.php?module=pos_pos&_spAction=deleteOrderItem&showHTML=0";
            $.get(url, {order_item_id: order_item_id}, function(){
                cpm.pos.pos.reloadOrderItems();
                cpm.pos.pos.updateTotalValues();
            });
        });

        $('.deleteOrderPayment').livequery('click', function(){
            var order_payment_id = $(this).attr('order_payment_id');
            Util.showProgressInd();
            var url = "index.php?module=pos_pos&_spAction=deleteOrderPayment&showHTML=0";
            $.get(url, {order_payment_id: order_payment_id}, function(){
                cpm.pos.pos.reloadOrderPayments();
                cpm.pos.pos.reloadOrderBalancePayments();
            });
        });

        $('#invoicePaymentRow .cash_editOrderPayment').livequery('click', function (e){
            var title = "Edit Invoice Payment";
            e.preventDefault();
            var expObj = {
                 submitBtnText: 'OK'
                ,callbackOnSuccess: function(){
                    $('#dialog1').dialog('close');
                    $('#dialog1').dialog('destroy');
                    $('#dialog1').remove();
                    cpm.pos.pos.reloadOrderPayments();
                    cpm.pos.pos.reloadOrderBalancePayments();
                    cpm.pos.pos.reloadPaymentMethods();
                }
                ,buttons: [
                {
                     click: function() {
                        var url = 'index.php?module=pos_pos&_spAction=clearAmount&showHTML=0';
                        $.get(url, function(html){
                            $('#cashPaid #fld_paid_amount').val('');
                        });
                     }
                    ,'class': 'btn-cancel'
                    ,text: 'Clear Amount'
                },
                {
                     click: function() {
                        $('#dialog1').dialog('close');
                        $('#dialog1').dialog('destroy');
                        $('#dialog1').remove();
                     }
                    ,'class': 'btn-cancel'
                    ,text: 'Cancel'
                },
                {
                     click: function() {
                         $('#editPayByCashForm').submit();
                     }
                    ,'class': 'btn-submit'
                    ,text: 'OK'
                }
                ]
            }
            Util.openFormInDialog.call(this, 'editPayByCashForm', title, 800, 400, expObj);
        });

        $('#invoicePaymentRow .creditCard_editOrderPayment').livequery('click', function (e){
            var title = "Edit Invoice Payment";
            e.preventDefault();
            var expObj = {
                 submitBtnText: 'OK'
                ,callbackOnSuccess: function(){
                    $('#dialog1').dialog('close');
                    $('#dialog1').dialog('destroy');
                    $('#dialog1').remove();
                    cpm.pos.pos.reloadOrderPayments();
                    cpm.pos.pos.reloadOrderBalancePayments();
                    cpm.pos.pos.reloadPaymentMethods();
                }
                ,buttons: [
                {
                     click: function() {
                        var url = 'index.php?module=pos_pos&_spAction=clearAmount&showHTML=0';
                        $.get(url, function(html){
                            $('#editPayByCreditCardForm #fld_card_amount').val('');
                        });
                     }
                    ,'class': 'btn-cancel'
                    ,text: 'Clear Amount'
                },
                {
                     click: function() {
                        $('#dialog1').dialog('close');
                        $('#dialog1').dialog('destroy');
                        $('#dialog1').remove();
                     }
                    ,'class': 'btn-cancel'
                    ,text: 'Cancel'
                },
                {
                     click: function() {
                         $('#editPayByCreditCardForm').submit();
                     }
                    ,'class': 'btn-submit'
                    ,text: 'OK'
                }
                ]
            }
            Util.openFormInDialog.call(this, 'editPayByCreditCardForm', title, 800, 400, expObj);
        });

        $('#pos .qtyOrderItem').livequery('change', function(){
            var qty = $(this).val();
            var unit_price = $('#pos .unitPrice').closest('td').html();
            var order_item_id = $(this).attr('order_item_id');
            var url = 'index.php?module=pos_pos&_spAction=updateQtyOrderItem&showHTML=0';
            $.get(url, {unit_price: unit_price, qty: qty, order_item_id: order_item_id}, function(html){
                //$('#pos .orderItemtotal').html(html);
                cpm.pos.pos.reloadOrderItems();
                cpm.pos.pos.updateTotalValues();
            });
        });

        $('#pos .discountOrderItem').livequery('change', function(){
            var discount = $(this).val();
            var order_item_id = $(this).attr('order_item_id');
            var url = 'index.php?module=pos_pos&_spAction=updateDiscountOrderItem&showHTML=0';
            $.get(url, {discount: discount, order_item_id: order_item_id}, function(html){
                //cpm.pos.pos.discountDigitsValidation();
                cpm.pos.pos.reloadOrderItems();
                cpm.pos.pos.updateTotalValues();
                if(html != 'Yes'){
                    alert('The number of digits entered exceed the limit');
                    //$('#pos #discount').val(html);
                }
            });
        });

        $('select[name=discount_type]').livequery('change', function(){
            var parent = $(this).closest('tr');
            var discountObj = $(this).val();
            var order_item_id = $(parent).attr('order_item_id');
            var url = 'index.php?module=pos_pos&_spAction=updateDiscountTypeOrderItem&showHTML=0';
            $.get(url, {discountObj: discountObj, order_item_id: order_item_id}, function(html){
                cpm.pos.pos.updateTotalValues();
            });
        });

        $('#pos .unitPriceOrderItem').livequery('change', function(){
            var unit_price = $(this).val();
            var order_item_id = $(this).attr('order_item_id');
            var url = 'index.php?module=pos_pos&_spAction=updateUnitPriceOrderItem&showHTML=0';
            $.get(url, {unit_price: unit_price, order_item_id: order_item_id}, function(html){
                //cpm.pos.pos.unitPriceDigitsValidation();
                cpm.pos.pos.reloadOrderItems();
                cpm.pos.pos.updateTotalValues();
                if(html != 'Yes'){
                    alert('The number of digits entered exceed the limit');
                }
            });
        });

        $('#pos #overallDiscount').livequery('change', function(){
            var overall_discount = $(this).val();
            var order_id = $(this).attr('order_id');
            var url = 'index.php?module=pos_pos&_spAction=updateOverallDiscountOrder&showHTML=0';
            $.get(url, {overall_discount: overall_discount, order_id: order_id}, function(html){
                $('#invoiceOverallDiscount').show();
                cpm.pos.pos.updateTotalValues();
                cpm.pos.pos.overallItemDiscDigitsValidation();
            });
        });

        $('#invoicePayment').livequery('click', function(e){
            var title = "Invoice Payment";
            invoice_no = $('#pos #fld_invoice_no').val();
            e.preventDefault();
            /*var url = 'index.php?module=pos_pos&_spAction=invoiceNumber&showHTML=0';
            $.get(url, {invoice_no: invoice_no}, function(html){
            });*/
            var exp = {
                'beforeCloseFn': function(){
                    if (invoice_no == ''){
                        invoice_no = $('#pos #fld_invoice_no').html();
                    }
                    Util.alert(invoice_no);
                    $('#jquery_jplayer').jPlayer("setMedia", { // Set the media
                        mp3: "/sounds/ding.mp3"
                    }).jPlayer("play"); // Attempt to auto play the media
                }
            }
            Util.openDialogForLink.call(this, title, 1050, 475, true, exp);
        });

        $('#invoicePaymentCash').livequery('click', function(e) {
            e.preventDefault();
            var exp = {
                 submitBtnText: 'OK'
                ,callbackOnSuccess: function(){
                    $('#dialog1').dialog('close');
                    $('#dialog1').dialog('destroy');
                    $('#dialog1').remove();
                    cpm.pos.pos.reloadOrderPayments();
                    cpm.pos.pos.reloadOrderBalancePayments();
                    cpm.pos.pos.reloadPaymentMethods();
                }
                ,buttons: [
                {
                     click: function() {
                        var url = 'index.php?module=pos_pos&_spAction=clearAmount&showHTML=0';
                        $.get(url, function(html){
                            $('#cashPaid #fld_paid_amount').val('');
                        });
                     }
                    ,'class': 'btn-cancel'
                    ,text: 'Clear Amount'
                },
                {
                     click: function() {
                        $('#dialog1').dialog('close');
                        $('#dialog1').dialog('destroy');
                        $('#dialog1').remove();
                     }
                    ,'class': 'btn-cancel'
                    ,text: 'Cancel'
                },
                {
                     click: function() {
                         $('#payByCashForm').submit();
                     }
                    ,'class': 'btn-submit'
                    ,text: 'OK'
                }
                ]
            }
            Util.openFormInDialog.call(this, 'payByCashForm', 'Pay by Cash', 800, 400, exp);
        });

        $('.secondaryAuthorization').livequery('click', function(e) {
            e.preventDefault();
            var exp = {
                 submitBtnText: 'Yes'
                ,cancelBtnText: 'No'
                ,callbackOnSuccess: function(){
                    $('#dialog').dialog('close');
                    $('#dialog').dialog('destroy');
                    $('#dialog').remove();
                    cpm.pos.pos.reloadOrderItems();
                }
            }
            Util.openFormInDialog.call(this, 'secondaryAuthorizationForm', 'Secondary Authorization', 400, 400, exp);
        });

        $('.secondaryAuthorizationOverall').livequery('click', function(e) {
            e.preventDefault();
            var exp = {
                 submitBtnText: 'Yes'
                ,cancelBtnText: 'No'
                ,callbackOnSuccess: function(){
                    $('#dialog').dialog('close');
                    $('#dialog').dialog('destroy');
                    $('#dialog').remove();
                    cpm.pos.pos.reloadOrderItems();
                }
            }
            Util.openFormInDialog.call(this, 'secondaryAuthorizationOverallForm', 'Secondary Authorization', 400, 400, exp);
        });

        $('#cashValue li a').livequery('click', function(){
            var cash = $(this).attr('cvalue');
            var url = 'index.php?module=pos_pos&_spAction=updatePaidAmount&showHTML=0';
            $.get(url, {cash: cash}, function(data){
                $('#fld_paid_amount').val(data);
            });
        });

        $('#invoicePaymentCreditCard').livequery('click', function(e) {
            e.preventDefault();
            var exp = {
                 submitBtnText: 'OK'
                ,callbackOnSuccess: function(){
                    $('#dialog1').dialog('close');
                    $('#dialog1').dialog('destroy');
                    $('#dialog1').remove();
                    cpm.pos.pos.reloadOrderPayments();
                    cpm.pos.pos.reloadOrderBalancePayments();
                    cpm.pos.pos.reloadPaymentMethods();
                }
                ,buttons: [
                {
                     click: function() {
                        var url = 'index.php?module=pos_pos&_spAction=clearAmount&showHTML=0';
                        $.get(url, function(html){
                            $('#payByCreditCardForm #fld_card_amount').val('');
                        });
                     }
                    ,'class': 'btn-cancel'
                    ,text: 'Clear Amount'
                },
                {
                     click: function() {
                        $('#dialog1').dialog('close');
                        $('#dialog1').dialog('destroy');
                        $('#dialog1').remove();
                     }
                    ,'class': 'btn-cancel'
                    ,text: 'Cancel'
                },
                {
                     click: function() {
                         $('#payByCreditCardForm').submit();
                     }
                    ,'class': 'btn-submit'
                    ,text: 'OK'
                }
                ]
            }
            Util.openFormInDialog.call(this, 'payByCreditCardForm', 'Pay by Credit Card', 800, 400, exp);
        });

        $('#clearInvoice').livequery('click', function (){
            var url = 'index.php?module=pos_pos&_spAction=clearOrder&showHTML=0';
            $.get(url, function(html){
                Util.alert('Your invoice has been created. New invoice no. ' + html);
                window.location.reload(true);
            });
        });

        $('#newInvoice').livequery('click', function (){
            var url = 'index.php?module=pos_pos&_spAction=clearOrder&showHTML=0';
            $.get(url, function(html){
                window.location.reload(true);
            });
        });

        $('input.pos_contactLink').livequery('click', function(){
            var member_code = $(this).attr('recid');
            var url = 'index.php?module=pos_pos&_spAction=populateMemberCode&showHTML=0';
            $.get(url, {member_code: member_code}, function(html){
                $('#fld_member_name').html(html);
            });
        });

        $('.disableState').livequery('click', function(){
            Util.alert('Number of payment method exceeded');
        });

       /* $('#pos #unit_price').change(function() {
            var unit_price = $('#pos #unit_price').val();
            var url = "index.php?module=pos_pos&_spAction=unitPriceValidate&showHTML=0";
            $.get(url, {unit_price: unit_price}, function(data){
                if(data != 'Yes'){
                    alert('The number of digits entered exceed the limit');
                    $('#pos #unit_price').val(data);
                }
            });
        });

        $('#pos #discount').change(function() {
            var discount = $('#pos #discount').val();
            var url = "index.php?module=pos_pos&_spAction=itemDiscountValidate&showHTML=0";
            $.get(url, {discount: discount}, function(data){
                if(data != 'Yes'){
                    alert('The number of digits entered exceed the limit');
                    $('#pos #discount').val(data);
                }
            });
        });

        $('#pos #overall_item_discount').change(function() {
            var overall_item_discount = $('#pos #overall_item_discount').val();
            var url = "index.php?module=pos_pos&_spAction=invoiceDiscountValidate&showHTML=0";
            $.get(url, {overall_item_discount: overall_item_discount}, function(data){
                alert(data);
            });
        }); */

        if ($("#jquery_jplayer").length > 0){
            var jssPath = $('#jssPath').val();
        	$("#jquery_jplayer").jPlayer({
                 swfPath: jssPath + "jquery/jPlayer-2.2.0/"
                ,preload: 'auto'
                ,backgroundColor: '#ffffff'
                ,errorAlerts: true
        		,ready: function () {
        		}
        	})
        }

    },

    reloadOrderItems: function(){
        var url = 'index.php?module=pos_pos&_spAction=orderItems&showHTML=0';
        $.get(url,  function(html){
            $('#pos #orderItems').html(html);
            Util.hideProgressInd();
        });
    },

    reloadOrderPayments: function(){
        var url = 'index.php?module=pos_pos&_spAction=invoicePaymentDetails&showHTML=0';
        $.get(url,  function(html){
            $('#invoicePaymentDetails').html(html);
            Util.hideProgressInd();
        });
    },

    updateTotalValues: function(){
        var url = 'index.php?module=pos_pos&_spAction=totalValues&showHTML=0';
        $.get(url, function(json){
            $('#pos #orderSubTotal').html(json.subTotal);
            $('#pos #orderDiscAmount').html(json.discTotal);
            $('#pos #orderActualAmount').html(json.actualTotal);
            $('#pos #orderNetTotal').html(json.netTotal);
            $('#pos #overallDiscountAmount').html(json.overallDiscount);
        });
    },

    reloadOrderBalancePayments: function(){
        var url = 'index.php?module=pos_pos&_spAction=orderPayments&showHTML=0';
        $.get(url,  function(html){
            $('#orderBalancePayments').html(html);
            Util.hideProgressInd();
        });
    },

    unitPriceDigitsValidation: function(){
        var unit_price = $('#pos #unit_price').val();
        var url = "index.php?module=pos_pos&_spAction=unitPriceValidate&showHTML=0";
        $.get(url, {unit_price: unit_price}, function(data){
            if(data != 'Yes'){
                alert('The number of digits entered exceed the limit');
                var parent = $(this).closest('tr');
                var unit_price = $(parent).attr('unit_price');
                $('#pos #unit_price').html(data);
            }
        });
    },

    discountDigitsValidation: function(){
        var discount = $('#pos #discount').val();
        var url = "index.php?module=pos_pos&_spAction=itemDiscountValidate&showHTML=0";
        $.get(url, {discount: discount}, function(data){
            if(data != 'Yes'){
                alert('The number of digits entered exceed the limit');
                $('#pos #discount').val(data);
            }
        });
    },

    overallItemDiscDigitsValidation: function(){
        var overall_item_discount = $('#pos #overall_item_discount').val();
        var url = "index.php?module=pos_pos&_spAction=invoiceDiscountValidate&showHTML=0";
        $.get(url, {overall_item_discount: overall_item_discount}, function(data){
            if(data != 'Yes'){
                alert('The number of digits entered exceed the limit');
                $('#pos #discount').val(data);
            }
        });
    },

    reloadPaymentMethods: function(){
        var url = 'index.php?module=pos_pos&_spAction=paymentMethods&showHTML=0';
        $.get(url,  function(html){
            $('#paymentMethods').html(html);
            Util.hideProgressInd();
        });
    }
}

