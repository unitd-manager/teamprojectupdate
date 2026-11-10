Util.createCPObject('cpm.accountsg.journalMaster');

cpm.accountsg.journalMaster = {
    baseCurrDecimals: 2,
    debit_base_total: 0,
    credit_base_total: 0,
    difference: 0,
    init: function() {
        this.cpAction = $('#cpAction').val();
        if (this.cpAction == 'new' || this.cpAction == 'edit') {
            $('#entry #fld_entry_date').focus().select();
            cpm.accountsg.journalMaster.initializeAccountTypeAhead();
            $('#entry input').live('keyup', cpm.accountsg.journalMaster.onEnterPress);

            $('#entry .fld-debit, #entry .fld-credit')
                    .live('change', cpm.accountsg.journalMaster.clearDebitCredit);
            $('#entry .fld-debit, #entry .fld-credit')
                    .live('change', cpm.accountsg.journalMaster.setRowBgColor);

            $('#entry .fld-debit, #entry .fld-credit, #entry .fld-exch_rate')
                    .live('change', cpm.accountsg.journalMaster.updateRowValues);

            $('#entry .mainFlds input, #entry .mainFlds select')
                    .keyup(cpm.accountsg.journalMaster.onEnterPressMainFlds);

            $('#entry .delete-small')
                    .live('click', cpm.accountsg.journalMaster.deleteRow);

            $('#entry #actBtn_saveContinue, #entry #actBtn_saveJournal')
                    .click(cpm.accountsg.journalMaster.saveJournal);

        } else if (this.cpAction == 'list') {
            //this.bindShortcutKeysList();
        }


        Util.prepopulatedTextbox();
        //journal master ledger authorize
        $('a.ledger-auth').click(cpm.accountsg.journalMaster.ledgerAuthorize);
        //journal master ledger authorize
        $('a.ledger-pending').click(cpm.accountsg.journalMaster.ledgerPending);
    },
    initializeAccountTypeAhead: function() {
        $('#entry .fld-account').autocomplete({
            source: 'index.php?module=accountsg_accHead&_spAction=accountHeadsAsJSON&showHTML=0',
            select: function(event, ui) {
                cpm.accountsg.journalMaster.updateRowValues.call(this, ui);
            }
        });
    },
    bindShortcutKeysList: function() {
        $(window).jkey('n', false, function() {
            var url = $('#actBtn_new').attr('href');
            document.location.href = url;
        });
    },
    bindShortcutKeysEntryScreen: function() {
        $(window).jkey('n', function() {
            var url = $('#actBtn_new').attr('href');
            document.location.href = url;
        });
    },
    onEnterPressMainFlds: function(e) {
        if (e.keyCode == 13) {
            var fldId = $(this).attr('id');
            if (fldId == 'fld_entry_date') {
                $('#entry #fld_voucher_type').focus();
            } else if (fldId == 'fld_voucher_type') {
                $('#entry #fld_narration_main').focus().select();
            } else if (fldId == 'fld_narration_main') {
                $('#entry .row:eq(1) .fld-account').focus().select();
            }
        }
    },
    onEnterPress: function(e) {
        if (e.keyCode == 13) {
            var fld = cpm.accountsg.lib.getCurrentFieldIndexArr($(this).attr('name'));
            var rowDiv = $(this).parents('div.row');
            if (fld.name == 'acc_head') {
                $(rowDiv).find('input[name=debit-' + fld.ind + ']').focus().select();
            } else if (fld.name == 'debit') {
                $(rowDiv).find('input[name=credit-' + fld.ind + ']').focus().select();
            } else if (fld.name == 'credit') {
                $(rowDiv).find('input[name=exch_rate_to_base-' + fld.ind + ']').focus().select();
            } else if (fld.name == 'exch_rate_to_base') {
                $(rowDiv).find('input[name=narration-' + fld.ind + ']').focus().select();
            } else if (fld.name == 'narration') {
                var rowDivNext = $(this).parents('div.row').next();
                if (rowDivNext.length > 0) {
                    $(rowDivNext).find('.fld-account').focus().select();
                } else if (!cpm.accountsg.journalMaster.isRowEmpty(rowDiv)) {
                    cpm.accountsg.journalMaster.newBlankRow(rowDiv);
                } else {
                    $('#entry #actBtn_saveContinue').focus();

                }
            }
        }
    },
    newBlankRow: function(rowDiv) {
        var clone = $(rowDiv).clone();
        var fld = cpm.accountsg.lib
                .getNextFieldIndexArr($(clone).find('input.fld-account').attr('name'));

        $(clone).find('input')
                .each(function() {
            //field name like: acc_head-1
            var fld2 = cpm.accountsg.lib.getNextFieldIndexArr($(this).attr('name'));
            $(this).attr('name', fld2.newName);
            //$(this).attr('value', '');
        });
        var newRow = "<div class='row'>" + $(clone).html() + "</div>";
        newRow = $(newRow).appendTo('#entry .jbody');
        $(newRow).find('input')
                .each(function() {
            $(this).attr('value', '');
        });
        $('#entry .fld-account').autocomplete('destroy');
        cpm.accountsg.journalMaster.initializeAccountTypeAhead();

        $('#entry').find('input[name=' + fld.newName + ']').focus();
    },
    isRowEmpty: function(rowDiv) {
        var returnVal = true;
        $(rowDiv).find('input')
                .each(function() {
            var value = $(this).val();
            if (value != '') {
                returnVal = false;
                return false;
            }
        });
        return returnVal;
    },
    deleteRow: function() {
        if ($('#entry .jbody .row').length > 1) {
            $(this).parents('div.row').remove();
        } else {
            var row = $(this).parents('div.row');
            cpm.accountsg.journalMaster.clearRow(row);
        }
        cpm.accountsg.journalMaster.setDifference();
    },
    updateRowValues: function(ui) {
        //this = either acc_head, exchange_rate_to_base, debit or credit
        //ui value only used for when acc_head used for updates

        var baseCurrDecimals = cpm.accountsg.journalMaster.baseCurrDecimals;

        var fld = cpm.accountsg.lib.getCurrentFieldIndexArr($(this).attr('name'));
        var row = $(this).parents('div.row');
        var acc_head_id_obj = $(row).find('.fld-acc_head_id');
        var debit_obj = $(row).find('.fld-debit');
        var credit_obj = $(row).find('.fld-credit');
        var debit_base_obj = $(row).find('.fld-debit_base');
        var credit_base_obj = $(row).find('.fld-credit_base');
        var exch_rate = 0;
        if (fld.name == 'acc_head') {
            $(acc_head_id_obj).val(ui.item.id);
            //ex: in edit mode (or new) if you have already selected a currency (ex: USD) and got an exch_rate
            //now you are choosing another account with same currency then it should not overwrite the exch_rate
            //unless the exch_rate field is manually cleared
        }
        var debit = $(debit_obj).val();
        var credit = $(credit_obj).val();

        var debit_base = (debit * exch_rate).toFixed(baseCurrDecimals);
        var credit_base = (credit * exch_rate).toFixed(baseCurrDecimals);
        $(debit_base_obj).val(debit_base);
        $(credit_base_obj).val(credit_base);

        cpm.accountsg.journalMaster.setDifference();
    },
    setDifference: function() {
        var debit_base_total_obj = $('#entry #debit_base_total');
        var credit_base_total_obj = $('#entry #credit_base_total');
        var difference_obj = $('#entry #difference .value');

        /*
        var debit_base_total = $('#entry .fld-debit_base').sum();
        var credit_base_total = $('#entry .fld-credit_base').sum();
        */
        var debit_base_total = 0;
        var credit_base_total = 0;
        var difference = debit_base_total - credit_base_total;
        difference = difference.toFixed(cpm.accountsg.journalMaster.baseCurrDecimals);

        cpm.accountsg.journalMaster.debit_base_total = debit_base_total;
        cpm.accountsg.journalMaster.credit_base_total = credit_base_total;
        cpm.accountsg.journalMaster.difference = difference;

        $(debit_base_total_obj).html(debit_base_total);
        $(credit_base_total_obj).html(credit_base_total);
        $(difference_obj).html(difference);

        if (difference != 0) {
            $(difference_obj).removeClass('green-highlight').addClass('red-highlight')
        } else {
            $(difference_obj).removeClass('red-highlight').addClass('green-highlight')
        }
    },
    clearDebitCredit: function() {
        var fld = cpm.accountsg.lib.getCurrentFieldIndexArr($(this).attr('name'));
        var row = $(this).parents('div.row');
        var debit_obj = $(row).find('.fld-debit');
        var credit_obj = $(row).find('.fld-credit');
        var debit = $(debit_obj).val();
        var credit = $(credit_obj).val();

        if (fld.name == 'credit') {
            if (credit != '') {
                $(debit_obj).val('');
            }
        } else if (fld.name == 'debit') {
            if (debit != '') {
                $(credit_obj).val('');
            }
        }
    },
    setRowBgColor: function() {
        var fld = cpm.accountsg.lib.getCurrentFieldIndexArr($(this).attr('name'));
        var row = $(this).parents('div.row');
        var debit = $(row).find('.fld-debit').val();
        var credit = $(row).find('.fld-credit').val();

        if (debit == '' && credit == '') {
            $(row).removeClass('green-bg red-bg');
        } else {
            if (fld.name == 'debit') {
                $(row).removeClass('green-bg').addClass('red-bg');
            } else if (fld.name == 'credit') {
                $(row).removeClass('red-bg').addClass('green-bg');
            }
        }
    },
    validateJournal: function() {
        var difference = cpm.accountsg.journalMaster.difference;
        //var difference = 1;
        var errorMessage = '';
        if (difference != 0) {
            errorMessage = 'The debit and credit total does not match';
            cpm.accountsg.journalMaster.displayError(errorMessage);
            return false;
        }

        var rowCount = $('#entry .jbody div.row').length;
        if (rowCount <= 0) {
        //if (rowCount <= 1) {
            errorMessage = "Please input an entry  and it's counter entry";
            cpm.accountsg.journalMaster.displayError(errorMessage);
            return false;
        }

        var returnVal = true;
        $('#entry .jbody div.row')
                .each(function() {
            var row = $(this);
            var acc_head_id = $(row).find('.fld-account').val();
            var debit = $(row).find('.fld-debit').val();
            var credit = $(row).find('.fld-credit').val();
            var debit_base = $(row).find('.fld-debit_base').val();
            var credit_base = $(row).find('.fld-credit_base').val();

            if (acc_head_id == '' && debit == '' && credit == '' &&
                    debit_base == '' && credit_base == '') {
                //do nothing. ok to have empty row
            } else {
                if (acc_head_id == '' || (debit == '' && credit == '') ||
                        (debit_base == '' && credit_base == '')) {
                    errorMessage = "Please input values for account/debit/credit properly";
                    cpm.accountsg.journalMaster.displayError(errorMessage);
                    returnVal = false;
                }
            }

        });

        return returnVal;
    },
    saveJournal: function() {
        var action = $(this).attr('action'); //save / saveContinue
        var urlList = $(this).attr('url');

        if (!cpm.accountsg.journalMaster.validateJournal()) {
            return;
        }

        Util.showProgressInd('Saving...');
        Util.clearPrepopulatedTextbox('#entry');
        var url = 'index.php?module=accountsg_journalMaster&_spAction=saveJournal&showHTML=0';
        var data = $('#entry input, #entry select').serialize();
        cpm.accountsg.journalMaster.enableDisableJournalButtons('disable');
        $.post(url, data, function(json) {
            cpm.accountsg.journalMaster.enableDisableJournalButtons('enable');
            Util.hideProgressInd();
            if (json.status == 'error') {
                cpm.accountsg.journalMaster.displayError(json.errorMsg);
            } else {
                var cpAction = $('#cpAction').val();
                if (cpAction == 'edit') {
                    $('#cpAction').val('new');
                    $('#entry input[name=journal_master_id]').val('');
                }
                cpm.accountsg.journalMaster.hideError();
                if (action == 'saveContinue') {
                    cpm.accountsg.journalMaster.clearEntryScreen();
                } else if (action == 'save') {
                    document.location.href = urlList;
                }
            }

        }, 'json');
    },
    enableDisableJournalButtons: function(action) {
        if (action == 'disable') {
            $('#actBtn_cancelNew, #actBtn_saveJournal, #actBtn_saveContinue').attr('disabled', 'disabled');
        } else {
            $('#actBtn_cancelNew, #actBtn_saveJournal, #actBtn_saveContinue').removeAttr('disabled');
        }
    },
    displayError: function(errorMsg) {
        // $('#entryErrorBox').html(errorMsg).fadeIn();
        // $('#entryErrorBox').oneTime(4000, 'errorBox', cpm.account.journalMaster.hideError);
        Util.alert(errorMsg);
    },
    hideError: function() {
        $('#entryErrorBox').fadeOut();
    },
    clearRow: function(row) {
        $(row).find('input').each(function() {
            $(this).val('');
        });
    },
    clearEntryScreen: function() {
        $('#entry #fld_voucher_type').val('');
        $('#entry .fld-narration').val('');
        $('#entry #fld_narration_main').val('');
        $('#entry #fld_voucher_code').html('');
        $('#entry .jbody div.row:gt(1)').remove();
        cpm.accountsg.journalMaster.clearRow('#entry .jbody div.row');
        $('#entry #fld_entry_date').focus().select();
    },
    ledgerAuthorize: function() {
        //journal master ledger authorize
        var jmId = $(this).attr('jm-id');
        var jmRecSel = 'a[jm-id=' + jmId + ']';

        var ledger_authorized = 1;
        if ($(jmRecSel).hasClass('green-dot')) {
            ledger_authorized = 0;
        }
        var url = 'index.php?module=accountsg_journalMaster&_spAction=ledgerAuthorize&showHTML=0' +
                '&ledger_authorized=' + ledger_authorized +
                '&journal_master_id=' + jmId;
        $.post(url, function(json) {
            if (ledger_authorized) {
                $(jmRecSel).removeClass('red-dot').addClass('green-dot');
            } else {
                $(jmRecSel).removeClass('green-dot').addClass('red-dot');
            }
        }, 'json');
    },
    ledgerPending: function() {
        //journal pending records
        var journal_id = $(this).attr('journal_id');
        var jRecSel = 'a[journal_id=' + journal_id + ']';

        var pending = 1;
        if ($(jRecSel).hasClass('light-red-dot')) {
            pending = 0;
        }
        var url = 'index.php?module=accountsg_journalMaster&_spAction=ledgerPending&showHTML=0' +
                '&pending=' + pending +
                '&journal_id=' + journal_id;
        $.post(url, function(json) {
            if (pending) {
                $(jRecSel).removeClass('light-grey-dot').addClass('light-red-dot');
            } else {
                $(jRecSel).removeClass('light-red-dot').addClass('light-grey-dot');
            }
        }, 'json');
    }

}
