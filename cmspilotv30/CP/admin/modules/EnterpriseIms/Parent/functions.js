Util.createCPObject('cpm.enterpriseIms.parent');

cpm.enterpriseIms.parent.init = function(){
    var paymentMode = $('.m-enterpriseIms_parent .row_mode_of_payment option[selected=selected]').val();
    if (paymentMode == 'Giro'){
        $('.row_giro_process_done').removeClass('hideme');
    }
}

// Modal window display for Enrollment process
$('#bulkParentCourseLink').livequery('click', function (e){
    var title = "Bulk Parent Contact Link";
    e.preventDefault();
    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            var msg = 'Updated successfully';
            Util.alert(msg, function(){
                Util.closeAllDialogs();
                Links.reloadPortalRecords('enterpriseIms_parent#enterpriseIms_orderLink', 'enterpriseIms_parent');
                window.location.reload(true);
            });
        }
    }
    Util.openFormInDialog.call(this, 'traineeSelectedForm', title, 1200, 475, expObj);        
});

/* Populating value for age in Parent # Student Linked New Record */
$('form.contactLink #fld_date_of_birth').livequery('change', function (e){
    var date_of_birth = $(this).val();

    var url = 'index.php?module=enterpriseIms_orderLink&_spAction=calculateStudentAge&showHTML=0';
    Util.showProgressInd();
    $.get(url, {date_of_birth: date_of_birth}, function(json){
        var intData = parseInt(json.age);
        $('#fld_age').val(intData);
        Util.hideProgressInd();
    },'json');
});

/* Adding a new contact and linking to the enrollment process */
$('a.newContactDetails').livequery('click', function(e) {
    e.preventDefault();

    $('#contactAddForm #fld_date_of_birth').livequery('change', function (e){
        var date_of_birth = $(this).val();

        var url = 'index.php?module=enterpriseIms_orderLink&_spAction=calculateStudentAge&showHTML=0';
        Util.showProgressInd();
        $.get(url, {date_of_birth: date_of_birth}, function(html){
            $('#contactAddForm #fld_age').val(html);
            Util.hideProgressInd();
        });
    });
        
    var exp = {
        callbackOnSuccess: function(){
            $('#dialog1').dialog('close');
            $('#dialog1').dialog('destroy');
            $('#dialog1').remove();
            var url = 'index.php?module=enterpriseIms_orderLink&_spAction=selectedStudentListRow&showHTML=0';
            Util.showProgressInd();
            $.get(url, function(html){
                //$('#traineeSelectedResult').html(html);
                $('#traineeSelectedResult tr:last').after(html);

                /* Showing of Course Summary */
                //cpm.enterpriseIms.company.populateCourseSummary.call(this);

                Util.hideProgressInd();
            });
        }
    }
    Util.openFormInDialog.call(this, 'contactAddForm', 'Add Contact', 500, 450, exp);
});

/* Searching of contacts who are linked to the parents - LEFT PANEL IN MODAL FORM*/
$('form#traineeSearchForm').livequery(function() {
    Util.setUpAjaxFormGeneral('traineeSearchForm', function(json){
        $('#traineeSearchResult').html(json.returnText);
        Util.hideProgressInd();
    });
});

/* Moving of contact(click add) from Left panel to Right in linking process */
$('table.traineeSearchRow .addTrainee').livequery('click', function(e){
    e.preventDefault();
    var parent = $(this).closest('tr');
    $(parent).remove();
    var contact_id  = $(this).attr('contact_id');
    var order_id    = $(this).attr('order_id');

    var url = 'index.php?module=enterpriseIms_orderLink&_spAction=selectedStudentListRow&showHTML=0';
 
    Util.showProgressInd('Adding selected trainee...');
    $.get(url, {contact_id: contact_id,order_id: order_id}, function(html){
        $('#traineeSelectedResult tr:last').after(html);
        $('#traineeSelectedResult .type-check').addClass('float_left');

        /* Showing of Course Summary */
        //cpm.enterpriseIms.company.populateCourseSummary.call(this);

        Util.hideProgressInd();
    });
});

/* Moving of contact(click remove) from Right panel to Left in linking process */
$('table.traineesSelectedLinked .removeTrainee').livequery('click', function(e){
    msg = "All invoice, receipt records will be cancelled, Do you want to Continue?";
    
    if (!confirm(msg)){
        return false;
    } else {
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
    }
});

/* Editing of contact(click edit) Right panel during linking process */
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

