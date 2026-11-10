Util.createCPObject('cpm.aceIms.company');
$('a#addCourseRows').livequery('click', function(){
    Util.showProgressInd();
    var url = $('#scopeRootAlias').val() + 'index.php?module=aceIms_orderLink&_spAction=courseRow&showHTML=0';
    var company_id = $(this).attr('company_id');
    $.get(url, {company_id: company_id}, function(html){
        $('#courseLinkList').append(html);
        Util.hideProgressInd();
    });
});

$('form#traineeSearchForm').livequery(function() {
    Util.setUpAjaxFormGeneral('traineeSearchForm', function(json){
        $('#traineeSearchResult').html(json.returnText);
        Util.hideProgressInd();
    });
});

$('a.viewContactDetails').livequery('click', function(e) {
    Util.openFormInDialog.call(this, '', 'View Contact', 500, 450);
});

$('a.newContactDetails').livequery('click', function(e) {
    e.preventDefault();
    var exp = {
        callbackOnSuccess: function(){
            $('#dialog1').dialog('close');
            $('#dialog1').dialog('destroy');
            $('#dialog1').remove();
            var url = 'index.php?module=aceIms_orderLink&_spAction=selectedTraineeResultRow&showHTML=0';
            Util.showProgressInd();
            $.get(url, function(html){
                //$('#traineeSelectedResult').html(html);
                $('#traineeSelectedResult tr:last').after(html);

                /* Showing of Course Summary */
                //cpm.aceIms.company.populateCourseSummary.call(this);

                Util.hideProgressInd();
            });
        }
    }
    Util.openFormInDialog.call(this, 'contactAddForm', 'Add Contact', 500, 450, exp);
});

$('a.subjectsForCourse').livequery('click', function(e) {
    e.preventDefault();
    var trObj     = $(this).closest('tr');
    var contactId = $(trObj).attr('contact_id_row');
    var courseId  = $(this).closest('tr').find('.fld_course_id_row').val();

    var url = 'index.php?module=aceIms_subject&_spAction=SubjectList&course_id=' + courseId + '&contact_id=' + contactId +'&showHTML=0';
    var exp = {
        url: url
    };

    Util.openDialogForLink('Subjects to be chosen',  700, 300, 0, exp);
});

$('a.editSubjectsForCourse').livequery('click', function(e) {
    e.preventDefault();
    var trObj     = $(this).closest('tr');
    var contactId = $(trObj).attr('contact_id');
    var orderId   = $(trObj).attr('order_id');

    var url = 'index.php?module=aceIms_subject&_spAction=EditSubjectList&order_id=' + orderId + '&contact_id=' + contactId +'&showHTML=0';
    var exp = {
        url: url
    };

    Util.openDialogForLink('Subjects to be chosen',  700, 300, 0, exp);
});

$('a.editContactDetails').livequery('click', function(e) {
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

$('table.traineeSearchRow .addTrainee').livequery('click', function(e){
    e.preventDefault();
    var parent = $(this).closest('tr');
    $(parent).remove();
    var contact_id  = $(this).attr('contact_id');
    var order_id    = $(this).attr('order_id');

    var url = 'index.php?module=aceIms_orderLink&_spAction=selectedStudentListRow&showHTML=0';

    Util.showProgressInd('Adding selected trainee...');
    $.get(url, {contact_id: contact_id,order_id: order_id}, function(html){
        //$('#traineeSelectedResult').append(html);
        $('#traineeSelectedResult tr:last').after(html);

        /* Showing of Course Summary */
        //cpm.aceIms.company.populateCourseSummary.call(this);

        Util.hideProgressInd();
    });
});

$('table.traineesSelectedLinked .removeTrainee').livequery('click', function(e){
    e.preventDefault();
    var parent = $(this).closest('tr');
    $(parent).remove();
    var company_id = $(this).attr('company_id');
    var contact_id = $(this).attr('contact_id');
    var order_id = $(this).attr('order_id');

    Util.showProgressInd('Removing selected trainee...');
    var url1 = 'index.php?_topRm=finance&module=aceIms_orderLink&_spAction=checkInvoiceForContactInCompanyEditEnrollment&showHTML=0';
    $.get(url1,{contact_id: contact_id, order_id: order_id}, function(html){
        if (html == 1) {
        } else {
            alert ('Please note that changing discount will cancel the existing invoice');
        }
    });

    var url = 'index.php?module=aceIms_orderLink&_spAction=removeTrainee&showHTML=0';

    $.get(url, {contact_id: contact_id, order_id: order_id, company_id: company_id}, function(html){
        $('#traineeSearchResult').html(html);
        Util.hideProgressInd();
    });
});

$('table.traineesSelectedLinked .removeTrainee1').livequery('click', function(e){
    e.preventDefault();
    var parent = $(this).closest('tr');
    $(parent).remove();
    var company_id = $(this).attr('company_id');
    var contact_id = $(this).attr('contact_id');
    var order_id = $(this).attr('order_id');
    var url = 'index.php?module=aceIms_orderLink&_spAction=removeTrainee&showHTML=0';

    Util.showProgressInd('Removing selected trainee...');
    $.get(url, {contact_id: contact_id, order_id: order_id}, function(){
        $('#traineeSearchForm').submit();
        Util.hideProgressInd();
    });
});

$('#bulkCompanyCourseLink').livequery('click', function (e){
    var title = "Bulk Company Trainee Link";
    e.preventDefault();
    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            var msg = 'Updated successfully';
            Util.alert(msg, function(){
                Util.closeAllDialogs();
                Links.reloadPortalRecords('aceIms_company#aceIms_orderLink', 'aceIms_company');
                window.location.reload(true);
            });
        }
    }
    Util.openFormInDialog.call(this, 'traineeSelectedForm', title, 1300, 550, expObj);
});

