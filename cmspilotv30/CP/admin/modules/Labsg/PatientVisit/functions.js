Util.createCPObject('cpm.labsg.patientVisit');

cpm.labsg.patientVisit = {
    init: function(){

        //initialize tabs
        $('#tabs').tabs();

        $('#tabs-2').removeClass('ui-tabs-hide');
        $('#tabs ul li.second').addClass('ui-tabs-selected ui-state-active');

        $('#tabs ul.ui-tabs-nav li:last').livequery(function() {
            $(this).css('border-right', '1px solid #D3D3D3');
        });

        $('.m-labsg_patientVisit .treatment_id').livequery('click', function (e){
            var treatment_id = $(this).val();
            var is_checked  = $(this).is(':checked');

            if(is_checked == true){
                $('.hideTreatmentDetails_'+treatment_id).show();
            } else {
                $('.hideTreatmentDetails_'+treatment_id).hide();
            }
        });

        $('.m-labsg_patientVisit .addNoteTreatment').livequery('click', function (e){
            var parent = $(this).closest('.treatmentNotes');
            $('.hideNotes', parent).slideToggle();
        });

        $('.m-labsg_patientVisit .printLabelPatientVisit').livequery('click', function (e){
            var title = "Print Label";
            var patient_information_id = $(this).attr('patient_information_id');
            var url   = "index.php?module=labsg_patientVisit&_spAction=printLabelPatientVisitForm&showHTML=0"; 
            var expObj = {
                url: url
               ,validate: true
               ,submitBtnText: 'Submit'
               ,cancelBtnText: 'Close'
               ,callbackOnSuccess: function(){
                    Util.showProgressInd();
                    var case_note        = $('#fld_case_note').val();
                    var lab_note         = $('#fld_lab_note').val();
                    var patient_visit_id = $('#record_id').val();
                    var convertUrl = "index.php?_topRm=main&module=labsg_patientVisit&_spAction=printLabel&case_note=" + case_note + "&lab_note=" + lab_note + "&patient_visit_id=" + patient_visit_id + "&patient_information_id=" + patient_information_id;
                    Util.closeAllDialogs();
                    Util.hideProgressInd();
                    window.open(convertUrl, 'blank');
                }
            };
            Util.openFormInDialog.call('', 'PrintLabelPatientVisitForm', title, 525, 'auto', expObj);
        });

        $('.m-labsg_patientVisit .printLabelPatientVisitList').livequery('click', function (e){
            var title = "Print Label";
            var patient_information_id = $(this).attr('patient_information_id');
            var patient_visit_id = $(this).attr('patient_visit_id');
            var url   = "index.php?module=labsg_patientVisit&_spAction=printLabelPatientVisitForm&showHTML=0"; 
            var expObj = {
                url: url
               ,validate: true
               ,submitBtnText: 'Submit'
               ,cancelBtnText: 'Close'
               ,callbackOnSuccess: function(){
                    Util.showProgressInd();
                    var case_note        = $('#fld_case_note').val();
                    var lab_note         = $('#fld_lab_note').val();
                    var convertUrl = "index.php?_topRm=main&module=labsg_patientVisit&_spAction=printLabel&case_note=" + case_note + "&lab_note=" + lab_note + "&patient_visit_id=" + patient_visit_id + "&patient_information_id=" + patient_information_id;
                    Util.closeAllDialogs();
                    Util.hideProgressInd();
                    window.open(convertUrl, 'blank');
                }
            };
            Util.openFormInDialog.call('', 'PrintLabelPatientVisitForm', title, 525, 'auto', expObj);
        });

        $(".order_item_type_value").live("keyup", function() {
            var totalAmount = 0;
            var total_count = $('#total_count').val();
            var inputval = $(this).val();
            if(inputval != ''){
                for ( var i = 1; i<=total_count; i++ ){
                    var inputval = $('.list_fees_'+i).val();
                    if(inputval == undefined){
                       inputval = parseInt(0);
                    }
                    totalAmount += Number(inputval);
                }

                $(".invoice_sub_total_amount").val(totalAmount.toFixed(2));

                var discount = $(".invoice_discount_amount").val();
                if(inputval != ''){
                    totalAmount = totalAmount - discount;
                }

                $(".invoice_total_amount").val(totalAmount.toFixed(2));

            }
        });

        /* Add Patient Record*/
        $('.m-labsg_patientVisit #addPatientRecord').livequery('click', function (e){
            var title = "Create Patient & Visit Record";
            var patient_visit_id   = $(this).attr('patient_visit_id');
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(json){
                    var msg = 'Patient & Visit Records created successfully';
                    Util.alert(msg, function(){
                    Util.closeAllDialogs();
                    document.location = json.returnUrl;
                    });
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 1100, 500, expObj);
        });
        
       $('#displayText').livequery('click', function (e){

            var ele = document.getElementById('toggleText');
            var text = document.getElementById('displayText');

            if(ele.style.display == 'block') {
                ele.style.display = 'none';
                text.innerHTML = 'Show More Fields (+)';
            }
            else {
                ele.style.display = 'block';
                text.innerHTML = 'Hide More Fields (-)';
            }
        });

       $('select[name="bill_type"]').livequery('change', function(){
            var bill_type = $(this).val();
            var category  = $(this).attr('category');

            if(bill_type == 'Company' || bill_type == 'Panel'){
                $('.companyDetailsTr').removeClass('companyDetailsHide');

                $('.showHideForBillType').removeClass('displayNone');
                $('.showHideForAppointmentType').addClass('displayNone');

                if(bill_type == 'Panel'){
                    $('.row_company_id label').html('Panel Name');
                    $('.showHideForBillType').addClass('displayNone');
                    $('.showHideForAppointmentType').removeClass('displayNone');
                }

                if(bill_type == 'Company'){
                    bill_type = 'Client';
                    $('.row_company_id label').html('Client Name*');
                    $('.showHideForBillType').addClass('displayNone');
                    $('.showHideForAppointmentType').removeClass('displayNone');
                }

                var url = 'index.php?module=labsg_patientVisit&_spAction=CompanyNameJSON&showHTML=0';
                $.get(url, {company_category: bill_type}, function (data) {
                    $("select[name='company_id']").cp_loadSelect(data);
                }, 'json');

            }else{
                $('.companyDetailsTr').addClass('companyDetailsHide');
                $('select[name=company_id]').val('');
                $('.showHideForBillType').removeClass('displayNone');
                $('.showHideForAppointmentType').addClass('displayNone');
            }

        });

        $(".invoice_discount_amount").live("keyup", function() {
            var totalAmount = 0;
            var total_count = $('#total_count').val();
            var discount = $(this).val();
            if(discount != ''){
                for ( var i = 1; i<=total_count; i++ ){
                    var inputval = $('.list_fees_'+i).val();
                    if(inputval == undefined){
                       inputval = parseInt(0);
                    }
                    totalAmount += Number(inputval);
                }

                $(".invoice_sub_total_amount").val(totalAmount.toFixed(2));

                totalAmount = totalAmount - discount;
                $(".invoice_total_amount").val(totalAmount.toFixed(2));

            }
        });

        $('#createOrderRecord').livequery('click', function (e){
            var link_text = $(this).html();

            if (link_text == 'Generate Bill') {
                msg = "Do you like to generate bill?";
            } else if(link_text == 'Update Bill') {
                msg = "Do you like to update bill?";
            }

            if (!confirm(msg)){
                return false;
            } else {
                Util.showProgressInd();
                var patient_visit_id = $(this).attr('patient_visit_id');

                var url = 'index.php?_topRm=main&module=labsg_patientVisit&_spAction=createOrder&showHTML=0' +
                        '&patient_visit_id=' + patient_visit_id;
                $.get(url, {patient_visit_id: patient_visit_id}, function (html) {
                    Util.hideProgressInd();
                    var convertUrl = "index.php?_topRm=finance&module=labsg_order&_action=edit&order_id=" + html;
                    document.location = convertUrl;
                });
            }
        });

        $('#createOrderRecordIndividual').livequery('click', function (e){
            var patient_visit_id = $(this).attr('patient_visit_id');
            e.preventDefault();

            if($('input[name=treatmentId\[\]]:checked').length == 0){
                Util.alert("Please check atleast one test item");
            }else{

                var dialog = $('<div>Do you like to create?</div>').dialog({
                    buttons: {
                        "Invoice": function() {
                            $('.ui-dialog').dialog('close');
                            $('.ui-dialog').dialog('destroy');
                            var urlOrder = 'index.php?_topRm=main&module=labsg_patientVisit&_spAction=createOrderIndividual&showHTML=0' +
                                           '&patient_visit_id=' + patient_visit_id;
                            $.get(urlOrder, {patient_visit_id: patient_visit_id}, function (html) {
                                $('#fld_order_id').val(html);
                                url = "index.php?module=labsg_order&_spAction=generateInvoiceForm&order_id="+html+"&showHTML=0";
                                var title = "Bill Generation";
                                e.preventDefault();

                                var expObj = {
                                    url: url
                                   ,validate: true
                                   ,callbackOnSuccess: function(){
                                        var msg = 'Invoice created successfully';
                                        Util.alert(msg, function(){
                                            Util.closeAllDialogs();
                                            dialog.remove();
                                            window.location.reload(true);
                                        });
                                    }
                                }
                                Util.openFormInDialog.call('', 'portalForm', title, 600, 'auto', expObj);
                            });

                        },
                        "Invoice & Receipt":  function() {
                            var urlOrder = 'index.php?_topRm=main&module=labsg_patientVisit&_spAction=createOrderIndividual&showHTML=0' +
                                           '&patient_visit_id=' + patient_visit_id;
                            $.get(urlOrder, {patient_visit_id: patient_visit_id}, function (html) {
                                $('#fld_order_id').val(html);
                                url = "index.php?module=labsg_order&_spAction=generateInvoiceForm&order_id="+html+"&receipt=1&showHTML=0";
                                var title = "Bill Generation";
                                e.preventDefault();

                                var expObj = {
                                    url: url
                                   ,validate: true
                                   ,callbackOnSuccess: function(){
                                        var msg = 'Invoice and Receipt are created successfully';
                                        Util.alert(msg, function(){
                                            Util.closeAllDialogs();
                                            dialog.remove();
                                            window.location.reload(true);
                                        });
                                    }
                                }
                                Util.openFormInDialog.call('', 'portalForm', title, 600, 'auto', expObj);
                            });
                        },
                        "Cancel":  function() {
                            dialog.dialog('close');
                        }
                    },
                     width: "400"
                    ,height:"250"
                });
            }
        });

        $('.m-labsg_patientVisit #generateReceipt').livequery('click', function (e){
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
            Util.openFormInDialog.call(this, 'portalForm', title, 500, 500, expObj);
        });

        /*
        $('.cancelReceipt').livequery('click', function (e){
            msg = "Do you like to cancel the Receipt?";
            if (!confirm(msg)){
                return false;
            }
            else {
                var url = 'index.php?_topRm=finance&module=labsg_order&_spAction=cancelReceipt&showHTML=0';
                Util.showProgressInd();
                var receipt_code = $(this).attr('receipt_code');
                var order_id     = $(this).attr('order_id');
                $.get(url,{receipt_code: receipt_code, order_id:order_id}, function(html){
                    alert ('Receipt Cancelled Succesfully');
                    Util.hideProgressInd();
                    window.location.reload(true);
                });
            }
        });
        */

        $('.cancelPatientVisit').livequery('click', function (e){
            var title = "Cancel Patient Visit";
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Patient Visit cancelled successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        window.location.reload(true);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 440, 400, expObj);
        });

        $('.cancelReceipt').livequery('click', function (e){
            var title = "Cancel Receipt";
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Receipt cancelled successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        window.location.reload(true);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 400, 400, expObj);
        });

        $('.cancelInvoice').livequery('click', function (e){
            var title = "Cancel Invoice";
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Invoice cancelled successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        window.location.reload(true);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 400, 400, expObj);
        });
        /*
        $('.cancelInvoice').livequery('click', function (e){
            var invoice_status = $(this).attr('invoice_status');

            if (invoice_status != 'Paid') {
                msg = "Do you like to cancel the Invoice?";
                if (!confirm(msg)){
                    return false;
                }
                else {
                    var url = 'index.php?_topRm=finance&module=labsg_order&_spAction=cancelInvoice&showHTML=0';
                    Util.showProgressInd();
                    var invoice_code = $(this).attr('invoice_code');
                    var invoice_id = $(this).attr('invoice_id');
                    $.get(url,{invoice_code: invoice_code, invoice_id:invoice_id}, function(html){

                        // Checking for one or more receipt for the invoice
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
        */

        $('.m-labsg_patientVisit input.invoiceCode').livequery('click', function (e){
            Util.showProgressInd();
            invoice_code = $(this).val();
            var checked    = $(this).attr('checked') ? 'checked' : '';
            var checkedVal = checked == 'checked' ? 1 : 0;

            var url = 'index.php?_topRm=finance&module=labsg_order&_spAction=populateReceiptAmount&showHTML=0';
            $.get(url,{invoice_code: invoice_code ,checkedVal: checkedVal}, function(html){
                $('input[id=fld_amount]').val(html);
                Util.hideProgressInd();
            });
        });

        $('.m-labsg_patientVisit #billSummaryOrder').livequery('click', function(e) {
            e.preventDefault();
            var order_id = $(this).attr('order_id');
            url = "index.php?module=labsg_patientVisit&_spAction=summaryInOrder&order_id="+order_id+"&showHTML=0";
            var exp = {
                url: url
            };

            Util.openDialogForLink('Bill Summary', 600, 'auto', 0, exp);
        });

        $('.m-labsg_patientVisit .searchPatientButton').livequery('click', function (e){
           var inputBoxVaue  = $('.searchInputPatientVisit').val();
           var dropdownValue = $('#fld_search_type_by_list').val();
           var lock = 1;
           var url = 'index.php?module=labsg_patientVisit&_spAction=patientVisitSearchResult&showHTML=0';
           Util.showProgressInd();
           $.get(url, {inputBoxVaue: inputBoxVaue, dropdownValue:dropdownValue, lock:lock}, function(html){
                Util.hideProgressInd();
                $('.searchTableInPatientVisit').html(html);
                $('.searchTableInPatientVisit').removeClass('searchTableInPatientVisithide');
                $('.searchTableInPatientVisitAppointment').hide();
                if(inputBoxVaue == ''){
                    $('.searchTableInPatientVisitAppointment').show();
                    $('.searchTableInPatientVisit').addClass('searchTableInPatientVisithide');
                }
           });

        });

        $('a.createVisit').livequery('click', function (e){
            cpm.labsg.patientVisit.createPatientVisit.call(this);
        });

        $('a.duplicatePatientVisit').livequery('click', function (e){
            msg = "Do you like to create another Patient Visit?";
            if (!confirm(msg)){
                return false;
            } else {
                cpm.labsg.patientVisit.createPatientVisit.call(this);
            }
        });

        $('.followUpDate select').livequery('change', function(){
            var follow_up_date = $(this).val();
            var parent = $(this).closest('.treatmentNotes');

            var url = 'index.php?module=labsg_patientVisit&_spAction=convertFollowUpDate&showHTML=0';
            $.get(url, {follow_up_date: follow_up_date}, function(html){
                $('.followUpDate input', parent).val(html);
            });
        });

        $('select[name=follow_up_value]').livequery('change', function(){
            var follow_up_date = $(this).val();

            var url = 'index.php?module=labsg_patientVisit&_spAction=convertFollowUpDate&showHTML=0';
            $.get(url, {follow_up_date: follow_up_date}, function(html){
                $('#fld_follow_up_date').val(html);
            });
        });

        $('.treatmentStatus').livequery('click', function (e){
            var status = $(this).val();
            var parent = $(this).closest('.treatmentNotes');
            if(status == 'Current'){
                $('.treatmentStatus', parent).blur();
                $('.treatmentStatus', parent).val('Future');
                $('.followUpDate', parent).show();

            } else {
                $('.treatmentStatus', parent).blur();
                $('.treatmentStatus', parent).val('Current');
                $('.followUpDate', parent).hide();
                $('.fld_date', parent).val('');
            }
        });


        $('.displayVisitRecords').livequery('click', function (e){
            $('.searchListDisplay').hide();
            $('.defaultListDisplay').show();
            $('.cpSearch').show();
            var urlRedirect = 'index.php?_topRm=main&module=labsg_patientVisit&_action=list&searchDone=1';
            document.location = urlRedirect;
        });

        $('#portalForm_treatmentDisplay').livequery('submit', function(){
          $.post($(this).attr('action'), $(this).serialize(), function(response){
                // do something here on success
                var mgsalert='Record Saved Successfully';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 2000,
                });
          },'json');
          return false;
       });


        $('#portalForm_summaryDisplay').livequery('submit', function(){
          $.post($(this).attr('action'), $(this).serialize(), function(response){
                // do something here on success
                var mgsalert='Informations updated Successfully';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 2000,
                });
          },'json');
          return false;
       });

    },

    createPatientVisitDetails: function(patient_information_id, appointment_id){
        var title = "Choose Doctor/Nurse";
        var url   = "index.php?module=labsg_patientVisit&_spAction=selectDoctorDetails&patient_information_id="+patient_information_id+"&appointment_id="+appointment_id+"&showHTML=0";

        var exp = {
            url: url
           ,validate: true
           ,submitBtnText: 'Submit'
           ,cancelBtnText: 'Close'
           ,callbackOnSuccess: function(){
                Util.closeAllDialogs();
                cpm.labsg.patientVisit.reloadSearchResult();
                cpm.labsg.patientVisit.reloadQueueno();
                var mgsalert2='Patient Visit Record Created';
                var n = noty({
                    text: mgsalert2,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 5000,
                });
            }
        };
        Util.openFormInDialog.call('','portalFormPatientVisitCreate', title,  588, 'auto', exp);
    },

    reloadSearchResult: function(){
        var inputBoxVaue  = $('.searchInputPatientVisit').val();
        var dropdownValue = $('#fld_search_type_by_list').val();
        var url = 'index.php?module=labsg_patientVisit&_spAction=patientVisitAppointmentSearchResult&showHTML=0';

        $.get(url, {inputBoxVaue: inputBoxVaue, dropdownValue:dropdownValue}, function(html){
            $('.searchTableInPatientVisit').html(html);
            $('.searchTableInPatientVisit').removeClass('searchTableInPatientVisithide');
            $('.searchTableInPatientVisitAppointment').hide();

        });
    },

    reloadQueueno: function(){
        var url = 'index.php?_theme=matrix&_spAction=patientQueueNo&showHTML=0';
        $.get(url,  function(html){
            $('.queueNumberDisplay').html(html);
        });
    },
}


