Util.createCPObject('cpm.pms.batchTransfer');
cpm.pms.batchTransfer.init = function(){
    /* Choosing appropriate Level and Batch accoring to Course selected */
    $('#studentSearchForm #fld_course_id').livequery('change', function(){
        var courseId = $(this).val();
        //var formName = $(this).closest('form').attr('id');
        Util.showProgressInd('Populating Session for the Selected Class.... Please wait');
        
        if (courseId != '') {
            $('#studentSearchForm .row_batch_id').removeClass('hideme');
        } else {
            $('#studentSearchForm .row_batch_id').addClass('hideme');
        }

        cpm.pms.batchTransfer.populateLevel.call(this);
        cpm.pms.batchTransfer.populateBatch.call(this);
    
        Util.hideProgressInd();
    });

    $('#studentSelectedForm #fld_enrollment_course_id').livequery('change', function(){
        var courseId = $(this).val();
        Util.showProgressInd('Populating Session for the Selected Class.... Please wait');
        
        if (courseId != '') {
            $('#studentSelectedForm .row_enrollment_batch_id').removeClass('hideme');
        } else {
            $('#studentSelectedForm .row_enrollment_batch_id').addClass('hideme');
        }

        cpm.pms.batchTransfer.populateLevel.call(this);
        cpm.pms.batchTransfer.populateBatch.call(this);
    
        Util.hideProgressInd();
    });

    $('#studentSelectedForm input[name=graduated]').livequery('click', function(){
        var graduatedVal = $(this).val();
        
        if (graduatedVal == '1') {
            $('#studentSelectedForm .row_enrollment_course_id').addClass('hideme');
            $('#studentSelectedForm .row_enrollment_level_id').addClass('hideme');
            $('#studentSelectedForm .row_enrollment_batch_id').addClass('hideme');
        } else {
            $('#studentSelectedForm .row_enrollment_course_id').removeClass('hideme');
        }
    });
}

cpm.pms.batchTransfer.populateLevel = function(){
    var courseId = $(this).val();
    var formName = $(this).closest('form').attr('id');
    
    if (formName == 'studentSearchForm') {
        var levelObj = $('#studentSearchForm #fld_level_id');
    } else if (formName == 'studentSelectedForm') {
        var levelObj = $('#studentSelectedForm #fld_enrollment_level_id');
    }
    
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

cpm.pms.batchTransfer.populateBatch = function(){
    var courseId = $(this).val();
    var formName = $(this).closest('form').attr('id');

    if (formName == 'studentSearchForm') {
        var batchObj = $('#studentSearchForm #fld_batch_id');
    } else if (formName == 'studentSelectedForm') {
        var batchObj = $('#studentSelectedForm #fld_enrollment_batch_id');
    }
    
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

$('form#studentSearchForm').livequery(function() {
    Util.setUpAjaxFormGeneral('studentSearchForm', function(json){
        $('#studentSearchResult').html(json.returnText);
        Util.hideProgressInd();
    });
});

/* Moving of contact(click add) from Left panel to Right in linking process */
$('table.studentSearchRow .addTrainee').livequery('click', function(e){
    e.preventDefault();
    var parent = $(this).closest('tr');
    $(parent).remove();
    var contact_id  = $(this).attr('contact_id');
    var url = 'index.php?module=pms_batchTransfer&_spAction=selectedStudentListRow&showHTML=0';
 
    Util.showProgressInd('Adding selected student...');
    $.get(url, {contact_id: contact_id}, function(html){
        $('#studentSelectedResult tr:last').after(html);
        $('#studentSelectedResult .type-check').addClass('float_left');
        Util.hideProgressInd();
    });
});

/* Moving of contact(click remove) from Right panel to Left in linking process */
$('table.studentsSelectedLinked .removeTrainee').livequery('click', function(e){
    e.preventDefault();
    var parent = $(this).closest('tr');
    $(parent).remove();
    var contact_id = $(this).attr('contact_id');
    var url = 'index.php?module=pms_batchTransfer&_spAction=removeTrainee&showHTML=0';
    
    Util.showProgressInd('Removing selected student...');
    $.get(url, {contact_id: contact_id}, function(){
        $('#studentSearchForm').submit();
        Util.hideProgressInd();
    }); 
});

/* Moving of all contacts(click add all) from Left panel to Right in linking process */
$('table.studentSearchRow .addAllTrainee').livequery('click', function(e){
    e.preventDefault();
    var parent = $('table.studentSearchRow tbody');
    $(parent).remove();
    var year        = $(this).attr('year');
    var course_id   = $(this).attr('course_id');
    var level_id    = $(this).attr('level_id');
    var batch_id    = $(this).attr('batch_id');
    var url = 'index.php?module=pms_batchTransfer&_spAction=allSelectedStudentListRow&showHTML=0';
 
    Util.showProgressInd('Adding all students...');
    $.get(url, {year: year, course_id: course_id, level_id: level_id, batch_id: batch_id}, function(html){
        $('#studentSelectedResult tr:last').after(html);
        $('#studentSelectedResult .type-check').addClass('float_left');
        Util.hideProgressInd();
    });
});

/* Moving of all contacts(click remove all) from Right panel to Left in linking process */
$('table.studentsSelectedLinked .removeAllTrainee').livequery('click', function(e){
    e.preventDefault();
    var parent = $('form#studentSelectedForm #studentSelectedResult');
    $(parent).remove();
    var url = 'index.php?module=pms_batchTransfer&_spAction=removeAllTrainee&showHTML=0';
    
    Util.showProgressInd('Removing all students...');
    $.get(url, function(){
        $('#studentSearchForm').submit();
        Util.hideProgressInd();
    }); 
});

$('form#studentSelectedForm').livequery(function() {
    Util.setUpAjaxFormGeneral('studentSelectedForm', function(json){
        $('#studentSearchResult').html(json.returnText);
        alert('Students promoted successfully.');
        window.location.reload(true);
        Util.hideProgressInd();
    });
});