/* Editing of Order link */
$('.editPortalRecord1').livequery('click', function (e){
    var title = "Bulk Parent Contact Link";
    e.preventDefault();
    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            var msg = 'Updated successfully';
            Util.alert(msg, function(){
                Util.closeAllDialogs();
                Links.reloadPortalRecords('enterpriseIms_parent#enterpriseIms_orderLink', 'enterpriseIms_parent');
                window.location.reload(true);
            });
        }
    }
    Util.openFormInDialog.call(this, 'traineeSelectedForm', title, 1200, 475, expObj);        
});

// Population of Level, Batch and Subsidy according to Course selected
$('#traineeSelectedForm .fld_course_id_row').livequery('change', function(){
    Util.showProgressInd('Populating Level, Batch and Subsidy for the Selected Course.... Please wait');
    
    cpm.enterpriseIms.parent.populateLevel.call(this);
    cpm.enterpriseIms.parent.populateBatch.call(this);
    cpm.enterpriseIms.parent.populateSubsidy.call(this);
    //cpm.enterpriseIms.parent.populateDiscount.call(this);

    Util.hideProgressInd();
});

$('#traineeSelectedForm #fld_year_of_enrollment').livequery('change', function(){
    var selected_year = $(this).val();

    var url = 'index.php?module=enterpriseIms_orderLink&_spAction=monthList&showHTML=0';
    Util.showProgressInd();
    $.get(url, {selected_year: selected_year}, function(html){
        $('#traineeSelectedForm #monthListWrapper').html(html);
        Util.hideProgressInd();
    });
});

// Parent Transfer Form
$('#parentTransfer').livequery('click', function (e){
    var parent_id = $(this).attr('parent_id');
    var title = "Transfer to other Branch";
    e.preventDefault();
    
    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            var urlRedirect = "index.php?_topRm=main&module=enterpriseIms_parent&_action=list";
            
            var msg = 'Student transferred successfully ..';
            Util.alert(msg, function(){
                Util.closeAllDialogs();
                document.location = urlRedirect;
            });
        }
    }
    Util.openFormInDialog.call(this, 'portalForm', title, 400, 300, expObj);        
});

$(".m-enterpriseIms_parent select[name='mode_of_payment']").livequery('change', function (e){
    var paymentMode = $(this).val();
    if (paymentMode == 'Giro') {
        Util.showProgressInd();
        $('.row_giro_process_done').removeClass('hideme');
        Util.hideProgressInd();
    } else {
        Util.showProgressInd();
        $('.row_giro_process_done').addClass('hideme');
        Util.hideProgressInd();
    }
});


// Population of Level when Course is selected
cpm.enterpriseIms.parent.populateLevel = function(){
    var courseId = $(this).val();
    var levelObj = $(this).closest('tr').find('.fld_level_id');
    var url = $('#scopeRootAlias').val() + 'index.php?module=enterpriseIms_levelLink&_spAction=levelValueForDropDown&showHTML=0';
    $.ajax({
        type: "POST",
        url: url,
        async: false,
        dataType: 'json',
        success: function(json){
            levelObj.empty();
            $.each(json, function() {
                levelObj.append(new Option(this.caption, this.value));
            });
        },
        data: {srcFld: 'course_id', srcValue: courseId}
    });
};

// Population of Batch when Course is selected
cpm.enterpriseIms.parent.populateBatch = function(){
    var courseId = $(this).val();
    var batchObj = $(this).closest('tr').find('.fld_batch_id');
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
            });
        },
        data: {srcFld: 'course_id', srcValue: courseId}
    });
};

// Population of Subsidy when Course is selected
cpm.enterpriseIms.parent.populateSubsidy = function(){
    var courseId   = $(this).val();
    var subsidyObj = $(this).closest('tr').find('.fld_course_subsidy_history_id');

    var url = 'index.php?module=enterpriseIms_courseSubsidyLink&_spAction=subsidyValueForDropDown&showHTML=0';
    //var url = $('#scopeRootAlias').val() + 'index.php?module=enterpriseIms_subsidyLink&_spAction=subsidyValueForDropDown&showHTML=0';
    
    $.ajax({
        type: "POST",
        url: url,
        async: false,
        dataType: 'json',
        success: function(json){
            subsidyObj.empty();
            $.each(json, function() {
                subsidyObj.append(new Option(this.caption, this.value));
            });
        },
        data: {srcFld: 'course_id', srcValue: courseId}
    });
};

// Population of Discount when Course is selected
cpm.enterpriseIms.parent.populateDiscount = function(){
    var courseId = $(this).val();
    var discountObj = $(this).closest('tr').find('.fld_discount_id');

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
            });
        },
        data: {srcFld: 'course_id', srcValue: courseId}
    });
};