$('.qucikaddPatientForm .addNewValue').livequery('click', function (e){
    var title = "Add New Value";
    e.preventDefault();

    var valuelist_name = $(this).attr('valuelist_name');
    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            $('#dialog1').dialog('close');
            $('#dialog1').dialog('destroy');
            $('#dialog1').remove();

            var url = 'index.php?module=labsg_patientVisit&_spAction=valueByValuelistJSON&showHTML=0';
            $.get(url, {valuelist_name: valuelist_name}, function (data) {
                if(valuelist_name == 'occupation'){
                    $('#fld_occupation').cp_loadSelect(data);
                }
            }, 'json');
        }
    }
    Util.openFormInDialog.call(this, 'portalFormValuelist', title, 400, 300, expObj);
});


/* Editing of contact(click edit) Right panel during linking process */
$('a.addNewValue1').livequery('click', function(e) {
    e.preventDefault();
    var trObj = $(this).closest('tr');
    
    var exp = {
        callbackOnSuccess: function(json){
            $('#dialog1').dialog('close');
            $('#dialog1').dialog('destroy');
            $('#dialog1').remove();
            var data = json.extraParam.data;
            $('td.first_name',trObj).html(data.first_name);
            $('td.last_name',trObj).html(data.last_name);
            $('td.id_card_no',trObj).html(data.id_card_no);
        }
    }
    Util.openFormInDialog.call(this, 'contactEditForm', 'Edit Contact', 500, 450, exp);
});

cpm.labsg.patientVisit.createPatientVisit = function(){
    var url = 'index.php?module=labsg_patientVisit&_spAction=createVisitRecordDirect&showHTML=0';
    var dr_required            = $(this).attr('dr_required');
    var patient_information_id = $(this).attr('patient_information_id');
    var appointment_id         = $(this).attr('appointment_id');

    $.get(url,{patient_information_id:patient_information_id, dr_required:dr_required, appointment_id:appointment_id}, function(html){
        Util.closeAllDialogs();
        cpm.labsg.patientVisit.reloadSearchResult();
        cpm.labsg.patientVisit.reloadQueueno();
        var mgsalert='Patient Visit Record Created';
        var n = noty({
            text: mgsalert,
            type: 'confirm',
            dismissQueue: true,
            layout: 'topCenter',
            theme: 'defaultTheme',
            timeout: 5000,
        });
    });
};
