Util.createCPObject('cpm.payroll.employee');

cpm.payroll.employee.init = function(){
    $(window).load(function(){
        employee_work_type = $('.m-payroll_employee select[name=employee_work_type]').val();

        /*citizen_val = $(".m-payroll_employee .row_is_citizen input[name='is_citizen']:checked").val();
        if (citizen_val == 1) {
            $('.m-payroll_employee .row_fin_no').hide();
        } else {
            $('.m-payroll_employee .row_nric_no').hide();
        }*/

        /*$(".m-payroll_employee .row_is_citizen input[name='is_citizen']").livequery('change', function(e){
            var citizen = $(this).val();
            if (citizen == 1) {
                $('.m-payroll_employee .row_fin_no').hide();
                $('.m-payroll_employee .row_nric_no').removeClass('hideme');
                $('.m-payroll_employee .row_nric_no').show();
            } else {
                $('.m-payroll_employee .row_nric_no').hide();
                $('.m-payroll_employee .row_fin_no').removeClass('hideme');
                $('.m-payroll_employee .row_fin_no').show();
            }
        });*/

        $('.m-payroll_employee.v-new .row_fin_no').hide();
        $('.m-payroll_employee.v-new .row_work_permit').hide();
        $('.m-payroll_employee.v-new .row_nric_no').hide();

        $(".m-payroll_employee .row_citizen select[name='citizen']").livequery('change', function(e){
            var citizen = $(this).val();
            $('.m-payroll_employee .passType').removeClass('displayNone');
            if (citizen == 'PR' || citizen == 'Citizen') {
                $('.m-payroll_employee .row_fin_no').hide();
                $('.m-payroll_employee .row_work_permit').hide();
                $('.m-payroll_employee .row_nric_no').show();
                $('.m-payroll_employee .row_fin_no_expiry_date').hide();
                $('.m-payroll_employee .row_work_permit_expiry_date').hide();
            } else if (citizen == 'EP' || citizen == 'SP' || citizen == 'DP') {
                $('.m-payroll_employee .row_spr_year').hide();
                $('.m-payroll_employee .row_nric_no').hide();
                $('.m-payroll_employee .row_fin_no').show();
                $('.m-payroll_employee .row_work_permit').hide();
                $('.m-payroll_employee .row_fin_no_expiry_date').show();
                $('.m-payroll_employee .row_work_permit_expiry_date').hide();
            } else if (citizen == 'WP') {
                $('.m-payroll_employee .row_spr_year').hide();
                $('.m-payroll_employee .row_nric_no').hide();
                $('.m-payroll_employee .row_fin_no').show();
                $('.m-payroll_employee .row_work_permit').show();
                $('.m-payroll_employee .row_fin_no_expiry_date').show();
                $('.m-payroll_employee .row_work_permit_expiry_date').show();
            } else {
                $('.m-payroll_employee .row_spr_year').hide();
                $('.m-payroll_employee .row_nric_no').hide();
                $('.m-payroll_employee .row_fin_no').hide();
                $('.m-payroll_employee .row_work_permit').hide();
                $('.m-payroll_employee .row_fin_no_expiry_date').hide();
                $('.m-payroll_employee .row_work_permit_expiry_date').hide();
            }

            if (citizen == 'Citizen') {
                $('.m-payroll_employee .row_spr_year').hide();
            }
            if (citizen == 'PR') {
                $('.m-payroll_employee .row_spr_year').show();
            }
        });

        if (employee_work_type != '' && employee_work_type == 'Part time') {
            $('.m-payroll_employee .addHourlyRate').show();
            $('.m-payroll_employee .salaryForFullTime').hide();
        } else if (employee_work_type != '' && employee_work_type == 'Full Time') {
            $('.m-payroll_employee .salaryForFullTime').show();
            $('.m-payroll_employee .addHourlyRate').hide();
        }
    });

    $('.m-payroll_employee select[name=employee_work_type]').livequery('change', function (e){
        var employee_work_type = $(this).val();

        if(employee_work_type == 'Part time'){
            $('.m-payroll_employee .addHourlyRate').show();
        } else {
            $('.m-payroll_employee .addHourlyRate').hide();
        }
    });

    $('.m-payroll_employee select[name=employee_work_type]').livequery('change', function (e){
        var employee_work_type = $(this).val();

        if(employee_work_type == 'Full Time'){
            $('.m-payroll_employee .salaryForFullTime').show();
        } else {
            $('.m-payroll_employee .salaryForFullTime').hide();
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

            var url = 'index.php?module=payroll_employee&_spAction=valueByValuelistJSON&showHTML=0';
            $.get(url, {valuelist_name: valuelist_name}, function (data) {
                if(valuelist_name == 'employeeGroup'){
                    $('#fld_employee_group').cp_loadSelect(data);
                }
            }, 'json');
        }
    }
    Util.openFormInDialog.call(this, 'portalForm', title, 400, 300, expObj);
    });

}