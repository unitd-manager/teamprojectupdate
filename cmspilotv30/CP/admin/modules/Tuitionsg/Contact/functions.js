Util.createCPObject('cpm.tuitionsg.contact');
//portalPvtLinkForm forms are meant for private institute.
cpm.tuitionsg.contact.init = function(){
    $(window).load(function(){
        $(".m-tuitionsg_contact .row_is_citizen input[name='is_citizen']").livequery('change', function(e){
            var studentPass = $(".m-tuitionsg_contact .row_student_pass_holder input[name='student_pass_holder']:checked").val();
            var citizen = $(this).val();
            if (citizen == 1 || studentPass == 1){
                $('.m-tuitionsg_contact .citizenNo').hide();
            } else {
                $('.m-tuitionsg_contact .citizenNo').removeClass('hideme');
                $('.m-tuitionsg_contact .citizenNo').show();
            }
        });

        $(".m-tuitionsg_contact .row_student_pass_holder input[name='student_pass_holder']").livequery('change', function(e){
            var citizen = $(".m-tuitionsg_contact .row_is_citizen input[name='is_citizen']:checked").val();
            var studentPass = $(this).val();
            if (citizen == 1 || studentPass == 1){
                $('.m-tuitionsg_contact .citizenNo').hide();
            } else {
                $('.m-tuitionsg_contact .citizenNo').removeClass('hideme');
                $('.m-tuitionsg_contact .citizenNo').show();
            }
        });

        $('.aceIms_contact__aceIms_courseLink select[name=course_id]').livequery(function(){
           cpm.tuitionsg.contact.populateBatch.call(this);
        });

        //TO display course termination date for Edit Form PVT
        $(".m-tuitionsg_contact #portalPvtLinkEditForm select[name='course_status']").livequery(function(){
            var courseStatus = $(this).val();
            if (courseStatus == 'Expelled' || courseStatus == 'Terminated' || courseStatus == 'Withdrawal') {
                $('#portalPvtLinkEditForm .row_course_termination_date').removeClass('hideme');
                $('#portalPvtLinkEditForm .row_remarks').removeClass('hideme');
            }
        });

        $(".m-tuitionsg_contact #portalPvtLinkEditForm select[name='course_status']").livequery('change', function(e){
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

        //TO display course for New Form PVT
        $('.m-tuitionsg_contact #portalForm #fld_course_type')
        .livequery('change', cpm.tuitionsg.contact.newEnrollmentCourseTypeChange);

        //TO display course for Edit Form PVT
        $('.m-tuitionsg_contact #portalPvtLinkEditForm #fld_course_type').livequery('change', function(){
            courseType = $(this).val();
            cpm.tuitionsg.contact.populateCourseForPvt(courseType, 'portalPvtLinkEditForm');
        }),

        // for pvt institute contact - course link , NEW form, to populate batch subject related to course
        $('.m-tuitionsg_contact #portalForm #fld_course_id')
        .livequery('change', cpm.tuitionsg.contact.studentNewEnrollmentCourseChange);

        // for pvt institute contact - course link , EDIT form, to populate batch subject related to course
        $('.m-tuitionsg_contact #portalPvtLinkEditForm #fld_course_id').livequery('change', function(){
            $('#populate_subject_id').empty();
            $('#fld_installment.text').val('');
            $('#fld_discount.text').val('');
            $('tr#courseAmount').empty();
            $('tr#discountAmount').empty();
            $('tr#installmentAmount').empty();
            $('td.amount').empty();

            course_id = $('.m-tuitionsg_contact #portalPvtLinkEditForm #fld_course_id').val();
            Util.showProgressInd('Populating related content.... Please wait');

            cpm.tuitionsg.contact.populateBatchPvt.call(this);
            cpm.tuitionsg.contact.populateSubjectPvt.call(this);
            cpm.tuitionsg.contact.populateDiscountPvt('portalPvtLinkEditForm');
            cpm.tuitionsg.contact.populateInstallementPvt('portalPvtLinkEditForm');
            cpm.tuitionsg.contact.populateCourseAmount(course_id);
        }),

        // for pvt institute contact - course link , NEW form, to populate batch subject related to course
        $('.m-tuitionsg_contact #portalForm #fld_subsidy_discount_id')
        .livequery('change', cpm.tuitionsg.contact.populateSubsidyAmountAfterCourseChange);

        // Subsidy change in student edit enrollment form
        $('.m-tuitionsg_contact #portalPvtLinkEditForm #fld_subsidy_discount_id')
        .livequery('change', cpm.tuitionsg.contact.populateSubsidyAmountAfterCourseChangeEdit);

        $('#portalForm input.subject_id')
        .livequery('click', cpm.tuitionsg.contact.subjectClick);

        $('#portalPvtLinkEditForm input.subject_id')
        .livequery('click', cpm.tuitionsg.contact.subjectClickInEdit);

        // Discount for pvt institute
        $('.m-tuitionsg_contact #portalForm #fld_discount').livequery('change', function(){
            cpm.tuitionsg.contact.populateDiscountPvt('portalForm');
        }),

        // Subsidy change in student edit enrollment form
        $('.m-tuitionsg_contact #portalPvtLinkEditForm #fld_discount')
        .livequery('change', cpm.tuitionsg.contact.populateDiscountAmountAfterCourseChangeEdit);

        $(this).livequery('change', function(){
            course_val = $('.m-tuitionsg_contact #portalForm .courseSummary .amount').val();
        });

        /* Show or Hide Auto generation of receipt with regards to auto generation of Invoice */
        $(".m-tuitionsg_contact .studentEnrollment input:radio[name='auto_generate_invoice']").livequery('click', function(e){
            var autoGenerationInvVal = $(this).val();
            if (autoGenerationInvVal == 0){
                $(".m-tuitionsg_contact .studentEnrollment .row_auto_generate_receipt").hide();
            } else {
                $(".m-tuitionsg_contact .studentEnrollment .row_auto_generate_receipt").show();
            }
        });

        /* Fees by module in NEW student enrollment */
        $(".m-aceIms_contact .studentEnrollment input:checkbox[name='fees_by_module']").livequery
        ('click', function(){
            Util.showProgressInd();
            course_id = $('.m-aceIms_contact .studentEnrollment #fld_course_id').val();
            fees_by_module = $('input:checkbox[name=fees_by_module]:checked').val();

            var url = 'index.php?module=aceIms_subjectLink&_spAction=addSubjectAmountToTotal&showHTML=0';
            $.get(url,{fees_by_module: fees_by_module, course_id: course_id}, function(html){
                $('tr#courseAmount').html(html);
            });

            var url = 'index.php?module=aceIms_courseLink&_spAction=courseSummary&showHTML=0';
            $.get(url, {course_id: course_id, fees_by_module: fees_by_module}, function(html){
                cpm.aceIms.contact.calculateCourseSummaryTotalPvt();
            });
            cpm.aceIms.contact.populateSubsidyAmountAfterCourseChange();
            cpm.aceIms.contact.populateDiscountPvt('portalForm');

            Util.hideProgressInd();
        });

        /* Fees by module in EDIT student enrollment */
        $(".m-aceIms_contact .studentEnrollmentEdit input:checkbox[name='fees_by_module']").livequery
        ('click', function(){
            Util.showProgressInd();
            course_id = $('input[name=course_id]').val();
            fees_by_module = $('input:checkbox[name=fees_by_module]:checked').val();

            var url = 'index.php?module=aceIms_subjectLink&_spAction=addSubjectAmountToTotal&showHTML=0';
            $.get(url,{fees_by_module: fees_by_module, course_id: course_id}, function(html){
                $('tr#courseAmount').html(html);
            });

            var url = 'index.php?module=aceIms_courseLink&_spAction=courseSummary&showHTML=0';
            $.get(url, {course_id: course_id, fees_by_module: fees_by_module}, function(html){
                cpm.aceIms.contact.calculateCourseSummaryTotalPvt();
            });
            cpm.aceIms.contact.populateSubsidyAmountAfterCourseChangeEdit();
            cpm.aceIms.contact.populateDiscountPvtEdit('portalPvtLinkEditForm');

            Util.hideProgressInd();
        });

    });
}

cpm.tuitionsg.contact.populateSubsidyAmountAfterCourseChange = function(e) {
    course_type = $('.m-aceIms_contact #portalForm #fld_course_type').val();
    fees_by_module = $('input:checkbox[name=fees_by_module]:checked').val();
    course_id = $('.m-aceIms_contact #portalForm #fld_course_id').val();
    course_contact_id = $('.m-aceIms_contact #portalForm #course_contact_id').val();
    subsidy_discount_id = $('.m-aceIms_contact #portalForm #fld_subsidy_discount_id').val();
    var url = 'index.php?module=aceIms_courseLink&_spAction=subsidyData&showHTML=0';

    Util.showProgressInd();
    $.get(url, {course_type: course_type, fees_by_module: fees_by_module, subsidy_discount_id:subsidy_discount_id, course_contact_id: course_contact_id,  course_id:course_id}, function(html){
        $('tr#subsidyAmount').html(html);
        cpm.aceIms.contact.calculateCourseSummaryTotalPvt();
        Util.hideProgressInd();
    });
},

cpm.tuitionsg.contact.populateSubsidyAmountAfterCourseChangeEdit: function(e) {
    course_id = $('input[name=course_id]').val();
    course_contact_id = $('input[name=course_contact_id]').val();
    fees_by_module = $('input:checkbox[name=fees_by_module]:checked').val();
    subsidy_discount_id = $('.m-aceIms_contact #portalPvtLinkEditForm #fld_subsidy_discount_id').val();
    var url = 'index.php?module=aceIms_courseLink&_spAction=subsidyData&showHTML=0';

    Util.showProgressInd();
    $.get(url, {fees_by_module: fees_by_module, subsidy_discount_id:subsidy_discount_id, course_contact_id: course_contact_id,  course_id:course_id}, function(html){
        $('tr#subsidyAmount').html(html);
        cpm.aceIms.contact.calculateCourseSummaryTotalPvt();
        Util.hideProgressInd();
    });
},

cpm.tuitionsg.contact.populateDiscountAmountAfterCourseChangeEdit = function(e) {
    discount  = $('.m-aceIms_contact #portalPvtLinkEditForm #fld_discount').val();
    course_id = $('input[name=course_id]').val();

    var url = 'index.php?module=aceIms_courseLink&_spAction=discountValueForPvt&showHTML=0';

    Util.showProgressInd();
    $.get(url, {course_id: course_id,discount: discount
    }, function(html){
        $('tr#discountAmount').html(html);
        cpm.aceIms.contact.calculateCourseSummaryTotalPvt();
        Util.hideProgressInd();
    });
},

cpm.tuitionsg.contact.newEnrollmentCourseTypeChange = function(e) {
    courseType = $(this).val();
    $('#fld_subsidy_discount_id').empty();
    $('#fld_discount').empty();
    $('tr#subsidyAmount').empty();
    $('tr#discountAmount').empty();

    if (courseType == 'Long Term') {
        $('.row_fees_by_module').removeClass('hideme');
    } else {
        $('.row_fees_by_module').addClass('hideme');
    }
    cpm.aceIms.contact.populateCourseForPvt(courseType, 'portalForm');
},

cpm.tuitionsg.contact.studentNewEnrollmentCourseChange = function(e) {
        $('#populate_subject_id').empty();
        $('#fld_discount.text').val('');
        $('#fld_subsidy_discount_id').empty();
        $('tr#courseAmount').empty();
        $('tr#subsidyAmount').empty();
        $('tr#discountAmount').empty();
        $('td.amount').empty();

        cpm.aceIms.contact.populateRegisterAmountPvt();

        fees_by_module = $('input:checkbox[name=fees_by_module]:checked').val();
        course_id = $('.m-aceIms_contact #portalForm #fld_course_id').val();
        course_type = $('.m-aceIms_contact #portalForm #fld_course_type').val();
        Util.showProgressInd('Populating related content.... Please wait');

        cpm.aceIms.contact.populateBatchPvt.call(this);
        cpm.aceIms.contact.populateSubjectPvt.call(this);
        cpm.aceIms.contact.populateSubsidy.call(this);
        cpm.aceIms.contact.populateDiscount.call(this);

        if (course_type == 'Short Term') {
            fees_by_module = 0;
        }

        cpm.aceIms.contact.populateCourseAmount(course_id, fees_by_module);

},

cpm.tuitionsg.contact.populateCourseType = function(courseType){
    Util.showProgressInd('Populating Course.... Please wait');
    $('#populate_subject_id').empty();
    $('#course_id').empty();
    $('#fld_installment.text').val('');
    $('#fld_discount.text').val('');
    $('tr#courseAmount').empty();
    $('tr#discountAmount').empty();
    $('tr#installmentAmount').empty();
    $('td.amount').empty();
    cpm.tuitionsg.contact.populateCourse.call(courseType);
    Util.hideProgressInd();
}

cpm.tuitionsg.contact.populateCourseForPvt = function(courseType, formId){
    Util.showProgressInd('Populating Course.... Please wait');
    $('#populate_subject_id').empty();
    $('#course_id').empty();
    $('#fld_installment.text').val('');
    $('#fld_discount.text').val('');
    $('tr#courseAmount').empty();
    $('tr#discountAmount').empty();
    $('tr#installmentAmount').empty();
    $('td.amount').empty();

    cpm.tuitionsg.contact.populateRegisterAmountPvt();

    if(formId == 'portalForm' && courseType == 'Long Term'){
        $('#fld_no_of_months.text').val('');
        $('#hideShortTermFlds').show();
        $('#hideLongTermFlds').hide();
    }
    else{
        $('#fld_no_of_months.text').val('');
        $('input:radio[name="medical_insurance"]').filter('[value="0"]').attr('checked', true);
        $('input:radio[name="full_time"]').filter('[value="0"]').attr('checked', true);
        $('#hideShortTermFlds').hide();
        $('#hideLongTermFlds').show();
    }

    courseObj  = $('.m-tuitionsg_contact #'+ formId +' #fld_course_id');

    courseId = courseObj.val();

    var url = $('#scopeRootAlias').val() + 'index.php?module=aceIms_courseLink&_spAction=courseValueForDropDown&showHTML=0';

    $.ajax({
        type: "POST",
        url: url,
        async: false,
        dataType: 'json',
        success: function(json){
            courseObj.empty();
            $.each(json, function() {
                courseObj.append(new Option(this.caption, this.value));
                courseObj.val(courseId);
            });
        },
        data: {courseType: courseType}
    });
    Util.hideProgressInd();
}

cpm.tuitionsg.contact.populateCourseAmount = function(course_id, fees_by_module){
    var url = 'index.php?module=aceIms_courseLink&_spAction=courseSummary&showHTML=0';
    $.get(url, {course_id: course_id, fees_by_module: fees_by_module}, function(html){
        $('tr#courseAmount').html(html);
        $('table#courseSummaryPvt').removeClass('hideme');
        cpm.aceIms.contact.calculateCourseSummaryTotalPvt();
        Util.hideProgressInd();
    });
}

/* Registration amount display when data is changed in EDIT form */
cpm.tuitionsg.contact.populateRegisterAmountPvtEdit: function(){
    registration_fee = $('input[name=reg_fee]').val();
    if (registration_fee) {
        html1 ='<td>Registration Amount</td><td style="text-align:right;" class="amount">'
        + registration_fee +
        '</td>';
        $('tr#registrationAmount').html(html1);
    } else {
        $('tr#registrationAmount').empty();
    }
}

cpm.tuitionsg.contact.populateBatch = function(){
    var parent = $(this).closest('tr');
    $(this).each(function(){
        courseId = $(this).val();
        batchObj = $('select[name=batch_id]', parent);
        batchObj = $('.m-tuitionsg_contact #portalForm #fld_batch_id');

        batchId = batchObj.val();

        var url = $('#scopeRootAlias').val() + 'index.php?module=aceIms_batchLink&_spAction=batchValueForDropDown&showHTML=0';

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

cpm.tuitionsg.contact.populateSubsidy = function(){
    var parent = $(this).closest('tr');
    $(this).each(function(){
        courseId = $(this).val();
        subsidyObj = $('.m-tuitionsg_contact #portalForm #fld_subsidy_discount_id');

        subsidyId = subsidyObj.val();

        var url = 'index.php?module=aceIms_courseSubsidyLink&_spAction=subsidyValueForDropDown&showHTML=0';

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

cpm.tuitionsg.contact.populateDiscount = function(){
    var parent = $(this).closest('tr');
    $(this).each(function(){
        courseId = $(this).val();
        discountObj = $('.m-tuitionsg_contact #portalForm #fld_discount');

        discountId = discountObj.val();

        var url = 'index.php?module=aceIms_courseSubsidyLink&_spAction=discountValueForDropDown&showHTML=0';

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

cpm.tuitionsg.contact.populateDiscountPvt = function(formId){
    discount = $('.m-aceIms_contact #' + formId +' #fld_discount').val();
    course_id = $('.m-aceIms_contact #' + formId +' #fld_course_id').val();
    add_registration_fee = $('input:radio[name=add_registration_fee]:checked').val();
    fees_by_module = $('.m-aceIms_contact #' + formId +' input:checkbox[name=fees_by_module]:checked').val();
    course_type = $('.m-aceIms_contact #' + formId +' #fld_course_type').val();

    var url = 'index.php?module=aceIms_courseLink&_spAction=discountValueForPvt&showHTML=0';

    Util.showProgressInd();
    $.get(url, {course_id: course_id, discount: discount, add_registration_fee: add_registration_fee,
                fees_by_module: fees_by_module, course_type: course_type
    }, function(html){
        $('tr#discountAmount').html(html);
        cpm.aceIms.contact.calculateCourseSummaryTotalPvt();
        Util.hideProgressInd();
    });
}

//TO display course for Edit Form
cpm.tuitionsg.contact.populateDiscountPvtEdit = function(formId){
    discount  = $('.m-aceIms_contact #' + formId +' #fld_discount').val();
    course_id = $('input[name=course_id]').val();
    add_registration_fee = $('input[name=reg_fee]').val();
    fees_by_module = $('.m-aceIms_contact #' + formId +' input:checkbox[name=fees_by_module]:checked').val();
    course_type = $('input[name=course_type]').val();

    var url = 'index.php?module=aceIms_courseLink&_spAction=discountValueForPvt&showHTML=0';

    Util.showProgressInd();
    $.get(url, {course_id: course_id, discount: discount, add_registration_fee: add_registration_fee,
                fees_by_module: fees_by_module, course_type: course_type
    }, function(html){
        $('tr#discountAmount').html(html);
        cpm.aceIms.contact.calculateCourseSummaryTotalPvt();
        Util.hideProgressInd();
    });
};

cpm.tuitionsg.contact.populateInstallementPvt = function(formId){
    instNumber = $('.m-tuitionsg_contact #' + formId +' #fld_installment').val();
    course_id  = $('.m-tuitionsg_contact #' + formId +' #fld_course_id').val();
    discount   = $('.m-tuitionsg_contact #' + formId +' #fld_discount').val();
    medical_ins= $('input:radio[name=medical_insurance]:checked').val();
    full_time  = $('input:radio[name=full_time]:checked').val();
    add_registration_fee = $('input:radio[name=add_registration_fee]:checked').val();
    no_of_months = $('.m-tuitionsg_contact #portalForm #fld_no_of_months').val();

    var url = 'index.php?module=aceIms_courseLink&_spAction=installmentAmountForPvt&showHTML=0';

    $.get(url, {course_id: course_id,discount: discount,instNumber: instNumber
    ,medical_ins: medical_ins, full_time: full_time
    ,add_registration_fe:add_registration_fee
    ,no_of_months:no_of_months
    }, function(html){
        $('tr#installmentAmount').html(html);
        cpm.tuitionsg.contact.calculateCourseSummaryTotalPvt();
    });
}

cpm.tuitionsg.contact.populateRegisterAmountPvt = function(){
    add_registration_fee = $('input:radio[name=add_registration_fee]:checked').val();
    registration_fee = $('input:hidden[name=registration_fee]').val();
    if (add_registration_fee == 1){
        $('table#courseSummaryPvt').removeClass('hideme');
        html1 ='<td>Registration Amount</td><td style="text-align:right;" class="amount">'
        + registration_fee +
        '</td>';
        $('tr#registrationAmount').html(html1);
        cpm.tuitionsg.contact.calculateCourseSummaryTotalPvt();
    }
    else{
        $('tr#registrationAmount').empty();
        cpm.tuitionsg.contact.calculateCourseSummaryTotalPvt();
    }
}

cpm.tuitionsg.contact.calculateCourseSummaryTotal = function(){
    var parent = $('table#courseSummary');

    var courseAmount   = parseInt($('tr#courseAmount td.amount').text());
    var subsidyAmount  = parseInt($('tr#subsidyAmount td.amount').text());
    var discountAmount = parseInt($('tr#discountAmount td.amount').text());

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

    //var total = total.toFixed(2)

    $('tr#totalAmount td.amount').html(total);
}

cpm.tuitionsg.contact.calculateCourseSummaryTotalPvt = function(){
    var parent = $('table#courseSummaryPvt');

    var courseAmount   = parseFloat($('tr#courseAmount td.amount').text());
    var subsidyAmount  = parseFloat($('tr#subsidyAmount td.amount').text());
    var discountAmount = parseFloat($('tr#discountAmount td.amount').text());
    var subjectAmount  = parseFloat($('tr#subjectAmount td.amount').text());
    var regAmount      = parseFloat($('tr#registrationAmount td.amount').text());
    var insAmount      = parseFloat($('tr#insuranceAmount td.amount').text());

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

    var total = Number(total).toFixed(2);

    $('tr#totalAmount td.amount').html(total);
}

$('.m-tuitionsg_contact #fld_company_id').livequery('change', function(){
    Util.showProgressInd();

    $('.m-tuitionsg_contact .row_c_reg_number').addClass('hideme');
    $('.m-tuitionsg_contact .row_c_address_flat').addClass('hideme');
    $('.m-tuitionsg_contact .row_c_address_street').addClass('hideme');
    $('.m-tuitionsg_contact .row_c_country_name').addClass('hideme');
    $('.m-tuitionsg_contact .row_c_address_po_code').addClass('hideme');
    $('.m-tuitionsg_contact .row_c_phone').addClass('hideme');
    $('.m-tuitionsg_contact .row_c_category').addClass('hideme');

    company_id = $(this).val();
    var url = 'index.php?module=tuitionsg_contact&_spAction=populateCompanyDetails&showHTML=0';

    $.get(url, {company_id: company_id}, function(html){
        $('.companyDetails').html(html);
    });

    Util.hideProgressInd();
});

$('a.newCompany').livequery('click', function(e) {
    e.preventDefault();
    var exp = {
        callbackOnSuccess: function(){
            var msg = 'Updated successfully';
            Util.alert(msg, function(){
                Util.closeAllDialogs();
                window.location.reload(true);
            });
        }
    }
    Util.openFormInDialog.call(this, 'companyAddForm', 'Add Company', 600, 550, exp);
});

$('#traineeLinkPvt').livequery('click', function (e){
    var title = "Student Link";
    e.preventDefault();
    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            var msg = 'Successfully completed enrollment. Please click Goto Finance to raise Invoice and Receipt';
            Util.alert(msg, function(){
                Util.closeAllDialogs();
                //Links.reloadPortalRecords('tuitionsg_contact#tuitionsg_courseLink', 'tuitionsg_contact');
                window.location.reload(true);
            });
        }
    }
    Util.openFormInDialog.call(this, 'portalForm', title, 800, 500, expObj);
});

cpm.tuitionsg.contact.populateBatchPvt = function(){
    var parent = $(this).closest('tr');
    $(this).each(function(){
        courseId = $(this).val();
        batchObj = $('select[name=batch_id]', parent);
        batchObj = $('.m-tuitionsg_contact #portalForm #fld_batch_id');

        batchId = batchObj.val();

        var url = $('#scopeRootAlias').val() + 'index.php?module=aceIms_batchLink&_spAction=batchValueForDropDownPvt&showHTML=0';

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

cpm.tuitionsg.contact.populateSubjectPvt = function(){
    var parent = $(this).closest('tr');
        courseId = $(this).val();
        var url = $('#scopeRootAlias').val() + 'index.php?module=aceIms_subjectLink&_spAction=subjectValueForCheckBox&showHTML=0';

        Util.showProgressInd();
        $.get(url, {srcFld: 'course_id', srcValue: courseId}, function(html){
            $('#populate_subject_id').html(html);
            Util.hideProgressInd();
        });
 }

//populate total according to the subject selected in NEW form
cpm.tuitionsg.contact.subjectClick = function(){
    Util.showProgressInd();
    subject_id = $(this).val();

    var course_id = $('#portalForm select[name=course_id]').val();
    fees_by_module = $('input:checkbox[name=fees_by_module]:checked').val();
    var checked    = $(this).attr('checked') ? 'checked' : '';
    var checkedVal = checked == 'checked' ? 1 : 0;
    var url = 'index.php?module=aceIms_subjectLink&_spAction=addSubjectAmountToTotal&showHTML=0';
    $.get(url,{subject_id: subject_id, fees_by_module: fees_by_module, checkedVal:checkedVal, course_id: course_id}, function(html){
        $('tr#courseAmount').html(html);
        cpm.aceIms.contact.populateSubsidyAmountAfterCourseChange();
        cpm.aceIms.contact.populateDiscountPvt('portalForm');
        cpm.aceIms.contact.populateRegisterAmountPvt();
        cpm.aceIms.contact.calculateCourseSummaryTotalPvt();
        Util.hideProgressInd();
    });
}

//populate total according to the subject selected in EDIT form
cpm.tuitionsg.contact.subjectClickInEdit = function(){
    Util.showProgressInd();
    subject_id = $(this).val();

    course_id = $('input[name=course_id]').val();
    fees_by_module = $('input:checkbox[name=fees_by_module]:checked').val();
    var checked    = $(this).attr('checked') ? 'checked' : '';
    var checkedVal = checked == 'checked' ? 1 : 0;
    var url = 'index.php?module=aceIms_subjectLink&_spAction=addSubjectAmountToTotal&showHTML=0';
    $.get(url,{subject_id: subject_id, fees_by_module: fees_by_module, checkedVal:checkedVal, course_id: course_id}, function(html){
        $('tr#courseAmount').html(html);
        cpm.aceIms.contact.populateSubsidyAmountAfterCourseChangeEdit();
        cpm.aceIms.contact.populateDiscountPvtEdit('portalPvtLinkEditForm');
        cpm.aceIms.contact.populateRegisterAmountPvtEdit();
        cpm.aceIms.contact.calculateCourseSummaryTotalPvt();
        Util.hideProgressInd();
    });
}


$('#portalForm input[name=full_time]').livequery('click', function (e){
    full_time = $(this).val();
    var url = 'index.php?module=aceIms_subjectLink&_spAction=addSubjectAmountToTotal&showHTML=0';
    $.get(url,{full_time: full_time}, function(html){
        $('tr#courseAmount').html(html);
        cpm.tuitionsg.contact.populateDiscountPvt('portalForm');
        cpm.tuitionsg.contact.populateInstallementPvt('portalForm');
        cpm.tuitionsg.contact.calculateCourseSummaryTotalPvt();
        Util.hideProgressInd();
    });
});


//For PVT : New Form To update the total when you change the full time radio button, applied for PART TIME
$('#portalPvtLinkEditForm input[name=full_time]').livequery('click', function (e){
    full_time = $(this).val();
    var url = 'index.php?module=aceIms_subjectLink&_spAction=addSubjectAmountToTotal&showHTML=0';
    $.get(url,{full_time: full_time}, function(html){
        $('tr#courseAmount').html(html);
        cpm.tuitionsg.contact.populateDiscountPvt('portalPvtLinkEditForm');
        cpm.tuitionsg.contact.populateInstallementPvt('portalPvtLinkEditForm');
        cpm.tuitionsg.contact.calculateCourseSummaryTotalPvt();
        Util.hideProgressInd();
    });
});

//For PVT : New Form To update the total when you change the full time radio button, applied for PART TIME
$('.m-tuitionsg_contact #portalForm #fld_no_of_months').livequery('change', function(){
    var no_of_months = $(this).val();
    var url = 'index.php?module=aceIms_subjectLink&_spAction=addSubjectAmountToTotal&showHTML=0';
    $.get(url,{no_of_months: no_of_months}, function(html){
        $('tr#courseAmount').html(html);
        cpm.tuitionsg.contact.populateDiscountPvt('portalForm');
        cpm.tuitionsg.contact.populateInstallementPvt('portalForm');
        cpm.tuitionsg.contact.calculateCourseSummaryTotalPvt();
        Util.hideProgressInd();
    });
});

//For PVT : Edit Form To update the total when you change the no of months, applied for PART TIME
$('.m-tuitionsg_contact #portalPvtLinkEditForm #fld_no_of_months').livequery('change', function(){
    var no_of_months = $(this).val();
    var url = 'index.php?module=aceIms_subjectLink&_spAction=addSubjectAmountToTotal&showHTML=0';
    $.get(url,{no_of_months: no_of_months}, function(html){
        $('tr#courseAmount').html(html);
        cpm.tuitionsg.contact.populateDiscountPvt('portalPvtLinkEditForm');
        cpm.tuitionsg.contact.populateInstallementPvt('portalPvtLinkEditForm');
        cpm.tuitionsg.contact.calculateCourseSummaryTotalPvt();
        Util.hideProgressInd();
    });
});


//For PVT New Form: when update total button is clicked respective calculations are updated in the bottom
$('#portalForm #updateTotal').livequery('click', function(e) {
    cpm.tuitionsg.contact.populateDiscountPvt('portalForm');
    cpm.tuitionsg.contact.populateInstallementPvt('portalForm');
});

//For PVT Edit Form: when update total button is clicked respective calculations are updated in the bottom
$('#portalPvtLinkEditForm #updateTotal').livequery('click', function(e) {
    cpm.tuitionsg.contact.populateDiscountPvt('portalPvtLinkEditForm');
    cpm.tuitionsg.contact.populateInstallementPvt('portalPvtLinkEditForm');
});

//For PVT : when reg fee is checked respective amount is displayed in the bottom
$('input[name=add_registration_fee]').livequery('click', function (e){
    cpm.tuitionsg.contact.populateRegisterAmountPvt();
});

//For PVT : when medical insurance is checked respective amount is displayed in the bottom
$('input[name=medical_insurance]').livequery('click', function (e){
    medical_insurance = $(this).val();
    if (medical_insurance == 1){
        $('table#courseSummaryPvt').removeClass('hideme');
        html1 ='<td>Medical Insurance Amount</td><td style="text-align:right;" class="amount">'
        + 75 +
        '</td>';
        $('tr#insuranceAmount').html(html1);
        cpm.tuitionsg.contact.populateInstallementPvt('portalPvtLinkEditForm');
    }
    else{
        $('tr#insuranceAmount').empty();
        cpm.tuitionsg.contact.populateInstallementPvt('portalPvtLinkEditForm');
    }
});

$('.editPortalPvtRecord').livequery('click', function (e){
    var title = "Edit Student Link";
    e.preventDefault();
    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            var msg = 'Updated successfully';
            Util.alert(msg, function(){
                Util.closeAllDialogs();
                //Links.reloadPortalRecords('aceIms_contact#aceIms_orderLink', 'aceIms_contact');
                window.location.reload(true);
            });
        }
    }
    Util.openFormInDialog.call(this, 'portalPvtLinkEditForm', title, 800, 500, expObj);
});

$('.deletePortalRecordContact').livequery('click', function (e){
    msg = "Are you sure you want to delete this record? You cannot undo this action!";
    if (!confirm(msg)){
        return false;
    } else {
        //var url = 'index.php?_topRm=finance&module=aceIms_order&_spAction=deleteInvoiceFormPvt&showHTML=0';
        var url = 'index.php?_spAction=deletePortalRecordByID&srcRoom=aceIms_company&lnkRoom=aceIms_orderLink&showHTML=0';
        Util.showProgressInd();
        var order_id = $(this).attr('order_id');
        $.get(url,{id: order_id}, function(html){
            alert ('Enrollment Cancelled Succesfully');
            Util.hideProgressInd();
            window.location.reload(true);
        });
    }
});

