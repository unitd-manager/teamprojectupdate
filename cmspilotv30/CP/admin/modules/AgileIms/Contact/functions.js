Util.createCPObject('cpm.agileIms.contact');
//portalPvtLinkForm forms are meant for private institute.
cpm.agileIms.contact.init = function(){
    $(window).load(function(){
        $('.m-agileIms_contact #frmEdit select[name=company_id]').livequery('change', function(e){
            var company_id = $(this).val();

            if (company_id == '') {
                    $('.m-agileIms_contact #frmEdit #fld_company_contact_person').html('');
                    $('.m-agileIms_contact #frmEdit #fld_office_no').html('');
                    $('.m-agileIms_contact #frmEdit #fld_company_fax').html('');
                    $('.m-agileIms_contact #frmEdit #fld_company_address_flat').html('');
                    $('.m-agileIms_contact #frmEdit #fld_company_address_street').html('');
                    $('.m-agileIms_contact #frmEdit #fld_company_address_po_code').html('');
                    $('.m-agileIms_contact #frmEdit #fld_company_address_country').html('');
            } else {
                var url = 'index.php?module=agileIms_company&_spAction=companyDetailsForContactJson&showHTML=0';
                $.get(url, {company_id: company_id}, function(json){
                    $('.m-agileIms_contact #frmEdit #fld_company_contact_person').html(json.contact_name);
                    $('.m-agileIms_contact #frmEdit #fld_office_no').html(json.phone);
                    $('.m-agileIms_contact #frmEdit #fld_company_fax').html(json.fax);
                    $('.m-agileIms_contact #frmEdit #fld_company_address_flat').html(json.address1);
                    $('.m-agileIms_contact #frmEdit #fld_company_address_street').html(json.address2);
                    $('.m-agileIms_contact #frmEdit #fld_company_address_po_code').html(json.address_po_code);
                    $('.m-agileIms_contact #frmEdit #fld_company_address_country').html(json.address_country);
                });
            }
        }),


        $(".m-agileIms_contact .row_is_citizen input[name='is_citizen']").livequery('change', function(e){
            var studentPass = $(".m-agileIms_contact .row_student_pass_holder input[name='student_pass_holder']:checked").val();
            var citizen = $(this).val();
            if (citizen == 1 || studentPass == 1){
                $('.m-agileIms_contact .citizenNo').hide();
            } else {
                $('.m-agileIms_contact .citizenNo').removeClass('hideme');
                $('.m-agileIms_contact .citizenNo').show();
            }
        });

        $('.agileIms_contact__agileIms_courseLink select[name=course_id]').livequery(function(){
           cpm.agileIms.contact.populateBatch.call(this);
        });
    
        //For Non Pvt getting related batch , subsidy and discount
        $('.m-agileIms_contact #portalForm #fld_course_id').livequery('change', function(){

            Util.showProgressInd('Populating related content.... Please wait');

            cpm.agileIms.contact.populateBatch.call(this);
            cpm.agileIms.contact.populateSubsidy.call(this);
            cpm.agileIms.contact.populateDiscount.call(this);

            course_id = $('.m-agileIms_contact #portalForm #fld_course_id').val();
            var url = 'index.php?module=agileIms_courseLink&_spAction=courseSummary&showHTML=0';
            $.get(url, {course_id: course_id}, function(html){
                $('tr#courseAmount').html(html);
                $('table#courseSummary').removeClass('hideme');
                cpm.agileIms.contact.calculateCourseSummaryTotal();
                Util.hideProgressInd();
            });
        }),
        
        //TO display course termination date for Edit Form PVT
        $(".m-agileIms_contact #portalPvtLinkEditForm select[name='course_status']").livequery(function(){
            var courseStatus = $(this).val();
            if (courseStatus == 'Expelled' || courseStatus == 'Terminated' || courseStatus == 'Withdrawal') {
                $('#portalPvtLinkEditForm .row_course_termination_date').removeClass('hideme');
                $('#portalPvtLinkEditForm .row_remarks').removeClass('hideme');
            }
        });

        $(".m-agileIms_contact #portalPvtLinkEditForm select[name='course_status']").livequery('change', function(e){
            var courseStatus = $(this).val();
            if (courseStatus == 'Expelled' || courseStatus == 'Terminated' || courseStatus == 'Withdrawal') {
                Util.showProgressInd();
                $('#portalPvtLinkEditForm .row_course_termination_date').removeClass('hideme');
                $('#portalPvtLinkEditForm .row_remarks').removeClass('hideme');
                Util.hideProgressInd();
            } else {
                Util.showProgressInd();
                $('#portalPvtLinkEditForm .row_course_termination_date').addClass('hideme');
                $('#portalPvtLinkEditForm .row_remarks').addClass('hideme');
                Util.hideProgressInd();
            }
        }),
        
        //Subsidy for pvt institute - Student enrollment new form
        $('.m-agileIms_contact #portalForm #fld_subsidy_discount_id').livequery('change', function(){
            cpm.agileIms.contact.populateSubsidyPvt('portalForm');
        }),

        //Subsidy for pvt institute - Student enrollment edit form
        $('.m-agileIms_contact .studentEnrollmentEdit #fld_subsidy_discount_id').livequery('change', function(){
            alert ('Please note that changing subsidy will cancel the existing invoice and create a new invoice.');
            cpm.agileIms.contact.populateSubsidyPvt('portalFormEdit');
        }),
        
        //Discount for pvt institute - Student enrollment new form
        $('.m-agileIms_contact #portalForm #fld_discount').livequery('change', function(){
            cpm.agileIms.contact.populateDiscountPvt('portalForm');
        }),

        //Discount for pvt institute - Student enrollment edit form
        $('.m-agileIms_contact .studentEnrollmentEdit #fld_discount').livequery('change', function(){
            alert ('Please note that changing discount will cancel the existing invoice and create a new invoice.');
            cpm.agileIms.contact.populateDiscountPvt('portalFormEdit');
        }),
        
        $(this).livequery('change', function(){
            course_val = $('.m-agileIms_contact #portalForm .courseSummary .amount').val();
        });

        /* Show or Hide Auto generation of receipt with regards to auto generation of Invoice */
        $(".m-agileIms_contact .studentEnrollment input:radio[name='auto_generate_invoice']").livequery('click', function(e){
            var autoGenerationInvVal = $(this).val();
            if (autoGenerationInvVal == 0){
                $(".m-agileIms_contact .studentEnrollment .row_auto_generate_receipt").hide();
            } else {
                $(".m-agileIms_contact .studentEnrollment .row_auto_generate_receipt").show();
            }
        });
    });

}

