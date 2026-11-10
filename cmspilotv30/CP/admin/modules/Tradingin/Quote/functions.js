Util.createCPObject('cpm.tradingin.quote');

cpm.tradingin.quote = {
    init: function(){
        $("select[name='company_id']").change(function() {
            var company_id = $("select[name='company_id']").val();
            var url = 'index.php?module=tradingin_contact&_spAction=multipleAddress&showHTML=0';
            $.get(url, {company_id: company_id}, function (data) {
                $("select[name='company_address_id']").cp_loadSelect(data);
            }, 'json');
        });

        $('#frmEdit select#fld_company_id').livequery('change', function(){
           Util.loadContactDropdown.call(this);
        });
        $('#portalForm select#fld_product_group_id').livequery('change', function(){
           cpm.tradingin.quote.loadCategoryDropdown.call(this);
        });

        //for edit portal product
        $('#portalForm .costs input, #portalForm .costs select')
        .livequery('change', cpm.tradingin.quote.calculateProductCosting);

        $('#portalForm #fld_other_costs_1_label').unbind('change');
        $('#portalForm #fld_other_costs_2_label').unbind('change');
        $('#portalForm #fld_other_costs_3_label').unbind('change');

        $('#portalForm a.next').livequery('click', cpm.tradingin.quote.nextLine);
        $('#portalForm a.previous').livequery('click', cpm.tradingin.quote.previousLine);

        $('select[name=product_id]').livequery('change', function(){
            var parent = $(this).closest('tr');
            var product_id = $(this).val();
            var rec_id = $(parent).attr('recid');
            var costPriceObj = $(this ).closest('tr').find('.cost-price');
            var marginObj = $(this ).closest('tr').find('.mark-up');
            var titleObj = $(this ).closest('tr').find('.title');
            var url = 'index.php?module=tradingin_quote&_spAction=updateProductLineItems&showHTML=0';
            $.get(url, {product_id: product_id, rec_id: rec_id}, function(json){
                costPriceObj.html(json.price);
                titleObj.html(json.title);
                //$("input[name=mark_up]', titleObj).val(35);
            });
            cpm.tradingin.quote.loadSupplierDropdown.call(this);
        });

        $('.row-tradingin_quote__tradingsg_productLink .mark-up').livequery('change', function(){
            var parent = $(this).closest('tr');
            var rec_id = $(parent).attr('recid');
            var sellingPriceObj = $(this ).closest('tr').find('.selling-price');
            var profitObj = $(this ).closest('tr').find('.profit');
            var sellingPriceAfterDiscountObj = $(this).closest('tr').find('.selling-price-after-discount');
            var totalSellingPriceObj = $(this).closest('tr').find('.total-selling-price');
            var url = 'index.php?module=tradingin_quote&_spAction=updateSellingLineItems&showHTML=0';
            $.get(url, {rec_id: rec_id}, function(json){
                sellingPriceObj.html(json.sellingPrice);
                profitObj.html(json.profit);
                sellingPriceAfterDiscountObj.html(json.sellingPriceAfterDiscount);
                totalSellingPriceObj.html(json.totalSellingPrice);
                //if(json.profit < 0){
                    //$(this).css('background-color', 'red')
                //}

            });
        });


        $(".productViewHistory").livequery('click', function (e){
            var quote_product_id = $(this).attr('quote_product_id');
            //alert(supplier_quote_id);
            e.preventDefault();
            var expObj = {
                beforeCloseFn: function(){
                    Util.closeAllDialogs();
                }
            }
            Util.openDialogForLink.call(this, 'Product Sales History', 700, 500, expObj);
        });

        //---------------------- CHANGE DISCOUNT VALUE IN PRODUCT LINK---------------------
        $('.row-tradingin_quote__tradingsg_productLink .discount-percentage-amount')
        .livequery('change', cpm.tradingin.quote.quoteProductDiscountPercentageAmount);

        $('.row-tradingin_quote__tradingsg_productLink .client-id').livequery('change', function(){
            var parent = $(this).closest('tr');
            var rec_id = $(parent).attr('recid');
            var clientIdObj = $(this).parents('tr').find('select[name=client_id]');
            var client_id = clientIdObj.val();
            var url = 'index.php?module=tradingin_quote&_spAction=updateClientId&showHTML=0';
            $.get(url, {rec_id: rec_id, client_id: client_id}, function(json){
                alert('Supplier Updated Successfully');
                window.location.reload(true);
            });
        });

        //This code runs when changing the discount value in quote-product
        $('.row-tradingin_quote__tradingsg_productLink .discount-percentage').livequery('change', function(){
            var parent = $(this).closest('tr');
            var discountObj = $(this).parents('tr').find('input[name=discount_percentage]');
            var discount = discountObj.val();
            var rec_id = $(parent).attr('recid');
            var profitObj = $(this ).closest('tr').find('.profit');
            var sellingPriceObj = $(this ).closest('tr').find('.selling-price');
            var sellingPriceAfterDiscountObj = $(this).closest('tr').find('.selling-price-after-discount');
            var totalSellingPriceObj = $(this ).closest('tr').find('.total-selling-price');
            var url = 'index.php?module=tradingin_quote&_spAction=updateSellingLineItems&showHTML=0';
            $.get(url, {rec_id: rec_id}, function(json){
                sellingPriceObj.html(json.sellingPrice);
                totalSellingPriceObj.html(json.totalSellingPrice);
                sellingPriceAfterDiscountObj.html(json.sellingPriceAfterDiscount);
                profitObj.html(json.profit);
            });
        });

        //---------------------- CHANGE QTY IN PRODUCT LINK---------------------
        $('.row-tradingin_quote__tradingsg_productLink .qty')
        .livequery('change', cpm.tradingin.quote.quoteProductQty);

        //---------------- COST PRICE UPDATE -----------------------------
        $('.row-tradingin_quote__tradingsg_productLink .cost-price')
        .livequery('change', cpm.tradingin.quote.quoteProductCostPrice);
        //---------------- DISCOUNT TYPE UPDATE -----------------------------
        $('.row-tradingin_quote__tradingsg_productLink .discount-type')
        .livequery('change', cpm.tradingin.quote.quoteProductDiscountType);

        //---------------- MARK UP TYPE UPDATE -----------------------------
        $('.row-tradingin_quote__tradingsg_productLink .mark-up-type').livequery('change', function(){
            var parent = $(this).closest('tr');
            var markUpTypeObj = $(this).parents('tr').find('input[name=mark_up_type]');
            var markUpType    = markUpTypeObj.val();

            var rec_id       = $(parent).attr('recid');
            //var profitObj = $(this ).closest('tr').find('.profit');
            var totalSellingPriceObj = $(this ).closest('tr').find('.total-selling-price');
            var costPriceObj = $(this ).closest('tr').find('.total-cost-price');
            var sellingPriceObj = $(this ).closest('tr').find('.selling-price-amount');

            var url = 'index.php?module=tradingin_quote&_spAction=updateSellingLineItems&showHTML=0';

            $.get(url, {rec_id: rec_id, markUpType: markUpType}, function(json){
                totalSellingPriceObj.html(json.totalSellingPrice);
                costPriceObj.html(json.totalCostPrice);
                sellingPriceObj.html(json.selling_price);
                $('tr.summary-row .totalCp').html(json.total_cost_price_sum);
                $('tr.summary-row .serviceCostSum').html(json.mark_up_amount_sum);
                $('tr.summary-row .totalSp').html(json.total_selling_price_sum);
            });

        });

        $('.row-tradingin_quote__tradingsg_productLink .mark-up-amount').livequery('change', function(){
            var parent = $(this).closest('tr');
            var markUpObj = $(this).parents('tr').find('input[name=mark_up_amount]');
            var mark_up    = markUpObj.val();

            var rec_id       = $(parent).attr('recid');
            var totalSellingPriceObj = $(this ).closest('tr').find('.total-selling-price');
            var sellingPriceObj = $(this ).closest('tr').find('.selling-price-amount');

            var url = 'index.php?module=tradingin_quote&_spAction=updateSellingLineItems&showHTML=0';

            $.get(url, {rec_id: rec_id, mark_up: mark_up}, function(json){
                totalSellingPriceObj.html(json.totalSellingPrice);
                sellingPriceObj.html(json.selling_price);
                $('tr.summary-row .totalCp').html(json.total_cost_price_sum);
                $('tr.summary-row .serviceCostSum').html(json.mark_up_amount_sum);
                $('tr.summary-row .totalSp').html(json.total_selling_price_sum);
            });

        });

        $('#raisePo').livequery('click', function(){
            msg = "Do you like to Raise PO?";

            if (!confirm(msg)){
                return false;
            }
            else{
                Util.showProgressInd();
                var quote_id = $(this).attr('quote_id');
                var url = 'index.php?module=tradingin_quote&_spAction=raisePurchaseOrder&showHTML=0&id=' + quote_id;
                $.get(url, {quote_id: quote_id}, function(html){
                    alert('Purchase Order Raised Successfully');
                    window.location.reload(true);
                });
                //Util.hideProgressInd();
            }
        });

        /* Updating of mark-up in quote_product table by product group */
        $('.m-tradingin_quote #updateMarkupByGroup').livequery('click', function (e){
            /*msg = "Do you like to Update Markup?";
            if (!confirm(msg)){
                return false;
            }
            else{*/
                var title = "Update Markup";
                e.preventDefault();

                var expObj = {
                    validate: true
                   ,callbackOnSuccess: function(){
                        Util.closeAllDialogs();
                        window.location.reload(true);
                    }
                }
                Util.openFormInDialog.call(this, 'portalForm', title, 400, 400, expObj);
            //}
        });

        /* Raising General Quotation */
        $('.m-tradingin_quote #raiseGeneralQuotation')
        .livequery('click', cpm.tradingin.quote.raiseGeneralQuotation);

        /*
        $('.m-tradingin_quote #raiseGeneralQuotation').livequery('click', function (e){
            msg = "Do you like to raise General Quotation?";
            if (!confirm(msg)){
                return false;
            }
            else{
                Util.showProgressInd();
                var quote_id = $(this).attr('quote_id');
                var url = 'index.php?module=tradingin_quote&_spAction=raiseGeneralQuotation&showHTML=0&quote_id=' + quote_id;
                $.get(url, {quote_id: quote_id}, function(html){
                    Util.hideProgressInd();
                    window.location.reload(true);
                });
            }
        });
        */

        /* Delete Products Linked */
        $('.m-tradingin_quote #deleteProducts').livequery('click', function (e){
            msg = "Do you like to delete the products?";
            if (!confirm(msg)){
                return false;
            }
            else{
                Util.showProgressInd();
                var quote_id = $(this).attr('quote_id');
                var url = 'index.php?module=tradingin_quote&_spAction=deleteProductsLinked&showHTML=0&quote_id=' + quote_id;
                $.get(url, {quote_id: quote_id}, function(html){
                    Util.hideProgressInd();
                    window.location.reload(true);
                });
            }
        });

        /* Updating of discount group in quote_product table by product group */
        $('.m-tradingin_quote #updateDiscountByGroupForm').livequery('click', function (e){
            /*msg = "Do you like to Update Discount By Group?";
            if (!confirm(msg)){
                return false;
            }
            else{*/
                var title = "Update Discount";
                e.preventDefault();

                var expObj = {
                    validate: true
                   ,callbackOnSuccess: function(){
                        Util.closeAllDialogs();
                        window.location.reload(true);
                    }
                }
                Util.openFormInDialog.call(this, 'portalForm', title, 900, 400, expObj);
            //}
        });

        /* Updating of discount group in quote_product table by product group */
        $('.m-tradingin_quote #updateDiscountForm').livequery('click', function (e){
            msg = "Do you like to Update Discount?";
            if (!confirm(msg)){
                return false;
            }
            else{
                var title = "Update Discount";
                e.preventDefault();

                var expObj = {
                    validate: true
                   ,callbackOnSuccess: function(){
                        Util.closeAllDialogs();
                        window.location.reload(true);
                    }
                }
                Util.openFormInDialog.call(this, 'portalForm', title, 400, 300, expObj);
            }
        });

        /* Updating of mark-up in quote_product table by category */
        $('.m-tradingin_quote #updateMarkupByCategory').livequery('click', function (e){
            msg = "Do you like to Update Markup?";
            if (!confirm(msg)){
                return false;
            }
            else{
                var title = "Update Markup";
                e.preventDefault();

                var expObj = {
                    validate: true
                   ,callbackOnSuccess: function(){
                        Util.closeAllDialogs();
                        window.location.reload(true);
                    }
                }
                Util.openFormInDialog.call(this, 'portalForm', title, 400, 400, expObj);
            }
        });

        /* Bulk Add Generate in quote_product link */
        $('#bulkAddProduct')
        .livequery('click', cpm.tradingin.quote.quoteProductBulkAdd);

        /* Add Product in quote_product link */
        $('#addProduct')
        .livequery('click', cpm.tradingin.quote.quoteProductAdd);

        /* Add note in quote po link*/
        $('.m-tradingin_quote a.addNotePo').livequery('click', function (e){
                var title = "Add Note";
                e.preventDefault();

                var expObj = {
                    validate: true
                   ,callbackOnSuccess: function(){
                        var msg = 'Note added Successfully';
                        Util.alert(msg, function(){
                            Util.closeAllDialogs();
                            //window.location.reload(true);
                        });
                    }
                }
                Util.openFormInDialog.call(this, 'portalForm', title, 600, 300, expObj);
        });

        //=========================== AUTO COMPLETE ===========================
        $(".row-tradingin_quote__tradingsg_productLink input[name='product_title']")
        .livequery(cpm.tradingin.quote.quoteProductProductTitle);

        $('#raiseInvoice').livequery('click', function(){
            msg = "Do you like to Raise Invoice?";

            if (!confirm(msg)){
                return false;
            }
            else{
                Util.showProgressInd();
                var quote_id = $(this).attr('quote_id');
                var url = 'index.php?module=tradingin_quote&_spAction=raiseInvoice&showHTML=0&id=' + quote_id;
                $.get(url, {quote_id: quote_id}, function(html){
                    alert('Invoice raised Successfully, Please use the link to goto Finance Module')
                    window.location.reload(true);
                });
            }
        });

        /* Checkbox for deleting quote_product link record*/
        $('.row-tradingin_quote__tradingsg_productLink td.qo-po-id').livequery('click', function (e){
            var parent = $(this).closest('tr');
            var rec_id = $(parent).attr('recid');
            var cbObj = $('input[name=qo_po_id]', parent);
            var checkedVal = cbObj.is(":checked") ? 1 : 0;

            var url = 'index.php?_topRm=order&module=tradingin_quote&_spAction=checkboxForDeleteProductsLinked&showHTML=0';
            $.get(url,{rec_id: rec_id ,checkedVal: checkedVal}, function(html){
            });

            //alert(checkedVal)
        });

        /* Deleting quote_product link records*/
        $('.m-tradingin_quote #deleteProductChecked').livequery('click', function (e){
            msg = "Do you like to delete the checked products? (Note: This will delete the products including purchase order and order)";
            if (!confirm(msg)){
                return false;
            }
            else{
                Util.showProgressInd();
                var quote_id = $(this).attr('quote_id');
                var url = 'index.php?module=tradingin_quote&_spAction=deleteCheckedProductsLinked&showHTML=0&quote_id=' + quote_id;
                $.get(url, {quote_id: quote_id}, function(html){
                    Util.hideProgressInd();
                    Util.alert('Products deleted Successfully')
                    window.location.reload(true);
                });
            }
        });

        $('.m-tradingin_quote #convertClientRequirement').livequery('click', function (e){
            msg = "Do you like to Convert General Quotation?";
            if (!confirm(msg)){
                return false;
            }
            else{
                Util.showProgressInd();
                var quote_id = $(this).attr('quote_id');
                var url = 'index.php?module=tradingin_quote&_spAction=updateGeneralQuotation&showHTML=0';
                $.get(url, {quote_id: quote_id}, function(html){
                    Util.hideProgressInd();
                    window.location.reload(true);
                });
            }
        });

    },

    loadSupplierDropdown: function(){
        //$(this).each(function(){
            var product_id = $(this).val();
            var clientIdObj = $(this ).parents('tr').find('.client-id select[name=client_id]');

            var url = $('#scopeRootAlias').val() + 'index.php?module=tradingin_quote&_spAction=supplierJsonByProductId&showHTML=0';

            $.getJSON(url, {product_id: product_id}, function(data) {
                clientIdObj.cp_loadSelect(data);
            });
        //});
    },

    populateProductRelatedValues: function(product_id){
        var parent = $(this).closest('tr');
        var product_id_change = product_id;
        var rec_id = $(parent).attr('recid');
        alert(product_id_change);
        alert(rec_id);
        var costPriceObj = $(this ).closest('tr').find('.cost-price');
        var marginObj = $(this ).closest('tr').find('.mark-up');
        var titleObj = $(this ).closest('tr').find('.title');
        var url = 'index.php?module=tradingin_quote&_spAction=updateProductLineItems&showHTML=0';
        $.get(url, {product_id: 1, rec_id: rec_id}, function(json){
            costPriceObj.html(json.price);
            titleObj.html(json.title);
            //$("input[name=mark_up]', titleObj).val(35);
        });
        cpm.tradingin.quote.loadSupplierDropdown.call(this);
    },

    loadCategoryDropdown: function(){
        $(this).each(function(){
            ProductGroupId = $(this).val();
            var url = $('#scopeRootAlias').val() + 'index.php?module=tradingsg_product&_spAction=categoryJsonByProductGroupId&showHTML=0'

            $.getJSON(url, {product_group_id: ProductGroupId}, function(data) {
                $('#portalForm select#fld_category_id').cp_loadSelect(data);
            });
        });
    },


    calculateProductCosting: function(e){
        var url = "index.php?module=tradingsg_product"
                + "&_spAction=calculatedValuesQuoteItems"
                + "&showHTML=0";
        var values = $('#portalForm input, #portalForm select').serialize();

        Util.showProgressInd();
        $.post(url, values, function(json) {
            Util.hideProgressInd();
            $('#txt_buy_unit_price_base').html(json.buy_unit_price_base);
            $('#fld_buy_unit_price_base').val(json.buy_unit_price_base);
            $('#txt_other_costs_1_base').html(json.other_costs_1_base);
            $('#fld_other_costs_1_base').val(json.other_costs_1_base);
            $('#txt_other_costs_2_base').html(json.other_costs_2_base);
            $('#fld_other_costs_2_base').val(json.other_costs_2_base);
            $('#txt_other_costs_3_base').html(json.other_costs_3_base);
            $('#fld_other_costs_3_base').val(json.other_costs_3_base);
            $('#txt_sell_unit_price_total_net_cost_base').html(json.sell_unit_price_total_net_cost_base);
            $('#fld_sell_unit_price_total_net_cost_base').val(json.sell_unit_price_total_net_cost_base);
            $('#txt_agent_comm_base').html(json.agent_comm_base);
            $('#fld_agent_comm_base').val(json.agent_comm_base);
            $('#txt_qc_comm_base').html(json.qc_comm_base);
            $('#fld_qc_comm_base').val(json.qc_comm_base);
            $('#txt_sell_unit_price_ex_fact_base').html(json.sell_unit_price_ex_fact_base);
            $('#fld_sell_unit_price_ex_fact_base').val(json.sell_unit_price_ex_fact_base);
            $('#txt_local_charges_base').html(json.local_charges_base);
            $('#fld_local_charges_base').val(json.local_charges_base);
            $('#txt_sell_unit_price_fob_base').html(json.sell_unit_price_fob_base);
            $('#fld_sell_unit_price_fob_base').val(json.sell_unit_price_fob_base);
            $('#txt_shipping_cost_base').html(json.shipping_cost_base);
            $('#fld_shipping_cost_base').val(json.shipping_cost_base);
            $('#txt_insurance_cost_base').html(json.insurance_cost_base);
            $('#fld_insurance_cost_base').val(json.insurance_cost_base);
            $('#txt_sell_unit_price_cif_base').html(json.sell_unit_price_cif_base);
            $('#fld_sell_unit_price_cif_base').val(json.sell_unit_price_cif_base);
            $('#txt_tax_amount_base').html(json.tax_amount_base);
            $('#fld_tax_amount_base').val(json.tax_amount_base);

            $('#tblSalesPrice td.ex_fact_markup span').html(json.ex_fact_markup);
            $('#tblSalesPrice td.fob_markup span').html(json.fob_markup);
            $('#tblSalesPrice td.cif_markup span').html(json.cif_markup);
            $('#tblSalesPrice td.ex_fact_markup_amount').html(json.ex_fact_markup_amount);
            $('#tblSalesPrice td.fob_markup_amount').html(json.fob_markup_amount);
            $('#tblSalesPrice td.cif_markup_amount').html(json.cif_markup_amount);
            $('#tblSalesPrice td.cif_markup_amount').html(json.cif_markup_amount);
            $('#txt_sell_unit_price_base_vat').html(json.sell_unit_price_base_vat);
            $('#fld_sell_unit_price_base_vat').val(json.sell_unit_price_base_vat);
            $('#txt_sell_price_base').html(json.sell_price_base);
            $('#fld_sell_price_base').val(json.sell_price_base);

        }, 'json');

    },

    setExchangeRateOtherCost: function(exchRateToUsd) {
        $('#t_other_costs_1_base').html(exchRateToUsd);
        $('#other_costs_1_base').html(exchRateToUsd);
    },

    raiseSOList: function() {
        var quote_id = $('#record_id').val();
        var url = 'index.php?module=tradingin_quote&_spAction=raiseSOListValidation&showHTML=0';
        $.getJSON(url, {quote_id: quote_id}, function (json) {
            if (json.status == 'error') {
                Util.alert(json.errorMsg);
                return;
            }

            var url = 'index.php?module=tradingin_quote&_spAction=raiseSOList' +
                      '&quote_id=' + quote_id +
                      '&showHTML=0';
            var exp = {
                url: url
               ,afterOpen: function() {
                    $('#btnRaiseSOCancel').click(function() {
                        $('#dialog').dialog('destroy');
                        $('#dialog').remove();
                    });
                    $('#btnRaiseSO').click(cpm.tradingin.quote.raiseSO);
                }
            };
            Util.openDialogForLink('Raise SO',  900, 500, 0, exp);

        });

    },

    raiseSO: function() {
        var selector = '#raiseList input.choose, ' +
                       '#raiseList input.quantity, ' +
                       '#raiseList select[name=company_id_supplier]';
        var data = $(selector).serialize();

        var quote_id = $('#record_id').val();
        var url = 'index.php?module=tradingin_quote&_spAction=raiseSO&showHTML=0' +
                  '&quote_id=' + quote_id;

        $.post(url, data, function (json) {
            if (json.status == 'error') {
                Util.alert(json.errorMsg);
                return;
            }
            document.location = json.returnUrl;
        }, 'json');

    },

    duplicate: function() {
        if (!confirm("Are you sure you want to duplicate the Quote?")){
            return;
        }
        var quote_id = $('#record_id').val();

        if (!confirm("Do you want to duplicate all linked Products?")){
            var url = 'index.php?module=tradingin_quote&_spAction=duplicateQuote&showHTML=0' +
                      '&quote_id=' + quote_id;
        } else {
            var url = 'index.php?module=tradingin_quote&_spAction=duplicateQuote&showHTML=0' +
                      '&quote_id=' + quote_id + '&linkedProduct=' + 1;
        }

        $.post(url, function (json) {
            if (json.status == 'error') {
                Util.alert(json.errorMsg);
                return;
            }
            document.location = json.returnUrl;
        }, 'json');

    },

    printQuote: function() {
        var quote_id = $('#record_id').val();
        var url = 'index.php?_spAction=printReport&record_id=' +
                   quote_id + '&showHTML=0&roomName=tradingin_quote&report=quote';
        document.location = url;
    },

    nextLine: function(e) {
        e.preventDefault();
        var record_id = $('#portalForm input[name=quote_items_id]').val();
        var nextRow = $('.tradingin_quote__tradingsg_productLink table tr[recid=' + record_id + ']').next();
        if (nextRow.length > 0) {
            $('#dialog').dialog('destroy');
            $('#dialog').remove();
            nextRow.find('.editPortalRecord').click();
        }
    },

    previousLine: function(e) {
        e.preventDefault();
        var record_id = $('#portalForm input[name=quote_items_id]').val();
        var prevRow = $('.tradingin_quote__tradingsg_productLink table tr[recid=' + record_id + ']')
                      .prev('[recid]');
        if (prevRow.length > 0) {
            $('#dialog').dialog('destroy');
            $('#dialog').remove();
            prevRow.find('.editPortalRecord').click();
        }
    },

    quoteProductQty: function(e) {
        e.preventDefault();
        var parent = $(this).closest('tr');
        var qtyObj = $(this).parents('tr').find('input[name=qty]');
        var qty = qtyObj.val();
        var discountValObj = $(this).parents('tr').find('input[name=discount_percentage_amount]');
        var discount_percentage = discountValObj.val();
        var rec_id = $(parent).attr('recid');
        var totalSellingPriceObj = $(this ).closest('tr').find('.total-selling-price');
        var costPriceObj = $(this ).closest('tr').find('.total-cost-price');
        var marginObj    = $(this ).closest('tr').find('.mark-up-amount');
        var discountObj = $(this ).closest('tr').find('.discount-percentage-amount');
        var url = 'index.php?module=tradingin_quote&_spAction=updateSellingLineItems&showHTML=0';

        $.get(url, {rec_id: rec_id, qty: qty, discount_percentage:discount_percentage}, function(json){
            totalSellingPriceObj.html(json.totalSellingPrice);
            costPriceObj.html(json.totalCostPrice);

            //The margin is uneditable for trading mass hence the condition is given as below
            if (m_tradingsg_quote_hasProductLinkForSplCase == 1){
                marginObj.html(json.mark_up_value);
            }
            $('tr.summary-row .totalCp').html(json.total_cost_price_sum);
            $('tr.summary-row .discountSum').html(json.discount_percentage_amount_sum);
            $('tr.summary-row .serviceCostSum').html(json.mark_up_amount_sum);
            $('tr.summary-row .totalSp').html(json.total_selling_price_sum);
            $("input[name=discount_percentage_amount]", discountObj).val(json.discount_value);
            $("input[name=mark_up_amount]", marginObj).val(json.mark_up_value);
        });
    },

    quoteProductProductTitle: function() {
        var titleObj = this;
    	$(titleObj).autocomplete({
             source : 'index.php?module=tradingin_quote&_spAction=searchProductTitle&showHTML=0'
            ,minLength : 1
    		,select: function(event, ui) {
                var selectedObj = ui.item;
    			var product_id = selectedObj.id
    			$(this).after("<input type='hidden' name='product_id' value=" + product_id + ">");

                //To Populate the related values in the table
                //--------------------------------------------
                Util.showProgressInd();
                var parent          = $(this).closest('tr');
                var rec_id          = $(parent).attr('recid');
                var productTitleObj = $(this ).closest('tr').find('.product-title');
                var costPriceObj    = $(this ).closest('tr').find('.cost-price');
                var marginObj       = $(this ).closest('tr').find('.mark-up-amount');
                var titleObj        = $(this ).closest('tr').find('.pg-title');
                var sellingPriceObj = $(this ).closest('tr').find('.selling-price-amount');
                var itemCodeObj     = $(this ).closest('tr').find('.item-code');
                var unitObj         = $(this ).closest('tr').find('.unit');
                var partNumberObj   = $(this ).closest('tr').find('.part-number');
                var discountObj     = $(this ).closest('tr').find('.discount-percentage-amount');
                var clientIdObj     = $(this ).parents('tr').find('.client-id select[name=client_id]');
                var markUpTypeObj     = $(this ).parents('tr').find('.mark-up-type select[name=mark_up_type]');
                var discountTypeObj     = $(this ).parents('tr').find('.discount-type select[name=discount_type]');
                var qtyObj         = $(this ).closest('tr').find('.qty');

                var url = 'index.php?module=tradingin_quote&_spAction=updateProductLineItems&showHTML=0';
                $.get(url, {product_id: product_id, rec_id: rec_id}, function(json){
                    if (json.msg != '') {
                        Util.hideProgressInd();
                        Util.alert(json.msg);
                        $('input[name=product_title]', productTitleObj).val('')
                        return;
                    }

                    //For General Trading
                    $("input[name=cost_price]", costPriceObj).val(json.price);
                    $("input[name=qty]", qtyObj).val(1);
                    discountTypeObj.val('Value');
                    markUpTypeObj.val('Value');
                    titleObj.html(json.title);
                    sellingPriceObj.html(json.sellingPrice);
                    itemCodeObj.html(json.itemCode);
                    unitObj.html(json.unit);
                    partNumberObj.html(json.partNumber);

                    var url = $('#scopeRootAlias').val() + 'index.php?module=tradingin_quote&_spAction=supplierJsonByProductId&showHTML=0';

                    $.getJSON(url, {product_id: product_id}, function(data) {
                        clientIdObj.cp_loadSelect(data);
                    });

                    Util.hideProgressInd();
                });
    		}
    	});
    },

    quoteProductBulkAdd: function(e) {
        e.preventDefault();
        var title = "Bulk Generate Product Records";
        var expObj = {
            validate: true
           ,callbackOnSuccess: function(){
                var msg = 'Generated successfully';
                Util.closeAllDialogs();
                Links.reloadPortalRecords('tradingin_quote#tradingsg_productLink');
            }
        }
        Util.openFormInDialog.call(this, 'portalForm', title, 300, 175, expObj);
    },

    quoteProductAdd: function(e) {
        e.preventDefault();
        var quote_id       = $(this).attr('quote_id');
        Util.showProgressInd();
        var url = 'index.php?module=tradingin_quote&_spAction=addProduct&quote_id=' + quote_id + '&showHTML=0';
        $.get(url, {quote_id: quote_id}, function(){
            Util.hideProgressInd();
        });
    },

    raiseGeneralQuotation: function(e) {
        msg = "Do you like to raise General Quotation?";
        if (!confirm(msg)){
            return false;
        }
        else{
            Util.showProgressInd();
            var quote_id = $(this).attr('quote_id');
            var url = 'index.php?module=tradingin_quote&_spAction=raiseGeneralQuotation&showHTML=0&quote_id=' + quote_id;
            $.get(url, {quote_id: quote_id}, function(html){
                Util.hideProgressInd();
                window.location.reload(true);
            });
        }
    },

    quoteProductCostPrice: function(e) {
        e.preventDefault();
        var parent = $(this).closest('tr');
        var costPriceObj = $(this).parents('tr').find('input[name=cost_price]');
        var costPrice    = costPriceObj.val();

        var rec_id       = $(parent).attr('recid');
        //var profitObj = $(this ).closest('tr').find('.profit');
        var totalSellingPriceObj = $(this ).closest('tr').find('.total-selling-price');
        var costPriceObj = $(this ).closest('tr').find('.total-cost-price');
        var markUpObj    = $(this ).closest('tr').find('.mark-up-amount');
        var discountObj  = $(this ).closest('tr').find('.discount-percentage-amount');
        var sellingPriceObj = $(this ).closest('tr').find('.selling-price-amount');

        var url = 'index.php?module=tradingin_quote&_spAction=updateSellingLineItems&showHTML=0';

        $.get(url, {rec_id: rec_id, costPrice: costPrice}, function(json){
            totalSellingPriceObj.html(json.totalSellingPrice);
            costPriceObj.html(json.totalCostPrice);
            $("input[name=discount_percentage_amount]", discountObj).val(json.discount_value);
            $("input[name=mark_up_amount]", markUpObj).val(json.mark_up_value);
            //when changing cost  for general trading margin is not going to change hence commented below
            sellingPriceObj.html(json.selling_price);
            $('tr.summary-row .totalCp').html(json.total_cost_price_sum);
            $('tr.summary-row .discountSum').html(json.discount_percentage_amount_sum);
            $('tr.summary-row .serviceCostSum').html(json.mark_up_amount_sum);
            $('tr.summary-row .totalSp').html(json.total_selling_price_sum);
        });
    },

    quoteProductDiscountPercentageAmount: function(e) {
        e.preventDefault();
        var parent = $(this).closest('tr');
        var discountPercentageObj = $(this).parents('tr').find('input[name=discount_percentage_amount]');
        var discount_percentage   = discountPercentageObj.val();

        var rec_id       = $(parent).attr('recid');
        var totalSellingPriceObj = $(this ).closest('tr').find('.total-selling-price');
        var sellingPriceObj = $(this ).closest('tr').find('.selling-price-amount');

        var url = 'index.php?module=tradingin_quote&_spAction=updateSellingLineItems&showHTML=0';

        $.get(url, {rec_id: rec_id, discount_percentage: discount_percentage}, function(json){
            totalSellingPriceObj.html(json.totalSellingPrice);
            sellingPriceObj.html(json.selling_price);
            $('tr.summary-row .totalCp').html(json.total_cost_price_sum);
            $('tr.summary-row .discountSum').html(json.discount_percentage_amount_sum);
            $('tr.summary-row .totalSp').html(json.total_selling_price_sum);
        });

    },

    quoteProductDiscountType: function(e) {
        e.preventDefault();
        var parent = $(this).closest('tr');
        var discountTypeObj     = $(this ).parents('tr').find('.discount-type select[name=discount_type]');
        var discount_type    = discountTypeObj.val();

        var rec_id       = $(parent).attr('recid');
        //var profitObj = $(this ).closest('tr').find('.profit');
        var totalSellingPriceObj = $(this ).closest('tr').find('.total-selling-price');
        var costPriceObj = $(this ).closest('tr').find('.total-cost-price');
        var sellingPriceObj = $(this ).closest('tr').find('.selling-price-amount');

        var url = 'index.php?module=tradingin_quote&_spAction=updateSellingLineItems&showHTML=0';

        $.get(url, {rec_id: rec_id, discount_type: discount_type}, function(json){
            totalSellingPriceObj.html(json.totalSellingPrice);
            costPriceObj.html(json.totalCostPrice);
            sellingPriceObj.html(json.selling_price);
            $('tr.summary-row .totalCp').html(json.total_cost_price_sum);
            $('tr.summary-row .discountSum').html(json.discount_percentage_amount_sum);
            $('tr.summary-row .totalSp').html(json.total_selling_price_sum);
        });
    }
}

/*var Links = $.extend(Links, {
    addNewGridRecord: function(e) {
        e.preventDefault();
        var linkName      = $(this).closest('.linkPortalWrapper').attr('id');
        var lnkRoomActual = $(this).closest('.linkPortalWrapper').attr('lnkRoomActual');

        var recId = $(this).attr('recId');
        var count = $(this).prev("input[name='add_record_count']").val();
        var url = $(this).attr('link')+"&add_record_count="+count;
        var exp = {
            portalDiv: $(this).closest('.linkPortalWrapper')
        }

        $.post(url, function(data){
            Links.reloadPortalRecords(linkName, lnkRoomActual, recId, 'edit', exp);
        });
    }

}); */
