Util.createCPObject('cpm.hms.patientVisit');

cpm.hms.patientVisit = {
    init: function(){
        $('.m-hms_patientVisit #editInvoice').livequery('click', function (e){
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

        $('.m-hms_patientVisit .editReceipt').livequery('click', function (e){
            var title = "Edit Receipt";
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Receipt updated successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        window.location.reload(true);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 600, 500, expObj);
        });

        /* Disable backspace (8) and spacebar (32) default key action outside text area */
        $(".m-hms_patientVisit.v-edit").keypress(function(e) {
            if (e.which == 8 && !$(e.target).is("textarea") && !$(e.target).is("input")) {
                return false;
            }

            if (e.which == 32 && !$(e.target).is("textarea") && !$(e.target).is("input")) {
                return false;
            }
        });

        // Copy over data from previous visit - FORTE HMS
        $('.copyOverAll').livequery('click', function (e){
            var subjectiveVal         = $(this).attr('subjective');
            var objectiveVal          = $(this).attr('objective');
            var analysisPlanVal       = $(this).attr('analysis_plan');
            var treatmentFollowupVal  = $(this).attr('treatment_followup');
            var followupPlanVal       = $(this).attr('followup_plan');

            msg = "Do you like to Copy over all data?";
            if (confirm (msg)){
                Util.showProgressInd();

                $('#fld_subjective').val(subjectiveVal);
                $('#fld_objective').val(objectiveVal);
                $('#fld_analysis_plan').val(analysisPlanVal);
                $('#fld_treatment_followup').val(treatmentFollowupVal);
                $('#fld_followup_plan').val(followupPlanVal);

                Util.closeAllDialogs();
                Util.hideProgressInd();
                var mgsalert2='All Data copy overed successfully';
                var n = noty({
                    text: mgsalert2,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 2000,
                });
                $('#fld_subjective').focus();
            }
        });

        // Copy over data from previous visit - FORTE HMS
        $('.copyOverBtn').livequery('click', function (e){
            var assessmentVal = $(this).attr('assessmentVal');
            var typeVal = $(this).attr('type');

            msg = "Do you like to Copy over data?";
            if (confirm (msg)){
                Util.showProgressInd();

                $('#fld_'+typeVal).val(assessmentVal);

                Util.closeAllDialogs();
                Util.hideProgressInd();
                var mgsalert2='Data copy overed successfully';
                var n = noty({
                    text: mgsalert2,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 2000,
                });
                $('#fld_'+typeVal).focus();
            }
        });

        // Update Patient Information if already available - FORTE HMS
        $('.updatePatientNric').livequery('click', function (e){
            var patient_information_id = $(this).attr('patient_information_id');
            var delete_pi_id = $(this).attr('delete_pi_id');
            var appointment_id = $(this).attr('appointment_id');

            msg = "Do you like to update Patient NRIC for the Patient Visit?";
            if (confirm (msg)){
                var url = 'index.php?module=hms_patientVisit&_spAction=updatePatientNricForPatientVisit';
                Util.showProgressInd();
                $.get(url, {patient_information_id: patient_information_id, delete_pi_id: delete_pi_id, appointment_id: appointment_id}, function(){
                    Util.hideProgressInd();
                    Util.alert("Patient details updated successfully. Please proceed to create patient visit.");
                    //cpm.hms.patientVisit.reloadPatientVisitCreateForm(patient_information_id, appointment_id);
                    //cpm.hms.patientVisit.reloadSearchResult();
                    window.location.reload(true);
                });
            }
        });
            
        //initialize tabs
        $('#tabs').tabs();

        $('#tabs ul.ui-tabs-nav li:last').livequery(function() {
            $(this).css('border-right', '1px solid #D3D3D3');
        });

        // For ECHMS
        $('.qucikaddPatientForm select[name=private_insurance]').livequery('change', function(){
            var insuranceVal = $(this).val();

            if (insuranceVal == 'Yes') {
                $('.qucikaddPatientForm .row_insurance_company').removeClass('hideme');
            } else {
                $('.qucikaddPatientForm .row_insurance_company').addClass('hideme');
            }
        });
        $('.qucikaddPatientForm select[name=dr_referral]').livequery('change', function(){
            var insuranceVal = $(this).val();

            if (insuranceVal == 'Yes') {
                $('.qucikaddPatientForm .row_referral_doctor_name').removeClass('hideme');
            } else {
                $('.qucikaddPatientForm .row_referral_doctor_name').addClass('hideme');
            }
        });


        /* Add Medicine in patient visit medicines tab */
        $('.m-hms_patientVisit #addMedicines')
        .livequery('click', cpm.hms.patientVisit.patientMedicineAdd);

        $(".m-hms_patientVisit input[name='title']")
        .livequery(cpm.hms.patientVisit.patientProductTitle);

        $('.m-hms_patientVisit .instruction').livequery('change', function(){
            var parent = $(this).closest('tr');
            var rec_id = $(parent).attr('recid');
            var product_id = $(parent).attr('product_id');
            var instructionObj = $(this).parents('tr').find('select[name=instruction]');
            var instruction = instructionObj.val();
            var url = 'index.php?module=hms_patientVisit&_spAction=updateProductLineItems&showHTML=0';
            $.get(url, {rec_id: rec_id, instruction: instruction, product_id: product_id}, function(json){

            });
        });

        $('.m-hms_patientVisit .dosage').livequery('change', function(){
            var parent = $(this).closest('tr');
            var rec_id = $(parent).attr('recid');
            var product_id = $(parent).attr('product_id');
            var dosageObj = $(this).parents('tr').find('input[name=dosage]');
            var dosage = dosageObj.val();
            var url = 'index.php?module=hms_patientVisit&_spAction=updateProductLineItems&showHTML=0';
            $.get(url, {rec_id: rec_id, dosage: dosage, product_id: product_id}, function(json){

            });
        });

        $('.m-hms_patientVisit .days').livequery('change', function(){
            var parent = $(this).closest('tr');
            var rec_id = $(parent).attr('recid');
            var product_id = $(parent).attr('product_id');
            var daysObj = $(this).parents('tr').find('input[name=days]');
            var days = daysObj.val();
            var url = 'index.php?module=hms_patientVisit&_spAction=updateProductLineItems&showHTML=0';
            $.get(url, {rec_id: rec_id, days: days, product_id: product_id}, function(json){

            });
        });

        $('.m-hms_patientVisit .qty > input').livequery('change', function(){
            var parent = $(this).closest('tr');
            var rec_id = $(parent).attr('recid');
            var product_id = $(parent).attr('product_id');
            var qtyObj = $(this).parents('tr').find('input[name=qty]');
            var qty = qtyObj.val();
            var previousQtyValue = $(this).attr('previousQtyValue');
            var stock = parseInt($(this).attr('stock'), 10);

            if(stock < qty){
                alert('The qty should be less than the stock qty');
                $('#fld_medicineQty_'+rec_id).val(previousQtyValue);
                $('#fld_medicineQty_'+rec_id).focus();
            } else {
                var url = 'index.php?module=hms_patientVisit&_spAction=updateProductLineItems&showHTML=0';
                $.get(url, {rec_id: rec_id, qty: qty, product_id: product_id}, function(json){

                });
            }

        });

        $('.m-hms_patientVisit .selling-price').livequery('change', function(){
            var parent = $(this).closest('tr');
            var rec_id = $(parent).attr('recid');
            var priceObj = $(this).parents('tr').find('input[name=selling_price]');
            var selling_price = priceObj.val();
            var url = 'index.php?module=hms_patientVisit&_spAction=updateProductLineItems&showHTML=0';
            $.get(url, {rec_id: rec_id, selling_price: selling_price}, function(json){

            });
        });

        $('.m-hms_patientVisit .employee_id').livequery('change', function(){
            var parent = $(this).closest('tr');
            var rec_id = $(parent).attr('recid');
            var employeeObj = $(this).parents('tr').find('select[name=employee_id]');
            var employee_id = employeeObj.val();
            var url = 'index.php?module=hms_patientVisit&_spAction=updateProductLineItems&showHTML=0';
            $.get(url, {rec_id: rec_id, employee_id: employee_id}, function(json){

            });
        });

        $('.m-hms_patientVisit #addDoctorRecord').livequery('click', function (e){
            var title = "Create Record";
            var patient_visit_id   = $(this).attr('patient_visit_id');
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Record created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        cpm.hms.patientVisit.reloadDoctorTab(patient_visit_id);
                        //window.location.reload(true);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 500, 400, expObj);
        });

        $('.m-hms_patientVisit #addTreatmentRecord').livequery('click', function (e){
            var title = "Create Record";
            var patient_visit_id = $('#record_id').val();
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Record created successfully';
                    var treatment_category  = $("select[name='category']").val();
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        var url = 'index.php?module=hms_patientVisit&_spAction=TreatmentPortalDisplay&showHTML=0';
                        $.get(url,{patient_visit_id:patient_visit_id, TreatmentCategory: treatment_category}, function(html){
                            $('.treatmentTabDisplay').html(html);
                            Util.hideProgressInd();
                        });
                    });
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 500, 340, expObj);
        });

        $('.m-hms_patientVisit #addDiagnosisRecord').livequery('click', function (e){
            var title = "Create Record";
            var patient_visit_id   = $('#record_id').val();
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Record created successfully';
                    var diagnosis_title  = $('#fld_diagnosis_title').val();
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        var url = 'index.php?module=hms_patientVisit&_spAction=DiagnosisPortalDisplay&showHTML=0';
                        $.get(url,{patient_visit_id:patient_visit_id, searchDiagnosis: diagnosis_title}, function(html){
                            $('.diagnosisTabDisplay').html(html);
                            Util.hideProgressInd();
                            $('.diagnosisSearchAuto').val(diagnosis_title);
                        });
                    });
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 500, 320, expObj);
        });

        /* Add Labs Record*/
        $('.m-hms_patientVisit #addLabsRecord').livequery('click', function (e){
            var title = "Create Record";
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
            Util.openFormInDialog.call(this, 'portalForm', title, 450, 250, expObj);
        });


        $("select[name='supplier_category']").livequery('change', function(){
            var url = 'index.php?module=hms_patientVisit&_spAction=labsSupplierJSON&showHTML=0';
            var supplier_category = $(this).val();
            $.get(url, {supplier_category: supplier_category}, function (data) {
                $("select[name='supplier_id']").cp_loadSelect(data);
            }, 'json');

        });

        /*$("select[name='patientVisitSummary_type']").livequery('change', function(){
            var patientVisitSummary_type = $(this).val();
            var patient_information_id   = $('#fld_patient_information_id').val();
            var url = 'index.php?module=hms_patientVisit&_spAction=PatientVisitSummaryPortal&showHTML=0';
            Util.showProgressInd();
            $.get(url, {patient_information_id:patient_information_id, patientVisitSummary_type:patientVisitSummary_type}, function(html){
                $('#patientVisitSummaryPortal').html(html);
                Util.hideProgressInd();
            });
        });*/

        $(".patientVisitSummary_type").livequery('click', function(){
            var link_text = $(this).html();

            if(link_text == 'Display payment due records'){
                var patientVisitSummary_type = 'Due';
                $(".patientVisitSummary_type").html('Show All Records');
            }else if(link_text == 'Show All Records'){
                var patientVisitSummary_type = 'All';
                $(".patientVisitSummary_type").html('Display payment due records');
            }

            var patient_information_id   = $('#fld_patient_information_id').val();
            var url = 'index.php?module=hms_patientVisit&_spAction=PatientVisitSummaryPortal&showHTML=0';
            Util.showProgressInd();
            $.get(url, {patient_information_id:patient_information_id, patientVisitSummary_type:patientVisitSummary_type}, function(html){
                $('#patientVisitSummaryPortal').html(html);
                Util.hideProgressInd();
            });
        });


        /* Add Patient Record*/
        $('.m-hms_patientVisit #addPatientRecord').livequery('click', cpm.hms.patientVisit.addPatientRecord);

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
                    $('.row_company_id label').html('Client Name');
                    $('.showHideForBillType').addClass('displayNone');
                    $('.showHideForAppointmentType').removeClass('displayNone');
                }

                var url = 'index.php?module=hms_patientVisit&_spAction=CompanyNameJSON&showHTML=0';
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

        /* For ECHMS */
        $('select[name="private_insurance"]').livequery('change', function(){
            var private_insurance = $(this).val();
            
            if(private_insurance == 'Yes'){
                $('.insuranceDetailsTr').removeClass('insuranceDetailsHide');
            } else {
                $('.insuranceDetailsTr').addClass('insuranceDetailsHide');
            }
        });
        /*$('.m-hms_patientVisit #addMedicines').livequery('click', function (e){
            var title = "Create Record";
            var patient_visit_id   = $(this).attr('patient_visit_id');
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Record created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        cpm.hms.patientVisit.reloadMedicineTab(patient_visit_id);
                        //window.location.reload(true);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 500, 400, expObj);
        });*/

        /* Add note in treatment tab*/
        /*$('.m-hms_patientVisit a.addNoteTreatment').livequery('click', function (e){
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
                Util.openFormInDialog.call(this, 'portalForm', title, 500, 300, expObj);
        });*/

        /* Add note in Labs tab*/
        /*$('.m-hms_patientVisit a.addNoteLab').livequery('click', function (e){
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
                Util.openFormInDialog.call(this, 'portalForm', title, 500, 300, expObj);
        });*/

        $('.m-hms_patientVisit #addLabRecord').livequery('click', function (e){
            var title = "Create Record";
            var patient_visit_id   = $(this).attr('patient_visit_id');
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Record created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        cpm.hms.patientVisit.reloadLabTab(patient_visit_id);
                        //window.location.reload(true);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 500, 400, expObj);
        });

        $('.m-hms_patientVisit .perio_chart_link').livequery('click', function (e){
            var title = "Perio Chart";
            var patient_visit_id   = $(this).attr('patient_visit_id');
            e.preventDefault();

            var url = "index.php?module=hms_patientVisit&_spAction=perioChartForm&patient_visit_id="+patient_visit_id+"&showHTML=0";
            var exp = {
                url: url
                ,afterOpen: function(){

                }
            };
            Util.openDialogForLink('Perio Chart', '965px', 'auto', 0, exp);

        });

        $('.m-hms_patientVisit #editDoctorRecord').livequery('click', function (e){
            var title = "Edit Record";
            var patient_visit_id   = $(this).attr('patient_visit_id');
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Record created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        cpm.hms.patientVisit.reloadDoctorTab(patient_visit_id);
                        //window.location.reload(true);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 500, 400, expObj);
        });

        $('.deleteDoctorRecord').livequery('click', function (){
            Util.showProgressInd();
            var url = 'index.php?module=hms_patientVisit&_spAction=deleteDoctorRecord&showHTML=0';
            var employee_visit_id = $(this).attr('employee_visit_id');
            var patient_visit_id   = $(this).attr('patient_visit_id');
            $.get(url,  {employee_visit_id:employee_visit_id}, function(html){
                cpm.hms.patientVisit.reloadDoctorTab(patient_visit_id);
            });
        });

        $('.m-hms_patientVisit #editLabsRecord').livequery('click', function (e){
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

        $('.m-hms_patientVisit #acrylicFormDenture').livequery('click', function (e){
            var title = "DENTURE EXPRESS";
            e.preventDefault();
            var patient_visit_id   = $(this).attr('patient_visit_id');
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
            Util.openFormInDialog.call(this, 'acrylicDentureForm', title, 810, 508, expObj);
        });

        $('.m-hms_patientVisit #addCeramicForm').livequery('click', function (e){
            var title = "CERAMIC FORM";
            e.preventDefault();
            var patient_visit_id   = $(this).attr('patient_visit_id');
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
            Util.openFormInDialog.call(this, 'AddCeramicFormDetail', title, 810, 508, expObj);
        });

        $('.m-hms_patientVisit #addOrthodontic').livequery('click', function (e){
            var title = "ORTHODONTIC FORM";
            e.preventDefault();
            var patient_visit_id   = $(this).attr('patient_visit_id');
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
            Util.openFormInDialog.call(this, 'ChromeFormDetail', title, 810, 508, expObj);
        });

        $('.m-hms_patientVisit #editMedicineRecord').livequery('click', function (e){
            var title = "Edit Record";
            var patient_visit_id   = $(this).attr('patient_visit_id');
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Record created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        cpm.hms.patientVisit.reloadMedicineTab(patient_visit_id);
                        //window.location.reload(true);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 500, 400, expObj);
        });

        $('.deleteMedicineRecord').livequery('click', function (){
            Util.showProgressInd();
            var url = 'index.php?module=hms_patientVisit&_spAction=deleteMedicineRecord&showHTML=0';
            var medicines_visit_id = $(this).attr('medicines_visit_id');
            var patient_visit_id   = $(this).attr('patient_visit_id');
            $.get(url,  {medicines_visit_id:medicines_visit_id}, function(html){
                cpm.hms.patientVisit.reloadMedicineTab(patient_visit_id);
            });
        });

        $('.m-hms_patientVisit #editLabRecord').livequery('click', function (e){
            var title = "Edit Record";
            var patient_visit_id   = $(this).attr('patient_visit_id');
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Record created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        cpm.hms.patientVisit.reloadLabTab(patient_visit_id);
                        //window.location.reload(true);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 500, 400, expObj);
        });

        $('.m-hms_patientVisit .treatment_id').livequery('click', function (e){
            var treatment_id = $(this).val();
            var is_checked  = $(this).is(':checked');

            if(is_checked == true){
                $('.hideTreatmentDetails_'+treatment_id).show();
                $(this).closest('.treatmentBox').addClass('checkedCheckBoxTreatment');
            } else {
                $('.hideTreatmentDetails_'+treatment_id).hide();
                $(this).closest('.treatmentBox').removeClass('checkedCheckBoxTreatment');
            }
        });

        $('.m-hms_patientVisit .diagnosis_id').livequery('click', function (e){
            var treatment_id = $(this).val();
            var is_checked  = $(this).is(':checked');

            if(is_checked == true){
                $(this).closest('.diagnosisBox').addClass('checkedCheckBoxTreatment');
            } else {
                $(this).closest('.diagnosisBox').removeClass('checkedCheckBoxTreatment');
            }
        });

        $('.m-hms_patientVisit .addNoteTreatment').livequery('click', function (e){
            var parent = $(this).closest('.treatmentNotes');
            $('.hideNotes', parent).slideToggle();
        });

        $('.m-hms_patientVisit .addNoteLab').livequery('click', function (e){
            var parent = $(this).closest('.labVisitNotes');
            $('.hideNotesLab', parent).slideToggle();
        });

        /*$('#createOrderRecord').livequery('click', function (e){
            var link_text = $(this).html();

            if(link_text == 'Generate Bill'){
                msg = "Do you like to generate order?";
            }else if(link_text == 'Update Bill'){
                msg = "Do you like to update order?";
            }

            if (!confirm(msg)){
                return false;
            }
            else{
                Util.showProgressInd();
                var patient_visit_id = $(this).attr('patient_visit_id');

                var url = 'index.php?_topRm=main&module=hms_patientVisit&_spAction=createOrder&showHTML=0' +
                        '&patient_visit_id=' + patient_visit_id;
                $.get(url, {patient_visit_id: patient_visit_id}, function (html) {
                    Util.hideProgressInd();
                    var convertUrl = "index.php?_topRm=finance&module=hms_order&_action=edit&order_id=" + html;
                    document.location = convertUrl;
                });
            }
        });*/

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

                var overAllTotalAmount = 0;
                var due_amount = $('input[id=fld_due_amount]').val();
                var receipt_amount = $('.m-hms_patientVisit input[name="due_receipt_amount"]').val();
                overAllTotalAmount_due = Number(parseInt(due_amount) + totalAmount);
                overAllTotalAmount = Number(parseInt(due_amount) + totalAmount - parseInt(receipt_amount));
                $('input[id=fld_overall_Total_invoice]').val(overAllTotalAmount_due.toFixed(2));
                $('input[id=balance_Total_invoice]').val(overAllTotalAmount.toFixed(2));

                $(".invoice_total_amount").val(totalAmount.toFixed(2));
                $("#overall_Total_invoice_hidden").val(totalAmount.toFixed(2));

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

                var overAllTotalAmount = 0;
                var due_amount = $('input[id=fld_due_amount]').val();
                var receipt_amount = $('.m-hms_patientVisit input[name="due_receipt_amount"]').val();
                overAllTotalAmount_due = Number(parseInt(due_amount) + totalAmount);
                overAllTotalAmount = Number(parseInt(due_amount) + totalAmount - parseInt(receipt_amount));
                $('input[id=fld_overall_Total_invoice]').val(overAllTotalAmount_due.toFixed(2));
                $('input[id=balance_Total_invoice]').val(overAllTotalAmount.toFixed(2));

                $(".invoice_total_amount").val(totalAmount.toFixed(2));
                $("#overall_Total_invoice_hidden").val(totalAmount.toFixed(2));

            }
        });

        $('.m-hms_patientVisit #createOrderRecord').livequery('click', function (e){
            var patient_visit_id = $(this).attr('patient_visit_id');
            e.preventDefault();
            if($('input[name="treatmentId[]"]:checked').length == 0){
                Util.alert("Please check atleast one treatment");
            }else{

                var dialog = $('<div>Do you like to create?</div>').dialog({
                    buttons: {
                        "Invoice": function() {
                            $('.ui-dialog').dialog('close');
                            $('.ui-dialog').dialog('destroy');
                            var urlOrder = 'index.php?_topRm=main&module=hms_patientVisit&_spAction=createOrder&showHTML=0' +
                                           '&patient_visit_id=' + patient_visit_id;

                            $.get(urlOrder, {patient_visit_id: patient_visit_id}, function (html) {
                                $('#fld_order_id').val(html);
                                url = "index.php?module=hms_order&_spAction=generateInvoiceForm&order_id="+html+"&showHTML=0";
                                var title = "Bill Generation";
                                e.preventDefault();
                                var expObj = {
                                    url: url
                                   ,validate: true
                                   ,callbackOnSuccess: function(){
                                        var invoice_amount_check = $('input[id=overall_Total_invoice_hidden]').val();

                                        if(parseFloat(invoice_amount_check) > 0){
                                            var msg = 'Invoice created successfully';
                                        }else{
                                            var msg = 'Please note as the invoice amount is zero no invoice or receipt created for this visit record';
                                        }

                                        Util.alert(msg, function(){
                                            Util.closeAllDialogs();
                                            dialog.remove();
                                            window.location.reload(true);
                                        });
                                    }
                                }
                                Util.openFormInDialog.call('', 'portalForm', title, 600, 600, expObj);
                            });

                        },
                        "Invoice & Receipt":  function() {
                            var urlOrder = 'index.php?_topRm=main&module=hms_patientVisit&_spAction=createOrder&showHTML=0' +
                                           '&patient_visit_id=' + patient_visit_id;
                            $.get(urlOrder, {patient_visit_id: patient_visit_id}, function (html) {
                                $('#fld_order_id').val(html);
                                url = "index.php?module=hms_order&_spAction=generateInvoiceForm&order_id="+html+"&receipt=1&showHTML=0";
                                var title = "Bill Generation";
                                e.preventDefault();

                                var expObj = {
                                    url: url
                                   ,validate: true
                                   ,callbackOnSuccess: function(){
                                        var msg = 'Invoice & Receipt created successfully';
                                        Util.alert(msg, function(){
                                            Util.closeAllDialogs();
                                            dialog.remove();
                                            window.location.reload(true);
                                        });
                                    }
                                }
                                Util.openFormInDialog.call('', 'portalForm', title, 600, 600, expObj);
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

        $('.cancelInvoice').live('click', function (e){
            var invoice_status = $(this).attr('invoice_status');
            var order_id = $('#fld_order_id').val();
            var patient_visit_id = $('#record_id').val();
            if (invoice_status != 'Paid') {
                msg = "Do you like to cancel the Invoice?";
                if (!confirm(msg)){
                    return false;
                }
                else {
                    var url = 'index.php?_topRm=finance&module=hms_order&_spAction=cancelInvoice&showHTML=0';
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
                            //cpm.hms.patientVisit.reloadInvoicePortal(order_id, patient_visit_id);
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

        $('.cancelReceipt').live('click', function (e){
            msg = "Do you like to cancel the Receipt?";
            var order_id = $('#fld_order_id').val();
            var patient_visit_id = $('#record_id').val();
            if (!confirm(msg)){
                return false;
            }
            else {
                var url = 'index.php?_topRm=finance&module=hms_order&_spAction=cancelReceipt&showHTML=0';
                Util.showProgressInd();
                var receipt_code = $(this).attr('receipt_code');
                var order_id     = $(this).attr('order_id');
                $.get(url,{receipt_code: receipt_code, order_id:order_id}, function(html){
                    alert ('Receipt Cancelled Succesfully');
                    Util.hideProgressInd();
                    cpm.hms.patientVisit.reloadReceiptPortal(order_id);
                    cpm.hms.patientVisit.reloadInvoicePortal(order_id, patient_visit_id);
                    //window.location.reload(true);
                });
            }
        });

        $('.m-hms_patientVisit #generateReceipt').livequery('click', function (e){
            var title = "Create Receipt";
            e.preventDefault();
            var order_id = $('#fld_order_id').val();
            var patient_visit_id = $('#record_id').val();
            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Receipt created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        cpm.hms.patientVisit.reloadReceiptPortal(order_id);
                        cpm.hms.patientVisit.reloadInvoicePortal(order_id, patient_visit_id);
                        //window.location.reload(true);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 500, 500, expObj);
        });

        $('.m-hms_patientVisit input.invoiceCode').livequery('click', function (e){
            Util.showProgressInd();
            invoice_code = $(this).val();
            var checked    = $(this).attr('checked') ? 'checked' : '';
            var checkedVal = checked == 'checked' ? 1 : 0;

            var url = 'index.php?_topRm=finance&module=hms_order&_spAction=populateReceiptAmount&showHTML=0';
            $.get(url,{invoice_code: invoice_code ,checkedVal: checkedVal}, function(html){
                $('input[id=fld_amount]').val(html);
                Util.hideProgressInd();
            });
        });

        $('.m-hms_patientVisit input.dueInvoiceCode').livequery('click', function (e){
            Util.showProgressInd();
            invoice_code = $(this).val();
            var checked    = $(this).attr('checked') ? 'checked' : '';
            var checkedVal = checked == 'checked' ? 1 : 0;

            var url = 'index.php?_topRm=finance&module=hms_order&_spAction=populateReceiptAmount&showHTML=0';
            $.get(url,{invoice_code: invoice_code ,checkedVal: checkedVal}, function(html){
                $('.due_amount_table_disable').slideToggle();
                var overAllTotalAmount = 0;
                var overAllBalanceAmount = 0;
                var invoice_amount = parseInt(html);
                var totalVal   = $('#overall_Total_invoice_hidden').val();
                var receiptVal = $('input[name="due_receipt_amount"]').val();
                overAllTotalAmount   = Number(parseInt(totalVal) + parseInt(invoice_amount));
                overAllBalanceAmount = Number(parseInt(totalVal) + parseInt(invoice_amount) -  parseInt(receiptVal));
                $('input[id=fld_due_amount_hidden]').val(invoice_amount.toFixed(2));
                $('input[id=fld_due_amount]').val(invoice_amount.toFixed(2));
                $('input[id=balance_Total_invoice]').val(overAllBalanceAmount.toFixed(2));
                $('input[id=fld_overall_Total_invoice]').val(overAllTotalAmount.toFixed(2));
                Util.hideProgressInd();
            });
        });

        $('.m-hms_patientVisit input[name="due_receipt_amount"]').live("keyup", function (){
            var overAllTotalAmount = 0;
            var receipt_amount = $(this).val();
            var due_amount = $('.m-hms_patientVisit input[name="due_amount"]').val();
            var checked    = $('.m-hms_patientVisit input.dueInvoiceCode').attr('checked') ? 'checked' : '';
            var checkedVal = checked == 'checked' ? 1 : 0;
            if(checkedVal == 1){
                var totalVal = $('input[id=fld_overall_Total_invoice]').val();
            }else{
                var totalVal = $('input[id=overall_Total_invoice_hidden]').val();
            }

            overAllTotalAmount = Number(parseInt(totalVal) - parseInt(receipt_amount));
            $('input[id=balance_Total_invoice]').val(overAllTotalAmount.toFixed(2));
        });

        $('.m-hms_patientVisit input[name="due_amount"]').live("keyup", function (){
            var overAllTotalAmount = 0;
            var due_amount = $(this).val();
            var totalVal = $('#overall_Total_invoice_hidden').val();
            overAllTotalAmount = Number(parseInt(totalVal) + parseInt(due_amount));
            $('input[id=fld_overall_Total_invoice]').val(overAllTotalAmount.toFixed(2));
        });

        $('.m-hms_patientVisit #billSummaryOrder').livequery('click', function (e){
            var title = "Bill Summary";
            var order_id = $(this).attr('order_id');
            e.preventDefault();

            var expObj = {
                afterOpen: function(){
                    Util.closeAllDialogs();
                }
            }

            Util.openDialogForLink.call(this, title, 600, 'auto', expObj);
        });

        $('.m-hms_patientVisit .labTitle').livequery('click', function (e){
            var title = $(this).val();
            var is_checked  = $(this).is(':checked');

            var parent = $(this).closest('.labTestBox');

            if(is_checked == true){
                $('.hideLabDetails', parent).show();
            } else {
                $('.hideLabDetails', parent).hide();
            }
        });

        $('.m-hms_patientVisit .searchPatientButton').livequery('click', function (e){
           var inputBoxVaue  = $('.searchInputPatientVisit').val();
           var dropdownValue = $('#fld_search_type_by_list').val();
           var lock = 1;
           var url = 'index.php?module=hms_patientVisit&_spAction=patientVisitSearchResult&showHTML=0';
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

        $('a.createVisit').livequery('click', cpm.hms.patientVisit.createPatientVisit);

        $('input[name="selected_tooth[]"]').live('click', function (e){
            var tooth_id  = $(this).val();
            var is_checked  = $(this).is(':checked');

            if(is_checked == true){
                cpm.hms.patientVisit.SelectSymbolsForm();
            }
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
            var tooth_part       = $(this).attr('tooth_part');

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

                    cpm.hms.patientVisit.reloadToothList2(patient_visit_id, tooth_form_type, labs_id);
                    cpm.hms.patientVisit.reloadToothList3(patient_visit_id, tooth_form_type, labs_id);
                }else{
                    cpm.hms.patientVisit.SelectSymbolsForm(checboxid, tooth_id, patient_visit_id, tooth_part);
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
            var tooth_part       = $(this).attr('tooth_part');

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

                    cpm.hms.patientVisit.reloadToothList3(patient_visit_id, tooth_form_type, labs_id);
                    cpm.hms.patientVisit.reloadToothList2(patient_visit_id, tooth_form_type, labs_id);
                }else{
                    cpm.hms.patientVisit.SelectSymbolsForm(checboxid, tooth_id, patient_visit_id, tooth_part);
                }
            }
        });

        $('input[name="selected_Symbols[]"]').live('click', function (e){
            var symbol_name      = $(this).val();
            var is_checked       = $(this).is(':checked');
            var tooth_id         = $('#tooth_id').val();
            var prevcount        = $('#Checkbox_ID').val();
            var patient_visit_id = $('#patient_visit_id').val();
            var tooth_form_type  = $('#fld_tooth_form_type').val();
            var labs_id          = $('#fld_labs_id').val();
            var tooth_part       = $('#tooth_part').val();

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
                        $.get(url, {symbol_name: symbol_name, tooth_id:tooth_id, patient_visit_id:patient_visit_id, tooth_form_type:tooth_form_type, labs_id:labs_id, tooth_part:tooth_part}, function(html){
                            $('#dialog1').dialog('close');
                            Util.hideProgressInd();
                        });

                        cpm.hms.patientVisit.reloadToothList2(patient_visit_id, tooth_form_type, labs_id);
                        cpm.hms.patientVisit.reloadToothList3(patient_visit_id, tooth_form_type, labs_id);
                    }
                }
                else{
                    Util.showProgressInd();
                    var url  = 'index.php?module=hms_patientVisit&_spAction=addPerioChartRecord&showHTML=0';
                    $.get(url, {symbol_name: symbol_name, tooth_id:tooth_id, patient_visit_id:patient_visit_id, tooth_form_type:tooth_form_type, labs_id:labs_id, tooth_part:tooth_part}, function(html){
                        $('#dialog1').dialog('close');
                        Util.hideProgressInd();
                    });

                    cpm.hms.patientVisit.reloadToothList2(patient_visit_id, tooth_form_type, labs_id);
                    cpm.hms.patientVisit.reloadToothList3(patient_visit_id, tooth_form_type, labs_id);
                }
            }
        });

        $('select[name=employee_id]').livequery('change', function(){
            var employee_id = $(this).val();

            var url = 'index.php?module=hms_patientVisit&_spAction=updateConsultingFees&showHTML=0';
            $.get(url, {employee_id: employee_id}, function(html){
                $('#fld_consultation_fees').val(html);
            });
        });

        $('.followUpDate select').livequery('change', function(){
            var follow_up_date = $(this).val();
            var parent = $(this).closest('.treatmentNotes');

            var url = 'index.php?module=hms_patientVisit&_spAction=convertFollowUpDate&showHTML=0';
            $.get(url, {follow_up_date: follow_up_date}, function(html){
                $('.followUpDate input', parent).val(html);
            });
        });

        $('select[name=follow_up_value]').livequery('change', function(){
            var follow_up_date = $(this).val();

            var url = 'index.php?module=hms_patientVisit&_spAction=convertFollowUpDate&showHTML=0';
            $.get(url, {follow_up_date: follow_up_date}, function(html){
                $('#fld_follow_up_date').val(html);
            });
        });

        $('select[name=longtime_follow_up_value]').livequery('change', function(){
            var follow_up_date = $(this).val();

            var url = 'index.php?module=hms_patientVisit&_spAction=convertFollowUpDate&showHTML=0';
            $.get(url, {follow_up_date: follow_up_date}, function(html){
                $('#fld_longtime_follow_up_date').val(html);
            });
        });

        $('.m-hms_patientVisit .selectedToothSymbolEdit').livequery('click', function (e){
            var tooth_id         = $(this).attr('tooth_id');
            var tooth_part       = $(this).attr('tooth_part');
            var patient_visit_id = $(this).attr('patient_visit_id');
            var checboxid        = $(this).attr('Checkbox_ID');
            cpm.hms.patientVisit.fnOpenButtonTextChangedDialog(checboxid, tooth_id, patient_visit_id, tooth_part);
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

        /*$('.goToSearchPatientVisit').livequery('click', function (e){
            $('.searchListDisplay').show();
            $('.defaultListDisplay').hide();
            $('.cpSearch').hide();
        });*/

        $('.displayVisitRecords').livequery('click', function (e){
            $('.searchListDisplay').hide();
            $('.defaultListDisplay').show();
            $('.cpSearch').show();
        });

        /*$('.m-hms_patientVisit .TreatmentSubmit').livequery('click', function (e){
            var url  = 'index.php?module=hms_patientVisit&_spAction=treatmentRecordSubmit&showHTML=0';
            $.get(url, function(html){
                alert('Submited');
            });

        });*/

        $('#portalForm_treatmentDisplay').livequery('submit', cpm.hms.patientVisit.addTreatmentRecord);
        $('#portalForm_subjectiveAssessmentDisplay').livequery('submit', cpm.hms.patientVisit.addPortalSaveRecord);
        $('#portalForm_objectiveAssessmentDisplay').livequery('submit', cpm.hms.patientVisit.addPortalSaveRecord);
        $('#portalForm_problemAnalysisDisplay').livequery('submit', cpm.hms.patientVisit.addPortalSaveRecord);
        $('#portalForm_goalSmartDisplay').livequery('submit', cpm.hms.patientVisit.addPortalSaveRecord);
        $('#portalForm_goalSmartDisplayForTreatmentTab').livequery('submit', cpm.hms.patientVisit.addPortalSaveRecord);
        $('#portalForm_appointmentFromPatientVisit').livequery('submit', cpm.hms.patientVisit.addPortalSaveRecord);

        $('#portalForm_labDisplay').livequery('submit', function(){
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

        $('#portalForm_labsDisplay').livequery('submit', function(){
          $.post($(this).attr('action'), $(this).serialize(), function(response){
                // do something here on success
                var mgsalert='Record Created Successfully';
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

        $('#portalForm_diagnosisDisplay').livequery('submit', function(){
          Util.showProgressInd();
          $.post($(this).attr('action'), $(this).serialize(), function(response){
                var mgsalert='Record Saved Successfully';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 2000,
                });
                var patient_visit_id = $('#record_id').val();
                cpm.hms.patientVisit.reloadDiagnosisTabPortal(patient_visit_id);
          },'json');
          return false;
       });

        $('#portalForm_medHisDisplay').livequery('submit', function(){
          $.post($(this).attr('action'), $(this).serialize(), function(response){
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

        $('#portalForm_oralHygienic').livequery('submit', function(){
          $.post($(this).attr('action'), $(this).serialize(), function(response){
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

        $('#portalForm_habits').livequery('submit', function(){
          $.post($(this).attr('action'), $(this).serialize(), function(response){
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

        $('#portalForm_intraOral').livequery('submit', function(){
          $.post($(this).attr('action'), $(this).serialize(), function(response){
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

        $('#portalForm_extraOral').livequery('submit', function(){
          $.post($(this).attr('action'), $(this).serialize(), function(response){
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

        $('#portalForm_peridontium').livequery('submit', function(){
          $.post($(this).attr('action'), $(this).serialize(), function(response){
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

        $('#portalForm_medicalCertificateDisplay').livequery('submit', function(){
          $.post($(this).attr('action'), $(this).serialize(), function(response){
                // do something here on success
                var mgsalert='Medical Certificate updated Successfully';
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

       $('.cancelVisitRecord').livequery('click', function(e){
            msg = "Please note related receipt,\n\n invoice will also be cancelled,\n\n Do you like to Cancel?";
            var patient_visit_id = $(this).attr('patient_visit_id');
            if (!confirm(msg)){
                return false;
            }
            else {
                Util.showProgressInd();
                var url = 'index.php?module=hms_patientVisit&_spAction=cancelPatientVisitRecord&showHTML=0';
                $.get(url,{patient_visit_id: patient_visit_id}, function(html){
                    Util.hideProgressInd();
                    Util.alert('Patient Visit & Related Invoice, Receipt Cancelled Successfully!')
                    window.location.reload(true);
                });
            }
       });

       $('.viewSummaryForTreatmentRecord').live('click', function (e){
            e.preventDefault();
            var expObj = {
                beforeCloseFn: function(){
                    Util.closeAllDialogs();
                }
            }
            Util.openDialogForLink.call(this, 'Treatment Summary', 1100, 550, expObj);
        });

       $('.viewSummaryForLabsRecord').live('click', function (e){
            e.preventDefault();
            var expObj = {
                beforeCloseFn: function(){
                    Util.closeAllDialogs();
                }
            }
            Util.openDialogForLink.call(this, 'Labs Payment Summary', 460, 422, expObj);
        });

        $('#supplier_categoryFormLink').live('click', function (e){
            alert('Please Cancel the receipt(s) to edit the record!');
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

        $('#generatenoReceipt').live('click', function (e){
            alert('Please generate receipt!');
        });

        $("select[name='treatmentCategory']").livequery('change', function (e){
            var TreatmentCategory = $(this).val();
            var patient_visit_id = $('#record_id').val();

            Util.showProgressInd();
            var url = 'index.php?module=hms_patientVisit&_spAction=TreatmentPortalDisplay&showHTML=0';
            $.get(url,{patient_visit_id:patient_visit_id, TreatmentCategory: TreatmentCategory}, function(html){
                $('.treatmentTabDisplay').html(html);
                Util.hideProgressInd();
            });
        });


        var timeoutId2;
        $(".treatmentSearchAuto").livequery("keyup", function (){
            clearTimeout(timeoutId2);
            var searchTreatment = $(this).val();
            var patient_visit_id = $('#record_id').val();

            timeoutId2 = setTimeout(function() {
                var url = 'index.php?module=hms_patientVisit&_spAction=TreatmentPortalDisplay&showHTML=0';
                $.get(url,{patient_visit_id:patient_visit_id, searchTreatment: searchTreatment}, function(html){
                    $('.treatmentTabDisplay').html(html);
                    Util.hideProgressInd();
                    $('.treatmentSearchAuto').val(searchTreatment);
                });
            }, 1000);
        });

        var timeoutId3;
        $(".diagnosisSearchAuto").livequery("keyup", function (){
            clearTimeout(timeoutId3);
            var searchDiagnosis = $(this).val();
            var patient_visit_id = $('#record_id').val();

            timeoutId3 = setTimeout(function() {
                var url = 'index.php?module=hms_patientVisit&_spAction=DiagnosisPortalDisplay&showHTML=0';
                $.get(url,{patient_visit_id:patient_visit_id, searchDiagnosis:searchDiagnosis}, function(html){
                    $('.diagnosisTabDisplay').html(html);
                    Util.hideProgressInd();
                    $('.diagnosisSearchAuto').val(searchDiagnosis);
                });
            }, 1000);
        });

        $(".dlg-portalFormTextAreaAddNotesOnImageMapping button.btn-cancel").live('click', function (e){
            var patient_visit_id = $('#record_id').val();
            cpm.hms.patientVisit.reloadImageMappingTab(patient_visit_id);
        });

        $(".dlg-portalFormTextAreaEditNotesOnImageMapping button.btn-cancel").live('click', function (e){
            var patient_visit_id = $('#record_id').val();
            cpm.hms.patientVisit.reloadImageMappingTab(patient_visit_id);
        });

        $(".dlg-portalFormTextAreaAddNotesOnImageMapping .ui-dialog-titlebar-close ").livequery("click", function (e){
            var patient_visit_id = $('#record_id').val();
            cpm.hms.patientVisit.reloadImageMappingTab(patient_visit_id);
        });

        $(".dlg-portalFormTextAreaEditNotesOnImageMapping .ui-dialog-titlebar-close ").livequery("click", function (e){
            var patient_visit_id = $('#record_id').val();
            cpm.hms.patientVisit.reloadImageMappingTab(patient_visit_id);
        });

    },

    reloadDoctorTab: function(patient_visit_id){
        var url = 'index.php?module=hms_patientVisit&_spAction=doctorPortalDisplay&showHTML=0';
        $.get(url, {patient_visit_id: patient_visit_id}, function(html){
            $('#doctorDisplay').html(html);
            Util.hideProgressInd();
        });
    },

    reloadLabsTab: function(patient_visit_id){
        var url = 'index.php?module=hms_patientVisit&_spAction=LabsDisplay&showHTML=0';
        $.get(url, {patient_visit_id: patient_visit_id}, function(html){
            $('#labsDisplay').html(html);
            Util.hideProgressInd();
        });
    },

    reloadMedicineTab: function(patient_visit_id){
        var url = 'index.php?module=hms_patientVisit&_spAction=medicinesPortalDisplay&showHTML=0';
        $.get(url, {patient_visit_id: patient_visit_id}, function(html){
            $('#medicinesDisplay').html(html);
            Util.hideProgressInd();
        });
    },

    reloadLabTab: function(patient_visit_id){
        var url = 'index.php?module=hms_patientVisit&_spAction=labPortalDisplay&showHTML=0';
        $.get(url, {patient_visit_id: patient_visit_id}, function(html){
            $('#labDisplay').html(html);
            Util.hideProgressInd();
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

    patientMedicineAdd: function(e) {
        e.preventDefault();
        var patient_visit_id = $(this).attr('patient_visit_id');
        Util.showProgressInd();
        var url = 'index.php?module=hms_patientVisit&_spAction=addMedicine&patient_visit_id=' + patient_visit_id + '&showHTML=0';
        $.get(url, {patient_visit_id: patient_visit_id}, function(){
            cpm.hms.patientVisit.reloadMedicineTab(patient_visit_id);
            Util.hideProgressInd();
        });
    },

    patientProductTitle: function() {
        var titleObj = this;
        $(titleObj).autocomplete({
             source : 'index.php?module=hms_patientVisit&_spAction=searchProductTitle&showHTML=0'
            ,minLength : 2
            ,select: function(event, ui) {
                var selectedObj = ui.item;
                var product_id = selectedObj.id
                //alert (product_id);
                $(this).after("<input type='hidden' name='product_id' value=" + product_id + ">");
                //alert ('12344444');

                //To Populate the related values in the table
                //--------------------------------------------
                Util.showProgressInd();
                var parent          = $(this).closest('tr');
                var rec_id          = $(parent).attr('recid');
                var productTitleObj = $(this ).closest('tr').find('.title');
                var instructionObj  = $(this ).parents('tr').find('.instruction select[name=instruction]');
                var sellingPriceObj = $(this ).closest('tr').find('.selling-price');
                var qtyObj          = $(this ).closest('tr').find('.qty');
                var dosageObj       = $(this ).closest('tr').find('.dosage');

                var url = 'index.php?module=hms_patientVisit&_spAction=updateProductLineItems&showHTML=0';
                $.get(url, {product_id: product_id, rec_id: rec_id}, function(json){
                    if (json.msg != '') {
                        Util.hideProgressInd();
                        Util.alert(json.msg);
                        $('input[name=title]', productTitleObj).val('');
                        return;
                    }

                    $("input[name=qty]", qtyObj).val(json.qty);
                    $("input[name=qty]", qtyObj).attr("stock", json.stock);
                    $("input[name=dosage]", dosageObj).val(json.dosage);
                    $("input[name=selling_price]", sellingPriceObj).val(json.sellingPrice);
                    Util.hideProgressInd();
                    //sellingPriceObj.html(json.sellingPrice);
                    //$("input[name=selling_price]", sellingPriceObj).val(json.sellingPrice);
                    //$("input[name=selling_price]", sellingPriceObj).val(json.sellingPrice);
                });
            }
        });
    },

    createPatientVisitDetails: function(patient_information_id, appointment_id){
        var title = "Choose Doctor/Nurse";
        var url   = "index.php?module=hms_patientVisit&_spAction=selectDoctorDetails&patient_information_id="+patient_information_id+"&appointment_id="+appointment_id+"&showHTML=0";

        var exp = {
            url: url
           ,validate: true
           ,submitBtnText: 'Submit'
           ,cancelBtnText: 'Close'
           ,callbackOnSuccess: function(){
                Util.closeAllDialogs();
                cpm.hms.patientVisit.reloadSearchResult();
                cpm.hms.patientVisit.reloadQueueno();
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

    SelectSymbolsForm: function(checboxid, tooth_id, patient_visit_id, tooth_part){
        var url = "index.php?module=hms_patientVisit&_spAction=perioChartSymbols&tooth_id="+tooth_id+"&checboxid="+checboxid+"&tooth_part="+tooth_part+"&showHTML=0";

        var exp = {
            url: url
            ,afterOpen: function(){

            }
        };
        Util.openDialogForLink('', 588, 'auto', 0, exp);
    },

    reloadSearchResult: function(){
        var inputBoxVaue  = $('.searchInputPatientVisit').val();
        var dropdownValue = $('#fld_search_type_by_list').val();
        var url = 'index.php?module=hms_patientVisit&_spAction=patientVisitAppointmentSearchResult&showHTML=0';

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

    DeleteFromPerioTable: function(tooth_id, patient_visit_id, tooth_form_type, labs_id, tooth_part){
        var url = 'index.php?module=hms_patientVisit&_spAction=deletePerioChartRecord&showHTML=0';
        $.get(url, {tooth_id:tooth_id, patient_visit_id:patient_visit_id, tooth_form_type:tooth_form_type, labs_id:labs_id, tooth_part:tooth_part}, function(html){
            cpm.hms.patientVisit.reloadToothList2(patient_visit_id, tooth_form_type, labs_id);
            cpm.hms.patientVisit.reloadToothList3(patient_visit_id, tooth_form_type, labs_id);
        });
    },

    fnOpenButtonTextChangedDialog: function(checboxid, tooth_id, patient_visit_id, tooth_part) {
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
                    cpm.hms.patientVisit.SelectSymbolsForm(checboxid, tooth_id, patient_visit_id, tooth_part);
                },
                "Delete": function() {
                    $(this).dialog('close');
                    cpm.hms.patientVisit.DeleteFromPerioTable(tooth_id, patient_visit_id, tooth_form_type, labs_id, tooth_part);
                },
                "Close": function() {
                    $(this).dialog('close');
                }
            }
        });
    },

    reloadInvoicePortal: function(order_id, patient_visit_id){
        var url = 'index.php?module=hms_patientVisit&_spAction=InvoicePortalDisplay&showHTML=0';
        Util.showProgressInd();
        $.get(url,{order_id:order_id}, function(html){
            $('#patientVisitInvoicePortal').html(html);

            var invoice_count    = $('#fld_invoice_count').val();
            if(invoice_count == 0){
                $('#billSummaryOrder').after("<a href='#' id='createOrderRecord' patient_visit_id="+patient_visit_id+" class='button'>Generate Bill</a>");
                $('#billSummaryOrder').remove();
            }

            Util.hideProgressInd();
        });
    },

    reloadReceiptPortal: function(order_id){
        var url = 'index.php?module=hms_patientVisit&_spAction=ReceiptPortalDisplay&showHTML=0';
        Util.showProgressInd();
        $.get(url,{order_id:order_id}, function(html){
            $('#patientVisitReceiptPortal').html(html);
            Util.hideProgressInd();
        });
    },

    reloadTreatmentTabPortal: function(patient_visit_id){
        var url = 'index.php?module=hms_patientVisit&_spAction=TreatmentPortalDisplay&showHTML=0';
        $.get(url,{patient_visit_id:patient_visit_id}, function(html){
            $('.treatmentTabDisplay').html(html);
            Util.hideProgressInd();
        });
    },

    reloadDiagnosisTabPortal: function(patient_visit_id){
        var url = 'index.php?module=hms_patientVisit&_spAction=DiagnosisPortalDisplay&showHTML=0';
        $.get(url,{patient_visit_id:patient_visit_id}, function(html){
            $('.diagnosisTabDisplay').html(html);
            Util.hideProgressInd();
        });
    },

    openTextAreaForImageSelectedArea: function(patient_visit_id, title){
        var url = 'index.php?_topRm=main&module=hms_patientVisit&_spAction=showNotesForImageMapping&showHTML=0'+'&patient_visit_id='+ patient_visit_id+'&title='+ title;

        var title = "Add Notes";
        var expObj = {
            url: url
           ,validate: true
           ,callbackOnSuccess: function(){
                var msg = 'Saved Successfully!';
                Util.alert(msg, function(){
                    $(".ui-dialog-content").dialog("close");
                    cpm.hms.patientVisit.reloadImageMappingTab(patient_visit_id);
                });
            }
        }
        Util.openFormInDialog.call('', 'portalFormTextAreaAddNotesOnImageMapping', title, 500, 300, expObj);
    },

    editTextAreaForImageSelectedArea: function(patient_visit_image_mapping_id, patient_visit_id){
        var url = 'index.php?_topRm=main&module=hms_patientVisit&_spAction=editNotesForImageMapping&showHTML=0'+'&patient_visit_id='+ patient_visit_id+'&patient_visit_image_mapping_id='+ patient_visit_image_mapping_id;

        var title = "Edit Notes";
        var expObj = {
            url: url
           ,validate: true
           ,callbackOnSuccess: function(){
                var msg = 'Updated Successfully!';
                Util.alert(msg, function(){
                    $(".ui-dialog-content").dialog("close");
                    cpm.hms.patientVisit.reloadImageMappingTab(patient_visit_id);
                });
            }
        }
        Util.openFormInDialog.call('', 'portalFormTextAreaEditNotesOnImageMapping', title, 500, 300, expObj);
    },

    deleteTextAreaForImageSelectedArea: function(patient_visit_image_mapping_id, patient_visit_id){
        var msg = "Are you sure to delete this item?";
        if (confirm(msg)){
            var url = 'index.php?_topRm=main&module=hms_patientVisit&_spAction=deleteNotesForImageMapping&showHTML=0'+'&patient_visit_id='+ patient_visit_id+'&patient_visit_image_mapping_id='+ patient_visit_image_mapping_id;

            $.get(url, {patient_visit_image_mapping_id: patient_visit_image_mapping_id, patient_visit_id: patient_visit_id}, function(json){
                var msg = 'Deleted Successfully!';
                Util.alert(msg, function(){
                    Util.closeAllDialogs();
                    cpm.hms.patientVisit.reloadImageMappingTab(patient_visit_id);
                });
            });
        }
    },

    openDBhasValueSelectedArea: function(patient_visit_image_mapping_id, patient_visit_id) {
        var buf = "Are you sure want to?";
        // buf will be shown on the body of Dialog.
        $("#dialog-confirm").html(buf);

        // Define the Dialog and its properties.
        $("#dialog-confirm").dialog({
            resizable: false,
            modal: true,
            title: "",
            height: 'auto',
            width: 260,
            buttons: {
                "View/Edit": function() {
                    $(this).dialog('close');
                    cpm.hms.patientVisit.editTextAreaForImageSelectedArea(patient_visit_image_mapping_id, patient_visit_id);
                },
                "Delete": function() {
                    $(this).dialog('close');
                    cpm.hms.patientVisit.deleteTextAreaForImageSelectedArea(patient_visit_image_mapping_id, patient_visit_id);
                },
                "Close": function() {
                    $(this).dialog('close');
                }
            }
        });
    },

    reloadImageMappingTab: function(patient_visit_id){
        var url = 'index.php?module=hms_patientVisit&_spAction=imageMappingDisplay&showHTML=0';
        Util.showProgressInd();
        $.get(url, {patient_visit_id: patient_visit_id}, function(html){
            Util.closeAllDialogs();
            $('#imageMapping').html(html);
            Util.hideProgressInd();
        });
    },

    reloadPatientVisitCreateForm: function(patient_information_id, appointment_id){
        var url = 'index.php?module=hms_patientVisit&_spAction=selectDoctorDetails&showHTML=0';
        $.get(url, {patient_information_id: patient_information_id, appointment_id:appointment_id}, function(html){
            $('.chooseDrFormForPatientVisitCreate').html(html);
            Util.hideProgressInd();
        });
    },
}

cpm.hms.patientVisit.createPatientVisit = function(){
    var url = 'index.php?module=hms_patientVisit&_spAction=createVisitRecordDirect&showHTML=0';
    var dr_required            = $(this).attr('dr_required');
    var patient_information_id = $(this).attr('patient_information_id');
    var appointment_id         = $(this).attr('appointment_id');

    if(dr_required == ''){
        cpm.hms.patientVisit.createPatientVisitDetails(patient_information_id, appointment_id);
    }else{
        $.get(url,{patient_information_id:patient_information_id, dr_required:dr_required, appointment_id:appointment_id}, function(html){
            Util.closeAllDialogs();
            cpm.hms.patientVisit.reloadSearchResult();
            cpm.hms.patientVisit.reloadQueueno();
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
    }
}

cpm.hms.patientVisit.addPatientRecord = function(e){
    var title = "Create Patient Record";
    var patient_visit_id   = $(this).attr('patient_visit_id');
    e.preventDefault();

    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            var msg = 'Record created successfully';
            Util.alert(msg, function(){
                Util.closeAllDialogs();
                //cpm.hms.patientVisit.reloadDoctorTab(patient_visit_id);
                //window.location.reload(true);
            });
        }
    }
    Util.openFormInDialog.call(this, 'portalForm', title, 1100, 500, expObj);
}

cpm.hms.patientVisit.addPortalSaveRecord = function(){
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
}

cpm.hms.patientVisit.addTreatmentRecord = function(){
    Util.showProgressInd();
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
        var patient_visit_id = $('#record_id').val();
        cpm.hms.patientVisit.reloadTreatmentTabPortal(patient_visit_id);
    },'json');
    return false;
}
