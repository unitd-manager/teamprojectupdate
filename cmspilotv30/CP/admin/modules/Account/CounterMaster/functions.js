Util.createCPObject('cpm.account.counterMaster');

cpm.account.counterMaster = {
    baseCurrDecimals: 2,
    debit_base_total: 0,
    credit_base_total: 0,
    difference: 0,

    init: function(){
        cpm.account.counterMaster.cpAction = null;

        $('#actBtn_counterSell').click(cpm.account.counterMaster.getCounterSell);
        $('#actBtn_counterBuy').click(cpm.account.counterMaster.getCounterBuy);
        $('#bodyList .edit a').click(cpm.account.counterMaster.getCounterEdit);
        $('#bodyList .detail a').click(cpm.account.counterMaster.getCounterDetail);

        //counter entry form
        $('#entry.mainFlds input')
        .live('keyup', cpm.account.counterMaster.onEnterPressMainFlds);

        $('#entry #actBtn_saveClose')
        .live('click', cpm.account.counterMaster.saveCounterPre);
        $('#entry #actBtn_savePrint')
        .live('click', cpm.account.counterMaster.saveCounterPrintPre);
        $('#entry #actBtn_print')
        .live('click', cpm.account.counterMaster.printCounter);

        //save and print
        var params = $.deparam.querystring();
        if (params.print == 1) {
            cpm.account.counterMaster.printCounter(params.journal_master_id);
        }
        

        $('#entry #actBtn_cancelCounter')
        .live('click', cpm.account.counterMaster.cancelCounterEntry);

        $('#entry .fld-account').live('change', cpm.account.counterMaster.getExchangeRate);
        $('#entry .fld-amount, #entry .fld-exch_rate')
        .live('change', cpm.account.counterMaster.calculateBaseCurrencyAmount);

        $('#entry .delete-small')
        .live('click', cpm.account.counterMaster.deleteRow);

        $('#entry .new-row-div a')
        .live('click', cpm.account.counterMaster.newBlankRow);

        $('#entry #fld_entry_date').focus().select();

        $('.fld-non-editable')
        .live('focus', function() {$(this).blur()});
    },

    getCounterSell: function(e){
        e.preventDefault();

        var url = 'index.php?module=account_counterMaster&_spAction=new&_action=new&subAction=sell'
                + '&showHTML=0';
        var exp = {
            url: url
            ,afterOpen: function(){cpm.account.counterMaster.cpAction = $('#c_action').val();}
        };
        Util.openDialogForLink('Sell',  900, 500, 0, exp);
    },

    getCounterBuy: function(e){
        e.preventDefault();

        var url = 'index.php?module=account_counterMaster&_spAction=new&_action=new&subAction=buy'
                + '&showHTML=0';
        var exp = {
            url: url
            ,afterOpen: function(){cpm.account.counterMaster.cpAction = $('#c_action').val();}
        };
        Util.openDialogForLink('Buy',  900, 500, 0, exp);
    },

    getCounterEdit: function(e){
        e.preventDefault();

        var rowId = $(this).parents('tr').attr('id'); //listRow__3370
        var journal_master_id = rowId.split('__')[1];

        var url = 'index.php?module=account_counterMaster&_spAction=edit&_action=edit'
                + '&journal_master_id=' + journal_master_id
                + '&showHTML=0';
        var exp = {
            url: url
            ,afterOpen: function(){cpm.account.counterMaster.cpAction = $('#c_action').val();}
        };
        Util.openDialogForLink('Edit', 900, 500, 0, exp);
    },

    getCounterDetail: function(e){
        e.preventDefault();

        var rowId = $(this).parents('tr').attr('id'); //listRow__3370
        var journal_master_id = rowId.split('__')[1];

        var url = 'index.php?module=account_counterMaster&_spAction=detail&_action=detail'
                + '&journal_master_id=' + journal_master_id
                + '&showHTML=0';
        var exp = {
            url: url
        };
        Util.openDialogForLink('Detail',  900, 500, 0, exp);
    },

    getExchangeRate: function(){
        var accHeadObj = $(this);
        var acc_head_id = accHeadObj.val();

        Util.showProgressInd2();
        var url = 'index.php?module=account_currencyConvert&_spAction=exchangeRate&showHTML=0'
                + '&acc_head_id=' + acc_head_id
                + '&rateFor=' + cpm.account.counterMaster.cpAction;
        $.get(url, function(exch_rate) {
            var row = accHeadObj.parents('.row');
            $('.fld-exch_rate', row).val(exch_rate);
            cpm.account.counterMaster.calculateBaseCurrencyAmount.call(accHeadObj);
            Util.hideProgressInd2();
        });
    },

    calculateBaseCurrencyAmount: function(){
        var row = $(this).parents('.row');
        var amount = $(row).find('.fld-amount').val();
        var exch_rate_to_base = $(row).find('.fld-exch_rate').val();
        var total = amount * exch_rate_to_base;
        $(row).find('.fld-amount_base').val(total);
    },

    bindShortcutKeysList: function(){
        $(window).jkey('n', false, function () {
            var url = $('#actBtn_new').attr('href');
            document.location.href = url;
        });
    },

    bindShortcutKeysEntryScreen: function(){
        $(window).jkey('n', function () {
            var url = $('#actBtn_new').attr('href');
            document.location.href = url;
        });
    },

    onEnterPressMainFlds: function(e) {
        if (e.keyCode == 13) {
            var fldId = $(this).attr('id');
            if (fldId == 'fld_counter_setup_id') {
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

    validateCounter: function(){
		var entry_date = $('#entry #fld_entry_date').val();
		var amount = $('#entry #fld_amount').val();
		var exch_rate_to_base = $('#entry #fld_exch_rate_to_base').val();

		entry_date = $.trim(entry_date);
		amount = $.trim(amount);
		exch_rate_to_base = $.trim(exch_rate_to_base);

        var errorMsg = '';
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
                errorMessage = "Please input values for currency/amount/exchange_rate properly";
                cpm.account.counterMaster.displayError(errorMessage);
                return returnVal = false;
            }
        });

        return returnVal;
    },

    cancelCounterEntry: function(e){
        $('#dialog').dialog('destroy');
        $('#dialog').remove();
    },

    saveCounterPre: function(e){
        e.preventDefault();
        cpm.account.counterMaster.saveCounter();
    },

    saveCounter: function(print){
        if (!cpm.account.counterMaster.validateCounter()) {
            return;
        }

        Util.showProgressInd('Saving...');
        Util.clearPrepopulatedTextbox('#entry');
        var url = 'index.php?module=account_counterMaster&_spAction=saveCounter&showHTML=0';
		var data = $('#entry input, #entry select').serialize();
        $.post(url, data, function(json){
            Util.hideProgressInd();
			if (json.status == 'error') {
				cpm.account.counterMaster.displayError(json.errorMsg);
                return false;
			} else {
                if (print != undefined) {
                    cpm.account.counterMaster.saveCounterPrint(json.journal_master_id);
                } else {
                    $('#dialog').dialog('destroy');
                    $('#dialog').remove();
                    document.location = document.location;
                }
			}
        }, 'json');
    },

    saveCounterPrintPre: function(){
        if (!cpm.account.counterMaster.validateCounter()) {
            return;
        }
        cpm.account.counterMaster.saveCounter(true);
    },

    saveCounterPrint: function(journal_master_id){
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

    printCounter: function(journal_master_id){
        if (typeof journal_master_id == 'undefined') {
            journal_master_id = $('#journal_master_id').val();
        }
        var url = 'index.php?module=account_counterMaster&_spAction=printCounter'
                + '&showHTML=0'
                + '&journal_master_id=' + journal_master_id;
        document.location = url;
    },

    displayError: function(errorMsg){
		// $('#cEntryErrorBox').html(errorMsg).fadeIn();
		// $('#cEntryErrorBox').oneTime(4000, 'errorBox', cpm.account.counterMaster.hideError);
        Util.alert(errorMsg);
	},

    hideError: function(){
		$('#cEntryErrorBox').fadeOut();
	},

    newBlankRow: function(e) {
        e.preventDefault();

        var rowDiv = $('.jbody .row').last();
        var newRow = Util.getOuterHTML(rowDiv);

        newRow = $(newRow).insertBefore('#entry .jbody .new-row-div');

        $(newRow).find('input, select')
        .each(function() {
            //field name like: acc_head-1
            var fld2 = cpm.account.lib.getNextFieldIndexArr($(this).attr('name'));
            $(this).attr('name', fld2.newName);
//            $(this).attr('value', '');
        });
        $(newRow).find('select').focus();
    },

    deleteRow: function() {
        if ($('#entry .jbody .row').length > 1) {
            $(this).parents('div.row').remove();
        } else {
            var row = $(this).parents('div.row');
            cpm.account.counterMaster.clearRow(row);
        }
    },

    clearRow: function(row){
		$(row).find('input').each(function() {
            $(this).val('');
        });
    }

}
