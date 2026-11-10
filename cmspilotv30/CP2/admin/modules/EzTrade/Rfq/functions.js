Util.createCPObject('cpm.trading.rfq');

cpm.trading.rfq.init = function(){
    $('select#fld_company_id_supplier').live('change', function() {
        var url = 'index.php?module=trading_contact&_spAction=contactByCompanyJSON&showHTML=0';
        var company_id = $(this).val();
        $.get(url, {company_id: company_id}, function (data) {
            $('#fld_contact_id_supplier').cp_loadSelect(data);
        }, 'json');
    });

    Rfq.setupEvents();
}

var Rfq = {
    setupEvents: function(){
        //for edit portal product
        $('#portalForm #fld_quantity, #portalForm #buy_unit_price').live('change', Rfq.calculateValues);
    },

    calculateValues: function(){
        var url = "index.php?_topRm=main&module=trading_product"
                + "&_spAction=calculatedValuesRfq"
                + "&showHTML=0";
        var values = $("#portalForm").serialize();
        $.post(url, values, function(json) {

            $('#buy_price').val(json.buy_price);
            $('#buy_unit_price_base').val(json.buy_unit_price_base);
            $('#buy_price_base').val(json.buy_price_base);

            $('#t_buy_price').html(json.buy_price);
            $('#t_buy_unit_price_base').html(json.buy_unit_price_base);
            $('#t_buy_price_base').html(json.buy_price_base);
        }, 'json');
    },

    printRfq: function() {
        Util.showAjaxErrorTemp();
        return;
    }
}