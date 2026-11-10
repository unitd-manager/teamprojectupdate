Util.createCPObject('cpm.enggCrm.employee');

cpm.enggCrm.employee = {
    init : function(){
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

        $('.m-enggCrm_employee .printAttachmentLink').livequery('click', function (e){
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
            var url = 'index.php?_topRm=project&module=enggCrm_employee&_spAction=printEmployeeAttachmentPdf&showHTML=1&employee_id=' + employee_id + '&attachType=' + attachType;
            window.open(url, '_blank');
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
                        cpm.enggCrm.employee.reloadEmployeeCategory(employee_id);
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

                var url = 'index.php?module=enggCrm_employee&_spAction=deleteEmployeeCategory&showHTML=0&employee_category_id=' + employee_category_id;
                $.get(url, {employee_category_id: employee_category_id}, function(html){
                    Util.hideProgressInd();
                    cpm.enggCrm.employee.reloadEmployeeCategory(employee_id);
                });
            }
        });
    },

    reloadEmployeeCategory: function(employee_id){
        var url = 'index.php?module=enggCrm_employee&_spAction=employeeCategoryPortal&showHTML=0';
        $.get(url,{employee_id:employee_id}, function(html){
            $('#employeeCategoryPortal').html(html);
            //Util.hideProgressInd();
        });
    },
}