cpm.agileIms.contact.populateCourseAmount = function(course_id){
    var url = 'index.php?module=agileIms_courseLink&_spAction=courseSummary&showHTML=0';
    $.get(url, {course_id: course_id}, function(html){
        $('tr#courseAmount').html(html);
        $('table#courseSummaryPvt').removeClass('hideme');
        cpm.agileIms.contact.calculateCourseSummaryTotalPvt();
        Util.hideProgressInd();
    });
}

cpm.agileIms.contact.populateBatch = function(){
    var parent = $(this).closest('tr');
    $(this).each(function(){
        courseId = $(this).val();
        batchObj = $('select[name=batch_id]', parent);
        batchObj = $('.m-agileIms_contact #portalForm #fld_batch_id');
		
        batchId = batchObj.val();

        var url = $('#scopeRootAlias').val() + 'index.php?module=agileIms_batchLink&_spAction=batchValueForDropDown&showHTML=0';
        
        $.ajax({
            type: "POST",
            url: url,
            async: false,
            dataType: 'json',
            success: function(json){
                batchObj.empty();
                $.each(json, function() {
                    batchObj.append(new Option(this.caption, this.value));
                    batchObj.val(batchId);
                });
            },
            data: {srcFld: 'course_id', srcValue: courseId}
        });
    });
}

