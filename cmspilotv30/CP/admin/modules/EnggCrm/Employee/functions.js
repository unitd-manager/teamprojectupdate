Util.createCPObject('cpm.enggCrm.employee');

cpm.enggCrm.employee.init = function(){
    $(window).load(function(){
        employee_work_type = $('.m-enggCrm_employee select[name=employee_work_type]').val();

        if (employee_work_type != '' && employee_work_type == 'Part time') {
            $('.m-enggCrm_employee .addHourlyRate').show();
            $('.m-enggCrm_employee .salaryForFullTime').hide();
        } else if (employee_work_type != '' && employee_work_type == 'Full Time') {
            $('.m-enggCrm_employee .salaryForFullTime').show();
            $('.m-enggCrm_employee .addHourlyRate').hide();
        }
    });

    $('.m-enggCrm_employee select[name=employee_work_type]').livequery('change', function (e){
        var employee_work_type = $(this).val();

        if(employee_work_type == 'Part time'){
            $('.m-enggCrm_employee .addHourlyRate').show();
        } else {
            $('.m-enggCrm_employee .addHourlyRate').hide();
        }
    });

    $('.m-enggCrm_employee select[name=employee_work_type]').livequery('change', function (e){
        var employee_work_type = $(this).val();

        if(employee_work_type == 'Full Time'){
            $('.m-enggCrm_employee .salaryForFullTime').show();
        } else {
            $('.m-enggCrm_employee .salaryForFullTime').hide();
        }
    });

    $('.addNewValue').livequery('click', function (e){
    var title = "Add New Value";
    e.preventDefault();

    var valuelist_name = $(this).attr('valuelist_name');

    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            Util.closeAllDialogs();
            //window.location.reload(true);
            //$(".m-manPower_opportunity select[name='valuelist_value']").val(valuelist_value);

            var url = 'index.php?module=enggCrm_employee&_spAction=valueByValuelistJSON&showHTML=0';
            $.get(url, {valuelist_name: valuelist_name}, function (data) {
                if(valuelist_name == 'positionType'){
                    $('#fld_position').cp_loadSelect(data);
                } 
            }, 'json');
        }
    }
    Util.openFormInDialog.call(this, 'portalForm', title, 400, 300, expObj);
    });

}