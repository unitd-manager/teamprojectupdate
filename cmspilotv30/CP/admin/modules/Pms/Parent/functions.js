Util.createCPObject('cpm.pms.parent');

cpm.pms.parent.init = function(){
    $(".m-pms_parent #traineeSelectedResult .fld_batch_id").livequery('change', function (e){
        Util.showProgressInd();
        var batch_id = $(this).val();
        var year     = $("#traineeSelectedForm select[name='year_of_enrollment']").val();

        var url = 'index.php?module=pms_orderLink&_spAction=calculateTotalStudentsInBatch&showHTML=0';
        $.get(url, {batch_id: batch_id, year: year}, function(html){
            if (html > 0) {
                if(html <= 5) {
                    var msg = 'You have ' + html + ' seats left for the selected batch';
                    Util.alert(msg);
                }
            } else if (html == 0) {
                var msg = 'Please note, enrolment is full. Kindly inform Parent that Student will be waitlisted.';
                Util.alert(msg);
            }
            Util.hideProgressInd();
        });
    });

    $('#bulkParentCourseLink')
    .livequery('click', cpm.pms.parent.newEnrollmentWindowOpen);

    $('.editPortalRecord1')
    .livequery('click', cpm.pms.parent.editEnrollmentWindowOpen);

    /* Populating value for age in Parent # Student Linked New Record */
    /*$('form.contactLink #fld_date_of_birth').livequery('change', function (e){
        var date_of_birth = $(this).val();

        var url = 'index.php?module=pms_orderLink&_spAction=calculateStudentAge&showHTML=0';
        Util.showProgressInd();
        $.get(url, {date_of_birth: date_of_birth}, function(json){
            //var intData = parseInt(json.age);
            $('#fld_age').val(json.age);
            Util.hideProgressInd();
        },'json');
    });*/

    /* Populating value for age in Parent # Student Linked Edit Record*/
    /*$('form.contactLink #fld_date_of_birth').livequery('change', function (e){
        var date_of_birth = $(this).val();

        var url = 'index.php?module=pms_orderLink&_spAction=calculateStudentAge&showHTML=0';
        Util.showProgressInd();
        $.get(url, {date_of_birth: date_of_birth}, function(data){
            var intData = parseInt(data);
            $('#fld_age').val(intData);
            Util.hideProgressInd();
        });
    });*/

    /*$('#contactAddForm #fld_date_of_birth').livequery('change', function (e){
        var date_of_birth = $(this).val();

        var url = 'index.php?module=pms_orderLink&_spAction=calculateStudentAge&showHTML=0';
        Util.showProgressInd();
        $.get(url, {date_of_birth: date_of_birth}, function(html){
            $('#contactAddForm #fld_age').val(html);
            Util.hideProgressInd();
        });
    });*/

    /* Adding a new contact and linking to the enrollment process */
    $('a.newContactDetails').livequery('click', function(e) {
        e.preventDefault();

        var exp = {
            callbackOnSuccess: function(){
                $('#dialog1').dialog('close');
                $('#dialog1').dialog('destroy');
                $('#dialog1').remove();
                var url = 'index.php?module=pms_orderLink&_spAction=selectedStudentListRow&showHTML=0';
                Util.showProgressInd();
                $.get(url, function(html){
                    //$('#traineeSelectedResult').html(html);
                    $('#traineeSelectedResult tr:last').after(html);

                    /* Showing of Course Summary */
                    //cpm.pms.company.populateCourseSummary.call(this);

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

        var url = 'index.php?module=pms_orderLink&_spAction=selectedStudentListRow&showHTML=0';
     
        Util.showProgressInd('Adding selected trainee...');
        $.get(url, {contact_id: contact_id,order_id: order_id}, function(html){
            $('#traineeSelectedResult tr:last').after(html);
            $('#traineeSelectedResult .type-check').addClass('float_left');

            /* Showing of Course Summary */
            //cpm.pms.company.populateCourseSummary.call(this);

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
            var url = 'index.php?module=pms_orderLink&_spAction=removeTrainee&showHTML=0';
            
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
    /*$('.editPortalRecord1').livequery('click', function (e){
        var title = "Bulk Parent Contact Link";
        e.preventDefault();
        var expObj = {
            validate: true
           ,callbackOnSuccess: function(){
                var msg = 'Updated successfully';
                Util.alert(msg, function(){
                    Util.closeAllDialogs();
                    Links.reloadPortalRecords('pms_parent#pms_orderLink', 'pms_parent');
                    window.location.reload(true);
                });
            }
        }
        Util.openFormInDialog.call(this, 'traineeSelectedForm', title, 1200, 475, expObj);        
    });*/


    $('#traineeSelectedForm .fld_course_id_row').livequery('change', function(){
        Util.showProgressInd('Populating Level, Batch and Subsidy for the Selected Course.... Please wait');
        
        cpm.pms.parent.populateLevel.call(this);
        cpm.pms.parent.populateBatch.call(this);
        cpm.pms.parent.populateSubsidy.call(this);
        //cpm.pms.parent.populateDiscount.call(this);

        Util.hideProgressInd();
    });

    $('#traineeSelectedForm #fld_year_of_enrollment').livequery('change', function(){
        var selected_year = $(this).val();

        var url = 'index.php?module=pms_orderLink&_spAction=monthList&showHTML=0';
        Util.showProgressInd();
        $.get(url, {selected_year: selected_year}, function(html){
            $('#traineeSelectedForm #monthListWrapper').html(html);
            Util.hideProgressInd();
        });
    });

    $('#parentTransfer').livequery('click', function (e){
        var parent_id = $(this).attr('parent_id');
        var title = "Transfer to other Branch";
        e.preventDefault();
        
        var expObj = {
            validate: true
           ,callbackOnSuccess: function(){
                var urlRedirect = "index.php?_topRm=main&module=pms_parent&_action=list";
                
                var msg = 'Student transferred successfully ..';
                Util.alert(msg, function(){
                    Util.closeAllDialogs();
                    document.location = urlRedirect;
                });
            }
        }
        Util.openFormInDialog.call(this, 'portalForm', title, 700, 400, expObj);        
    });

    $('.parentTransferForm .fld_site_id').livequery('change', function(){
        var selected_branch = $(this).val();
        //var contact_id = $(this).closest('tr').find('.contact_id').val();
        //var course_id = $(this).closest('tr').find('.course_id').val();
        var course_contact_id = $(this).closest('tr').find('.course_contact_id').val();
        var batchObj = $(this).closest('tr').find('.batch_id select.fld_batch_id');
        
        var url = $('#scopeRootAlias').val() + 'index.php?module=pms_batchLink&_spAction=batchValueForDropDownFromBatchTransfer&showHTML=0';
        Util.showProgressInd();
        $.getJSON(url, {selected_branch: selected_branch, course_contact_id: course_contact_id}, function(data){
            batchObj.cp_loadSelect(data);
            Util.hideProgressInd();
        });
    });

    $('.createDda').livequery('click', function(e){
        msg = "Do you like to create DDA?";
        if (!confirm(msg)){
            return false;
        }
        else {
            var url = 'index.php?module=pms_parent&_spAction=generateDda&showHTML=0';
            Util.showProgressInd();
            var parent_id = $(this).attr('parent_id');
            $.get(url,{parent_id: parent_id}, function(){
                alert ('DDA generated Succesfully');
                Util.hideProgressInd();
                window.location.reload(true);
            });
        }
    });
}

cpm.pms.parent.newEnrollmentWindowOpen = function(){
    var title = "Bulk Parent Contact Link";
    e.preventDefault();
    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            var msg = 'Updated successfully';
            Util.alert(msg, function(){
                Util.closeAllDialogs();
                Links.reloadPortalRecords('pms_parent#pms_orderLink', 'pms_parent');
                window.location.reload(true);
            });
        }
    }
    Util.openFormInDialog.call(this, 'traineeSelectedForm', title, 1200, 475, expObj);        
};

cpm.pms.parent.editEnrollmentWindowOpen = function(){
    var title = "Bulk Parent Contact Link";
    e.preventDefault();
    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            var msg = 'Updated successfully';
            Util.alert(msg, function(){
                Util.closeAllDialogs();
                Links.reloadPortalRecords('pms_parent#pms_orderLink', 'pms_parent');
                window.location.reload(true);
            });
        }
    }
    Util.openFormInDialog.call(this, 'traineeSelectedForm', title, 1200, 475, expObj);        

};

cpm.pms.parent.populateLevel = function(){
    var courseId = $(this).val();
    var levelObj = $(this).closest('tr').find('.fld_level_id');
    var url = $('#scopeRootAlias').val() + 'index.php?module=pms_levelLink&_spAction=levelValueForDropDown&showHTML=0';
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

cpm.pms.parent.populateBatch = function(){
    var courseId = $(this).val();
    var batchObj = $(this).closest('tr').find('.fld_batch_id');
    var url = $('#scopeRootAlias').val() + 'index.php?module=pms_batchLink&_spAction=batchValueForDropDown&showHTML=0';
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

cpm.pms.parent.populateSubsidy = function(){
    var courseId   = $(this).val();
    var subsidyObj = $(this).closest('tr').find('.fld_course_subsidy_history_id');

    var url = 'index.php?module=pms_courseSubsidyLink&_spAction=subsidyValueForDropDown&showHTML=0';
    //var url = $('#scopeRootAlias').val() + 'index.php?module=pms_subsidyLink&_spAction=subsidyValueForDropDown&showHTML=0';
    
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

cpm.pms.parent.populateDiscount = function(){
    var courseId = $(this).val();
    var discountObj = $(this).closest('tr').find('.fld_discount_id');

    var url = 'index.php?module=pms_courseSubsidyLink&_spAction=discountValueForDropDown&showHTML=0';
    
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