cpm.agileIms.contact.populateSubsidy = function(){
    var parent = $(this).closest('tr');
    $(this).each(function(){
    //alert (123);
        courseId = $(this).val();
        //subsidyObj = $('select[name=subsidy_discount_id]', parent);
        subsidyObj = $('.m-agileIms_contact #portalForm #fld_subsidy_discount_id');
		
        subsidyId = subsidyObj.val();

        var url = 'index.php?module=agileIms_courseSubsidyLink&_spAction=subsidyValueForDropDown&showHTML=0';
        
        $.ajax({
            type: "POST",
            url: url,
            async: false,
            dataType: 'json',
            success: function(json){
                subsidyObj.empty();
                $.each(json, function() {
                    subsidyObj.append(new Option(this.caption, this.value));
                    subsidyObj.val(subsidyId);
                });
            },
            data: {srcFld: 'course_id', srcValue: courseId}
        });
    });
}

cpm.agileIms.contact.populateDiscount = function(){
    var parent = $(this).closest('tr');
    $(this).each(function(){
        courseId = $(this).val();
        discountObj = $('.m-agileIms_contact #portalForm #fld_discount');
		
        discountId = discountObj.val();

        var url = 'index.php?module=agileIms_courseSubsidyLink&_spAction=discountValueForDropDown&showHTML=0';
        
        $.ajax({
            type: "POST",
            url: url,
            async: false,
            dataType: 'json',
            success: function(json){
                discountObj.empty();
                $.each(json, function() {
                    discountObj.append(new Option(this.caption, this.value));
                    discountObj.val(discountId);
                });
            },
            data: {srcFld: 'course_id', srcValue: courseId}
        });
    });
}

cpm.agileIms.contact.calculateCourseSummaryTotal = function(){
    var parent = $('table#courseSummary');
    
    var courseAmount   = parseInt($('tr#courseAmount td.amount').text());
    var subsidyAmount  = parseInt($('tr#subsidyAmount td.amount').text());
    var discountAmount = parseInt($('tr#discountAmount td.amount').text());
    var regAmount      = parseInt($('tr#registrationAmount td.amount').text());
    
    var total = 0;
    if (!isNaN(courseAmount)){
        var total = courseAmount;
    }
    
    if (!isNaN(subsidyAmount)){
        var total = total - subsidyAmount;
    }

    if (!isNaN(discountAmount)){
        var total = total - discountAmount;
    }

    if (!isNaN(regAmount)){
        var total = total + regAmount;
    }

    $('tr#totalAmount td.amount').html(total);
}

$('#traineeNewEnrollment').livequery('click', function (e){
    var title = "Student Enrollment";
    e.preventDefault();
    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            var msg = 'Successfully completed enrollment. Please Goto Finance to create invoice and receipt.';
            Util.alert(msg, function(){
                Util.closeAllDialogs();
                window.location.reload(true);
            });
        }
    }
    Util.openFormInDialog.call(this, 'portalForm', title, 800, 500, expObj);        
});

//For PVT : when reg fee is checked respective amount is displayed in the bottom
$('input[name=add_registration_fee]').livequery('click', function (e){
    cpm.agileIms.contact.populateRegisterAmountPvt();
});

cpm.agileIms.contact.populateRegisterAmountPvt = function(){
    add_registration_fee = $('input:radio[name=add_registration_fee]:checked').val();
    registration_fee = $('input:hidden[name=registration_fee]').val();
    if (add_registration_fee == 1) {
        $('table#courseSummary').removeClass('hideme');
        html1 ='<td>Registration Amount</td><td style="text-align:right;" class="amount">'
        + registration_fee +
        '</td>';
        $('tr#registrationAmount').html(html1);
        cpm.agileIms.contact.calculateCourseSummaryTotal();
    }
    else{
        $('tr#registrationAmount').empty();
        cpm.agileIms.contact.calculateCourseSummaryTotal();
    }
}

$('.editStudentEnrollment').livequery('click', function (e){
    var title = "Edit Student Enrollment";
    e.preventDefault();
    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            var msg = 'Updated successfully';
            Util.alert(msg, function(){
                Util.closeAllDialogs();
                window.location.reload(true);
            });
        }
    }
    Util.openFormInDialog.call(this, 'portalFormEdit', title, 800, 500, expObj);        
});

