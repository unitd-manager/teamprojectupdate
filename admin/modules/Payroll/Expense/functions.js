Util.createCPObject('cpm.payroll.expense');

cpm.payroll.expense = {
    init: function(){

        $('#frmNew select#fld_mode_of_payment').livequery('change', function(){
            cpm.payroll.expense.populatePaymentMode.call(this);
        });

        $('#frmNew select#fld_payment_status').livequery('change', function(){
            var paymentStatus = $(this).val();
            if (paymentStatus == 'Paid') {
                Util.showProgressInd();
                $('#frmNew .row_payment_cleared_date').removeClass('hideme');
                Util.hideProgressInd();
            } else {
                Util.showProgressInd();
                $('#frmNew .row_payment_cleared_date').addClass('hideme');
                Util.hideProgressInd();
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

                    var url = 'index.php?module=payroll_expense&_spAction=valueByValuelistJSON&showHTML=0';
                    $.get(url, {valuelist_name: valuelist_name}, function (data) {
                        if(valuelist_name == 'Group'){
                            $('#fld_group').cp_loadSelect(data);
                        } 
                    }, 'json');
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 400, 300, expObj);
        });

        $('#frmNew select#fld_type').livequery('change', function(){
            var type = $(this).val();
            Util.showProgressInd();
            var url = 'index.php?module=payroll_expense&_spAction=groupByTypeJSON&showHTML=0';
            $.get(url, {type: type}, function (data) {
                $('#fld_group').cp_loadSelect(data);
                $('#fld_sub_group').cp_loadSelect('');
                $('.expenseFields').hide();
                $('.expenseSubGroup56').hide();
                $('.expenseSubGroup1112').hide();
                $('.invoiceFields').hide();
                $('.expensePaymentMode').hide();
                $('.invoiceFieldsOther').hide();
                $('.incomeFields').hide();
                $('.serviceChargeField').hide();
                Util.hideProgressInd();
            }, 'json');
        });

        $('.group select#fld_group').livequery('change', function(){
            var group_id = $(this).val();
            //var type = $("#fld_type").val();
            Util.showProgressInd();

            var url = 'index.php?module=payroll_expense&_spAction=subgroupByGroupJSON&showHTML=0';
            $.get(url, {group_id: group_id}, function (data) {
                $('#fld_sub_group').cp_loadSelect(data);
                Util.hideProgressInd();
                $("input[name='amount']").val('');
                $("input[name='service_charge']").val('');
                $('.expenseFields').hide();
                $('.expenseSubGroup56').hide();
                $('.expenseSubGroup1112').hide();
                $('.serviceChargeField').hide();
                $('#fld_total_amount').html('');
            }, 'json');
        });

        $('.subGroup select#fld_sub_group').livequery('change', function(){
            $("input[name='amount']").val('');
            $("input[name='service_charge']").val('');
            $('#fld_total_amount').html('');
        });

        $('.expenseGroup select#fld_group').livequery('change', function(){
            var expense_group_id = $(this).val();
            Util.showProgressInd();

            //alert(expense_group_id);
            var url = 'index.php?module=payroll_expense&_spAction=expSubgroupByGroupJSON&showHTML=0';
            $.get(url, {expense_group_id: expense_group_id}, function (data) {
                $("input[name='amount']").val('');
                $('#fld_sub_group').cp_loadSelect(data);
                Util.hideProgressInd();
            }, 'json');
        });

        $('.incomeGroup select#fld_group').livequery('change', function(){
            var income_group_id = $(this).val();
            var site_name = $("#fld_site_name_hidden").val();
            Util.showProgressInd();

            //alert(income_group_id);
            var url = 'index.php?module=payroll_expense&_spAction=incomeSubgroupByGroupJSON&showHTML=0';
            $.get(url, {income_group_id: income_group_id}, function (data) {
                $("input[name='amount']").val('');
                $('#fld_sub_group').cp_loadSelect(data);
                Util.hideProgressInd();
            }, 'json');
        });

        $('.displayVisitRecords').livequery('click', function (e){
            $('.newListDisplay').hide();
            $('.defaultListDisplay').show();
            $('.cpSearch').hide();
        });

        $('input[name=new_company]').livequery('click', function (){
            var companyVal = $(this).val();
            if(companyVal == 1){
                $('.existingCompany').hide();
                $('.newCompany').show();
            } else {
                $('.existingCompany').show();
                $('.newCompany').hide();                
            }
        });

        /*$("input[name='existing_company_name']").livequery(function(){
            var titleObj = this;
            $(titleObj).autocomplete({
                 source : 'index.php?module=payroll_expense&_spAction=searchSupplierName&showHTML=0'
                ,minLength : 2
                ,select: function(event, ui) {
                    var selectedObj = ui.item;
                    var supplier_id = selectedObj.id
                    //alert (company_id);
                    $(this).after("<input type='hidden' name='supplier_id' value=" + supplier_id + ">");
                }
            });
        });*/

        $("input[name='existing_company_name']").livequery(function(){
            var titleObj = this;
            $(titleObj).autocomplete({
                source: function(request, response) {
                    $.ajax({
                      url: 'index.php?module=payroll_expense&_spAction=searchSupplierName&showHTML=0',
                      dataType: "json",
                      data: request,                    
                      success: function (data) {
                        // No matching result
                        if (data.length == 0) {
                            alert('This company name does not exist');
                            $('input[name=new_company][value=1]').prop('checked',true);
                            var existing_company_name = $("input[name='existing_company_name']").val();
                            $('input[name=existing_company_name]').val("");
                            $('input[name=new_company_name]').val(existing_company_name);
                            $('.existingCompany').hide();
                            $('.newCompany').show();
                            response("");
                        }

                        else {
                          response(data);
                        }

                      }});
                },
                minLength : 2,
                selectFirst: true,
                autoFocus: true,
                select: function(event, ui) {
                    var selectedObj            = ui.item;
                    var supplier_id = selectedObj.id

                    $(this).after("<input type='hidden' name='supplier_id' value=" + supplier_id + ">");
                    //$('input[name=bmi]').val(bmi);
                }
            });
        });
        /*$(".createExpenseSaveButton").livequery("click", function (e){
            Util.showProgressInd();
            var url = 'index.php?module=payroll_expense&_spAction=add&showHTML=0';
            $.get(url, function(){
                Util.hideProgressInd();
            });
        });*/


       /* $('.m-payroll_expense.v-list #fld_description').focus(function () {
            $(this).animate({ height: "4em" }, 500);
        });*/

        $('input[name=amount]').livequery('change', function(){
            var amount = $(this).val();
            var serviceCharge = $('form.cpJqForm input[name=service_charge]').val();

            var formType = $("form.cpJqForm").attr("name");            
            if(formType == "edit") {
                var mainType = $("form.cpJqForm .row_type div.txt").html();
            } else {
                var mainType = $("form.cpJqForm select[name=type]").val();
            }

            var gstval    = $('input[name=gst]').attr('checked') ? 'checked' : '';
            var gst = gstval == 'checked' ? 1 : 0;

            var url = 'index.php?module=payroll_expense&_spAction=calculateTotalAmtWithServiceCharge&showHTML=0';
            $.get(url, {serviceCharge: serviceCharge, amount: amount, gst:gst}, function(html){
                $('#fld_total_amount').html(html);
            });
        });

        $('input[name=service_charge]').livequery('change', function(){
            var serviceCharge = $(this).val();
            var amount = $('input[name=amount]').val();

            var gstval    = $('input[name=gst]').attr('checked') ? 'checked' : '';
            var gst = gstval == 'checked' ? 1 : 0;

            var url = 'index.php?module=payroll_expense&_spAction=calculateTotalAmtWithServiceCharge&showHTML=0';
            $.get(url, {serviceCharge: serviceCharge, amount: amount, gst:gst}, function(html){
                $('#fld_total_amount').html(html);
            });
        });

        $('input[name=gst]').livequery('change', function(){
            var formType = $("form.cpJqForm").attr("name");            
            if(formType == "edit") {
                var typeVal = $("form.cpJqForm .row_type div.txt").html();
            } else {
                var typeVal = $("form.cpJqForm select[name=type]").val();
            }
            var gst = $(this).val();
            var amount = $('input[name=amount]').val();
            var serviceCharge = $('form.cpJqForm input[name=service_charge]').val();

            var url = 'index.php?module=payroll_expense&_spAction=calculateTotalAmtWithServiceCharge&showHTML=0';
            $.get(url, {serviceCharge: serviceCharge, amount: amount, gst:gst}, function(html){
                $('#fld_total_amount').html(html);
            });
        });

        $('.row_sub_group select#fld_sub_group').livequery('change', function(){
            var subGroup = $(this).val();

            var formType = $("form.cpJqForm").attr("name");            
            if(formType == "edit") {
                var typeVal = $("form.cpJqForm .row_type div.txt").html();
            } else {
                var typeVal = $("form.cpJqForm select[name=type]").val();
            }
            var groupVal = $("#fld_group").val();

            if(typeVal == 'Income'){
                $('.expensePaymentMode').hide();

                if(typeVal == 'Income' && groupVal == '2' && subGroup == '4'){ // REVENUE && SALES
                    $('.invoiceFields').show();
                    $('.invoiceFieldsOther').hide();
                    $('.incomeFields').show();
                    $('.expenseFields').hide();                
                    $('.expenseSubGroup56').hide();
                    $('.expenseSubGroup1112').hide();
                } else {
                    $('.invoiceFieldsOther').show();
                    $('.incomeFields').hide();
                    $('.invoiceFields').hide();
                    $('.expenseFields').hide();
                    $('.expenseSubGroup56').hide();
                    $('.expenseSubGroup1112').hide();
                }
            }

            if(typeVal == 'Expense'){
                var amount = $('input[name=amount]').val();
                var serviceCharge = $('form.cpJqForm input[name=service_charge]').val();
                var groupVal = $("form.cpJqForm select[name=group]").val();
                var subGroup = $("form.cpJqForm select[name=sub_group]").val();

                //var gst = $('input[name=gst]').val();

                $('.expensePaymentMode').show();
                var gstval    = $('input[name=gst]').attr('checked') ? 'checked' : '';
                var gst = gstval == 'checked' ? 1 : 0;

                $('.expenseFields').show();
                if(typeVal == 'Expense' && groupVal == '1'){
                    //$('.expenseFields').show();
                    $('.expenseSubGroupSupplier').show();
                    $('.expenseSubGroup56').hide();
                    $('.expenseSubGroup1112').hide();
                    $('.expenseSubGroup56Supplier').hide();
                    $('.invoiceFields').hide();
                    $('.incomeFields').hide();
                    $('.invoiceFieldsOther').hide();
                    $('.serviceChargeField').hide();
                    $('.expensePaymentMode').hide();

                    if (subGroup == '5' || subGroup == '6') {
                        $('.expenseSubGroup56').show();
                        $('.expenseSubGroup56Supplier').show();
                        $('.expenseSubGroupSupplier').hide();
                        $('.expenseSubGroup1112').hide();
                        $('.expensePaymentMode').show();
                    }

                    var url = 'index.php?module=payroll_expense&_spAction=calculateTotalAmt&showHTML=0';
                    $.get(url, {amount: amount, gst:gst}, function(html){
                        $('#fld_total_amount').html(html);
                    });
                } else if (typeVal == 'Expense' && groupVal == '2' && subGroup == '12') {
                    $('#frmNew .row_service_charge').removeClass('hideme');
                    $('.serviceChargeField').show();
                    $('.expenseSubGroup56').show();
                    //$('.expenseFields').hide();                
                    //$('.expenseSubGroup56').hide();
                    $('.invoiceFields').hide();
                    $('.invoiceFieldsOther').hide();
                    $('.incomeFields').hide();                
                    $('.expenseSubGroupSupplier').hide();
                    //$('.expenseSubGroup1112').show();

                    var url = 'index.php?module=payroll_expense&_spAction=calculateTotalAmtWithServiceCharge&showHTML=0';
                    $.get(url, {serviceCharge: serviceCharge, amount: amount, gst:gst}, function(html){
                        $('#fld_total_amount').html(html);
                    });
                } else {
                    $('.expenseSubGroupSupplier').hide();
                    $('.expenseSubGroup56').show();
                    if (typeVal == 'Expense' && groupVal == '2' && subGroup == '11') {
                        //$('.expenseFields').hide();
                        //$('.expenseSubGroup56').hide();
                        //$('.expenseSubGroup1112').show();
                        $('.invoiceFields').hide();
                        $('.incomeFields').hide();
                        $('.invoiceFieldsOther').hide();
                        $('.serviceChargeField').hide();
                    } else if (typeVal == 'Expense' && groupVal == '2' && subGroup == '22') {
                        //$('.expenseFields').hide();
                        //$('.expenseSubGroup56').hide();
                        //$('.expenseSubGroup1112').show();
                        $('.invoiceFields').hide();
                        $('.incomeFields').hide();
                        $('.invoiceFieldsOther').hide();
                        $('.serviceChargeField').hide();
                    } else {
                        //$('.expenseFields').hide();
                        //$('.expenseSubGroup56').hide();
                        $('.expenseSubGroup1112').hide();
                        $('.invoiceFields').hide();
                        $('.incomeFields').hide();
                        $('.invoiceFieldsOther').hide();
                        $('.serviceChargeField').hide();
                    }

                    var url = 'index.php?module=payroll_expense&_spAction=calculateTotalAmt&showHTML=0';
                    $.get(url, {amount: amount, gst:gst}, function(html){
                        $('#fld_total_amount').html(html);
                    });
                }
            }
        });

        $('#generateReceipt').livequery('click', function (e){
            var title = "Create Receipt";
            e.preventDefault();

            $("select[name='mode_of_payment']").livequery('change', function (e){
                cpm.payroll.expense.populatePaymentMode.call(this);
            });

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Receipt created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        window.location.reload(true);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 400, 400, expObj);
        });

        $('#editReceipt').livequery('click', function (e){
            var title = "Edit Receipt";
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Receipt updated successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        window.location.reload(true);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 400, 400, expObj);
        });

        $("#paymentPortalForm select[name='mode_of_payment']").livequery('change', function (){
            var paymentMode = $(this).val();
            //alert(paymentMode);
            if (paymentMode == 'Cheque') {
                Util.showProgressInd();
                $('#paymentPortalForm .row_cheque_no').removeClass('hideme');
                $('#paymentPortalForm .row_cheque_date').removeClass('hideme');
                $('#paymentPortalForm .row_bank_name').removeClass('hideme');
                Util.hideProgressInd();
            } else {
                Util.showProgressInd();
                $('#paymentPortalForm .row_cheque_no').addClass('hideme');
                $('#paymentPortalForm .row_cheque_date').addClass('hideme');
                $('#paymentPortalForm .row_bank_name').addClass('hideme');
                Util.hideProgressInd();
            }
        });
        $('.generatePayment').livequery('click', function (e){
            var title = "Create Payment";
            e.preventDefault();

            $("select[name='mode_of_payment']").livequery('change', function (e){
                cpm.payroll.expense.populatePaymentMode.call(this);
            });

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Payment created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        window.location.reload(true);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'paymentPortalForm', title, 400, 400, expObj);
        });

        $('.generatePaymentNew').livequery('click', function (e){
            var title = "Create Payment";
            e.preventDefault();

            $("select[name='mode_of_payment']").livequery('change', function (e){
                cpm.payroll.expense.populatePaymentMode.call(this);
            });

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Payment created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        //window.location.reload(true);
                        cpm.payroll.expense.reloadNewListDisplay();
                    });
                }
            }
            Util.openFormInDialog.call(this, 'paymentPortalForm', title, 400, 400, expObj);
        });

        $('.generatePaymentList').livequery('click', function (e){
            var title = "Create Payment";
            e.preventDefault();

            $("select[name='mode_of_payment']").livequery('change', function (e){
                cpm.payroll.expense.populatePaymentMode.call(this);
            });

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Payment created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        window.location.reload(true);
                        //cpm.payroll.expense.reloadListDisplay();
                    });
                }
            }
            Util.openFormInDialog.call(this, 'paymentPortalForm', title, 400, 400, expObj);
        });

       $('.viewPayment').live('click', function (e){
            e.preventDefault();
            var expObj = {
                beforeCloseFn: function(){
                    Util.closeAllDialogs();
                }
            }
            Util.openDialogForLink.call(this, 'Payment History', 500, 400, expObj);
        });
    },
    reloadNewListDisplay: function(){
        var url = 'index.php?module=payroll_expense&_spAction=newList&showHTML=0';
        $.get(url, function(html){
            $('.defaultListDisplay').html(html);
            Util.hideProgressInd();
        });
    },
    reloadListDisplay: function(){
        var url = 'index.php?module=payroll_expense&_spAction=list&showHTML=0';
        $.get(url, function(html){
            $('.newListDisplay').html(html);
            Util.hideProgressInd();
        });
    },
}

cpm.payroll.expense.populatePaymentMode = function(){
    var paymentMode = $(this).val();
    if (paymentMode == 'Cheque') {
        Util.showProgressInd();
        $('#frmNew .row_cheque_no').removeClass('hideme');
        $('#frmNew .row_issued_date').removeClass('hideme');
        $('#frmNew .row_bank').removeClass('hideme');
        Util.hideProgressInd();
    } else {
        Util.showProgressInd();
        $('#frmNew .row_cheque_no').addClass('hideme');
        $('#frmNew .row_issued_date').addClass('hideme');
        $('#frmNew .row_bank').addClass('hideme');
        Util.hideProgressInd();
    }
}
