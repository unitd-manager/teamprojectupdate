Util.createCPObject('cpm.trading.salesOrder');

cpm.trading.salesOrder = {
    init: function(){
        $('select#fld_company_id_customer').livequery('change', function() {
            var url = 'index.php?module=trading_contact&_spAction=contactByCompanyJSON&showHTML=0';
            var company_id = $(this).val();
            $.get(url, {company_id: company_id}, function (data) {
                $('#fld_contact_id_customer').cp_loadSelect(data);
            }, 'json');
        });

        //for edit portal product
        $('#fld_quantity, #buy_unit_price, #sell_unit_price, \n\
           #other_costs_1_curr, #other_costs_2_curr, #other_costs_3_curr, \n\
           #other_costs_1, #other_costs_2, #other_costs_3')
        .live('change', cpm.trading.salesOrder.calculateValues);

        $('#recalculate').live('click', cpm.trading.salesOrder.recalculate);

        $('#linkRight .suppliers-cont input.checkbox')
        .live('click', cpm.trading.salesOrder.chooseSupplier);

        //quote request arrows under enquiry line
        $('.callbackWrapper .showHide').live('click', function (e){
            $(this).toggleClass('arrowDown');
            var qrDiv = $(this).next();
            $(qrDiv).slideToggle('fast');
        });

        //choose RFQ checkbox
        $('#chooseRFQ input.checkbox').live('click', cpm.trading.salesOrder.clearChooseRFQCheckbox);

        $('.m-trading_salesOrder #actBtn_apply, .m-trading_salesOrder #actBtn_save')
        .click(cpm.trading.salesOrder.validateChangeInventoryStatus);

        $('#showInventory').click(cpm.trading.salesOrder.editInventoryForm);
        $('#updateSellPriceFromQuote').click(cpm.trading.salesOrder.updateSellPriceFromQuote);
    },

    validateChangeInventoryStatus: function() {
        var fld_status = $('#fld_status').val();
        var fld_status_prev = $('#fld_status_prev').val();
        var fld_order_type = $('#fld_order_type_hid').val();
        var msg = '';
        //if SOR you do not need to do anything
        if (fld_status != fld_status_prev && fld_order_type != 'SOR') {
            if (fld_status == 'new') {
                msg = "Changing status to new will update the Inventory records status " +
                      "to 'on enquiry'. Are you sure to continue?";
                if (!confirm(msg)){
                    return false;
                }

            } else if (fld_status == 'confirmed') {
                msg = "Changing status to confirmed will update the Inventory records status " +
                      "to 'sold'. Are you sure to continue?";
                if (!confirm(msg)){
                    return false;
                }

            } else if (fld_status == 'on hold') {
                msg = "Changing status to 'on hold' will update the Inventory records status " +
                      "to 'on enquiry'. Are you sure to continue?";
                if (!confirm(msg)){
                    return false;
                }
            } else if (fld_status == 'cancelled') {
                msg = "Changing status to cancelled will update the Inventory records status " +
                      "to available. Are you sure to continue?";
                if (!confirm(msg)){
                    return false;
                }
            }
        }

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
        cpm.trading.salesOrder.calculateValues(event, true);
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

    clearChooseRFQCheckbox: function() {
        var checked = $(this).attr('checked');
        $('#chooseRFQ input.checkbox').removeAttr('checked')

        $(this).attr('checked', checked);
    },

    chooseRFQForLine: function(sales_order_items_id, quote_items_id) {
        quote_items_id = parseInt(quote_items_id);
        if (quote_items_id) {
            var msg = "You already have a RFQ from a quote. You can not choose another one.";
            alert(msg);
            return;
        }
        var url = 'index.php?module=trading_salesOrder&_spAction=chooseRFQFormForLine' +
                  '&sales_order_items_id=' + sales_order_items_id +
                  '&showHTML=0';
        var exp = {
            url: url
           ,afterOpen: function() {
                $('#btnChooseRFQCancel').click(function() {
                    $('#dialog').dialog('destroy');
                    $('#dialog').remove();
                });
                $('#btnChooseRFQSave').click(cpm.trading.salesOrder.chooseRFQForLineSave);
            }
        };
        Util.openDialogForLink('Choose Previous Rfq',  950, 550, 0, exp);

    },

    chooseRFQForLineSave: function() {
        var selector = '#chooseRFQ input.checkbox:checked';
        var quote_request_items_id = $(selector).val();
        quote_request_items_id = quote_request_items_id == undefined ? '' : quote_request_items_id;

        var sales_order_items_id = $('#sales_order_items_id').val();

        var url = 'index.php?module=trading_salesOrder&_spAction=chooseRFQForLine&showHTML=0'
                  + '&sales_order_items_id=' + sales_order_items_id
                  + '&quote_request_items_id=' + quote_request_items_id;

        $.get(url, function () {
            Util.alert('RFQ selection saved', function() {
                document.location = document.location;
            });
        });
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
                    $('#btnRaiseShipment').live('click', cpm.trading.salesOrder.raiseShipment);
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
        var enquiry_id = parseInt($('#fld_enquiry_id').val());
        var order_type = $('#fld_order_type_hid').val();

        //SO not created through Enquiry process
        if (!enquiry_id && order_type != 'Internal SO') {
            return cpm.trading.salesOrder.raiseInvoiceListInventory();
        }

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
                    $('#btnRaiseInvoice').click(cpm.trading.salesOrder.raiseInvoice);
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

        Util.showProgressInd();
        $.post(url, data, function (json) {
            if (json.status == 'error') {
                Util.hideProgressInd();
                Util.alert(json.errorMsg);
                return;
            }
            document.location = json.returnUrl;
        }, 'json');
    },

    raiseInvoiceListInventory: function() {
        var sales_order_id = $('#record_id').val();
        var url = 'index.php?module=trading_salesOrder&_spAction=raiseInvoiceListInventoryValidation&showHTML=0';
        $.getJSON(url, {sales_order_id: sales_order_id}, function (json) {
            if (json.status == 'error') {
                Util.alert(json.errorMsg);
                return;
            }

            var url = 'index.php?module=trading_salesOrder&_spAction=raiseInvoiceListInventory' +
                      '&sales_order_id=' + sales_order_id +
                      '&showHTML=0';
            var exp = {
                url: url
               ,afterOpen: function() {
                    $('#btnRaiseInvoiceCancel').click(function() {
                        $('#dialog').dialog('destroy');
                        $('#dialog').remove();
                    });
                    $('#btnRaiseInvoice').click(cpm.trading.salesOrder.raiseInvoiceInventory);
               }
            };
            Util.openDialogForLink('Raise Invoice',  900, 500, 0, exp);
        });

    },

    raiseInvoiceInventory: function() {
        var selector = '#raiseList input.choose';
        var data = $(selector).serialize();

        var is_product_selected = $('#raiseList input.choose:checked').length;
        var sales_order_id = $('#record_id').val();
        var url = 'index.php?module=trading_salesOrder&_spAction=raiseInvoiceInventory&showHTML=0' +
                  '&sales_order_id=' + sales_order_id +
                  '&is_product_selected=' + is_product_selected;

        Util.showProgressInd();
        $.post(url, data, function (json) {
            if (json.status == 'error') {
                Util.hideProgressInd();
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
                    $('#btnRaisePO').click(cpm.trading.salesOrder.raisePO);
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

        Util.showProgressInd();
        $.post(url, data, function (json) {
            if (json.status == 'error') {
                Util.hideProgressInd();
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
    },

    validateChooseProductLink: function() {
       var order_type = $('#fld_order_type').val();
       if (order_type == 'SOR') {
           var msg = 'Sorry you cannot choose from products for SOR order. '
                   + 'Please choose from Inventory';
           Util.alert(msg, function (){
               return false;
           });
       }
       return true;
    },

    validateInventoryEditLink: function() {
        var retValue = true;
        var enquiry_id = $('#record_id').val();
        var url = 'index.php?module=trading_salesOrder&_spAction=chooseInventoryLinkValidation&showHTML=0';
        $.ajax({
            url: url,
            async: false,
            data: {enquiry_id: enquiry_id},
            dataType: 'json',
            success: function (json) {
                if (json.status == 'error') {
                    Util.alert(json.errorMsg);
                    retValue = false;
                }
            }
        });
        return retValue;
    },

    validateEditProductItemLink: function(exp) {
        var retValue = true;
        var sales_order_items_id = exp.recId;
        var url = 'index.php?module=trading_salesOrder'
                + '&_spAction=validateEditProductItemLink&showHTML=0';
        $.ajax({
            url: url,
            async: false,
            data: {sales_order_items_id: sales_order_items_id},
            dataType: 'json',
            success: function (json) {
                if (json.status == 'error') {
                    Util.alert(json.errorMsg);
                    retValue = false;
                }
            }
        });
        return retValue;
    },

    printSO: function() {
        var enquiry_id = parseInt($('#fld_enquiry_id').val());
        //SO not created through Enquiry process
        var reportName = '';
        var sales_order_id = $('#record_id').val();
        var order_type = $('#fld_order_type_hid').val();

        var url = 'index.php?_spAction=printReport&record_id='
                + sales_order_id + '&showHTML=0&roomName=trading_salesOrder';
        if (!enquiry_id && order_type != 'Internal SO') {
            var urlSOInv = url + '&report=salesOrderInventory';
            var urlSOInvSerial = url + '&report=salesOrderInventorySerial';
            var text = "<a href='" + urlSOInv + "'>Print SO - No Serial</a><br>" +
                       "<a href='" + urlSOInvSerial + "'>Print SO - With Serial</a><br>";

            Util.alert(text, null, 'Print SO');
        } else {
            reportName = 'salesOrder';
            url += '&report=' + reportName;
            document.location = url;
        }
    },

    printDeliveryNote: function() {
        var sales_order_id = $('#record_id').val();

        var url = 'index.php?_spAction=printReport&record_id='+ sales_order_id
                + '&showHTML=0&roomName=trading_salesOrder'
                + '&report=deliveryNoteSalesOrder';
        document.location = url;
    },

    editInventoryForm: function(e) {
        e.preventDefault();
        var sales_order_id = $(this).attr('sales_order_id');
        var url = 'index.php?module=trading_salesOrder&_spAction=editInventoryForm' +
                  '&sales_order_id=' + sales_order_id +
                  '&showHTML=0';
        var exp = {
            url: url
           ,afterOpen: function() {
                $('#btnUpdateInventoryCancel').click(function() {
                    $('#dialog').dialog('destroy');
                    $('#dialog').remove();
                });
                $('#btnUpdateInventory').click(cpm.trading.salesOrder.saveInventoryForm);
                $('#location_common').change(function() {
                    $('#updateInventory .location').val($(this).val());
                });
                $('#status_common').change(function() {
                    $('#updateInventory .status').val($(this).val());
                });
            }
        };
        Util.openDialogForLink('Edit Inventory',  900, 500, 0, exp);
    },

    saveInventoryForm: function() {
        var url = "index.php?module=trading_salesOrder"
                + "&_spAction=saveInventory"
                + "&showHTML=0";
        var values = $('#updateInventory input, #updateInventory select').serialize();

        Util.showProgressInd();
        $.post(url, values, function(json) {
            Util.alert(json.html, function() {
                Util.hideProgressInd();
                $('#dialog').dialog('destroy');
                $('#dialog').remove();

                var enquiry_id = parseInt($('#fld_enquiry_id').val());
                //if inventory sales order
                if (!enquiry_id) {
                    document.location = document.location;
                }
            });
        }, 'json');
    },

    updateSellPriceFromQuote: function(e) {
        e.preventDefault();
        var update = confirm('Are your to update sell price from quote?');
        if (!update) {
            return;
        }
        var record_id = $('#record_id').val();
        var url = "index.php?module=trading_salesOrder"
                + "&_spAction=updateSellPriceFromQuote"
                + "&showHTML=0&sales_order_id=" + record_id;

        Util.showProgressInd();
        $.get(url, function(json) {
            Util.alert(json.html, function() {
                Util.hideProgressInd();
                $('#dialog').dialog('destroy');
                $('#dialog').remove();

                document.location = document.location;
            });
        });
    }
}