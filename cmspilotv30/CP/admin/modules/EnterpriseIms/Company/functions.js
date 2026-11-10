Util.createCPObject('cpm.enterpriseIms.parent');
$('a#addCourseRows').livequery('click', function(){
    Util.showProgressInd();
    var url = $('#scopeRootAlias').val() + 'index.php?module=enterpriseIms_orderLink&_spAction=courseRow&showHTML=0';
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
            var url = 'index.php?module=enterpriseIms_orderLink&_spAction=selectedTraineeResultRow&showHTML=0';
            Util.showProgressInd();
            $.get(url, function(html){
                //$('#traineeSelectedResult').html(html);
                $('#traineeSelectedResult tr:last').after(html);

                /* Showing of Course Summary */
                cpm.enterpriseIms.company.populateCourseSummary.call(this);

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

    var url = 'index.php?module=enterpriseIms_orderLink&_spAction=selectedStudentListRow&showHTML=0';
 
    Util.showProgressInd('Adding selected trainee...');
    $.get(url, {contact_id: contact_id,order_id: order_id}, function(html){
        //$('#traineeSelectedResult').append(html);
        $('#traineeSelectedResult tr:last').after(html);

        /* Showing of Course Summary */
        cpm.enterpriseIms.company.populateCourseSummary.call(this);

        Util.hideProgressInd();
    });
});

$('table.traineesSelectedLinked .removeTrainee').livequery('click', function(e){
    e.preventDefault();
    var parent = $(this).closest('tr');
    $(parent).remove();
    var contact_id = $(this).attr('contact_id');
    var order_id = $(this).attr('order_id');
    var url = 'index.php?module=enterpriseIms_orderLink&_spAction=removeTrainee&showHTML=0';

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
                Links.reloadPortalRecords('enterpriseIms_company#enterpriseIms_orderLink', 'enterpriseIms_company');
                window.location.reload(true);
            });
        }
    }
    Util.openFormInDialog.call(this, 'traineeSelectedForm', title, 1050, 475, expObj);        
});

$('#editPortalRecord').livequery('click', function (e){
    var title = "Bulk Company Trainee Link";
    e.preventDefault();
    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            var msg = 'Updated successfully';
            Util.alert(msg, function(){
                Util.closeAllDialogs();
                Links.reloadPortalRecords('enterpriseIms_company#enterpriseIms_orderLink', 'enterpriseIms_company');
                window.location.reload(true);
            });
        }
    }
    Util.openFormInDialog.call(this, 'traineeSelectedForm', title, 1050, 475, expObj);        
});

cpm.enterpriseIms.company.init = function(){
    $(window).load(function(){
        $('.enterpriseIms_company__enterpriseIms_orderLink select[name=course_id]').livequery(function(){
           cpm.enterpriseIms.company.populateBatch.call(this);
        });
        
        $('.m-enterpriseIms_company #traineeSelectedForm #fld_course_id').livequery('change', function(){
            Util.showProgressInd('Populating Batch, Subsidy and Discount for the Selected Course.... Please wait');

            /* Showing of Course Summary */
            cpm.enterpriseIms.company.populateCourseSummary.call(this);

            cpm.enterpriseIms.company.populateBatch.call(this);
            cpm.enterpriseIms.company.populateSubsidy.call(this);
            cpm.enterpriseIms.company.populateDiscount.call(this);
            Util.hideProgressInd();
        });
    });
}

cpm.enterpriseIms.company.populateBatch = function(){
    Util.showProgressInd();
    var parent = $(this).closest('tr');
    $(this).each(function(){
        courseId = $(this).val();
        //batchObj = $('select[name=batch_id]', parent);
        batchObj = $('.m-enterpriseIms_company #traineeSelectedForm #fld_batch_id');
		
        batchId = batchObj.val();

        var url = $('#scopeRootAlias').val() + 'index.php?module=enterpriseIms_batchLink&_spAction=batchValueForDropDown&showHTML=0';
        
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

cpm.enterpriseIms.company.populateSubsidy = function(){
    courseId = $(this).val();
    $('.traineesSelectedLinked tr').each(function(){
        isCitizen = $(this).attr('isCitizen');
        if (!isCitizen){
            return true;
        }
        subsidyObj = $('.m-enterpriseIms_company #traineeSelectedForm #fld_course_subsidy_history_id');
		
        subsidyId = subsidyObj.val();

        var url = 'index.php?module=enterpriseIms_courseSubsidyLink&_spAction=subsidyValueForDropDown&showHTML=0';
        
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

cpm.enterpriseIms.company.populateDiscount = function(){
    var parent = $(this).closest('tr');
    $(this).each(function(){
    //alert (123);
        courseId = $(this).val();
        discountObj = $('select[name=discount_id]', parent);
        discountObj = $('.m-enterpriseIms_company #traineeSelectedForm #fld_discount_id');
		
        discountId = discountObj.val();

        var url = 'index.php?module=enterpriseIms_courseSubsidyLink&_spAction=discountValueForDropDown&showHTML=0';

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

cpm.enterpriseIms.company.calculateCourseSummaryTotal = function(){
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

cpm.enterpriseIms.company.populateCourseSummary = function(){
    $('table#courseSummary').removeClass('hideme');
    var num_rows = $('#traineeSelectedResult table#courseSummary').attr('no_of_rows');
    $('#courseSummary tr#noOfTrainee td.totalNoOfTrainee').html(num_rows);
    
    course_id = $('.m-enterpriseIms_company #traineeSelectedForm #fld_course_id').val();
    var url = 'index.php?module=enterpriseIms_courseLink&_spAction=courseCompanySummary&num_rows=' + num_rows + '&showHTML=0';
    
    $.get(url, {course_id: course_id}, function(html){
        $('tr#courseAmount').html(html);
        $('table#courseSummary').removeClass('hideme');
        cpm.enterpriseIms.company.calculateCourseSummaryTotal();
    });
}
