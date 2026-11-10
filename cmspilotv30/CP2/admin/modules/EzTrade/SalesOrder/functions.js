Util.createCPObject('cpm.trading.salesOrder');

cpm.trading.salesOrder.init = function(){
    $('select#fld_company_id_customer').livequery('change', function() {
        var url = 'index.php?module=trading_contact&_spAction=contactByCompanyJSON&showHTML=0';
        var company_id = $(this).val();
        $.get(url, {company_id: company_id}, function (data) {
            $('#fld_contact_id_customer').cp_loadSelect(data);
        }, 'json');

        //
    });

    SO.setupEvents();

    //quote request arrows under enquiry line
    $('.callbackWrapper .showHide').live('click', function (e){
        $(this).toggleClass('arrowDown');
        var qrDiv = $(this).next();
        $(qrDiv).slideToggle('fast');
    });
}

var SO = {
    setupEvents: function(){
        //for edit portal product
        $('#fld_quantity, #buy_unit_price, #sell_unit_price, \n\
           #other_costs_1_curr, #other_costs_2_curr, #other_costs_3_curr, \n\
           #other_costs_1, #other_costs_2, #other_costs_3')
        .live('change', SO.calculateValues);

        $('#recalculate').live('click', SO.recalculate);

        $('#linkRight .suppliers-cont input.checkbox').live('click', SO.chooseSupplier);
    },

    chooseSupplier: function() {
        var url = 'index.php?module=trading_salesOrder&_spAction=chooseSupplierForProduct&showHTML=0';
        var sales_order_id = $(this).attr('id');
        var product_id = $(this).attr('product_id');
        var company_id_supplier = $(this).attr('company_id_supplier');
        var params = {
             sales_order_id: sales_order_id
            ,product_id: product_id
            ,company_id_supplier: company_id_supplier
        };
        $.get(url, params, function (data) {
        });
    },

    recalculate: function(event){
        SO.calculateValues(event, true);
    },

    calculateValues: function(event, recalculate){
        var recalculateText = '';
        if (recalculate) {
            recalculateText = '&recalculate=1';
        }

        var url = "index.php?_topRm=main&module=trading_product"
                + "&_spAction=calculatedValuesSoItems"
                + recalculateText
                + "&showHTML=0";
        var values = $("#portalForm").serialize();

        $.post(url, values, function(json) {
            $('#buy_unit_price_base').val(json.buy_unit_price_base);

            $('#t_buy_price').html(json.buy_price);
            $('#t_buy_unit_price_base').html(json.buy_unit_price_base);
            $('#t_buy_price_base').html(json.buy_price_base);

            $('#sell_unit_price_base').val(json.sell_unit_price_base);
            $('#markup').val(json.markup);

            $('#t_sell_price').html(json.sell_price);
            $('#t_sell_unit_price_base').html(json.sell_unit_price_base);
            $('#t_sell_price_base').html(json.sell_price_base);
            $('#t_markup').html(json.markup);

            $('#t_other_costs_1_base').html(json.other_costs_1_base);
            $('#t_other_costs_2_base').html(json.other_costs_2_base);
            $('#t_other_costs_3_base').html(json.other_costs_3_base);
            $('#other_costs_1_base').val(json.other_costs_1_base);
            $('#other_costs_2_base').val(json.other_costs_2_base);
            $('#other_costs_3_base').val(json.other_costs_3_base);

        }, 'json');

    },

    raiseShipmentList: function() {
        var sales_order_id = $('#record_id').val();
        var url = 'index.php?module=trading_salesOrder&_spAction=raiseShipmentListValidation&showHTML=0';
        $.getJSON(url, {sales_order_id: sales_order_id}, function (json) {
            if (json.status == 'error') {
                Util.alert(json.errorMsg);
                return;
            }

            var url = 'index.php?module=trading_salesOrder&_spAction=raiseShipmentList' +
                      '&sales_order_id=' + sales_order_id +
                      '&showHTML=0';
            var exp = {
                url: url
               ,afterOpen: function() {
                    $('#btnRaiseShipmentCancel').live('click', function() {
                        $('#dialog').dialog('destroy');
                        $('#dialog').remove();
                    });
                    $('#btnRaiseShipment').live('click', SO.raiseShipment);
               }
            };
            Util.openDialogForLink('Raise Shipment',  900, 500, 0, exp);
        });

    },

    raiseShipment: function() {
        var selector = '#raiseList input.choose, ' +
                       '#raiseList input.quantity, ' +
                       '#raiseList .quantities_to_ship';
        var data = $(selector).serialize();

        var is_product_selected = $('#raiseList input.choose:checked').length;
        var sales_order_id = $('#record_id').val();
        var url = 'index.php?module=trading_salesOrder&_spAction=raiseShipment&showHTML=0' +
                  '&sales_order_id=' + sales_order_id +
                  '&is_product_selected=' + is_product_selected;

        $.post(url, data, function (json) {
            if (json.status == 'error') {
                Util.alert(json.errorMsg);
                return;
            }
            document.location = json.returnUrl;
        }, 'json');
    },

    raiseInvoiceList: function() {
        var sales_order_id = $('#record_id').val();
        var url = 'index.php?module=trading_salesOrder&_spAction=raiseInvoiceListValidation&showHTML=0';
        $.getJSON(url, {sales_order_id: sales_order_id}, function (json) {
            if (json.status == 'error') {
                Util.alert(json.errorMsg);
                return;
            }

            var url = 'index.php?module=trading_salesOrder&_spAction=raiseInvoiceList' +
                      '&sales_order_id=' + sales_order_id +
                      '&showHTML=0';
            var exp = {
                url: url
               ,afterOpen: function() {
                    $('#btnRaiseInvoiceCancel').click(function() {
                        $('#dialog').dialog('destroy');
                        $('#dialog').remove();
                    });
                    $('#btnRaiseInvoice').click(SO.raiseInvoice);
               }
            };
            Util.openDialogForLink('Raise Invoice',  900, 500, 0, exp);
        });

    },

    raiseInvoice: function() {
        var selector = '#raiseList input.choose, ' +
                       '#raiseList input.quantity, ' +
                       '#raiseList input[name^=sell_price_to_invoice_]';
        var data = $(selector).serialize();

        var is_product_selected = $('#raiseList input.choose:checked').length;
        var sales_order_id = $('#record_id').val();
        var url = 'index.php?module=trading_salesOrder&_spAction=raiseInvoice&showHTML=0' +
                  '&sales_order_id=' + sales_order_id +
                  '&is_product_selected=' + is_product_selected;

        $.post(url, data, function (json) {
            if (json.status == 'error') {
                Util.alert(json.errorMsg);
                return;
            }
            document.location = json.returnUrl;
        }, 'json');
    },

    raisePOList: function() {
        var sales_order_id = $('#record_id').val();
        var url = 'index.php?module=trading_salesOrder&_spAction=raisePOListValidation&showHTML=0';
        $.getJSON(url, {sales_order_id: sales_order_id}, function (json) {
            if (json.status == 'error') {
                Util.alert(json.errorMsg);
                return;
            }

            var url = 'index.php?module=trading_salesOrder&_spAction=raisePOList' +
                      '&sales_order_id=' + sales_order_id +
                      '&showHTML=0';
            var exp = {
                url: url
               ,afterOpen: function() {
                    $('#btnRaisePOCancel').click(function() {
                        $('#dialog').dialog('destroy');
                        $('#dialog').remove();
                    });
                    $('#btnRaisePO').click(SO.raisePO);
                }
            };
            Util.openDialogForLink('Raise PO',  900, 500, 0, exp);

        });

    },

    raisePO: function() {
        if (!confirm("Are you sure you want to raise PO for the selected items?")){
            return;
        }

        var selector = '#raiseList input.choose, ' +
                       '#raiseList input.quantity, ' +
                       '#raiseList select[name=company_id_supplier]';
        var data = $(selector).serialize();

        var is_product_selected = $('#raiseList input.choose:checked').length;
        var sales_order_id = $('#record_id').val();
        var url = 'index.php?module=trading_salesOrder&_spAction=raisePO&showHTML=0' +
                  '&sales_order_id=' + sales_order_id +
                  '&is_product_selected=' + is_product_selected;

        $.post(url, data, function (json) {
            if (json.status == 'error') {
                Util.alert(json.errorMsg);
                return;
            }
            document.location = json.returnUrl;
        }, 'json');

    },

    duplicate: function() {
        if (!confirm("Are you sure you want to duplicate the SO?")){
            return;
        }

        var sales_order_id = $('#record_id').val();
        var url = 'index.php?module=trading_salesOrder&_spAction=duplicateSO&showHTML=0' +
                  '&sales_order_id=' + sales_order_id;

        $.post(url, function (json) {
            if (json.status == 'error') {
                Util.alert(json.errorMsg);
                return;
            }
            document.location = json.returnUrl;
        }, 'json');

    }

}