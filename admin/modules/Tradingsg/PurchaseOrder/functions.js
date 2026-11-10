Util.createCPObject('cpm.tradingsg.purchaseOrder');

var poProductChecked = [];
var deliveryOrderChecked = [];
cpm.tradingsg.purchaseOrder = {
    init: function(){
        $("select[name='company_id']").change(function() {
            var company_id = $("select[name='company_id']").val();
            var url = 'index.php?module=tradingsg_contact&_spAction=multipleAddress&showHTML=0';
            $.get(url, {company_id: company_id}, function (data) {
                $("select[name='company_address_id']").cp_loadSelect(data);
            }, 'json');
        });

        $('.click-all-top .check-all').livequery('click', function(e){
            e.preventDefault();
            cpm.tradingsg.purchaseOrder.checkAllCol.call(this);
        });

        $('.click-all-top .uncheck-all').livequery('click', function(e){
            e.preventDefault();
            cpm.tradingsg.purchaseOrder.uncheckAllCol.call(this);
        });

        $("input[name='product_title[]']")
        .livequery(cpm.tradingsg.purchaseOrder.poProductTitle);

        $("input[name='product_title_list[]']")
        .livequery(cpm.tradingsg.purchaseOrder.poProductTitleList);

        // Auto-calculate price = quantity * cost_price
        $("input[name='qty[]'], input[name='cost_price[]']").livequery('change', function(){
            var parent = $(this).closest('tr');
            var qty = parseFloat($("input[name='qty[]']", parent).val()) || 0;
            var costPrice = parseFloat($("input[name='cost_price[]']", parent).val()) || 0;
            var totalPrice = qty * costPrice;
            $("input[name='selling_price[]']", parent).val(totalPrice.toFixed(2));
        });

        // Auto-calculate price in edit form = quantity * cost_price
        $('#EditPoProductForm input[name="qty"], #EditPoProductForm input[name="cost_price"]').livequery('change', function(){
            var qty = parseFloat($('#EditPoProductForm input[name="qty"]').val()) || 0;
            var costPrice = parseFloat($('#EditPoProductForm input[name="cost_price"]').val()) || 0;
            var totalPrice = qty * costPrice;
            $('#EditPoProductForm input[name="selling_price"]').val(totalPrice.toFixed(2));
        });

        /*$("select[name='product_id[]']").livequery('change', function(){
            var product_id = $(this).val();
            var purchase_order_id = $("input[name='purchase_order_id']").val();
            var parent = $(this).closest('tr');
            var url = 'index.php?module=tradingsg_purchaseOrder&_spAction=lastQuotedPrice&showHTML=0';
            $.get(url, {product_id: product_id, purchase_order_id:purchase_order_id}, function (data) {
                $(".last_price", parent).val(data);
            }, 'json');

        });*/


        $('#portalForm select#fld_product_group_id').livequery('change', function(){
           cpm.tradingsg.purchaseOrder.loadCategoryDropdown.call(this);
        });

        //for edit portal product
        $('#portalForm .costs input, #portalForm .costs select')
        .live('change', cpm.tradingsg.purchaseOrder.calculateProductCosting);

        $('#portalForm #fld_other_costs_1_label').unbind('change');
        $('#portalForm #fld_other_costs_2_label').unbind('change');
        $('#portalForm #fld_other_costs_3_label').unbind('change');

        $('#portalForm a.next').live('click', cpm.tradingsg.purchaseOrder.nextLine);
        $('#portalForm a.previous').live('click', cpm.tradingsg.purchaseOrder.previousLine);

        /* Add Product */
        $('.addNewProductPopup').livequery('click', function (e){
            var title = "Add Product";
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    /*var msg = 'Product Added Successfully!';
                    Util.alert(msg, function(){*/
                        $('#dialog1').dialog('close');
                        $('#dialog1').dialog('destroy');
                        $('#dialog1').remove();
                    //});
                }
            }
            Util.openFormInDialog.call(this, 'NewProductPortalForm', title, 530, 232, expObj);
        });

        /* Add Product */
        $('#AddProduct').live('click', function (e){
                var title = "Add Product";
                var purchase_order_id = $(this).attr('purchase_order_id');
                e.preventDefault();
                var expObj = {
                    validate: true
                   ,callbackOnSuccess: function(){
                        Util.closeAllDialogs();
                        var msg = 'Product Added Successfully';
                        Util.alert(msg, function(){
                            //cpm.tradingsg.purchaseOrder.reloadPoProductLink(purchase_order_id);
                            window.location.reload(true);
                        });
                    }
                }
            Util.openFormInDialog.call(this, 'addMultipleLineItemForm', title, '1100', '550', expObj);
        });

        /* Add Product List*/
        $('#AddProductList').live('click', function (e){
                var title = "Add Existing Product";
                e.preventDefault();
                var expObj = {
                    validate: true
                   ,callbackOnSuccess: function(){
                        Util.closeAllDialogs();
                        var msg = 'Product Added Successfully';
                        Util.alert(msg, function(){
                            //cpm.tradingsg.purchaseOrder.reloadPoProductLink(purchase_order_id);
                            window.location.reload(true);
                        });
                    }
                }
            Util.openFormInDialog.call(this, 'addMultipleLineItemListForm', title, 'auto', 'auto', expObj);
        });

        /* Add New Product */
        $('#AddNewProduct').live('click', function (e){
                var title = "Add New Product(s)";
                var purchase_order_id = $(this).attr('purchase_order_id');
                e.preventDefault();
                var expObj = {
                    validate: true
                   ,callbackOnSuccess: function(){
                        Util.closeAllDialogs();
                        var msg = 'Product Added Successfully';
                        Util.alert(msg, function(){
                            //cpm.tradingsg.purchaseOrder.reloadPoProductLink(purchase_order_id);
                            window.location.reload(true);
                        });
                    }
                }
            Util.openFormInDialog.call(this, 'addNewproductForm', title, '900', '600', expObj);
        });

        /* Add New Product List*/
        $('#AddNewProductList').live('click', function (e){
                var title = "Add New Product(s)";
                e.preventDefault();
                var expObj = {
                    validate: true
                   ,callbackOnSuccess: function(){
                        Util.closeAllDialogs();
                        var msg = 'Product Added Successfully';
                        Util.alert(msg, function(){
                            //cpm.tradingsg.purchaseOrder.reloadPoProductLink(purchase_order_id);
                            window.location.reload(true);
                        });
                    }
                }
            Util.openFormInDialog.call(this, 'addNewproductListForm', title, 'auto', 'auto', expObj);
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
                        cpm.tradingsg.purchaseOrder.reloadPoProductLink(purchase_order_id);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'EditPoProductForm', title, 530, 362, expObj);
        });

        /* Add Supplier */
        $('#addSupplier').livequery('click', function (e){
            var title = "Add Supplier";
            e.preventDefault();
            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'New supplier successfully created';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                    });
                }
            }
            Util.openFormInDialog.call(this, 'supplierPortalForm', title, 530, 362, expObj);
        });

        /* Edit Product */
        $('.deletePoProduct').live('click', function (e){
            var po_product_id = $(this).attr('po_product_id');
            var purchase_order_id = $(this).attr('purchase_order_id');
            var url = 'index.php?module=tradingsg_purchaseOrder&_spAction=deletePoProduct'
                    + '&showHTML=0';
            $.get(url, {po_product_id:po_product_id, purchase_order_id:purchase_order_id} ,function(html){
                alert('Deleted Successfully!');
                cpm.tradingsg.purchaseOrder.reloadPoProductLink(purchase_order_id);
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

        $('.deliveryOrderEdit').livequery('click', function (e){
            var title      = "Edit Delivery Order";
            var delivery_order_id = $(this).attr('delivery_order_id');

            e.preventDefault();
            var expObj = {
                validate: true,
                callbackOnSuccess: function(data){
                    Util.closeAllDialogs();
                    var mgsalert = 'Updated Delivery Order Successfully!';
                    var n = noty({
                        text: mgsalert,
                        type: 'confirm',
                        dismissQueue: true,
                        layout: 'topCenter',
                        theme: 'defaultTheme',
                        timeout: 5000,
                    });
                    Util.hideProgressInd();
                }
            }

            Util.openFormInDialog.call(this, 'editForDO', title, 700, 500, expObj);
        });


        /* Adding row in new Line Item */
        $(".addSinglePoRow").livequery('click', function (e){
            var url = 'index.php?module=tradingsg_purchaseOrder&_spAction=addSingleLineItem'
                    + '&showHTML=0';

            $.get(url, '' ,function(html){
                $('#addMultipleLineItemForm tr:last').after(html);
            });
        });

        /* Adding row in new Line Item */
        $(".addSinglePoRowNew").livequery('click', function (e){
            var url = 'index.php?module=tradingsg_purchaseOrder&_spAction=addSingleLineItemNew'
                    + '&showHTML=0';

            $.get(url, '' ,function(html){
                $('#addNewproductForm tr:last').after(html);
            });
        });

        /* Adding row in new Line Item */
        $(".addSinglePoRowList").livequery('click', function (e){
            var url = 'index.php?module=tradingsg_purchaseOrder&_spAction=addSingleLineItemList'
                    + '&showHTML=0';

            $.get(url, '' ,function(html){
                $('#addMultipleLineItemListForm tr:last').after(html);
            });
        });

        /* Adding row in new Line Item */
        $(".addSinglePoRowNewList").livequery('click', function (e){
            var url = 'index.php?module=tradingsg_purchaseOrder&_spAction=addSingleLineItemNewList'
                    + '&showHTML=0';

            $.get(url, '' ,function(html){
                $('#addNewproductListForm tr:last').after(html);
            });
        });

        $("a.clearPoProductItem").livequery('click', function (e){
            var titleObj     = $(this).closest('tr').find('.poProductTitle');
            var productIdObj = $(this).closest('tr').find('.product_id_hidden');
            var amountObj    = $(this).closest('tr').find('.lineItemDescription');
            var quantityObj  = $(this).closest('tr').find('.poQuantity');
            var gstObj  = $(this).closest('tr').find('.poGst');
            var inventoryCodeObj  = $(this).closest('tr').find('.inventoryCode');
            var itemCodeObj  = $(this).closest('tr').find('.itemCode');
            var costPriceObj  = $(this).closest('tr').find('.poCostPrice');
            var hsnObj  = $(this).closest('tr').find('.hsn');
            var productWeightObj  = $(this).closest('tr').find('.productWeight');
            var supplierObj  = $(this).closest('tr').find('.supplier');

            titleObj.val('');
            productIdObj.val('');
            amountObj.val('');
            quantityObj.val('');
            gstObj.val('');
            costPriceObj.val('');
            inventoryCodeObj.html('');
            itemCodeObj.html('');
            hsnObj.html('');
            productWeightObj.html('');
        });

        $("a.clearAllItem").livequery('click', function (e){
            if (!confirm("Do you want to clear all items?")){
                return;
            }
            var titleObj     = $('.poProductTitle');
            var productIdObj = $('.product_id_hidden');
            var amountObj    = $('.lineItemDescription');
            var quantityObj  = $('.poQuantity');
            var gstObj  = $('.poGst');
            var inventoryCodeObj  = $('.inventoryCode');
            var itemCodeObj  = $('.itemCode');
            var costPriceObj  = $('.poCostPrice');
            var hsnObj  = $('.hsn');
            var productWeightObj  = $('.productWeight');
            var supplierObj  = $('.supplier');

            titleObj.val('');
            productIdObj.val('');
            amountObj.val('');
            quantityObj.val('');
            gstObj.val('');
            costPriceObj.val('');
            inventoryCodeObj.html('');
            itemCodeObj.html('');
            supplierObj.html('');
            hsnObj.val('');
            productWeightObj.val('');
        });

        $("a.applyQtyAll").livequery('click', function (e){
            if (!confirm("Do you want to apply qty for all items?")){
                return;
            }
            var quantityObj  = $('.poQuantity');
            var qty = $('.allQty').val();

            quantityObj.val(qty);
        });

        $("a.loadMOL").livequery('click', function (e){
            if (!confirm("Do you want to load all minimum stock level items?")){
                return;
            }
            var titleObj     = $('.poProductTitle');
            var productIdObj = $('.product_id_hidden');
            var amountObj    = $('.lineItemDescription');
            var quantityObj  = $('.poQuantity');
            var gstObj  = $('.poGst');
            var inventoryCodeObj  = $('.inventoryCode');
            var itemCodeObj  = $('.itemCode');
            var costPriceObj  = $('.poCostPrice');
            var supplierObj  = $('.supplier');

            var url = "index.php?module=tradingsg_purchaseOrder&_spAction=searchMOLProductList&showHTML=0";
            Util.showProgressInd();
            $.get(url ,function(html){
                $('#po_productTable tr:first').after(html);
                /*titleObj.val(json.title);
                productIdObj.val(json.id);
                amountObj.val(json.price);
                quantityObj.val(json.cost_price);
                gstObj.val(json.gst);
                costPriceObj.val(json.cost_price);
                inventoryCodeObj.html(json.inventory_code);
                itemCodeObj.html(json.item_code);
                supplierObj.html(json.company_name);*/
            });
            Util.hideProgressInd();
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

        $('.deliveryOrderId').livequery('click', function(e){
            var deliveryOrderId  = $(this).val();
            var is_checked   = $(this).is(':checked');
            if(is_checked == true){
                deliveryOrderChecked.push(deliveryOrderId);
            }else{
                var index = deliveryOrderChecked.indexOf(deliveryOrderId);
                deliveryOrderChecked.splice(index, 1);
            }
        });

        $('.qtyAllDeliveredPo').livequery('click', function(e){
            if (poProductChecked.length == 0){
                Util.alert('Please select atleast one product!');
            }else{
                if (!confirm("Do you want to add all qty to stock?")){
                    return;
                }
                var purchase_order_id = $(this).attr('purchase_order_id');
                var url = "index.php?_topRm=inventory&module=tradingsg_purchaseOrder&_spAction=UpdateQtyDelivered";
                Util.showProgressInd();
                $.get(url, {poProductChecked:poProductChecked} ,function(html){
                    cpm.tradingsg.purchaseOrder.reloadPoProductLink(purchase_order_id);
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
                    //window.location.reload(true);
                });
            }
        });

        $('.deliveryOrder').livequery('click', function(e){
            if (deliveryOrderChecked.length == 0){
                Util.alert('Please select atleast one product!');
            }else{
                if (!confirm("Do you want to create delivery order?")){
                    return;
                }
                var purchase_order_id = $(this).attr('purchase_order_id');
                var url = "index.php?_topRm=inventory&module=tradingsg_purchaseOrder&_spAction=createDeliveryOrder";
                Util.showProgressInd();
                $.get(url, {deliveryOrderChecked:deliveryOrderChecked, purchase_order_id:purchase_order_id} ,function(html){
                    cpm.tradingsg.purchaseOrder.reloadPoProductLink(purchase_order_id);
                    cpm.tradingsg.purchaseOrder.reloadDeliveryOrderLink(purchase_order_id);

                    deliveryOrderChecked = [];
                    Util.hideProgressInd();
                    var mgsalert = 'Delivery Order Created Successfully!';
                    var n = noty({
                        text: mgsalert,
                        type: 'confirm',
                        dismissQueue: true,
                        layout: 'topCenter',
                        theme: 'defaultTheme',
                        timeout: 5000,
                    });
                    //window.location.reload(true);
                });
            }
        });

        $("a.duplicatePO").livequery('click', function (e){

            if (!confirm("Are you sure you want to duplicate the PO?")){
                return;
            }
            var purchase_order_id = $('#record_id').val();

            var url = 'index.php?module=tradingsg_purchaseOrder&_spAction=duplicatePO&showHTML=0' +
                          '&purchase_order_id=' + purchase_order_id + '&linkedProduct=' + 1;

            $.post(url, function (json) {
                if (json.status == 'error') {
                    Util.alert(json.errorMsg);
                    return;
                }
                document.location = json.returnUrl;
            }, 'json');
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
        var url = 'index.php?module=tradingsg_purchaseOrder&_spAction=addProduct&showHTML=0';
        Util.showProgressInd();
        $.get(url, {purchase_order_id: purchase_order_id}, function(html){
            Util.hideProgressInd();
            $('#productLinkPortal').html(html);
        });
    },

    reloadDeliveryOrderLink: function(purchase_order_id){
        var url = 'index.php?module=tradingsg_purchaseOrder&_spAction=deliveryOrderPortal&showHTML=0';
        Util.showProgressInd();
        $.get(url, {purchase_order_id: purchase_order_id}, function(html){
            Util.hideProgressInd();
            $('#deliveryOrderPortal').html(html);
        });
    },

    poProductTitle: function() {
        var titleObj = this;
        var supplier_id = $('input[name=supplier_id]').val();
        $(titleObj).autocomplete({
             source : 'index.php?module=tradingsg_purchaseOrder&_spAction=searchProductTitle&supplier_id='+supplier_id+'&showHTML=0'
            ,minLength : 2
            ,selectFirst: true
            ,autoFocus: true
            ,select: function(event, ui) {
                var selectedObj    = ui.item;
                var product_id     = selectedObj.id;
                var item_code      = selectedObj.item_code;
                var price          = selectedObj.price;
                var gst            = selectedObj.gst;
                var cost_price     = selectedObj.cost_price;
                var product_code   = selectedObj.product_code;
                var parent         = $(this).closest('tr');

                $("input[name='product_id[]']", parent).val(product_id);
                $(".itemCode", parent).html(item_code);
                $("input[name='selling_price[]']", parent).val(price);
                $("input[name='gst[]']", parent).val(gst);
                $("input[name='cost_price[]']", parent).val(cost_price);
                $(".productCode", parent).html(product_code);
                //$(this).after("<input type='hidden' name='product_id[]' value=" + product_id + ">");
            }
        });
    },

    poProductTitleList: function() {
        var titleObj = this;
        $(titleObj).autocomplete({
             source : 'index.php?module=tradingsg_purchaseOrder&_spAction=searchProductTitle&showHTML=0'
            ,minLength : 2
            ,selectFirst: true
            ,autoFocus: true
            ,select: function(event, ui) {
                var selectedObj = ui.item;
                var product_id = selectedObj.id;
                var inventory_code = selectedObj.inventory_code;
                var item_code = selectedObj.item_code;
                var supplier = selectedObj.company_name;
                var supplier_id = selectedObj.supplier_id;
                var price = selectedObj.price;
                var gst = selectedObj.gst;
                var cost_price = selectedObj.cost_price;
                var parent = $(this).closest('tr');
                $("input[name='product_id[]']", parent).val(product_id);
                $(".inventoryCode", parent).html(inventory_code);
                $(".itemCode", parent).html(item_code);
                $(".supplier", parent).html(supplier);
                $("input[name='supplier_id[]']", parent).val(supplier_id);
                $("input[name='selling_price[]']", parent).val(price);
                $("input[name='gst[]']", parent).val(gst);
                $("input[name='cost_price[]']", parent).val(cost_price);
                //$(this).after("<input type='hidden' name='product_id[]' value=" + product_id + ">");
            }
        });
    },

    calculateProductCosting: function(e){
        var url = "index.php?module=tradingsg_product"
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
        var nextRow = $('.tradingsg_treatment__tradingsg_productLink table tr[recid=' + record_id + ']').next();
        if (nextRow.length > 0) {
            $('#dialog').dialog('destroy');
            $('#dialog').remove();
            nextRow.find('.editPortalRecord').click();
        }
    },

    previousLine: function(e) {
        e.preventDefault();
        var record_id = $('#portalForm input[name=quote_items_id]').val();
        var prevRow = $('.tradingsg_treatment__tradingsg_productLink table tr[recid=' + record_id + ']')
                      .prev('[recid]');
        if (prevRow.length > 0) {
            $('#dialog').dialog('destroy');
            $('#dialog').remove();
            prevRow.find('.editPortalRecord').click();
        }
    },
}
cpm.tradingsg.purchaseOrder.afterNewSupplier = function(){
    Util.closeAllDialogs();
    Util.alert('New supplier successfully created.', function(){
        cpm.tradingsg.purchaseOrder.loadSupplier();
        //window.location.reload(true);
    });
},
cpm.tradingsg.purchaseOrder.loadSupplier = function(){
    var url = 'index.php?module=tradingsg_supplier&_spAction=supplierList&showHTML=0';
    $.get(url, function (data) {
        $('#fld_company_id_supplier').cp_loadSelect(data);
    }, 'json');
}


