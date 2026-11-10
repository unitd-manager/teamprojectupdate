Util.createCPObject('cpm.payroll.incomeHead');

cpm.payroll.incomeHead = {
    init: function(){

        /* Add income Sub Head */
        $('#AddIncomeSubHead').live('click', function (e){
                var title = "Add Income Sub Head";
                e.preventDefault();
                var income_group_id = $(this).attr('income_group_id');

                var expObj = {
                    validate: true
                   ,callbackOnSuccess: function(){
                        var msg = 'income Sub Head Added Successfully';
                        Util.alert(msg, function(){
                            Util.closeAllDialogs();
                            cpm.payroll.incomeHead.reloadincomeHead(income_group_id);
                            //window.location.reload(true);
                        });
                    }
                }
                Util.openFormInDialog.call(this, 'incomeSubHeadPortalForm', title, 600, 400, expObj);
        });

            /* Edit income Sub Head */
        $('.EditIncomeSubHead').live('click', function (e){
            var title = "Edit Income Sub Head";
            var income_group_id = $(this).attr('income_group_id');
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    Util.closeAllDialogs();
                    Util.alert('income Sub Head Updated Successfully');
                    cpm.payroll.incomeHead.reloadincomeHead(income_group_id);
                    //window.location.reload(true);
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 600, 400, expObj);
        });


        /* Delete income Sub Head */
        $('.deleteIncomeSubHead').live('click', function (e){
            msg = "Do you like to delete the income Sub Head?";
            if (!confirm(msg)){
                return false;
            }
            else{
                Util.showProgressInd();
                var income_sub_group_id = $(this).attr('income_sub_group_id');
                var income_group_id = $(this).attr('income_group_id');

                var url = 'index.php?module=payroll_incomeHead&_spAction=DeleteIncomeSubHead&showHTML=0&income_sub_group_id=' + income_sub_group_id;
                $.get(url, {income_sub_group_id: income_sub_group_id}, function(html){
                    Util.hideProgressInd();
                    cpm.payroll.incomeHead.reloadincomeHead(income_group_id);
                    //window.location.reload(true);
                });
            }
        });

    },

    reloadincomeHead: function(income_group_id){
        var url = 'index.php?module=payroll_incomeHead&_spAction=IncomeSubHeadDetail&showHTML=0';
        $.get(url,{income_group_id:income_group_id}, function(html){
            $('#IncomeSubHeadLinkPortal').html(html);
            //Util.hideProgressInd();
        });
    },

}

