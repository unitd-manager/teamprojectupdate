Util.createCPObject('cpm.edukloud.contact');
//portalPvtLinkForm forms are meant for private institute.
cpm.edukloud.contact.init = function(){
    $(window).load(function(){
        /*var citizen = $('.m-edukloud_contact .row_is_citizen input[name=is_citizen]').val();
        
        if (citizen == 1){
            $('.m-edukloud_contact .citizenNo').hide();
        } else {
            $('.m-edukloud_contact .citizenNo').show();
        }*/

        var maritalStatus = $('.m-edukloud_contact .row_marital_status option[selected=selected]').val();
        if (maritalStatus == ''){
            $('.m-edukloud_contact .row_marital_status option[selected=selected]').html('NA');    
        }
        
        var race = $('.m-edukloud_contact .row_race option[selected=selected]').val();
        if (race == ''){
            $('.m-edukloud_contact .row_race option[selected=selected]').val('NA');    
        }
        
        var parentNric = $('.m-edukloud_contact .row_parent_id_card_no input[id=fld_parent_id_card_no]').val();
        if (parentNric == ''){
            $('.m-edukloud_contact .row_parent_id_card_no input[id=fld_parent_id_card_no]').val('Nil');    
        }
        
        var phone = $('.m-edukloud_contact .row_phone input[id=fld_phone]').val();
        if (phone == ''){
            $('.m-edukloud_contact .row_phone input[id=fld_phone]').val('Nil');    
        }
        
        var parentPhone = $('.m-edukloud_contact .row_emergency_contact_office_no input[id=fld_emergency_contact_office_no]').val();
        if (parentPhone == ''){
            $('.m-edukloud_contact .row_emergency_contact_office_no input[id=fld_emergency_contact_office_no]').val('Nil');    
        }
        
        $(".m-edukloud_contact .row_is_citizen input[name='is_citizen']").livequery('change', function(e){
            var studentPass = $(".m-edukloud_contact .row_student_pass_holder input[name='student_pass_holder']:checked").val();
            var citizen = $(this).val();
            if (citizen == 1 || studentPass == 1){
                $('.m-edukloud_contact .citizenNo').hide();
            } else {
                $('.m-edukloud_contact .citizenNo').removeClass('hideme');
                $('.m-edukloud_contact .citizenNo').show();
            }
        });

        /* Populating value for age in Parent # Student Linked New Record */
        $('.m-edukloud_contact #frmEdit #fld_date_of_birth').livequery('change', function (e){
            var date_of_birth = $(this).val();
        
            var url = 'index.php?module=edukloud_orderLink&_spAction=calculateStudentAge&showHTML=0';
            Util.showProgressInd();
            $.get(url, {date_of_birth: date_of_birth}, function(json){
                var intData = parseInt(json.age);
                $('.m-edukloud_contact #frmEdit #fld_age').val(intData);
                Util.hideProgressInd();
            },'json');
        });

        $(".m-edukloud_contact .row_student_pass_holder input[name='student_pass_holder']").livequery('change', function(e){
            var citizen = $(".m-edukloud_contact .row_is_citizen input[name='is_citizen']:checked").val();
            var studentPass = $(this).val();
            if (citizen == 1 || studentPass == 1){
                $('.m-edukloud_contact .citizenNo').hide();
            } else {
                $('.m-edukloud_contact .citizenNo').removeClass('hideme');
                $('.m-edukloud_contact .citizenNo').show();
            }
        });

        $('.edukloud_contact__edukloud_courseLink select[name=course_id]').livequery(function(){
           cpm.edukloud.contact.populateBatch.call(this);
        });
    
        //For Non Pvt getting related batch , subsidy and discount
        $('.m-edukloud_contact #portalForm #fld_course_id').livequery('change', function(){

            Util.showProgressInd('Populating related content.... Please wait');

            cpm.edukloud.contact.populateBatch.call(this);
            cpm.edukloud.contact.populateSubsidy.call(this);
            cpm.edukloud.contact.populateDiscount.call(this);

            course_id = $('.m-edukloud_contact #portalForm #fld_course_id').val();
            var url = 'index.php?module=edukloud_courseLink&_spAction=courseSummary&showHTML=0';
            $.get(url, {course_id: course_id}, function(html){
                $('tr#courseAmount').html(html);
                $('table#courseSummary').removeClass('hideme');
                cpm.edukloud.contact.calculateCourseSummaryTotal();
                Util.hideProgressInd();
            });
        }),
        
        //TO display course termination date for Edit Form PVT
        $(".m-edukloud_contact #portalPvtLinkEditForm select[name='course_status']").livequery(function(){
            var courseStatus = $(this).val();
            if (courseStatus == 'Expelled' || courseStatus == 'Terminated' || courseStatus == 'Withdrawal') {
                $('#portalPvtLinkEditForm .row_course_termination_date').removeClass('hideme');
                $('#portalPvtLinkEditForm .row_remarks').removeClass('hideme');
            }
        });

        $(".m-edukloud_contact #portalPvtLinkEditForm select[name='course_status']").livequery('change', function(e){
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
        $('.m-edukloud_contact #portalPvtLinkForm #fld_course_type').livequery('change', function(){
            courseType = $(this).val();
            cpm.edukloud.contact.populateCourseForPvt(courseType, 'portalPvtLinkForm');
        }),
        
        //TO display course for Edit Form PVT
        $('.m-edukloud_contact #portalPvtLinkEditForm #fld_course_type').livequery('change', function(){
            courseType = $(this).val();
            cpm.edukloud.contact.populateCourseForPvt(courseType, 'portalPvtLinkEditForm');
        }),
        
        // for pvt institute contact - course link , NEW form, to populate batch subject related to course
        $('.m-edukloud_contact #portalPvtLinkForm #fld_course_id').livequery('change', function(){
            $('#populate_subject_id').empty();
            $('#fld_installment.text').val('');
            $('#fld_discount.text').val('');
            $('tr#courseAmount').empty();
            $('tr#discountAmount').empty();
            $('tr#installmentAmount').empty();
            $('td.amount').empty();

            course_id = $('.m-edukloud_contact #portalPvtLinkForm #fld_course_id').val();
            Util.showProgressInd('Populating related content.... Please wait');

            cpm.edukloud.contact.populateBatchPvt.call(this);
            cpm.edukloud.contact.populateSubjectPvt.call(this);
            cpm.edukloud.contact.populateDiscountPvt('portalPvtLinkForm');
            cpm.edukloud.contact.populateInstallementPvt('portalPvtLinkForm');            
            cpm.edukloud.contact.populateCourseAmount(course_id);
        }),

/*        $('.m-edukloud_contact .button #actBtn_status').livequery('click', function(e){
            var contact_id = $(this).attr('contact_id');
            var title ="Change Status";
    
            e.preventDefault();
            var expObj = {
                validate: true
               ,callbackOnSuccess: function(json){

                    var urlRedirect = "index.php?_topRm=main&module=edukloud_contact&_action=edit&record_id=" + contact_id;
                    
                    var msg = 'Status Changed Succesfully..';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        document.location = urlRedirect;
                    });
                }
            }
            Util.openFormInDialog.call(this, 'changeStatusForm', title, 500, 300, expObj);
        }), */
        
        $('.m-edukloud_contact .button #actBtn_status').livequery('click', function(e){
            var contact_id = $(this).attr('contact_id');
            var title ="Change Status";
    
            e.preventDefault();
            var expObj = {
                validate: true
               ,callbackOnSuccess: function(json){

                    var urlRedirect = "index.php?_topRm=main&module=edukloud_contact&_action=edit&record_id=" + contact_id;
                    
                    var msg = 'Status Changed Succesfully..';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        document.location = urlRedirect;
                    });
                }
            }
            Util.openFormInDialog.call(this, 'changeStatusForm', title, 500, 300, expObj);
        }),
        

        $('.m-edukloud_contact .button #actBtn_statusToActive').livequery('click', function(e){
            var contact_id = $(this).attr('contact_id');

            var urlRedirect = "index.php?_topRm=main&module=edukloud_contact&_action=edit&record_id=" + contact_id;
            var url = 'index.php?module=edukloud_contact&_spAction=changeStatusToActive&showHTML=0';
            $.get(url, {contact_id: contact_id}, function(html){
                document.location = urlRedirect;
            });
        }),

        // for pvt institute contact - course link , EDIT form, to populate batch subject related to course
        $('.m-edukloud_contact #portalPvtLinkEditForm #fld_course_id').livequery('change', function(){
            $('#populate_subject_id').empty();
            $('#fld_installment.text').val('');
            $('#fld_discount.text').val('');
            $('tr#courseAmount').empty();
            $('tr#discountAmount').empty();
            $('tr#installmentAmount').empty();
            $('td.amount').empty();

            course_id = $('.m-edukloud_contact #portalPvtLinkEditForm #fld_course_id').val();
            Util.showProgressInd('Populating related content.... Please wait');

            cpm.edukloud.contact.populateBatchPvt.call(this);
            cpm.edukloud.contact.populateSubjectPvt.call(this);
            cpm.edukloud.contact.populateDiscountPvt('portalPvtLinkEditForm');
            cpm.edukloud.contact.populateInstallementPvt('portalPvtLinkEditForm');            
            cpm.edukloud.contact.populateCourseAmount(course_id);
        }),

        $('.m-edukloud_contact #portalForm #fld_course_subsidy_history_id').livequery('change', function(){
            course_id = $('.m-edukloud_contact #portalForm #fld_course_id').val();
            course_contact_id = $('.m-edukloud_contact #portalForm #course_contact_id').val();
            course_subsidy_history_id = $('.m-edukloud_contact #portalForm #fld_course_subsidy_history_id').val();
            var url = 'index.php?module=edukloud_courseLink&_spAction=subsidyData&showHTML=0';
            
            Util.showProgressInd();
            $.get(url, {course_subsidy_history_id:course_subsidy_history_id, course_contact_id: course_contact_id,  course_id:course_id}, function(html){
                $('tr#subsidyAmount').html(html);
                cpm.edukloud.contact.calculateCourseSummaryTotal();
                Util.hideProgressInd();
            });
        }),

        $('.m-edukloud_contact #portalForm #fld_discount').livequery('change', function(){
            course_id = $('.m-edukloud_contact #portalForm #fld_course_id').val();
            course_contact_id = $('.m-edukloud_contact #portalForm #course_contact_id').val();
            course_subsidy_history_id = $('.m-edukloud_contact #portalForm #fld_discount').val();
            var url = 'index.php?module=edukloud_courseLink&_spAction=subsidyData&showHTML=0';
            
            Util.showProgressInd();
            $.get(url, {course_subsidy_history_id:course_subsidy_history_id, course_contact_id: course_contact_id,  course_id:course_id}, function(html){
                $('tr#discountAmount').html(html);
                cpm.edukloud.contact.calculateCourseSummaryTotal();
                Util.hideProgressInd();
            });
        }),
        
        //Discount for pvt institute
        $('.m-edukloud_contact #portalPvtLinkForm #fld_discount').livequery('change', function(){
            cpm.edukloud.contact.populateDiscountPvt('portalPvtLinkForm');
        }),

        $(this).livequery('change', function(){
            course_val = $('.m-edukloud_contact #portalForm .courseSummary .amount').val();
        });
    });

}