$('.editPortalRecordCompany').livequery('click', function (e){
    var order_id = $(this).attr('recid');

    var url = 'index.php?module=aceIms_subject&_spAction=addSubjectToSessionInEditEnrollment&showHTML=0';
    $.get(url,{order_id: order_id}, function(html){
    });

    var title = "Bulk Company Trainee Link";
    e.preventDefault();
    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            var msg = 'Updated successfully';
            Util.alert(msg, function(){
                Util.closeAllDialogs();
                Links.reloadPortalRecords('aceIms_company#aceIms_orderLink', 'aceIms_company');
                window.location.reload(true);
            });
        }
    }
    Util.openFormInDialog.call(this, 'traineeSelectedForm', title, 1300, 550, expObj);
});

cpm.aceIms.company.init = function(){
    $(window).load(function(){
        $('.subjectForStudents input.subject_id').livequery('click', function(){
            Util.showProgressInd();
            subject_id     = $(this).val();
            var parent     = $(this).closest('tr');
            contact_id     = $(parent).attr('contact_id');
            var checked    = $(this).attr('checked') ? 'checked' : '';
            var checkedVal = checked == 'checked' ? 1 : 0;

            var url = 'index.php?module=aceIms_subject&_spAction=addSubjectToSession&showHTML=0';
            $.get(url,{subject_id: subject_id, contact_id: contact_id, checkedVal:checkedVal}, function(html){
                Util.hideProgressInd();
            });
        });

        $('.subjectForStudents select.batch_id').livequery('change', function(){
            Util.showProgressInd();
            batch_id     = $(this).val();
            var parent     = $(this).closest('tr');
            contact_id     = $(parent).attr('contact_id');

            var url = 'index.php?module=aceIms_subject&_spAction=addBatchToSession&showHTML=0';
            $.get(url,{batch_id: batch_id, contact_id: contact_id}, function(html){
                Util.hideProgressInd();
            });
        });

        $('.aceIms_company__aceIms_orderLink select[name=course_id]').livequery(function(){
            cpm.aceIms.company.populateBatch.call(this);
        });

        $('.m-aceIms_company .traineesSelectedLinked .fld_course_type_row').livequery('change', function(){
            Util.showProgressInd('Populating Course for the Selected Course Type.... Please wait');
            var courseType = $(this).val();
            var parent = $(this).closest('tr');
            cpm.aceIms.company.populateCourse.call(this);
            cpm.aceIms.company.populateSubsidy.call(this);
            cpm.aceIms.company.populateDiscount.call(this);
            cpm.aceIms.company.populateBatch.call(this);


            if (courseType == 'Long Term') {
                $('.feesByModuleCell input', parent).removeClass('hideme');
                //$('#hideLongTermFlds').hide();
                $(".fld_batch_id").attr("disabled","1");
            } else {
                $('.feesByModuleCell input', parent).addClass('hideme');
                $('.subjectsCell a', parent).addClass('hideme');
                //$('#hideLongTermFlds').show();
                $(".fld_batch_id").removeAttr("disabled");
            }
            Util.hideProgressInd();
        });

        $('.m-aceIms_company .traineesSelectedLinked .fld_course_id_row').livequery('change', function(){
            Util.showProgressInd('Populating Batch, Subsidy and Discount for the Selected Course.... Please wait');

            var courseId   = $(this).val();
            var parent     = $(this).closest('tr');
            var courseType = $(this).closest('tr').find('.fld_course_type_row').val();

            if (courseType == 'Long Term' && courseId != '') {
                $('.subjectsCell a', parent).removeClass('hideme');
                $(".fld_batch_id").attr("disabled","1");
            } else {
                $('.subjectsCell a', parent).addClass('hideme');
                $(".fld_batch_id").removeAttr("disabled");
            }

            /* Showing of Course Summary */
            //cpm.aceIms.company.populateCourseSummary.call(this);
            cpm.aceIms.company.populateBatch.call(this);
            cpm.aceIms.company.populateSubsidy.call(this);
            cpm.aceIms.company.populateDiscount.call(this);
            Util.hideProgressInd();
        });

        $('.m-aceIms_company .traineesSelectedLinked #fld_course_subsidy_history_id').livequery('change', function(){
            Util.showProgressInd();
            cpm.aceIms.company.checkInvoiceForContact.call(this);
            Util.hideProgressInd();
        });

        $('.m-aceIms_company .traineesSelectedLinked #fld_discount_id').livequery('change', function(){
            Util.showProgressInd();
            cpm.aceIms.company.checkInvoiceForContact.call(this);
            Util.hideProgressInd();
        });

        /* Show or Hide Auto generation of receipt with regards to auto generation of Invoice */
        $(".m-aceIms_company .highlightBorder input:radio[name='auto_generate_invoice']").livequery('click', function(e){
            var autoGenerationInvVal = $(this).val();
            if (autoGenerationInvVal == 0){
                $(".m-aceIms_company .highlightBorder .row_auto_generate_receipt").hide();
            } else {
                $(".m-aceIms_company .highlightBorder .row_auto_generate_receipt").show();
            }
        });
    });
}

