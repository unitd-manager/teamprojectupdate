Util.createCPObject('cpm.agileIms.company');

cpm.agileIms.company.init = function(){
    $(window).load(function(){
        $('.agileIms_company__agileIms_orderLink select[name=course_id]').livequery(function(){
           cpm.agileIms.company.populateBatch.call(this);
        });
        
        $('.m-agileIms_company .traineesSelectedLinked .fld_course_id_row').livequery('change', function(){
            Util.showProgressInd('Populating Batch, Subsidy and Discount for the Selected Course.... Please wait');

            cpm.agileIms.company.populateBatch.call(this);
            cpm.agileIms.company.populateSubsidy.call(this);
            cpm.agileIms.company.populateDiscount.call(this);
            Util.hideProgressInd();
        });

        $('.m-agileIms_company .traineesSelectedLinked #fld_course_subsidy_history_id').livequery('change', function(){
            Util.showProgressInd();
            cpm.agileIms.company.checkInvoiceForContact.call(this);
            Util.hideProgressInd();
        });

        $('.m-agileIms_company .traineesSelectedLinked #fld_discount_id').livequery('change', function(){
            Util.showProgressInd();
            cpm.agileIms.company.checkInvoiceForContact.call(this);
            Util.hideProgressInd();
        });

        /* Show or Hide Auto generation of receipt with regards to auto generation of Invoice */
        $(".m-agileIms_company .highlightBorder input:radio[name='auto_generate_invoice']").livequery('click', function(e){
            var autoGenerationInvVal = $(this).val();
            if (autoGenerationInvVal == 0){
                $(".m-agileIms_company .highlightBorder .row_auto_generate_receipt").hide();
            } else {
                $(".m-agileIms_company .highlightBorder .row_auto_generate_receipt").show();
            }
        });
    });
}

$('a#addCourseRows').livequery('click', function(){
    Util.showProgressInd();
    var url = $('#scopeRootAlias').val() + 'index.php?module=agileIms_orderLink&_spAction=courseRow&showHTML=0';
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
            var url = 'index.php?module=agileIms_orderLink&_spAction=selectedTraineeResultRow&showHTML=0';
            Util.showProgressInd();
            $.get(url, function(html){
                //$('#traineeSelectedResult').html(html);
                $('#traineeSelectedResult tr:last').after(html);

                /* Showing of Course Summary */
                //cpm.agileIms.company.populateCourseSummary.call(this);

                Util.hideProgressInd();
            });
        }
    }
    Util.openFormInDialog.call(this, 'contactAddForm', 'Add Contact', 500, 450, exp);
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

    var url = 'index.php?module=agileIms_orderLink&_spAction=selectedStudentListRow&showHTML=0';
 
    Util.showProgressInd('Adding selected trainee...');
    $.get(url, {contact_id: contact_id,order_id: order_id}, function(html){
        $('#traineeSelectedResult tr:last').after(html);

        /* Showing of Course Summary */
        //cpm.agileIms.company.populateCourseSummary.call(this);

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
    var url1 = 'index.php?_topRm=finance&module=agileIms_orderLink&_spAction=checkInvoiceForContactInCompanyEditEnrollment&showHTML=0';
    $.get(url1,{contact_id: contact_id, order_id: order_id}, function(html){
        if (html == 1) {
        } else {
            alert ('Please note that changing discount will cancel the existing invoice');
        }
    });

    var url = 'index.php?module=agileIms_orderLink&_spAction=removeTrainee&showHTML=0';

    $.get(url, {contact_id: contact_id, order_id: order_id, company_id: company_id}, function(html){
        $('#traineeSearchResult').html(html);
        Util.hideProgressInd();
    });
});

$('#bulkCompanyCourseLink').livequery('click', function (e){
    var title = "Bulk Company Trainee Link";
    e.preventDefault();
    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            var msg = 'Successfully completed enrollment. Please Goto Finance to create invoice and receipt.';
            Util.alert(msg, function(){
                Util.closeAllDialogs();
                Links.reloadPortalRecords('agileIms_company#agileIms_orderLink', 'agileIms_company');
                window.location.reload(true);
            });
        }
    }
    Util.openFormInDialog.call(this, 'traineeSelectedForm', title, 1050, 475, expObj);        
});

