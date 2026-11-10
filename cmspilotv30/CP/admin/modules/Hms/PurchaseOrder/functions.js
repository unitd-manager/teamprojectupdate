Util.createCPObject('cpm.hms.purchaseOrder');

var poProductChecked = [];
cpm.hms.purchaseOrder = {
    init: function(){
        $("select[name='company_id']").change(function() {
            var company_id = $("select[name='company_id']").val();
            var url = 'index.php?module=hms_contact&_spAction=multipleAddress&showHTML=0';
            $.get(url, {company_id: company_id}, function (data) {
                $("select[name='company_address_id']").cp_loadSelect(data);
            }, 'json');
        });

        $('.click-all-top .check-all').livequery('click', function(e){
            e.preventDefault();
            cpm.hms.purchaseOrder.checkAllCol.call(this);
        });

        $('.click-all-top .uncheck-all').livequery('click', function(e){
            e.preventDefault();
            cpm.hms.purchaseOrder.uncheckAllCol.call(this);
        });

        $("input[name='product_title[]']")
        .livequery(cpm.hms.purchaseOrder.poProductTitle);

        /*$("select[name='product_id[]']").livequery('change', function(){
            var product_id = $(this).val();
            var purchase_order_id = $("input[name='purchase_order_id']").val();
            var parent = $(this).closest('tr');
            var url = 'index.php?module=hms_purchaseOrder&_spAction=lastQuotedPrice&showHTML=0';
            $.get(url, {product_id: product_id, purchase_order_id:purchase_order_id}, function (data) {
                $(".last_price", parent).val(data);
            }, 'json');

        });*/


        $('#portalForm select#fld_product_group_id').livequery('change', function(){
           cpm.hms.purchaseOrder.loadCategoryDropdown.call(this);
        });

        //for edit portal product
        $('#portalForm .costs input, #portalForm .costs select')
        .live('change', cpm.hms.purchaseOrder.calculateProductCosting);

        $('#portalForm #fld_other_costs_1_label').unbind('change');
        $('#portalForm #fld_other_costs_2_label').unbind('change');
        $('#portalForm #fld_other_costs_3_label').unbind('change');

        $('#portalForm a.next').live('click', cpm.hms.purchaseOrder.nextLine);
        $('#portalForm a.previous').live('click', cpm.hms.purchaseOrder.previousLine);

        
        /* Add Product */
        $('#AddProduct').live('click', function (e){
                var title = "Add Product";
                var purchase_order_id = $(this).attr('purchase_order_id');
                e.preventDefault();
                var expObj = {
                    validate: true
                   ,callbackOnSuccess: function(){
                        var msg = 'Product Added Successfully';
                        Util.alert(msg, function(){
                            Util.closeAllDialogs();
                            cpm.hms.purchaseOrder.reloadPoProductLink(purchase_order_id);
                        });
                    }
                }
            Util.openFormInDialog.call(this, 'addMultipleLineItemForm', title, 'auto', 'auto', expObj);
        });

        /* Edit Product */
        $('.EditPoProduct').live('click', function (e){
            var title = "Edit Product";
            e.preventDefault();
            var purchase_order_id = $(this).attr('purchase_order_id');
            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Product Updated Successfully';
                    Util.alert(msg, function(){ 
                        Util.closeAllDialogs();
                        cpm.hms.purchaseOrder.reloadPoProductLink(purchase_order_id);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'EditPoProductForm', title, 530, 362, expObj);
        });

        /* Edit Product */
        $('.deletePoProduct').live('click', function (e){
            var po_product_id = $(this).attr('po_product_id');
            var purchase_order_id = $(this).attr('purchase_order_id');
            var url = 'index.php?module=hms_purchaseOrder&_spAction=deletePoProduct'
                    + '&showHTML=0';
            $.get(url, {po_product_id:po_product_id, purchase_order_id:purchase_order_id} ,function(html){
                alert('Deleted Successfully!');
                cpm.hms.purchaseOrder.reloadPoProductLink(purchase_order_id);
            });
        });

        /* Product Stock */
        $('.stockDetailLocation').live('click', function (e){
            e.preventDefault();
            var expObj = {
                beforeCloseFn: function(){
                    Util.closeAllDialogs();
                }
            }
            Util.openDialogForLink.call(this, 'Stock Details', 698, 294, expObj);
        });

        $(".productViewHistory").live('click', function (e){
            var po_product_id = $(this).attr('po_product_id');
            e.preventDefault();

            var expObj = {
                beforeCloseFn: function(){
                    Util.closeAllDialogs();
                }
            }
            Util.openDialogForLink.call(this, 'Product Sales History', 700, 500, expObj);
        });
        

        /* Adding row in new Line Item */
        $(".addSinglePoRow").livequery('click', function (e){
            var url = 'index.php?module=hms_purchaseOrder&_spAction=addSingleLineItem'
                    + '&showHTML=0';

            $.get(url, '' ,function(html){
                $('#addMultipleLineItemForm tr:last').after(html);
            });
        });

        $("a.clearPoProductItem").livequery('click', function (e){
            var titleObj     = $(this).closest('tr').find('.poProductTitle');
            var productIdObj = $(this).closest('tr').find('.product_id_hidden');
            var amountObj    = $(this).closest('tr').find('.lineItemDescription');
            var quantityObj  = $(this).closest('tr').find('.poQuantity');

            titleObj.val('');
            productIdObj.val('');
            amountObj.val('');
            quantityObj.val('');
        });

        $('.poProductId').livequery('click', function(e){
            var poProductId  = $(this).val();
            var is_checked   = $(this).is(':checked');
            if(is_checked == true){
                poProductChecked.push(poProductId);
            }else{
                var index = poProductChecked.indexOf(poProductId);
                poProductChecked.splice(index, 1);
            }
        });

        $('.qtyAllDelivered').livequery('click', function(e){
            if (poProductChecked.length == 0){
                Util.alert('Please select atleast one product!');
            }else{
                var purchase_order_id = $(this).attr('purchase_order_id');
                var url = "index.php?_topRm=inventory&module=hms_purchaseOrder&_spAction=UpdateQtyDelivered";
                Util.showProgressInd();
                $.get(url, {poProductChecked:poProductChecked} ,function(html){
                    cpm.hms.purchaseOrder.reloadPoProductLink(purchase_order_id);
                    var colPo = $('.click-all-top .uncheck-all').parent().index();
                    $('.room-poProduct-table tbody tr').each(function(rowIndex, trObj) {
                        var checkbox = $(trObj).find('td:eq(' + colPo + ') input');
                        checkbox.removeAttr('checked');

                        var is_checked = checkbox.is(':checked');
                        if(is_checked == false){
                            var index = poProductChecked.indexOf(checkbox.val());
                            poProductChecked.splice(index, 1);
                        }
                    });

                    poProductChecked = [];
                    Util.hideProgressInd();
                    Util.alert('All Quantity Delivered and Added to The Stock!');
                });
            }
        });

        var timeoutId2;
        $(".productSearchAuto").livequery("keyup", function (){
            clearTimeout(timeoutId2);
            var searchTreatment = $(this).val();
            var purchase_order_id = $('#record_id').val();

            timeoutId2 = setTimeout(function() {
                var url = 'index.php?module=hms_purchaseOrder&_spAction=AddProductDetail&showHTML=0';
                $.get(url,{purchase_order_id:purchase_order_id, searchTreatment: searchTreatment}, function(html){
                    $('#AddProductPortal').html(html);
                });
            }, 1000);
        });
    },

    checkAllCol: function(e){
        var colPo = $(this).parent().index();
        $('.room-poProduct-table tbody tr').each(function(rowIndex, trObj) {
            var checkbox = $(trObj).find('td:eq(' + colPo + ') input');
            checkbox.attr('checked', 'checked');
            var is_checked   = checkbox.is(':checked');
            if(is_checked == true){
                poProductChecked.push(checkbox.val());
            }
        });
    },

    uncheckAllCol: function(e){
        var colPo = $(this).parent().index();
        $('.room-poProduct-table tbody tr').each(function(rowIndex, trObj) {
            var checkbox = $(trObj).find('td:eq(' + colPo + ') input');
            checkbox.removeAttr('checked');

            var is_checked   = checkbox.is(':checked');
            if(is_checked == false){
                var index = poProductChecked.indexOf(checkbox.val());
                poProductChecked.splice(index, 1);
            }
        });
    },

    reloadPoProductLink: function(purchase_order_id){
        var url = 'index.php?module=hms_purchaseOrder&_spAction=addProduct&showHTML=0';
        Util.showProgressInd();
        $.get(url, {purchase_order_id: purchase_order_id}, function(html){
            Util.hideProgressInd();
            $('#productLinkPortal').html(html);
        });
    },

    poProductTitle: function() {
        var titleObj = this;
        $(titleObj).autocomplete({
             source : 'index.php?module=hms_purchaseOrder&_spAction=searchProductTitle&showHTML=0'
            ,minLength : 3
            ,selectFirst: true
            ,autoFocus: true
            ,select: function(event, ui) {
                var selectedObj = ui.item;
                var product_id = selectedObj.id
                var parent = $(this).closest('td');
                $("input[name='product_id[]']", parent).val(product_id);
                //$(this).after("<input type='hidden' name='product_id[]' value=" + product_id + ">");
            }
        });
    },

    calculateProductCosting: function(e){
        var url = "index.php?module=hms_product"
                + "&_spAction=calculatedValuesItems"
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

    nextLine: function(e) {
        e.preventDefault();
        var record_id = $('#portalForm input[name=quote_items_id]').val();
        var nextRow = $('.hms_treatment__hms_productLink table tr[recid=' + record_id + ']').next();
        if (nextRow.length > 0) {
            $('#dialog').dialog('destroy');
            $('#dialog').remove();
            nextRow.find('.editPortalRecord').click();
        }
    },

    previousLine: function(e) {
        e.preventDefault();
        var record_id = $('#portalForm input[name=quote_items_id]').val();
        var prevRow = $('.hms_treatment__hms_productLink table tr[recid=' + record_id + ']')
                      .prev('[recid]');
        if (prevRow.length > 0) {
            $('#dialog').dialog('destroy');
            $('#dialog').remove();
            prevRow.find('.editPortalRecord').click();
        }
    },

}

