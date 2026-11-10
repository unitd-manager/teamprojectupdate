Util.createCPObject('cpm.account.cashMaster');

cpm.account.cashMaster = {
    baseCurrDecimals: 2,
    debit_base_total: 0,
    credit_base_total: 0,
    difference: 0,

    init: function(){
        cpm.account.cashMaster.cpAction = null;

        $('#actBtn_cashPayment').click(cpm.account.cashMaster.getCashPayment);
        $('#actBtn_cashReceipt').click(cpm.account.cashMaster.getCashReceipt);
        $('#bodyList .edit a').click(cpm.account.cashMaster.getCashEdit);
        $('#bodyList .detail a').click(cpm.account.cashMaster.getCashDetail);

        //cash entry form
        $('#entry.mainFlds input')
        .live('keyup', cpm.account.cashMaster.onEnterPressMainFlds);

        $('#entry #actBtn_saveClose')
        .live('click', cpm.account.cashMaster.saveCashPre);
        $('#entry #actBtn_savePrint')
        .live('click', cpm.account.cashMaster.saveCashPrintPre);
        $('#entry #actBtn_print')
        .live('click', cpm.account.cashMaster.printCash);

        //save and print
        var params = $.deparam.querystring();
        if (params.print == 1) {
            cpm.account.cashMaster.printCash(params.journal_master_id);
        }

        //entry form - cancel button
        $('#entry #actBtn_cancelCash')
        .live('click', cpm.account.cashMaster.cancelCashEntry);

        //customer dropdown selection
        $('#entry #fld_acc_head_id_customer').live('change', cpm.account.cashMaster.setCustomerCurr);

        //individual row stuff
        $('#entry .fld-account').live('change', cpm.account.cashMaster.getExchangeRate);
        $('#entry .fld-amount, #entry .fld-exch_rate')
        .live('change', function() {
            cpm.account.cashMaster.calculateBaseCurrencyAmount.call($(this));
            cpm.account.cashMaster.setBaseCurrencySum();
            cpm.account.cashMaster.calculateCustCurrSumExchRate
            .call($('.summary-row2 .fld-exch_rate_cust_curr'));
        });

        //sumary row stuff
        $('.fld-amount_sum_cust_curr,' +
          '.fld-exch_rate_cust_curr,' +
          '.fld-exch_rate_cust_curr_lc_to_fc')
        .live('change', cpm.account.cashMaster.calculateCustCurrSumExchRate);

        $('#entry .delete-small')
        .live('click', cpm.account.cashMaster.deleteRow);

        //new row button
        $('#entry .new-row-div a')
        .live('click', cpm.account.cashMaster.newBlankRow);

        $('#entry #fld_entry_date').focus().select();

        $('.fld-non-editable')
        .live('focus', function() {$(this).blur()});

        $('#fld_amount, .fld-amount_sum_cust_curr')
        .live('change', function() {cpm.account.cashMaster.setDifference();});
    },

    getCashPayment: function(e){
        e.preventDefault();

        var url = 'index.php?module=account_cashMaster&_spAction=new&_action=new&subAction=payment'
                + '&showHTML=0';
        var exp = {
            url: url
            ,afterOpen: function(){
                cpm.account.cashMaster.cpAction = $('#c_action2').val();
                cpm.account.cashMaster.setDifference();
            }
        };
        Util.openDialogForLink('Payment',  900, 600, 0, exp);
    },

    getCashReceipt: function(e){
        e.preventDefault();

        var url = 'index.php?module=account_cashMaster&_spAction=new&_action=new&subAction=receipt'
                + '&showHTML=0';
        var exp = {
            url: url
            ,afterOpen: function(){
                cpm.account.cashMaster.cpAction = $('#c_action2').val();
                cpm.account.cashMaster.setDifference();
            }
        };
        Util.openDialogForLink('Receipt',  900, 600, 0, exp);
    },

    getCashEdit: function(e){
        e.preventDefault();

        var rowId = $(this).parents('tr').attr('id'); //listRow__3370
        var journal_master_id = rowId.split('__')[1];

        var url = 'index.php?module=account_cashMaster&_spAction=edit&_action=edit'
                + '&journal_master_id=' + journal_master_id
                + '&showHTML=0';
        var exp = {
            url: url
            ,afterOpen: function(){cpm.account.cashMaster.cpAction = $('#c_action2').val();}
        };
        Util.openDialogForLink('Edit', 900, 500, 0, exp);
    },

    getCashDetail: function(e){
        e.preventDefault();

        var rowId = $(this).parents('tr').attr('id'); //listRow__3370
        var journal_master_id = rowId.split('__')[1];

        var url = 'index.php?module=account_cashMaster&_spAction=detail&_action=detail'
                + '&journal_master_id=' + journal_master_id
                + '&showHTML=0';
        var exp = {
            url: url
        };
        Util.openDialogForLink('Detail',  900, 600, 0, exp);
    },

    getExchangeRate: function(){
        var accHeadObj = $(this);
        var acc_head_id = accHeadObj.val();

        Util.showProgressInd2();
        var url = 'index.php?module=account_currencyConvert&_spAction=exchangeRate&showHTML=0'
                + '&acc_head_id=' + acc_head_id
                + '&rateFor=' + cpm.account.cashMaster.cpAction;
        $.get(url, function(exch_rate) {
            var row = accHeadObj.parents('.row');
            $('.fld-exch_rate', row).val(exch_rate);
            cpm.account.cashMaster.calculateBaseCurrencyAmount.call(accHeadObj);
            cpm.account.cashMaster.calculateCustCurrSumExchRate
            .call($('.summary-row2 .fld-exch_rate_cust_curr'));
            Util.hideProgressInd2();
        });
    },

    calculateBaseCurrencyAmount: function(){
        var row = $(this).parents('.row');
        var amount = $(row).find('.fld-amount').val();
        var exch_rate_to_base = $(row).find('.fld-exch_rate').val();
        var total = amount * exch_rate_to_base;
        $(row).find('.fld-amount_base').val(total);
        cpm.account.cashMaster.setBaseCurrencySum();
    },

    getBaseCurrencySum: function(){
        var sum = 0;
        $.each($('.jbody .row'), function(key, row) {
            var amt = parseFloat($('.fld-amount_base', row).val());
            sum += amt;
        });
        return sum;
    },

    setBaseCurrencySum: function(){
        var sum = cpm.account.cashMaster.getBaseCurrencySum();
        $('.fld-amount_base_sum').val(sum);
    },

    setCustomerCurr: function(){
        var custObj = $(this);
        var acc_head_id = custObj.val();
        var url = 'index.php?module=account_accHead&_spAction=accHeadDetails&showHTML=0'
                + '&acc_head_id=' + acc_head_id
                + '&rateFor=' + cpm.account.cashMaster.cpAction;
        Util.showProgressInd2();
        $.getJSON(url, function(json) {
            $('.summary-row2 .customer-curr').html(json.currency_code);
            $('.row_amount .fld-prefix').html(json.currency_code);
            $('.summary-row2 .fld-exch_rate_cust_curr').val(json.exch_rate);
            $('.summary-row2 .fld-exch_rate_cust_curr_lc_to_fc').val(json.exch_rate_lc_to_fc);

            cpm.account.cashMaster.calculateCustCurrSumExchRate
            .call($('.summary-row2 .fld-exch_rate_cust_curr'));
            Util.hideProgressInd2();
        });
    },

    calculateCustCurrSumExchRate: function(){
        var fldName = $(this).attr('name');
        var exch_rate_cust_curr_obj          = $('.summary-row2 .fld-exch_rate_cust_curr');
        var exch_rate_cust_curr_lc_to_fc_obj = $('.summary-row2 .fld-exch_rate_cust_curr_lc_to_fc');
        var amount_sum_cust_curr_obj = $('.summary-row2 .fld-amount_sum_cust_curr');

        var exch_rate_cust_curr          = exch_rate_cust_curr_obj.val();
        var exch_rate_cust_curr_lc_to_fc = exch_rate_cust_curr_lc_to_fc_obj.val();
        var amount_sum_cust_curr         = amount_sum_cust_curr_obj.val();

        var amount_base_sum = cpm.account.cashMaster.getBaseCurrencySum();

        //if currency amount changed then calculate rate
        if (fldName == 'amount_sum_cust_curr') {
            exch_rate_cust_curr_obj.val(amount_sum_cust_curr / amount_base_sum);
            exch_rate_cust_curr_lc_to_fc_obj.val(1 / exch_rate_cust_curr_obj.val());
        } else if (fldName == 'exch_rate_cust_curr') {
            amount_sum_cust_curr_obj.val(exch_rate_cust_curr * amount_base_sum);
            exch_rate_cust_curr_lc_to_fc_obj.val(1 / exch_rate_cust_curr);

        } else if (fldName == 'exch_rate_cust_curr_lc_to_fc') {
            amount_sum_cust_curr_obj.val(exch_rate_cust_curr * amount_base_sum);
            exch_rate_cust_curr_obj.val(1 / exch_rate_cust_curr_lc_to_fc);
        }
    },

    onEnterPressMainFlds: function(e) {
        if (e.keyCode == 13) {
            var fldId = $(this).attr('id');
            if (fldId == 'fld_cash_setup_id') {
                $('#entry #fld_entry_date').focus();
            } else if (fldId == 'fld_entry_date') {
                $('#entry #fld_acc_head_id').focus();
            } else if (fldId == 'fld_acc_head_id') {
                $('#entry #fld_amount').focus().select();
            } else if (fldId == 'fld_amount') {
                $('#entry #fld_exch_rate_to_base').focus().select();
            } else if (fldId == 'fld_exch_rate_to_base') {
                $('#entry #fld_narration').focus().select();
            } else {
                $('#entry #actBtn_saveContinue').focus();
            }

        }
    },

    validateCash: function(){
		var entry_date = $('#entry #fld_entry_date').val();

		entry_date = $.trim(entry_date);
        if (!entry_date) {
            errorMsg = 'Please enter entry date';
            Util.alert(errorMsg);
            return false;
        }

        var returnVal = true;
		$('#entry .jbody div.row')
        .each(function() {
            var row = $(this);
            var acc_head_id = $(row).find('.fld-account').val();
            var amount = $(row).find('.fld-amount').val();
            var exch_rate = $(row).find('.fld-exch_rate').val();
            var amount_base = $(row).find('.fld-amount_base').val();

            if (acc_head_id == '' || amount == '' || exch_rate == '' || amount_base == '') {
                errorMsg = "Please input values for currency/amount/exchange_rate properly";
                cpm.account.cashMaster.displayError(errorMsg);
                return returnVal = false;
            }
        });

        //exchange rate deviation
		var exch_rate_cust_curr = parseFloat($('#entry .fld-exch_rate_cust_curr').val());

		var exch_rate_cust_curr_system = parseFloat($('#fld_exch_rate_cust_curr_system').val());
		var exchRateDeviationLimit     = parseFloat($('#fld_exchRateDeviationLimit').val());

        var errorMsg = '';
		var deviationAllowed = exch_rate_cust_curr_system * (exchRateDeviationLimit/100);
		var deviationHigh = exch_rate_cust_curr_system + deviationAllowed;
		var deviationLow  = exch_rate_cust_curr_system - deviationAllowed;
        if (exch_rate_cust_curr > deviationHigh || exch_rate_cust_curr < deviationLow) {
            errorMsg = 'The exch rate deviation is exceeding more than allowed '
                     + exchRateDeviationLimit + '%. Are you sure to continue?';
            var result = confirm(errorMsg);
            return result;
        }

        return returnVal;
    },

    cancelCashEntry: function(e){
        $('#dialog').dialog('destroy');
        $('#dialog').remove();
    },

    saveCashPre: function(e){
        e.preventDefault();
        cpm.account.cashMaster.saveCash();
    },

    saveCash: function(print){
        if (!cpm.account.cashMaster.validateCash()) {
            return;
        }

        Util.showProgressInd('Saving...');
        Util.clearPrepopulatedTextbox('#entry');
        var url = 'index.php?module=account_cashMaster&_spAction=saveCash&showHTML=0';
		var data = $('#entry input, #entry select').serialize();
        $.post(url, data, function(json){
            Util.hideProgressInd();
			if (json.status == 'error') {
				cpm.account.cashMaster.displayError(json.errorMsg);
                return false;
			} else {
                if (print != undefined) {
                    cpm.account.cashMaster.saveCashPrint(json.journal_master_id);
                } else {
                    $('#dialog').dialog('destroy');
                    $('#dialog').remove();
                    document.location = document.location;
                }
			}
        }, 'json');
    },

    saveCashPrintPre: function(){
        if (!cpm.account.cashMaster.validateCash()) {
            return;
        }
        cpm.account.cashMaster.saveCash(true);
    },

    saveCashPrint: function(journal_master_id){
        if (journal_master_id) {
            $('#dialog').dialog('destroy');
            $('#dialog').remove();

            //get the current url as a json object
            var params = $.deparam.querystring();
            params.journal_master_id = journal_master_id;
            params.print = 1;
            var url = document.location.href;
            url = jQuery.param.querystring(url, params);
            document.location = url;
        }
    },

    printCash: function(journal_master_id){
        //journal_master_id is an event object
        if (typeof journal_master_id == 'object') {
            journal_master_id = $('#journal_master_id').val();
        }
        var url = 'index.php?module=account_cashMaster&_spAction=printCash'
                + '&showHTML=0'
                + '&journal_master_id=' + journal_master_id;
        document.location = url;
    },

    displayError: function(errorMsg){
		// $('#cEntryErrorBox').html(errorMsg).fadeIn();
		// $('#cEntryErrorBox').oneTime(4000, 'errorBox', cpm.account.cashMaster.hideError);
        Util.alert(errorMsg);
	},

    hideError: function(){
		$('#cEntryErrorBox').fadeOut();
	},

    newBlankRow: function(e) {
        e.preventDefault();

        var rowDiv = $('.jbody .row').last();
        var newRow = Util.getOuterHTML(rowDiv);

        newRow = $(newRow).insertAfter('#entry .jbody .row:last');

        $(newRow).find('input, select')
        .each(function() {
            //field name like: acc_head-1
            var fld2 = cpm.account.lib.getNextFieldIndexArr($(this).attr('name'));
            $(this).attr('name', fld2.newName);
            $(this).val('');
        });
        $(newRow).find('select').focus();
    },

    deleteRow: function() {
        if ($('#entry .jbody .row').length > 1) {
            $(this).parents('div.row').remove();
        } else {
            var row = $(this).parents('div.row');
            cpm.account.cashMaster.clearRow(row);
        }
    },

    clearRow: function(row){
		$(row).find('input').each(function() {
            $(this).val('');
        });
    },

    getDifference: function(){
		var amount_obj = $('#entry #fld_amount');
		var amount_cust_curr_obj = $('#entry .fld-amount_sum_cust_curr');

		var amount           = amount_obj.val();
		var amount_cust_curr = amount_cust_curr_obj.val();
		var difference = amount - amount_cust_curr;


        return difference;
    },

    setDifference: function(){

		var difference = cpm.account.cashMaster.getDifference();
		//difference = difference.toFixed(cpm.account.cashMaster.baseCurrDecimals);

		var difference_obj = $('#entry #difference .value');
		$(difference_obj).html(difference);

		if (difference != 0) {
			$(difference_obj).removeClass('green-highlight').addClass('red-highlight')
		} else {
			$(difference_obj).removeClass('red-highlight').addClass('green-highlight')
		}
    }

}
