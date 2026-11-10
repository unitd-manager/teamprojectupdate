Util.createCPObject('cpm.trading.quote');

cpm.trading.quote.init = function(){
    $("select[name='company_id']").change(function() {
        var company_id = $("select[name='company_id']").val();
        var url = 'index.php?module=trading_contact&_spAction=multipleAddress&showHTML=0';
        $.get(url, {
            company_id: company_id
        }, function (data) {
            $("select[name='company_address_id']").cp_loadSelect(data);
        }, 'json');
    });

    Quote.setupEvents();
    Util.setLinkPortalHeight();
}

var Quote = {
    setupEvents: function(){
        //for edit portal product
        $('#fld_quantity, #buy_unit_price, #sell_unit_price, \n\
           #other_costs_1_curr, #other_costs_2_curr, #other_costs_3_curr, \n\
           #other_costs_1, #other_costs_2, #other_costs_3')
        .live('change', Quote.calculateValues);

        $('#recalculate').live('click', Quote.recalculate);

    },

    recalculate: function(event){
        Quote.calculateValues(event, true);
    },

    calculateValues: function(event, recalculate){
        var recalculateText = '';
        if (recalculate) {
            recalculateText = '&recalculate=1';
        }

        var url = "index.php?_topRm=main&module=trading_product"
                + "&_spAction=calculatedValuesQuoteItems"
                + recalculateText
                + "&showHTML=0";
        var values = $("#portalForm").serialize();

        $.post(url, values, function(json) {
            $('#buy_unit_price_base').val(json.buy_unit_price_base);

            $('#t_buy_price').html(json.buy_price);
            $('#t_buy_unit_price_base').html(json.buy_unit_price_base);
            $('#t_buy_price_base').html(json.buy_price_base);

            $('#sell_unit_price').val(json.sell_unit_price);
            $('#sell_unit_price_base').val(json.sell_unit_price_base);
            $('#markup').val(json.markup);

            $('#t_sell_price').html(json.sell_price);
            $('#t_sell_unit_price_base').html(json.sell_unit_price_base);
            $('#t_sell_price_base').html(json.sell_price_base);
            $('#t_markup').html(json.markup);

            $('#t_other_costs_1_base').html(json.other_costs_1_base);
            $('#other_costs_1_base').val(json.other_costs_1_base);
            $('#t_other_costs_2_base').html(json.other_costs_2_base);
            $('#other_costs_2_base').val(json.other_costs_2_base);
            $('#t_other_costs_3_base').html(json.other_costs_3_base);
            $('#other_costs_3_base').val(json.other_costs_3_base);

        }, 'json');

    },

    setExchangeRateOtherCost: function(exchRateToUsd) {
        $('#t_other_costs_1_base').html(exchRateToUsd);
        $('#other_costs_1_base').html(exchRateToUsd);
    },

    raiseSOList: function() {
        var quote_id = $('#record_id').val();
        var url = 'index.php?module=trading_quote&_spAction=raiseSOListValidation&showHTML=0';
        $.getJSON(url, {quote_id: quote_id}, function (json) {
            if (json.status == 'error') {
                Util.alert(json.errorMsg);
                return;
            }

            var url = 'index.php?module=trading_quote&_spAction=raiseSOList' +
                      '&quote_id=' + quote_id +
                      '&showHTML=0';
            var exp = {
                url: url
               ,afterOpen: function() {
                    $('#btnRaiseSOCancel').click(function() {
                        $('#dialog').dialog('destroy');
                        $('#dialog').remove();
                    });
                    $('#btnRaiseSO').click(Quote.raiseSO);
                }
            };
            Util.openDialogForLink('Raise SO',  900, 500, 0, exp);

        });

    },

    raiseSO: function() {
        var selector = '#raiseList input.choose, ' +
                       '#raiseList input.quantity, ' +
                       '#raiseList select[name=company_id_supplier]';
        var data = $(selector).serialize();

        var quote_id = $('#record_id').val();
        var url = 'index.php?module=trading_quote&_spAction=raiseSO&showHTML=0' +
                  '&quote_id=' + quote_id;

        $.post(url, data, function (json) {
            if (json.status == 'error') {
                Util.alert(json.errorMsg);
                return;
            }
            document.location = json.returnUrl;
        }, 'json');

    },

    duplicate: function() {
        if (!confirm("Are you sure you want to duplicate the Quote?")){
            return;
        }

        var quote_id = $('#record_id').val();
        var url = 'index.php?module=trading_quote&_spAction=duplicateQuote&showHTML=0' +
                  '&quote_id=' + quote_id;

        $.post(url, function (json) {
            if (json.status == 'error') {
                Util.alert(json.errorMsg);
                return;
            }
            document.location = json.returnUrl;
        }, 'json');

    }

}