Util.createCPObject('cpm.hms.labs');

cpm.hms.labs = {
    init: function(){
        $("select[name='company_id']").change(function() {
            var company_id = $("select[name='company_id']").val();
            var url = 'index.php?module=hms_contact&_spAction=multipleAddress&showHTML=0';
            $.get(url, {company_id: company_id}, function (data) {
                $("select[name='company_address_id']").cp_loadSelect(data);
            }, 'json');
        });

        $("select[name='supplier_category']").livequery('change', function(){
            var url = 'index.php?module=hms_labs&_spAction=labsSupplierJSON&showHTML=0';
            var supplier_category = $(this).val();
            $.get(url, {supplier_category: supplier_category}, function (data) {
                $("select[name='supplier_id']").cp_loadSelect(data);
            }, 'json');

        });

        $('#portalForm select#fld_product_group_id').livequery('change', function(){
           cpm.hms.labs.loadCategoryDropdown.call(this);
        });

        //for edit portal product
        $('#portalForm .costs input, #portalForm .costs select')
        .live('change', cpm.hms.labs.calculateProductCosting);

        $('#portalForm #fld_other_costs_1_label').unbind('change');
        $('#portalForm #fld_other_costs_2_label').unbind('change');
        $('#portalForm #fld_other_costs_3_label').unbind('change');

        $('#portalForm a.next').live('click', cpm.hms.labs.nextLine);
        $('#portalForm a.previous').live('click', cpm.hms.labs.previousLine);


        /* Add Product */
        $('#AddProduct').livequery('click', function (e){
                var title = "Add Product";
                e.preventDefault();
                var expObj = {
                    validate: true
                   ,callbackOnSuccess: function(){
                        var msg = 'Product Added Successfully';
                        Util.alert(msg, function(){
                            Util.closeAllDialogs();
                            //window.location.reload(true);
                        });
                    }
                }
            Util.openFormInDialog.call(this, 'addMultipleLineItemForm', title, 1100, 500, expObj);
        });

        /* Adding row in new Line Item */
        $(".addSinglePoRow").livequery('click', function (e){
            var url = 'index.php?module=hms_labs&_spAction=addSingleLineItem'
                    + '&showHTML=0';

            $.get(url, '' ,function(html){
                $('#addMultipleLineItemForm tr:last').after(html);
            });
        });

        /* Add Labs Record*/
        $('.m-hms_labs #addLabsRecord').livequery('click', function (e){
            var title = "Create Record";
            var patient_visit_id   = $(this).attr('patient_visit_id');
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Record created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        cpm.hms.labs.reloadLabsTab(patient_visit_id);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 450, 250, expObj);
        });


        $("select[name='supplier_category']").livequery('change', function(){
            var url = 'index.php?module=hms_patientVisit&_spAction=labsSupplierJSON&showHTML=0';
            var supplier_category = $(this).val();
            $.get(url, {supplier_category: supplier_category}, function (data) {
                $("select[name='supplier_id']").cp_loadSelect(data);
            }, 'json');

        });

        $('.m-hms_labs #editLabsRecord').livequery('click', function (e){
            var title = "Edit Record";
            var patient_visit_id   = $(this).attr('patient_visit_id');
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Record created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        cpm.hms.patientVisit.reloadLabsTab(patient_visit_id);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'EditLabsRecordportalForm', title, 450, 250, expObj);
        });

        $('.deleteLabsRecord').livequery('click', function (){
            Util.showProgressInd();
            var url = 'index.php?module=hms_patientVisit&_spAction=deleteLabsRecord&showHTML=0';
            var labs_id = $(this).attr('labs_id');
            var patient_visit_id   = $(this).attr('patient_visit_id');
            $.get(url,  {labs_id:labs_id}, function(html){
                cpm.hms.patientVisit.reloadLabsTab(patient_visit_id);
            });
        });

        $('.m-hms_labs #acrylicFormDenture').livequery('click', function (e){
            var title = "DENTURE EXPRESS";
            e.preventDefault();
            var patient_visit_id = $('#fld_patient_visit_id').val();
            var labs_id = $('#record_id').val();
            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Record created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        cpm.hms.labs.reloadLabsTab(patient_visit_id, labs_id);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'acrylicDentureForm', title, 810, 508, expObj);
        });

        $('.m-hms_labs #addCeramicForm').livequery('click', function (e){
            var title = "CERAMIC FORM";
            e.preventDefault();
            var patient_visit_id = $('#fld_patient_visit_id').val();
            var labs_id = $('#record_id').val();
            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Record created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        cpm.hms.labs.reloadLabsTab(patient_visit_id, labs_id);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'AddCeramicFormDetail', title, 810, 508, expObj);
        });

        $('.m-hms_labs #addOrthodontic').livequery('click', function (e){
            var title = "ORTHODONTIC FORM";
            e.preventDefault();
            var patient_visit_id = $('#fld_patient_visit_id').val();
            var labs_id = $('#record_id').val();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Record created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        cpm.hms.labs.reloadLabsTab(patient_visit_id, labs_id);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'ChromeFormDetail', title, 810, 508, expObj);
        });

        $('input[name="selected_tooth2[]"]').live('click', function (e){
            var tooth_id         = $(this).val();
            var is_checked       = $(this).is(':checked');
            var patient_visit_id = $('#patient_visit_id').val();
            var checboxid        = $(this).attr('Checkbox_ID');
            var symbol_name      = $('#bridge_id').val();
            var prevTooth_count  = $('#toothPrev_count').val();
            var tooth_form_type  = $('#fld_tooth_form_type').val();
            var labs_id          = $('#fld_labs_id').val();

            if(is_checked == true){
                if(symbol_name != undefined){
                    var i;
                    for (i = parseInt(prevTooth_count); i <= parseInt(checboxid); i++) {
                        $("#selected_tooth2_"+i).attr( 'checked', true );
                        var tooth_id = $("#selected_tooth2_"+i).val();
                        var url  = 'index.php?module=hms_patientVisit&_spAction=addPerioChartRecord&showHTML=0';
                        $.get(url, {symbol_name: symbol_name, tooth_id:tooth_id, patient_visit_id:patient_visit_id, tooth_form_type:tooth_form_type, labs_id:labs_id}, function(html){
                        });
                    }

                    for (i = parseInt(prevTooth_count); i >= parseInt(checboxid); i--) {
                        $("#selected_tooth2_"+i).attr( 'checked', true );
                        var tooth_id = $("#selected_tooth2_"+i).val();
                        var url  = 'index.php?module=hms_patientVisit&_spAction=addPerioChartRecord&showHTML=0';
                        $.get(url, {symbol_name: symbol_name, tooth_id:tooth_id, patient_visit_id:patient_visit_id, tooth_form_type:tooth_form_type, labs_id:labs_id}, function(html){
                        });
                    }

                    cpm.hms.labs.reloadToothList2(patient_visit_id, tooth_form_type, labs_id);
                    cpm.hms.labs.reloadToothList3(patient_visit_id, tooth_form_type, labs_id);
                }else{
                    cpm.hms.labs.SelectSymbolsForm(checboxid, tooth_id, patient_visit_id);
                }
            }
        });

        $('input[name="selected_tooth3[]"]').live('click', function (e){
            var tooth_id         = $(this).val();
            var is_checked       = $(this).is(':checked');
            var patient_visit_id = $('#patient_visit_id').val();
            var checboxid        = $(this).attr('Checkbox_ID');
            var symbol_name      = $('#bridge_id').val();
            var prevTooth_count  = $('#toothPrev_count').val();
            var tooth_form_type  = $('#fld_tooth_form_type').val();
            var labs_id          = $('#fld_labs_id').val();

            if(is_checked == true){
                if(symbol_name != undefined){
                    var i;
                    for (i = parseInt(prevTooth_count); i <= parseInt(checboxid); i++) {
                        $("#selected_tooth3_"+i).attr( 'checked', true );
                        var tooth_id = $("#selected_tooth3_"+i).val();
                        var url  = 'index.php?module=hms_patientVisit&_spAction=addPerioChartRecord&showHTML=0';
                        $.get(url, {symbol_name: symbol_name, tooth_id:tooth_id, patient_visit_id:patient_visit_id, tooth_form_type:tooth_form_type, labs_id:labs_id}, function(html){
                        });
                    }


                    for (i = parseInt(prevTooth_count); i >= parseInt(checboxid); i--) {
                        $("#selected_tooth3_"+i).attr( 'checked', true );
                        var tooth_id = $("#selected_tooth3_"+i).val();
                        var url  = 'index.php?module=hms_patientVisit&_spAction=addPerioChartRecord&showHTML=0';
                        $.get(url, {symbol_name: symbol_name, tooth_id:tooth_id, patient_visit_id:patient_visit_id, tooth_form_type:tooth_form_type, labs_id:labs_id}, function(html){
                        });
                    }

                    cpm.hms.labs.reloadToothList3(patient_visit_id, tooth_form_type, labs_id);
                    cpm.hms.labs.reloadToothList2(patient_visit_id, tooth_form_type, labs_id);
                }else{
                    cpm.hms.labs.SelectSymbolsForm(checboxid, tooth_id, patient_visit_id);
                }
            }
        });

        $('input[name="selected_Symbols[]"]').live('click', function (e){
            var symbol_name = $(this).val();
            var is_checked  = $(this).is(':checked');
            var tooth_id    = $('#tooth_id').val();
            var prevcount   = $('#Checkbox_ID').val();
            var patient_visit_id = $('#patient_visit_id').val();
            var tooth_form_type  = $('#fld_tooth_form_type').val();
            var labs_id          = $('#fld_labs_id').val();

            if(is_checked == true){
                if(symbol_name == 'Bridge'){
                    var msg = "Select the tooth to be connected?";
                    if (confirm(msg)){
                        $('#dialog1').dialog('close');
                        $('.ym-fbox-check').after("<input class='bridgeIDPassing' type='hidden' id='bridge_id' value=" + symbol_name + ">");
                        $('.ym-fbox-check').after("<input class='previousToothcount' type='hidden' id='toothPrev_count' value=" + prevcount + ">");
                    }else{
                        Util.showProgressInd();
                        var url  = 'index.php?module=hms_patientVisit&_spAction=addPerioChartRecord&showHTML=0';
                        $.get(url, {symbol_name: symbol_name, tooth_id:tooth_id, patient_visit_id:patient_visit_id, tooth_form_type:tooth_form_type, labs_id:labs_id}, function(html){
                            $('#dialog1').dialog('close');
                            Util.hideProgressInd();
                        });

                        cpm.hms.labs.reloadToothList3(patient_visit_id, tooth_form_type, labs_id);
                        cpm.hms.labs.reloadToothList2(patient_visit_id, tooth_form_type, labs_id);
                    }
                }
                else{
                    Util.showProgressInd();
                    var url  = 'index.php?module=hms_patientVisit&_spAction=addPerioChartRecord&showHTML=0';
                    $.get(url, {symbol_name: symbol_name, tooth_id:tooth_id, patient_visit_id:patient_visit_id, tooth_form_type:tooth_form_type, labs_id:labs_id}, function(html){
                        $('#dialog1').dialog('close');
                        Util.hideProgressInd();
                    });

                    cpm.hms.labs.reloadToothList3(patient_visit_id, tooth_form_type, labs_id);
                    cpm.hms.labs.reloadToothList2(patient_visit_id, tooth_form_type, labs_id);
                }
            }
        });

        $('.m-hms_labs .selectedToothSymbol').live('click', function (e){
            var tooth_id = $(this).attr('tooth_id');
            var patient_visit_id = $(this).attr('patient_visit_id');
            cpm.hms.labs.fnOpenButtonTextChangedDialog(tooth_id, patient_visit_id);
        });

        $('.m-hms_labs #generateReceipt').livequery('click', function (e){
            var patient_visit_id = $(this).attr('patient_visit_id');
            var order_id = $(this).attr('order_id');
            var labs_id = $(this).attr('labs_id');
            var title = "Create Receipt";
            e.preventDefault();
            var urlInvoice = 'index.php?_topRm=main&module=hms_labs&_spAction=createInvoiceLabs&showHTML=0';
            $.get(urlInvoice, {patient_visit_id: patient_visit_id, order_id:order_id, labs_id:labs_id}, function (html) {
                $('#fld_order_id').val(html);
                url = "index.php?module=hms_labs&_spAction=generateReceiptForm&order_id="+order_id+"&patient_visit_id="+patient_visit_id+"&labs_id="+labs_id+"&showHTML=0";
                var title = "Bill Generation";
                e.preventDefault();
                var expObj = {
                    url: url
                   ,validate: true
                   ,callbackOnSuccess: function(){
                        var msg = 'Receipt created successfully';
                        Util.alert(msg, function(){
                            Util.closeAllDialogs();
                            cpm.hms.labs.reloadReceiptPortal(order_id, labs_id);
                            cpm.hms.labs.reloadLabsTab(patient_visit_id, labs_id);
                        });
                    }
                }
                Util.openFormInDialog.call('', 'portalForm', title, 464, 406, expObj);
            });
        });

        $('.m-hms_labs input.invoiceCode').livequery('click', function (e){
            Util.showProgressInd();
            invoice_code = $(this).val();
            var checked    = $(this).attr('checked') ? 'checked' : '';
            var checkedVal = checked == 'checked' ? 1 : 0;

            var url = 'index.php?_topRm=finance&module=hms_labs&_spAction=populateReceiptAmount&showHTML=0';
            $.get(url,{invoice_code: invoice_code ,checkedVal: checkedVal}, function(html){
                $('input[id=fld_amount]').val(html);
                Util.hideProgressInd();
            });
        });

        $('.cancelReceipt').live('click', function (e){
            msg = "Do you like to cancel the Receipt?";
            var order_id = $('#fld_order_id').val();
            var labs_id  = $(this).attr('labs_id');
            var patient_visit_id = $('#fld_patient_visit_id').val();
            if (!confirm(msg)){
                return false;
            }
            else {
                var url = 'index.php?module=hms_labs&_spAction=cancelReceipt&showHTML=0';
                Util.showProgressInd();
                var receipt_code = $(this).attr('receipt_code');
                var order_id     = $(this).attr('order_id');
                $.get(url,{receipt_code: receipt_code, order_id:order_id}, function(html){
                    alert ('Receipt Cancelled Succesfully');
                    Util.hideProgressInd();
                    cpm.hms.labs.reloadReceiptPortal(order_id, labs_id);
                    cpm.hms.labs.reloadLabsTab(patient_visit_id, labs_id);
                });
            }
        });

        $('#supplier_categoryFormLink').live('click', function (e){
            alert('Please Cancel the payment(s) to edit the record!');
        });

        $('#supplier_DeleteLink').live('click', function (e){
            alert('Please Cancel the receipt(s) to delete the record!');
        });

        $('#generateReceiptnoOrder_Id').live('click', function (e){
            alert('Please click patient visit code in the supplier link behind.\n\nAnd Generate Bill !');
        });

        $('#generateReceiptnoAmount').live('click', function (e){
            alert('Please enter amount before generating receipt!');
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

    SelectSymbolsForm: function(checboxid, tooth_id, patient_visit_id){
        var url = "index.php?module=hms_patientVisit&_spAction=perioChartSymbols&tooth_id="+tooth_id+"&checboxid="+checboxid+"&showHTML=0";

        var exp = {
            url: url
            ,afterOpen: function(){

            }
        };
        Util.openDialogForLink('', 588, 'auto', 0, exp);
    },

    DeleteFromPerioTable: function(tooth_id, patient_visit_id, tooth_form_type, labs_id){
        var url = 'index.php?module=hms_patientVisit&_spAction=deletePerioChartRecord&showHTML=0';
        $.get(url, {tooth_id:tooth_id, patient_visit_id:patient_visit_id, tooth_form_type:tooth_form_type, labs_id:labs_id}, function(html){
            cpm.hms.labs.reloadToothList2(patient_visit_id, tooth_form_type, labs_id);
            cpm.hms.labs.reloadToothList3(patient_visit_id, tooth_form_type, labs_id);
        });
    },

    fnOpenButtonTextChangedDialog: function(tooth_id, patient_visit_id) {
        var buf = "Are you sure want to?";
        var tooth_form_type  = $('#fld_tooth_form_type').val();
        var labs_id          = $('#fld_labs_id').val();
        // buf will be shown on the body of Dialog.
        $("#dialog-confirm").html(buf);

        // Define the Dialog and its properties.
        $("#dialog-confirm").dialog({
            resizable: false,
            modal: true,
            title: "",
            height: 'auto',
            width: 400,
            buttons: {
                "Edit": function() {
                    $(this).dialog('close');
                    cpm.hms.labs.SelectSymbolsForm(tooth_id, patient_visit_id);
                },
                "Delete": function() {
                    $(this).dialog('close');
                    cpm.hms.labs.DeleteFromPerioTable(tooth_id, patient_visit_id, tooth_form_type, labs_id);
                },
                "Close": function() {
                    $(this).dialog('close');
                }
            }
        });
    },

    reloadToothList2: function(patient_visit_id, tooth_form_type, labs_id){
        var url = 'index.php?module=hms_patientVisit&_spAction=toothlistFirst&showHTML=0';
        $.get(url, {patient_visit_id:patient_visit_id, tooth_form_type:tooth_form_type, labs_id:labs_id}, function(html){
            $('.toothSelectCheckbox2').html(html);
        });
    },

    reloadToothList3: function(patient_visit_id, tooth_form_type, labs_id){
        var url = 'index.php?module=hms_patientVisit&_spAction=toothlistSecond&showHTML=0';
        $.get(url, {patient_visit_id:patient_visit_id, tooth_form_type:tooth_form_type, labs_id:labs_id}, function(html){
            $('.toothSelectCheckbox3').html(html);
        });
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

    reloadReceiptPortal: function(order_id, labs_id){
        var url = 'index.php?module=hms_labs&_spAction=ReceiptPortalDisplay&showHTML=0';
        Util.showProgressInd();
        $.get(url,{order_id:order_id, labs_id:labs_id}, function(html){
            $('#orderReceiptPortal').html(html);
            Util.hideProgressInd();
        });
    },

    reloadLabsTab: function(patient_visit_id, labs_id){
        var url = 'index.php?module=hms_labs&_spAction=LabsDisplay&showHTML=0';
        $.get(url, {patient_visit_id: patient_visit_id, labs_id:labs_id}, function(html){
            $('#labsDisplay').html(html);
            Util.hideProgressInd();
        });
    },
}

