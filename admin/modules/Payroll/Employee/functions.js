Util.createCPObject('cpm.payroll.employee');

cpm.payroll.employee = {
    init : function(){
        //initialize tabs
        $('#tabs').tabs();

        $('#tabs ul.ui-tabs-nav li:last').livequery(function() {
            $(this).css('border-right', '1px solid #D3D3D3');
        });

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

        $('.m-payroll_employee .printAttachmentLink').livequery('click', function (e){
            var employee_id = $(this).attr('employee_id');
            var cboxObj  = $('input[name=attach_type\\[\\]]:checked').val();
            var attachType = '';
            $('input[name=attach_type\\[\\]]:checked').each(function(){
                var checkedVal = $(this).val();
                if(attachType != ''){
                    attachType = attachType + ",'" + checkedVal + "'";
                } else {
                    attachType = "'" + checkedVal + "'";                
                }
            });

            //alert(attachType);
            var url = 'index.php?_topRm=project&module=payroll_employee&_spAction=printEmployeeAttachmentPdf&showHTML=1&employee_id=' + employee_id + '&attachType=' + attachType;
            window.open(url, '_blank');
        });

        /* Add employee category */
        $('#addEmployeeCategory').live('click', function (e){
            var title = "Add Category";
            e.preventDefault();
            var employee_id = $(this).attr('employee_id');

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Category Added Successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        cpm.payroll.employee.reloadEmployeeCategory(employee_id);
                        //window.location.reload(true);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'employeeCategoryPortalForm', title, 400, 300, expObj);
        });

        /* Delete employee category */
        $('.deleteEmployeeCategory').live('click', function (e){
            msg = "Do you like to delete the category?";
            if (!confirm(msg)){
                return false;
            }
            else{
                Util.showProgressInd();
                var employee_category_id = $(this).attr('employee_category_id');
                var employee_id = $(this).attr('employee_id');

                var url = 'index.php?module=payroll_employee&_spAction=deleteEmployeeCategory&showHTML=0&employee_category_id=' + employee_category_id;
                $.get(url, {employee_category_id: employee_category_id}, function(html){
                    Util.hideProgressInd();
                    cpm.payroll.employee.reloadEmployeeCategory(employee_id);
                });
            }
        });

        $('select[name=dormitory_id]').live('change', function (e){
            var dormitory_id = $(this).val();
            var employee_id  = $('#record_id').val();
            if (dormitory_id != "") {
                Util.showProgressInd();
                var url = 'index.php?module=payroll_employee&_spAction=showDormitoryFields&showHTML=0';
                $.get(url, {dormitory_id: dormitory_id, employee_id: employee_id}, function(html){
                    Util.hideProgressInd();
                    $('tr.dormitoryFields').after(html);
                });
            } else {
                $('.dormitoryFieldsAppended').remove();
            }
        });
    },

    reloadEmployeeCategory: function(employee_id){
        var url = 'index.php?module=payroll_employee&_spAction=employeeCategoryPortal&showHTML=0';
        $.get(url,{employee_id:employee_id}, function(html){
            $('#employeeCategoryPortal').html(html);
            //Util.hideProgressInd();
        });
    },

}