Util.createCPObject('cpm.trading.enquiry');

cpm.trading.enquiry.init = function(){
    $("select[name='company_id']").change(function() {
        var company_id = $("select[name='company_id']").val();
        var url = 'index.php?module=trading_contact&_spAction=multipleAddress&showHTML=0';
        $.get(url, {
            company_id: company_id
        }, function (data) {
            $("select[name='company_address_id']").cp_loadSelect(data);
        }, 'json');
    });

    Enquiry.setupEvents();
    //Util.setLinkPortalHeight();

    //quote request arrows under enquiry line
    $('.callbackWrapper .showHide').live('click', function (e){
        $(this).toggleClass('arrowDown');
        var qrDiv = $(this).next();
        $(qrDiv).slideToggle('fast');
    });

    //$('.callbackWrapper .showHide').tooltip();

    $('input.select-rfq').live('click', Enquiry.selectConfirmedRfq);

    //category / sub category in link window
    $('#searchNotLinked select[name=category_id]')
    .livequery('change', function(){
        var url = 'index.php?module=webBasic_subCategory&_spAction=subCategoryByCategoryJSON&showHTML=0';
        var category_id = $(this).val();
        $.get(url, {category_id: category_id}, function (data) {
            $('#searchNotLinked select[name=sub_category_id]').cp_loadSelect(data);
        }, 'json');
    });

    $('#searchLinked select[name=category_id]')
    .livequery('change', function(){
        var url = 'index.php?module=webBasic_subCategory&_spAction=subCategoryByCategoryJSON&showHTML=0';
        var category_id = $(this).val();
        $.get(url, {category_id: category_id}, function (data) {
            $('#searchLinked select[name=sub_category_id]').cp_loadSelect(data);
        }, 'json');
    });
}

$(function(){
});


