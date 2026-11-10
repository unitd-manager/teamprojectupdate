Util.createCPObject('cpm.payroll.payrollManagement');

cpm.payroll.payrollManagement.init = function(){
    $(window).load(function(){
        $('.m-payroll_payrollManagement .GenerateRecords').livequery('click', function(){
            msg = "Do you wish to Generate Payslip?";
            
            if (!confirm(msg)){
                return false;
            } else {
                var current_Month = $(this).attr('current_Month');
                var current_Year = $(this).attr('current_Year');
                var url = 'index.php?module=payroll_payrollManagement&_spAction=updateRecords&showHTML=0';
                var record_count = $(this).attr('record_count');
                if(record_count == 0){
                    $.get(url, {current_Month: current_Month, current_Year: current_Year}, function(json){
                        window.location.reload(true);
                    });
                }else{
                    Util.alert('Records Already Created');
                }
            }
        });

        /*
        $('.m-payroll_payrollManagement .loanBreakup').livequery('click', function(e){
            var payroll_management_id = $(this).attr('payroll_management_id');
            alert(payroll_management_id);
        });
        */
    });

    /*Earnings: on key change popualte Amount and Total*/
    $('.m-payroll_payrollManagement #fld_ot_hours').livequery('keyup', function(){
        populateAmount.populateEarningsAmount();
    });

    $('.m-payroll_payrollManagement #fld_commission').livequery('keyup', function(){
        populateAmount.populateEarningsAmount();
    });

    $('.m-payroll_payrollManagement #fld_monthly_allowance').livequery('keyup', function(){
        populateAmount.populateEarningsAmount();
    });

    $('.m-payroll_payrollManagement #fld_additional_wages').livequery('keyup', function(){
        populateAmount.populateEarningsAmount();
    });

    /*Deductions: on key change popualte Amount and Total*/
    $('.m-payroll_payrollManagement #fld_sdl').livequery('keyup', function(){
        populateAmount.populateDeductionAmount();
    });

    $('.m-payroll_payrollManagement #fld_loan_amount').livequery('keyup', function(){
        populateAmount.populateDeductionAmount();
    });

    $('.m-payroll_payrollManagement #fld_income_tax_amount').livequery('keyup', function(){
        populateAmount.populateDeductionAmount();
    });

    $('.m-payroll_payrollManagement #fld_pay_cdac').livequery('keyup', function(){
        populateAmount.populateDeductionAmount();
    });

    $('.m-payroll_payrollManagement #fld_pay_sinda').livequery('keyup', function(){
        populateAmount.populateDeductionAmount();
    });

    $('.m-payroll_payrollManagement #fld_pay_mbmf').livequery('keyup', function(){
        populateAmount.populateDeductionAmount();
    });

    $('.m-payroll_payrollManagement #fld_pay_eucf').livequery('keyup', function(){
        populateAmount.populateDeductionAmount();
    });

    $('.m-payroll_payrollManagement #fld_loan_deduction').livequery('keyup', function(){
        populateAmount.populateDeductionAmount();
    });

    $('.m-payroll_payrollManagement .loanBreakup').livequery('click', function (e){
        var title = "Loan payment history";

        e.preventDefault();
        var expObj = {
            validate: true
           ,callbackOnSuccess: function(){
                var msg = 'Updated successfully';
                Util.alert(msg, function(){
                    Util.closeAllDialogs();
                    window.location.reload(true);
                });
            }
        }
        Util.openFormInDialog.call(this, 'portalForm', title, 1100,500, expObj);
    });

    $("a.printPayslipForAllLink").livequery('click', function (e){
        var title = "Generate All Payslip";
        var url   = "index.php?module=payroll_payrollManagement&_spAction=printPayslipForm&showHTML=0"; 
        var expObj = {
            url: url
           ,validate: true
           ,submitBtnText: 'Submit'
           ,cancelBtnText: 'Close'
           ,callbackOnSuccess: function(){
                Util.showProgressInd();
                var year        = $('#fld_payroll_year').val();
                var month       = $('#fld_payroll_Month').val();
                //month           = populateAmount.pad2(month);
                var convertUrl = "index.php?_topRm=payroll&module=payroll_payrollManagement&_spAction=printPaySlipForAllPdf&payroll_year=" + year + "&payroll_month=" + month;
                Util.closeAllDialogs();
                Util.hideProgressInd();
                window.open(convertUrl, 'blank');
                //document.location = convertUrl;
            }
        };
        Util.openFormInDialog.call('', 'portalFormPrintPayslip', title, 525, 'auto', expObj);
    });

    $("a.GenerateTerminatingRecords").livequery('click', function (e){
        var title = "Generate Terminating Payslips";
        var url   = "index.php?module=payroll_payrollManagement&_spAction=terminatingEmployeeListForm&showHTML=0"; 
        var expObj = {
            url: url
           ,validate: true
           ,submitBtnText: 'Submit'
           ,cancelBtnText: 'Close'
           ,callbackOnSuccess: function(){
                Util.showProgressInd();
                Util.closeAllDialogs();
                Util.hideProgressInd();
                alert('Terminating Payslips generated successfully.');
                window.location.reload(true);
            }
        };
        Util.openFormInDialog.call('', 'portalFormTerminatingPayslip', title, 750, 'auto', expObj);
    });

    $(".m-payroll_payrollManagement .row_status select[name='status']").livequery('change', function(e){
        var status = $(this).val();
        $('.m-payroll_payrollManagement .passType').removeClass('displayNone');
        if (status == 'Paid') {
            $('.m-payroll_payrollManagement .row_paid_date').show();
        }else{
            $('.m-payroll_payrollManagement .row_paid_date').hide();
            $('.m-payroll_payrollManagement .passType').addClass('displayNone');
        }
    });

    $("a.sendPayslipPDFToEmployee").livequery('click', function (e){
        var employeeNames = "";
        Util.showProgressInd();
        var url = "index.php?module=payroll_payrollManagement&_spAction=ApprovedEmployeesName&showHTML=0"; 
        $.get(url, function(html){
            if(html != "") {
                Util.hideProgressInd();
                var msg = "Do you want to sent payslip(s) for the following employees?\n"+html;
                if (confirm (msg)){
                    Util.showProgressInd();
                    var urlMail = "index.php?module=payroll_payrollManagement&_spAction=sentPaySlipToEmployee&showHTML=0";
                    $.get(urlMail, function(){
                        var mgsalert2 = 'Email Sent Successfully';
                        var n = noty({
                            text: mgsalert2,
                            type: 'confirm',
                            dismissQueue: true,
                            layout: 'topCenter',
                            theme: 'defaultTheme',
                            timeout: 5000,
                        });
                        
                        Util.hideProgressInd();
                    });
                }
            } else {
                Util.hideProgressInd();
                Util.alert("There is no approved employee(s)");
            }
        });
    });

    var populateAmount = {
        populateDeductionAmount: function(){
            var totalAmount     = 0;
            var netTotal        = 0;
            var sdl             = $('.m-payroll_payrollManagement #fld_sdl').val();
            var cpf             = $('.m-payroll_payrollManagement .cpfEmployee').html();
            var loanAmount      = $('.m-payroll_payrollManagement #fld_loan_amount').val();
            var incomeTaxAmount = $('.m-payroll_payrollManagement #fld_income_tax_amount').val();
            var pay_cdac        = $('.m-payroll_payrollManagement #fld_pay_cdac').val();
            var pay_sinda       = $('.m-payroll_payrollManagement #fld_pay_sinda').val();
            var pay_mbmf        = $('.m-payroll_payrollManagement #fld_pay_mbmf').val();
            var pay_eucf        = $('.m-payroll_payrollManagement #fld_pay_eucf').val();
            var loan_deduction  = $('.m-payroll_payrollManagement #fld_loan_deduction').val();

            if(sdl == undefined || sdl == ''){
               sdl = parseInt(0);
            }

            if(cpf == undefined || cpf == ''){
               cpf = parseInt(0);
            }

            if(loanAmount == undefined || loanAmount == ''){
               loanAmount = parseInt(0);
            }

            if(incomeTaxAmount == undefined || incomeTaxAmount == ''){
               incomeTaxAmount = parseInt(0);
            }

            if(pay_cdac == undefined || pay_cdac == ''){
               pay_cdac = parseInt(0);
            }

            if(pay_sinda == undefined || pay_sinda == ''){
               pay_sinda = parseInt(0);
            }

            if(pay_mbmf == undefined || pay_mbmf == ''){
               pay_mbmf = parseInt(0);
            }

            if(pay_eucf == undefined || pay_eucf == ''){
               pay_eucf = parseInt(0);
            }

            if(loan_deduction == undefined || loan_deduction == ''){
               loan_deduction = parseInt(0);
            }

            totalAmount = parseFloat(parseInt(cpf) + parseInt(sdl) + parseInt(loanAmount) + parseInt(incomeTaxAmount) + parseInt(pay_cdac) + parseInt(pay_sinda) + parseInt(pay_mbmf) + parseInt(pay_eucf) + parseInt(loan_deduction));

            $('.m-payroll_payrollManagement .totalDeduction').html(totalAmount.toFixed(2));

            var totalDeduction = $('.m-payroll_payrollManagement .totalDeduction').html();
            var totalEarnings  = $('.m-payroll_payrollManagement .grossPay').html();

            if(totalDeduction == undefined || totalDeduction == ''){
               totalDeduction = parseInt(0);
            }

            if(totalEarnings == undefined || totalEarnings == ''){
               totalEarnings = parseInt(0);
            }

            netTotal  =   parseFloat(parseInt(totalEarnings) - parseInt(totalDeduction));
            $('.m-payroll_payrollManagement .netTotalPayrollMgmt').html(netTotal.toFixed(2));
        },

        populateEarningsAmount: function(){
            var ot_amount          = 0;
            var totalAmount        = 0;
            var netTotal           = 0;
            var hours              = $('.m-payroll_payrollManagement #fld_ot_hours').val();
            var payRate            = $('.m-payroll_payrollManagement .otPayRate').html();
            var basicPay           = $('.m-payroll_payrollManagement .basicPayRate').html();
            var commission         = $('.m-payroll_payrollManagement input[name=commission]').val();
            var additional_wages   = $('.m-payroll_payrollManagement input[name=additional_wages]').val();
            var monthly_allowance  = $('.m-payroll_payrollManagement input[name=monthly_allowance]').val();
            
            ot_amount = hours * payRate;

            if(basicPay == undefined || basicPay == ''){
               basicPay = parseInt(0);
            }

            if(ot_amount == undefined || ot_amount == ''){
               ot_amount = parseInt(0);
            }

            if(commission == undefined || commission == ''){
               commission = parseInt(0);
            }

            if(monthly_allowance == undefined || monthly_allowance == ''){
               monthly_allowance = parseInt(0);
            }

            if(additional_wages == undefined || additional_wages == ''){
               additional_wages = parseInt(0);
            }

            totalAmount = parseFloat(parseInt(basicPay) + parseInt(ot_amount) + parseInt(commission) + parseInt(monthly_allowance) + parseInt(additional_wages));

            //$('.m-payroll_payrollManagement input[id=fld_ot_amount]').val(ot_amount.toFixed(2));
            $('.m-payroll_payrollManagement .ot_amount').html(ot_amount.toFixed(2));
            $('.m-payroll_payrollManagement .grossPay').html(totalAmount.toFixed(2));

            var totalDeduction = $('.m-payroll_payrollManagement .totalDeduction').html();
            var totalEarnings  = $('.m-payroll_payrollManagement .grossPay').html();
            
            if(totalDeduction == undefined || totalDeduction == ''){
               totalDeduction = parseInt(0);
            }

            if(totalEarnings == undefined || totalEarnings == ''){
               totalEarnings = parseInt(0);
            }

            netTotal  =   parseFloat(parseInt(totalEarnings) - parseInt(totalDeduction));
            $('.m-payroll_payrollManagement .netTotalPayrollMgmt').html(netTotal.toFixed(2));
        },

        pad2: function(number) {
            return (number < 10 ? '0' : '') + number
        }

    }

}

cpm.payroll.payrollManagement.mailSentRecordFromList = function(room, rowID, currentValue, reUploadRecord){
    if(reUploadRecord){
        reUpload = 1;
    } else {
        reUpload = 0;
    }

    var url = $('#scopeRootAlias').val() + "index.php?_spAction=mailSentRecordByID&showHTML=0";

    var cell = "#txt__payroll_payrollManagement__" + rowID

    $(cell).html('processing');
    var data = {
         record_id: rowID
        ,room: room
        ,currentValue: currentValue
        ,reUpload: reUpload
    };
    $.post(url, data, function (data) {
        $(cell).html(data);
    });
}