$('.editCompanyEnrollment').livequery('click', function (e){
    var title = "Bulk Company Trainee Link";
    e.preventDefault();
    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            var msg = 'Updated successfully';
            Util.alert(msg, function(){
                Util.closeAllDialogs();
                Links.reloadPortalRecords('agileIms_company#agileIms_orderLink', 'agileIms_company');
                window.location.reload(true);
            });
        }
    }
    Util.openFormInDialog.call(this, 'traineeSelectedForm', title, 1050, 475, expObj);        
});

cpm.agileIms.company.populateBatch = function(){
    Util.showProgressInd();
    var parent = $(this).closest('tr');
    $(this).each(function(){
        courseId = $(this).val();
        var batchObj = $(this).closest('tr').find('.fld_batch_id');
		
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

cpm.agileIms.company.populateSubsidyOld = function(){
    courseId = $(this).val();
    $('.traineesSelectedLinked tr').each(function(){
        isCitizen = $(this).attr('isCitizen');
        if (isCitizen == 0){
            return true;
        }
        var subsidyObj = $(this).closest('tr').find('.fld_subsidy_discount_id');
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
                    subsidyObj.val(batchId);
                });
            },
            data: {srcFld: 'course_id', srcValue: courseId}
        });
    });
}

cpm.agileIms.company.populateSubsidy = function(){
    var parent = $(this).closest('tr');
    courseId = $(this).val();
    isCitizen = $(parent).attr('isCitizen');
    if (isCitizen == 0){
        return true;
    }
    var subsidyObj = $(this).closest('tr').find('.fld_course_subsidy_history_id');
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
                subsidyObj.val(batchId);
            });
            $('option:first-child', discountObj).attr("selected", "selected");
        },
        data: {srcFld: 'course_id', srcValue: courseId}
    });
}

cpm.agileIms.company.populateDiscount = function(){
var parent = $(this).closest('tr');
    isCitizen = $(parent).attr('isCitizen');
    if (isCitizen == 1){
        return true;
    }
    courseId = $(this).val();
    var discountObj = $(this).closest('tr').find('.fld_discount_id');
	
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
            $('option:first-child', discountObj).attr("selected", "selected");
        },
        data: {srcFld: 'course_id', srcValue: courseId}
    });
}

cpm.agileIms.company.calculateCourseSummaryTotal = function(){
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

cpm.agileIms.company.populateCourseSummary = function(){
    $('table#courseSummary').removeClass('hideme');
    var num_rows = $('#traineeSelectedResult table#courseSummary').attr('no_of_rows');
    $('#courseSummary tr#noOfTrainee td.totalNoOfTrainee').html(num_rows);
    
    course_id = $('.m-agileIms_company #traineeSelectedForm #fld_course_id').val();
    var url = 'index.php?module=agileIms_courseLink&_spAction=courseCompanySummary&num_rows=' + num_rows + '&showHTML=0';
    
    $.get(url, {course_id: course_id}, function(html){
        $('tr#courseAmount').html(html);
        $('table#courseSummary').removeClass('hideme');
        cpm.agileIms.company.calculateCourseSummaryTotal();
    });
}

/* Cancel of Enrollment in Company Right Panel */
$('.cancelEnrollment').livequery('click', function (e) {
    msg = "Do you like to cancel enrollment? Please note that all the invoice(s) and receipt(s) will also be cancelled";
    if (!confirm(msg)) {
        return false;
    } else {
        var url = 'index.php?module=agileIms_company&_spAction=cancelEnrollmentForCompany&showHTML=0';
        Util.showProgressInd();
        var order_id = $(this).attr('order_id');
        $.get(url,{order_id: order_id}, function(html){
            alert ('Enrollment Cancelled Succesfully');
            Util.hideProgressInd();
            window.location.reload(true); 
        });
    }
});

cpm.agileIms.company.checkInvoiceForContact = function(){
    var parent     = $(this).closest('tr');
    var contact_id = $(parent).attr('contact_id');
    var order_id   = $(parent).attr('order_id');
    
    var url = 'index.php?_topRm=finance&module=agileIms_orderLink&_spAction=checkInvoiceForContactInCompanyEditEnrollment&showHTML=0';
    $.get(url,{contact_id: contact_id, order_id: order_id}, function(html){
        if (html == 1) {
        } else {
            alert ('Please note that changing subsidy/discount will cancel the existing invoice');
        }
    });
}