var Enquiry = {

    setupEvents: function() {
        //in linking panel
        $('#linkRight .suppliers-cont input.checkbox').live('click', Enquiry.chooseSupplier);
    },

    validateEditLink: function() {
        var retValue = true;
        var enquiry_id = $('#record_id').val();
        var url = 'index.php?module=trading_enquiry&_spAction=chooseLinkValidation&showHTML=0';
        $.ajax({
            url: url,
            async: false,
            data: {enquiry_id: enquiry_id},
            dataType: 'json',
            success: function (json) {
                if (json.status == 'error') {
                    Util.alert(json.errorMsg);
                    retValue = false;
                }
            }
        });
        return retValue;
    },

    chooseSupplier: function() {
        var url = 'index.php?module=trading_enquiry&_spAction=chooseSupplierForProduct&showHTML=0';
        var enquiry_id = $(this).attr('id');
        var product_id = $(this).attr('product_id');
        var company_id_supplier = $(this).attr('company_id_supplier');
        var product_supplier_id = $(this).attr('product_supplier_id');
        var params = {
             enquiry_id: enquiry_id
            ,product_id: product_id
            ,company_id_supplier: company_id_supplier
            ,product_supplier_id: product_supplier_id
        };
        $.get(url, params, function (data) {
        });
    },

    raiseRfqList: function() {
        var enquiry_id = $('#record_id').val();
        var url = 'index.php?module=trading_enquiry&_spAction=raiseRfqListValidation&showHTML=0';
        $.getJSON(url, {enquiry_id: enquiry_id}, function (json) {
            if (json.status == 'error') {
                Util.alert(json.errorMsg);
                return;
            }

            var url = 'index.php?module=trading_enquiry&_spAction=raiseRfqList' +
                      '&enquiry_id=' + enquiry_id +
                      '&showHTML=0';
            var exp = {
                url: url
               ,afterOpen: function() {
                    $('#btnRaiseRfqCancel').click(function() {
                        $('#dialog').dialog('destroy');
                        $('#dialog').remove();
                    });
                    $('#btnRaiseRfq').click(Enquiry.raiseRfq);
                }
            };
            Util.openDialogForLink('Raise Rfq',  900, 500, 0, exp);

        });

    },

    raiseRfq: function() {
        var selector = '#raiseList input.choose, ' +
                       '#raiseList input.quantity, ' +
                       '#raiseList select[name=company_id_supplier]';
        var data = $(selector).serialize();

        var is_product_selected = $('#raiseList input.choose:checked').length;
        var enquiry_id = $('#record_id').val();
        var url = 'index.php?module=trading_enquiry&_spAction=raiseRfq&showHTML=0' +
                  '&enquiry_id=' + enquiry_id +
                  '&is_product_selected=' + is_product_selected;

        $.post(url, data, function (json) {
            if (json.status == 'error') {
                Util.alert(json.errorMsg);
                return;
            }
            document.location = json.returnUrl;
        }, 'json');

    },

    rfqComparison: function() {
        var enquiry_id = $('#record_id').val();
        var url = 'index.php?_topRm=main&module=trading_rfqComparison&enquiry_id=' + enquiry_id + '&_showBodyOnly=1';
        var exp = {url: url, useIframe: true, beforeCloseFn: function() {
            Links.reloadPortalRecords('enquiry#product');
        }};
        var width = $(window).width();
        var height = $(window).width();
        width = parseInt(width * 0.9);
        height = parseInt(height * 0.85);
        Util.openDialogForLink('Edit Link',  width, 600, 0, exp);

    },

    raiseQuoteList: function() {
        var enquiry_id = $('#record_id').val();
        var url = 'index.php?module=trading_enquiry&_spAction=raiseQuoteListValidation&showHTML=0';
        $.getJSON(url, {enquiry_id: enquiry_id}, function (json) {
            if (json.status == 'error') {
                Util.alert(json.errorMsg);
                return;
            }

            var url = 'index.php?module=trading_enquiry&_spAction=raiseQuoteList' +
                      '&enquiry_id=' + enquiry_id +
                      '&showHTML=0';
            var exp = {
                url: url
               ,afterOpen: function() {
                    $('#btnRaiseQuoteCancel').click(function() {
                        $('#dialog').dialog('destroy');
                        $('#dialog').remove();
                    });
                    $('#btnRaiseQuote').click(Enquiry.raiseQuote);
                }
            };
            Util.openDialogForLink('Raise Quote',  900, 500, 0, exp);

        });

    },

    raiseQuote: function() {

        var selector = '#raiseList input.choose, ' +
                       '#raiseList input.quantity, ' +
                       '#raiseList select[name=company_id_supplier]';
        var data = $(selector).serialize();

        var is_product_selected = $('#raiseList input.choose:checked').length;
        var enquiry_id = $('#record_id').val();
        var url = 'index.php?module=trading_enquiry&_spAction=raiseQuote&showHTML=0' +
                  '&enquiry_id=' + enquiry_id +
                  '&is_product_selected=' + is_product_selected;
        $.post(url, data, function (json) {
            if (json.status == 'error') {
                Util.alert(json.errorMsg);
                return;
            }
            document.location = json.returnUrl;
        }, 'json');

    },

    duplicateLine: function(enquiry_product_id) {
        if (!confirm("Are you sure you want to duplicate this Enquiry Line?")){
            return;
        }

        var url = 'index.php?module=trading_enquiry&_spAction=duplicateLine&showHTML=0';
        $.getJSON(url, {enquiry_product_id: enquiry_product_id}, function (json) {
            if (json.status == 'error') {
                Util.alert(json.errorMsg);
                return;
            }
            //document.location = document.location;
        });

    },

    chooseRFQForLine: function(enquiry_product_id) {
        var url = 'index.php?module=trading_enquiry&_spAction=chooseRFQFormForLine' +
                  '&enquiry_product_id=' + enquiry_product_id +
                  '&showHTML=0';
        var exp = {
            url: url
           ,afterOpen: function() {
                $('#btnChooseRFQCancel').click(function() {
                    $('#dialog').dialog('destroy');
                    $('#dialog').remove();
                });
                $('#btnChooseRFQSave').click(Enquiry.chooseRFQForLineSave);
            }
        };
        Util.openDialogForLink('Choose Rfq',  950, 550, 0, exp);

    },

    chooseRFQForLineSave: function() {
        var selector = '#chooseRFQ input.checkbox:checked';
        var data = $(selector).serialize();

        var enquiry_product_id     = $('#enquiry_product_id').val();
        var quote_request_items_id = $(selector).attr('quote_request_items_id');

        var url = 'index.php?module=trading_enquiry&_spAction=chooseRFQForLine&showHTML=0'
                  + '&enquiry_product_id=' + enquiry_product_id;

        $.post(url, data, function () {
            Util.alert('RFQ selection saved', function() {
                document.location = document.location;
            });
        });
    },

    selectConfirmedRfq: function() {
        var enquiry_product_id     = $(this).attr('enquiry_product_id');
        var quote_request_items_id = $(this).attr('quote_request_items_id');
        var checked = $(this).attr('checked') ? 'checked' : '';
        var checkedVal = checked == 'checked' ? 1 : 0;

        $('input.select-' + enquiry_product_id).attr('checked', false);
        $(this).attr('checked', checked);

        var url = 'index.php?module=trading_enquiry&_spAction=chooseConfirmedRFQForLine&showHTML=0'
                  + '&enquiry_product_id=' + enquiry_product_id
                  + '&quote_request_items_id=' + quote_request_items_id
                  + '&checked=' + checkedVal;
        $.get(url, function () {
            Util.alert('Confirmed RFQ selection saved');
        });
    }
}