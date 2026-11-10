Util.createCPObject('cpm.payroll.expenseHead');

cpm.payroll.expenseHead = {
    init: function(){

        /* Add Expense Sub Head */
        $('#AddExpenseSubHead').live('click', function (e){
                var title = "Add Expense Sub Head";
                e.preventDefault();
                var expense_group_id = $(this).attr('expense_group_id');

                var expObj = {
                    validate: true
                   ,callbackOnSuccess: function(){
                        var msg = 'Expense Sub Head Added Successfully';
                        Util.alert(msg, function(){
                            Util.closeAllDialogs();
                            cpm.payroll.expenseHead.reloadexpenseHead(expense_group_id);
                            //window.location.reload(true);
                        });
                    }
                }
                Util.openFormInDialog.call(this, 'expenseSubHeadPortalForm', title, 600, 400, expObj);
        });

            /* Edit Expense Sub Head */
        $('.EditExpenseSubHead').live('click', function (e){
            var title = "Edit Expense Sub Head";
            var expense_group_id = $(this).attr('expense_group_id');
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    Util.closeAllDialogs();
                    Util.alert('Expense Sub Head Updated Successfully');
                    cpm.payroll.expenseHead.reloadexpenseHead(expense_group_id);
                    //window.location.reload(true);
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 600, 500, expObj);
        });


        /* Delete Expense Sub Head */
        $('.deleteExpenseSubHead').live('click', function (e){
            msg = "Do you like to delete the Expense Sub Head?";
            if (!confirm(msg)){
                return false;
            }
            else{
                Util.showProgressInd();
                var expense_sub_group_id = $(this).attr('expense_sub_group_id');
                var expense_group_id = $(this).attr('expense_group_id');

                var url = 'index.php?module=payroll_expenseHead&_spAction=DeleteExpenseSubHead&showHTML=0&expense_sub_group_id=' + expense_sub_group_id;
                $.get(url, {expense_sub_group_id: expense_sub_group_id}, function(html){
                    Util.hideProgressInd();
                    cpm.payroll.expenseHead.reloadexpenseHead(expense_group_id);
                    //window.location.reload(true);
                });
            }
        });

    },

    reloadexpenseHead: function(expense_group_id){
        var url = 'index.php?module=payroll_expenseHead&_spAction=ExpenseSubHeadDetail&showHTML=0';
        $.get(url,{expense_group_id:expense_group_id}, function(html){
            $('#ExpenseSubHeadLinkPortal').html(html);
            //Util.hideProgressInd();
        });
    },

}

