Util.createCPObject('cpm.event.event');

cpm.event.event.init = function(){
    $('#frmEdit select#fld_section_id').livequery('change', function(){
       Util.loadCategoryDropdown.call(this);
    });        
    
    $('#frmEdit select#fld_category_id').livequery('change', function(){
       Util.loadSubCategoryDropdown.call(this);
    });
    
    $('#frmEdit select#fld_repeat_type').change(function(){
        var repeat_type = $('#frmEdit select#fld_repeat_type').val();
        cpm.event.event.showHideRepeatFldsByType(repeat_type, false);
    });
    
    $('#frmEdit select#fld_country_code').livequery('change', function(){
        Util.loadDropdownByJSON('country_code', $(this).val(), 'fld_geo_region_id', 'common_geoRegion');
    });

    var repeat_type = $('#frmEdit select#fld_repeat_type').val();
    cpm.event.event.showHideRepeatFldsByType(repeat_type, true);    
    
    
}

cpm.event.event.showHideRepeatFldsByType= function(repeat_type, is_initial_load){
    $('#repeatEvery').show();    
    $('#repeatWeekly').hide();
    $('#repeatMonthly').hide();
    
    if (repeat_type == "Never" || repeat_type == ""){
        $('#repeatEvery').hide();        
    } else if(repeat_type == "Daily"){
        $('#repeatEvery .formFieldNotesRight').html('Days');
        
    } else if (repeat_type == "Weekly"){
        $('#repeatEvery .formFieldNotesRight').html('Weeks');
        $('#repeatWeekly').show();
        
    } else if (repeat_type == "Monthly"){
        $('#repeatEvery .formFieldNotesRight').html('Month');
        $('#repeatMonthly').show();
    } else if (repeat_type == "Yearly"){
        $('#repeatEvery .formFieldNotesRight').html('Year');
    }
    
    if(!is_initial_load){
        Util.loadDropdownByJSON('repeat_type', repeat_type, 'fld_repeat_every', 'event_event');
    }    
}