cpm.aceIms.company.populateCourse = function(){
    Util.showProgressInd();
    var parent = $(this).closest('tr');
    $(this).each(function(){
        courseType = $(this).val();
        courseObj = $(this).closest('tr').find('.fld_course_id_row');

        courseId = courseObj.val();

        var url = $('#scopeRootAlias').val() + 'index.php?module=aceIms_course&_spAction=courseValueForDropDown&showHTML=0';

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
            data: {srcFld: 'course_type', srcValue: courseType}
        });
    });
}

cpm.aceIms.company.populateBatch = function(){
    Util.showProgressInd();
    var parent = $(this).closest('tr');
    $(this).each(function(){
        courseObj = $(this).closest('tr').find('.fld_course_id_row');
        courseId  = courseObj.val();
        batchObj  = $(this).closest('tr').find('.fld_batch_id');

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

cpm.aceIms.company.populateSubsidy = function(){
    var parent = $(this).closest('tr');
    courseObj  = $(this).closest('tr').find('.fld_course_id_row');
    courseId   = courseObj.val();
    isCitizen = $(parent).attr('isCitizen');
    if (isCitizen == 0){
        return true;
    }
    var subsidyObj = $(this).closest('tr').find('.fld_course_subsidy_history_id');
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
                subsidyObj.val(batchId);
            });
            $('option:first-child', discountObj).attr("selected", "selected");
        },
        data: {srcFld: 'course_id', srcValue: courseId}
    });
}

cpm.aceIms.company.populateDiscount = function(){
var parent = $(this).closest('tr');
    isCitizen = $(parent).attr('isCitizen');
    if (isCitizen == 1){
        return true;
    }
    courseObj = $(this).closest('tr').find('.fld_course_id_row');
    courseId  = courseObj.val();
    var discountObj = $(this).closest('tr').find('.fld_discount_id');

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
            $('option:first-child', discountObj).attr("selected", "selected");
        },
        data: {srcFld: 'course_id', srcValue: courseId}
    });
}

cpm.aceIms.company.calculateCourseSummaryTotal = function(){
    var parent = $('table#courseSummary');

    var courseAmount = parseInt($('tr#courseAmount td.amount').text());
    var subsidyAmount = parseInt($('tr#subsidyAmount td.amount').text());
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

cpm.aceIms.company.populateCourseSummary = function(){
    $('table#courseSummary').removeClass('hideme');
    var num_rows = $('#traineeSelectedResult table#courseSummary').attr('no_of_rows');
    $('#courseSummary tr#noOfTrainee td.totalNoOfTrainee').html(num_rows);

    course_id = $('.m-aceIms_company #traineeSelectedForm #fld_course_id').val();
    var url = 'index.php?module=aceIms_courseLink&_spAction=courseCompanySummary&num_rows=' + num_rows + '&showHTML=0';

    $.get(url, {course_id: course_id}, function(html){
        $('tr#courseAmount').html(html);
        $('table#courseSummary').removeClass('hideme');
        cpm.aceIms.company.calculateCourseSummaryTotal();
    });
}

cpm.aceIms.company.checkInvoiceForContact = function(){
    var parent     = $(this).closest('tr');
    var contact_id = $(parent).attr('contact_id');
    var order_id   = $(parent).attr('order_id');

    var url = 'index.php?_topRm=finance&module=aceIms_orderLink&_spAction=checkInvoiceForContactInCompanyEditEnrollment&showHTML=0';
    $.get(url,{contact_id: contact_id, order_id: order_id}, function(html){
        if (html == 1) {
        } else {
            alert ('Please note that changing subsidy/discount will cancel the existing invoice');
        }
    });
}

$('.deletePortalRecordCompany').livequery('click', function (e){
    msg = "Are you sure you want to delete this record? You cannot undo this action!1";
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

