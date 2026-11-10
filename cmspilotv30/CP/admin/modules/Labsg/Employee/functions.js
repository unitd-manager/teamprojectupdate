Util.createCPObject('cpm.labsg.employee');

cpm.labsg.employee.init = function(){
    $('.m-labsg_employee select[name=employee_work_type]').livequery('change', function (e){
        var employee_work_type = $(this).val();

        if(employee_work_type == 'Part time'){
            $('.m-labsg_employee .addHourlyRate').show();
        } else {
            $('.m-labsg_employee .addHourlyRate').hide();
        }
    });

    $('.m-labsg_employee select[name=employee_work_type]').livequery('change', function (e){
        var employee_work_type = $(this).val();

        if(employee_work_type == 'Full Time'){
            $('.m-labsg_employee .salaryForFullTime').show();
        } else {
            $('.m-labsg_employee .salaryForFullTime').hide();
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

            var url = 'index.php?module=labsg_employee&_spAction=valueByValuelistJSON&showHTML=0';
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