/* Cancel of Invoice in Invoice portal */
$('.cancelEnrollment').livequery('click', function (e) {
    msg = "Do you like to cancel enrollment? Please note that all the invoice(s) and receipt(s) will also be cancelled";
    if (!confirm(msg)) {
        return false;
    } else {
        var url = 'index.php?_topRm=finance&module=agileIms_contact&_spAction=cancelEnrollmentForStudent&showHTML=0';
        Util.showProgressInd();
        var order_id = $(this).attr('order_id');
        $.get(url,{order_id: order_id}, function(html){
            alert ('Enrollment Cancelled Succesfully');
            Util.hideProgressInd();
            window.location.reload(true); 
        });
    }
});

cpm.agileIms.contact.populateSubsidyPvt = function(formId){
    subsidy_discount_id = $('.m-agileIms_contact #' + formId +' #fld_subsidy_discount_id').val();
    if (formId == 'portalFormEdit') {
        course_id = $('input[name=course_id]').val();
        course_contact_id = $('input[name=course_contact_id]').val();
    } else {
        course_id = $('.m-agileIms_contact #' + formId +' #fld_course_id').val();
        course_contact_id = $('input[name=course_contact_id]').val();
    }
    
    var url = 'index.php?module=agileIms_courseLink&_spAction=subsidyData&showHTML=0';
    
    Util.showProgressInd();
    $.get(url, {subsidy_discount_id:subsidy_discount_id, course_contact_id: course_contact_id,  course_id:course_id}, function(html){
        $('tr#subsidyAmount').html(html);
        cpm.agileIms.contact.calculateCourseSummaryTotalPvt();
        Util.hideProgressInd();
    });
}

cpm.agileIms.contact.populateDiscountPvt = function(formId){
    discount = $('.m-agileIms_contact #' + formId +' #fld_discount').val();
    if (formId == 'portalFormEdit') {
        course_id = $('input[name=course_id]').val();
    } else {
        course_id = $('.m-agileIms_contact #' + formId +' #fld_course_id').val();
    }
    
    medical_ins = $('input:radio[name=medical_insurance]:checked').val();
    add_registration_fee = $('input:radio[name=add_registration_fee]:checked').val();
    full_time  = $('input:radio[name=full_time]:checked').val();

    var url = 'index.php?module=agileIms_courseLink&_spAction=discountValueForPvt&showHTML=0';
    
    Util.showProgressInd();
    $.get(url, {course_id: course_id,discount: discount
    ,add_registration_fee:add_registration_fee
    ,full_time:full_time
    }, function(html){
        $('tr#discountAmount').html(html);
        cpm.agileIms.contact.calculateCourseSummaryTotalPvt();
        Util.hideProgressInd();
    });
}

cpm.agileIms.contact.calculateCourseSummaryTotalPvt = function(){
    var parent = $('table#courseSummaryPvt');
    
    var courseAmount   = parseInt($('tr#courseAmount td.amount').text());
    var subsidyAmount  = parseInt($('tr#subsidyAmount td.amount').text());
    var discountAmount = parseInt($('tr#discountAmount td.amount').text());
    var subjectAmount  = parseInt($('tr#subjectAmount td.amount').text());
    var regAmount      = parseInt($('tr#registrationAmount td.amount').text());
    var insAmount      = parseInt($('tr#insuranceAmount td.amount').text());
    
    var total = 0;
    if (!isNaN(courseAmount)){
        var total = courseAmount;
    }
    
    if (!isNaN(subsidyAmount)){
        var total = total - subsidyAmount;
    }

    if (!isNaN(discountAmount)){
        var total = total - discountAmount;
    }

    if (!isNaN(subjectAmount)){
        var total = total + subjectAmount;
    }

    if (!isNaN(regAmount)){
        var total = total + regAmount;
    }

    if (!isNaN(insAmount)){
        var total = total + insAmount;
    }

    $('tr#totalAmount td.amount').html(total);
}