cpm.edukloud.contact.populateCourseType = function(courseType){
    Util.showProgressInd('Populating Course.... Please wait');
    $('#populate_subject_id').empty();
    $('#course_id').empty();
    $('#fld_installment.text').val('');
    $('#fld_discount.text').val('');
    $('tr#courseAmount').empty();
    $('tr#discountAmount').empty();
    $('tr#installmentAmount').empty();
    $('td.amount').empty();
    cpm.edukloud.contact.populateCourse.call(courseType);
    Util.hideProgressInd();
}

cpm.edukloud.contact.populateCourseForPvt = function(courseType, formId){
    Util.showProgressInd('Populating Course.... Please wait');
    $('#populate_subject_id').empty();
    $('#course_id').empty();
    $('#fld_installment.text').val('');
    $('#fld_discount.text').val('');
    $('tr#courseAmount').empty();
    $('tr#discountAmount').empty();
    $('tr#installmentAmount').empty();
    $('td.amount').empty();
    
    if(formId == 'portalPvtLinkForm' && courseType == 'Long Term'){
        $('#fld_no_of_months.text').val('9');
        $('#hideShortTermFlds').show();
    }
    else{ 
        $('#fld_no_of_months.text').val('');
        $('input:radio[name="medical_insurance"]').filter('[value="0"]').attr('checked', true);
        $('input:radio[name="full_time"]').filter('[value="0"]').attr('checked', true);
        $('#hideShortTermFlds').hide();
    }
    
    courseObj  = $('.m-edukloud_contact #'+ formId +' #fld_course_id');
    
    courseId = courseObj.val();

    var url = $('#scopeRootAlias').val() + 'index.php?module=edukloud_courseLink&_spAction=courseValueForDropDown&showHTML=0';
    
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

cpm.edukloud.contact.populateCourseAmount = function(course_id){
    var url = 'index.php?module=edukloud_courseLink&_spAction=courseSummary&showHTML=0';
    $.get(url, {course_id: course_id}, function(html){
        $('tr#courseAmount').html(html);
        $('table#courseSummaryPvt').removeClass('hideme');
        cpm.edukloud.contact.calculateCourseSummaryTotalPvt();
        Util.hideProgressInd();
    });
}

cpm.edukloud.contact.populateBatch = function(){
    var parent = $(this).closest('tr');
    $(this).each(function(){
        courseId = $(this).val();
        batchObj = $('select[name=batch_id]', parent);
        batchObj = $('.m-edukloud_contact #portalForm #fld_batch_id');
		
        batchId = batchObj.val();

        var url = $('#scopeRootAlias').val() + 'index.php?module=edukloud_batchLink&_spAction=batchValueForDropDown&showHTML=0';
        
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

cpm.edukloud.contact.populateSubsidy = function(){
    var parent = $(this).closest('tr');
    $(this).each(function(){
    //alert (123);
        courseId = $(this).val();
        //subsidyObj = $('select[name=course_subsidy_history_id]', parent);
        subsidyObj = $('.m-edukloud_contact #portalForm #fld_course_subsidy_history_id');
		
        subsidyId = subsidyObj.val();

        var url = 'index.php?module=edukloud_courseSubsidyLink&_spAction=subsidyValueForDropDown&showHTML=0';
        
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

cpm.edukloud.contact.populateDiscount = function(){
    var parent = $(this).closest('tr');
    $(this).each(function(){
        courseId = $(this).val();
        discountObj = $('.m-edukloud_contact #portalForm #fld_discount');
		
        discountId = discountObj.val();

        var url = 'index.php?module=edukloud_courseSubsidyLink&_spAction=discountValueForDropDown&showHTML=0';
        
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

cpm.edukloud.contact.populateDiscountPvt = function(formId){
    discount = $('.m-edukloud_contact #' + formId +' #fld_discount').val();
    course_id = $('.m-edukloud_contact #' + formId +' #fld_course_id').val();
    medical_ins = $('input:radio[name=medical_insurance]:checked').val();
    add_registration_fee = $('input:radio[name=add_registration_fee]:checked').val();
    full_time  = $('input:radio[name=full_time]:checked').val();

    var url = 'index.php?module=edukloud_courseLink&_spAction=discountValueForPvt&showHTML=0';
    
    Util.showProgressInd();
    $.get(url, {course_id: course_id,discount: discount,medical_ins: medical_ins
    ,add_registration_fee:add_registration_fee
    ,full_time:full_time
    }, function(html){
        $('tr#discountAmount').html(html);
        cpm.edukloud.contact.calculateCourseSummaryTotalPvt();
        Util.hideProgressInd();
    });
}

cpm.edukloud.contact.populateInstallementPvt = function(formId){
    instNumber = $('.m-edukloud_contact #' + formId +' #fld_installment').val();
    course_id  = $('.m-edukloud_contact #' + formId +' #fld_course_id').val();
    discount   = $('.m-edukloud_contact #' + formId +' #fld_discount').val();
    medical_ins= $('input:radio[name=medical_insurance]:checked').val();
    full_time  = $('input:radio[name=full_time]:checked').val();
    add_registration_fee = $('input:radio[name=add_registration_fee]:checked').val();
    no_of_months = $('.m-edukloud_contact #portalPvtLinkForm #fld_no_of_months').val();

    var url = 'index.php?module=edukloud_courseLink&_spAction=installmentAmountForPvt&showHTML=0';
    
    $.get(url, {course_id: course_id,discount: discount,instNumber: instNumber
    ,medical_ins: medical_ins, full_time: full_time
    ,add_registration_fe:add_registration_fee
    ,no_of_months:no_of_months
    }, function(html){
        $('tr#installmentAmount').html(html);
        cpm.edukloud.contact.calculateCourseSummaryTotalPvt();
    });
}

cpm.edukloud.contact.populateRegisterAmountPvt = function(){
    add_registration_fee = $('input:radio[name=add_registration_fee]:checked').val();
    if (add_registration_fee == 1){
        $('table#courseSummaryPvt').removeClass('hideme');
        html1 ='<td>Registration Amount</td><td style="text-align:right;" class="amount">'
        + 50 +
        '</td>';
        $('tr#registrationAmount').html(html1);
        cpm.edukloud.contact.calculateCourseSummaryTotalPvt();
    }
    else{
        $('tr#registrationAmount').empty();
        cpm.edukloud.contact.calculateCourseSummaryTotalPvt();
    }
}


cpm.edukloud.contact.calculateCourseSummaryTotal = function(){
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

    $('tr#totalAmount td.amount').html(total);
}

cpm.edukloud.contact.calculateCourseSummaryTotalPvt = function(){
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

$('.m-edukloud_contact #fld_company_id').livequery('change', function(){
    Util.showProgressInd();
    
    $('.m-edukloud_contact .row_c_reg_number').addClass('hideme');
    $('.m-edukloud_contact .row_c_address_flat').addClass('hideme');
    $('.m-edukloud_contact .row_c_address_street').addClass('hideme');
    $('.m-edukloud_contact .row_c_country_name').addClass('hideme');
    $('.m-edukloud_contact .row_c_address_po_code').addClass('hideme');
    $('.m-edukloud_contact .row_c_phone').addClass('hideme');
    $('.m-edukloud_contact .row_c_category').addClass('hideme');
    
    company_id = $(this).val();
    var url = 'index.php?module=edukloud_contact&_spAction=populateCompanyDetails&showHTML=0';
    
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
    var title = "Trainee Link";
    e.preventDefault();
    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            var msg = 'Successfully completed enrollment. Please click Goto Finance to raise Invoice and Receipt';
            Util.alert(msg, function(){
                Util.closeAllDialogs();
                //Links.reloadPortalRecords('edukloud_contact#edukloud_courseLink', 'edukloud_contact');
                window.location.reload(true);
            });
        }
    }
    Util.openFormInDialog.call(this, 'portalPvtLinkForm', title, 500, 475, expObj);        
});

cpm.edukloud.contact.populateBatchPvt = function(){
    var parent = $(this).closest('tr');
    $(this).each(function(){
        courseId = $(this).val();
        batchObj = $('select[name=batch_id]', parent);
        batchObj = $('.m-edukloud_contact #portalPvtLinkForm #fld_batch_id');
		
        batchId = batchObj.val();

        var url = $('#scopeRootAlias').val() + 'index.php?module=edukloud_batchLink&_spAction=batchValueForDropDownPvt&showHTML=0';
        
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

cpm.edukloud.contact.populateSubjectPvt = function(){
    var parent = $(this).closest('tr');
        courseId = $(this).val();
        var url = $('#scopeRootAlias').val() + 'index.php?module=edukloud_subjectLink&_spAction=subjectValueForCheckBox&showHTML=0';
        
        Util.showProgressInd();
        $.get(url, {srcFld: 'course_id', srcValue: courseId}, function(html){
            $('#populate_subject_id').html(html);
            Util.hideProgressInd();
        });
 }

//populate total according to the subject selected in NEW form 
$('input.subject_id').livequery('click', function (e){
    Util.showProgressInd();
    subject_id = $(this).val();
    full_time = $('input:radio[name=full_time]:checked').val();
    var checked    = $(this).attr('checked') ? 'checked' : '';
    var checkedVal = checked == 'checked' ? 1 : 0;
    var no_of_months = $('.m-edukloud_contact #portalPvtLinkForm #fld_no_of_months').val();
    var url = 'index.php?module=edukloud_subjectLink&_spAction=addSubjectAmountToTotal&showHTML=0';
    $.get(url,{subject_id: subject_id,checkedVal:checkedVal, full_time: full_time, no_of_months: no_of_months}, function(html){
        $('tr#courseAmount').html(html); 
        cpm.edukloud.contact.populateDiscountPvt('portalPvtLinkForm');
        cpm.edukloud.contact.populateInstallementPvt('portalPvtLinkForm');
        cpm.edukloud.contact.populateRegisterAmountPvt();
        cpm.edukloud.contact.calculateCourseSummaryTotalPvt();
        Util.hideProgressInd();
    });
});

//populate total according to the subject selected in EDIT form
$('input[name=subject_id]').livequery('click', function (e){
    Util.showProgressInd();
    full_time = $('input:radio[name=full_time]:checked').val();
    subject_id = $(this).val();
    var checked    = $(this).attr('checked') ? 'checked' : '';
    var checkedVal = checked == 'checked' ? 1 : 0;
    var no_of_months = $('.m-edukloud_contact #portalPvtLinkEditForm #no_of_months').val();

    var url = 'index.php?module=edukloud_subjectLink&_spAction=addSubjectAmountToTotal&showHTML=0';
    $.get(url,{subject_id: subject_id,checkedVal:checkedVal, full_time: full_time, no_of_months: no_of_months}, function(html){
        $('tr#courseAmount').html(html);
        cpm.edukloud.contact.populateDiscountPvt('portalPvtLinkEditForm');
        cpm.edukloud.contact.populateInstallementPvt('portalPvtLinkEditForm');
        cpm.edukloud.contact.calculateCourseSummaryTotalPvt();
        Util.hideProgressInd();
    });
});


$('#portalPvtLinkForm input[name=full_time]').livequery('click', function (e){
    full_time = $(this).val();
    var url = 'index.php?module=edukloud_subjectLink&_spAction=addSubjectAmountToTotal&showHTML=0';
    $.get(url,{full_time: full_time}, function(html){
        $('tr#courseAmount').html(html);
        cpm.edukloud.contact.populateDiscountPvt('portalPvtLinkForm');
        cpm.edukloud.contact.populateInstallementPvt('portalPvtLinkForm');
        cpm.edukloud.contact.calculateCourseSummaryTotalPvt();
        Util.hideProgressInd();
    });
});


//For PVT : New Form To update the total when you change the full time radio button, applied for PART TIME
$('#portalPvtLinkEditForm input[name=full_time]').livequery('click', function (e){
    full_time = $(this).val();
    var url = 'index.php?module=edukloud_subjectLink&_spAction=addSubjectAmountToTotal&showHTML=0';
    $.get(url,{full_time: full_time}, function(html){
        $('tr#courseAmount').html(html);
        cpm.edukloud.contact.populateDiscountPvt('portalPvtLinkEditForm');
        cpm.edukloud.contact.populateInstallementPvt('portalPvtLinkEditForm');
        cpm.edukloud.contact.calculateCourseSummaryTotalPvt();
        Util.hideProgressInd();
    });
});

//For PVT : New Form To update the total when you change the full time radio button, applied for PART TIME
$('.m-edukloud_contact #portalPvtLinkForm #fld_no_of_months').livequery('change', function(){
    var no_of_months = $(this).val();
    var url = 'index.php?module=edukloud_subjectLink&_spAction=addSubjectAmountToTotal&showHTML=0';
    $.get(url,{no_of_months: no_of_months}, function(html){
        $('tr#courseAmount').html(html);
        cpm.edukloud.contact.populateDiscountPvt('portalPvtLinkForm');
        cpm.edukloud.contact.populateInstallementPvt('portalPvtLinkForm');
        cpm.edukloud.contact.calculateCourseSummaryTotalPvt();
        Util.hideProgressInd();
    });
});

//For PVT : Edit Form To update the total when you change the no of months, applied for PART TIME
$('.m-edukloud_contact #portalPvtLinkEditForm #fld_no_of_months').livequery('change', function(){
    var no_of_months = $(this).val();
    var url = 'index.php?module=edukloud_subjectLink&_spAction=addSubjectAmountToTotal&showHTML=0';
    $.get(url,{no_of_months: no_of_months}, function(html){
        $('tr#courseAmount').html(html);
        cpm.edukloud.contact.populateDiscountPvt('portalPvtLinkEditForm');
        cpm.edukloud.contact.populateInstallementPvt('portalPvtLinkEditForm');
        cpm.edukloud.contact.calculateCourseSummaryTotalPvt();
        Util.hideProgressInd();
    });
});


//For PVT New Form: when update total button is clicked respective calculations are updated in the bottom
$('#portalPvtLinkForm #updateTotal').livequery('click', function(e) {
    cpm.edukloud.contact.populateDiscountPvt('portalPvtLinkForm');
    cpm.edukloud.contact.populateInstallementPvt('portalPvtLinkForm');
});

//For PVT Edit Form: when update total button is clicked respective calculations are updated in the bottom
$('#portalPvtLinkEditForm #updateTotal').livequery('click', function(e) {
    cpm.edukloud.contact.populateDiscountPvt('portalPvtLinkEditForm');
    cpm.edukloud.contact.populateInstallementPvt('portalPvtLinkEditForm');
});

//For PVT : when reg fee is checked respective amount is displayed in the bottom
$('input[name=add_registration_fee]').livequery('click', function (e){
    cpm.edukloud.contact.populateRegisterAmountPvt();
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
        cpm.edukloud.contact.populateInstallementPvt('portalPvtLinkEditForm');
    }
    else{
        $('tr#insuranceAmount').empty();
        cpm.edukloud.contact.populateInstallementPvt('portalPvtLinkEditForm');
    }
});

$('.editPortalPvtRecord').livequery('click', function (e){
    var title = "Edit Trainee Link";
    e.preventDefault();
    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            var msg = 'Updated successfully';
            Util.alert(msg, function(){
                Util.closeAllDialogs();
                //Links.reloadPortalRecords('edukloud_contact#edukloud_orderLink', 'edukloud_contact');
                window.location.reload(true);
            });
        }
    }
    Util.openFormInDialog.call(this, 'portalPvtLinkEditForm', title, 500, 475, expObj);        
});