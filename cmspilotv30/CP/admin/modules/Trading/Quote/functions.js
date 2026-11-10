Util.createCPObject('cpm.trading.quote');

cpm.trading.quote = {
    init: function(){
        $("select[name='company_id']").change(function() {
            var company_id = $("select[name='company_id']").val();
            var url = 'index.php?module=trading_contact&_spAction=multipleAddress&showHTML=0';
            $.get(url, {
                company_id: company_id
            }, function (data) {
                $("select[name='company_address_id']").cp_loadSelect(data);
            }, 'json');
        });

        //for edit portal product
        $('#portalForm .costs input, #portalForm .costs select')
        .live('change', cpm.trading.quote.calculateProductCosting);

        $('#portalForm #fld_other_costs_1_label').unbind('change');
        $('#portalForm #fld_other_costs_2_label').unbind('change');
        $('#portalForm #fld_other_costs_3_label').unbind('change');        
        
        $('#portalForm a.next').live('click', cpm.trading.quote.nextLine);
        $('#portalForm a.previous').live('click', cpm.trading.quote.previousLine);
    },
    
    calculateProductCosting: function(e){
        var url = "index.php?module=trading_product"
                + "&_spAction=calculatedValuesQuoteItems"
                + "&showHTML=0";
        var values = $('#portalForm input, #portalForm select').serialize();

        Util.showProgressInd();
        $.post(url, values, function(json) {
            Util.hideProgressInd();
            $('#txt_buy_unit_price_base').html(json.buy_unit_price_base);
            $('#fld_buy_unit_price_base').val(json.buy_unit_price_base);
            $('#txt_other_costs_1_base').html(json.other_costs_1_base);
            $('#fld_other_costs_1_base').val(json.other_costs_1_base);
            $('#txt_other_costs_2_base').html(json.other_costs_2_base);
            $('#fld_other_costs_2_base').val(json.other_costs_2_base);
            $('#txt_other_costs_3_base').html(json.other_costs_3_base);
            $('#fld_other_costs_3_base').val(json.other_costs_3_base);            
            $('#txt_sell_unit_price_total_net_cost_base').html(json.sell_unit_price_total_net_cost_base);
            $('#fld_sell_unit_price_total_net_cost_base').val(json.sell_unit_price_total_net_cost_base);  
            $('#txt_agent_comm_base').html(json.agent_comm_base);
            $('#fld_agent_comm_base').val(json.agent_comm_base);  
            $('#txt_qc_comm_base').html(json.qc_comm_base);
            $('#fld_qc_comm_base').val(json.qc_comm_base);  
            $('#txt_sell_unit_price_ex_fact_base').html(json.sell_unit_price_ex_fact_base);
            $('#fld_sell_unit_price_ex_fact_base').val(json.sell_unit_price_ex_fact_base);  
            $('#txt_local_charges_base').html(json.local_charges_base);
            $('#fld_local_charges_base').val(json.local_charges_base);  
            $('#txt_sell_unit_price_fob_base').html(json.sell_unit_price_fob_base);
            $('#fld_sell_unit_price_fob_base').val(json.sell_unit_price_fob_base);  
            $('#txt_shipping_cost_base').html(json.shipping_cost_base);
            $('#fld_shipping_cost_base').val(json.shipping_cost_base);  
            $('#txt_insurance_cost_base').html(json.insurance_cost_base);
            $('#fld_insurance_cost_base').val(json.insurance_cost_base);  
            $('#txt_sell_unit_price_cif_base').html(json.sell_unit_price_cif_base);
            $('#fld_sell_unit_price_cif_base').val(json.sell_unit_price_cif_base);  
            $('#txt_tax_amount_base').html(json.tax_amount_base);
            $('#fld_tax_amount_base').val(json.tax_amount_base);  

            $('#tblSalesPrice td.ex_fact_markup span').html(json.ex_fact_markup);  
            $('#tblSalesPrice td.fob_markup span').html(json.fob_markup);  
            $('#tblSalesPrice td.cif_markup span').html(json.cif_markup);  
            $('#tblSalesPrice td.ex_fact_markup_amount').html(json.ex_fact_markup_amount);  
            $('#tblSalesPrice td.fob_markup_amount').html(json.fob_markup_amount);  
            $('#tblSalesPrice td.cif_markup_amount').html(json.cif_markup_amount);  
            $('#tblSalesPrice td.cif_markup_amount').html(json.cif_markup_amount);  
            $('#txt_sell_unit_price_base_vat').html(json.sell_unit_price_base_vat);  
            $('#fld_sell_unit_price_base_vat').val(json.sell_unit_price_base_vat);  
            $('#txt_sell_price_base').html(json.sell_price_base);  
            $('#fld_sell_price_base').val(json.sell_price_base);  
            
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
                    $('#btnRaiseSO').click(cpm.trading.quote.raiseSO);
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

    },
    
    printQuote: function() {
        var quote_id = $('#record_id').val();
        var url = 'index.php?_spAction=printReport&record_id=' + 
                   quote_id + '&showHTML=0&roomName=trading_quote&report=quote';
        document.location = url;
    },
    
    nextLine: function(e) {
        e.preventDefault();
        var record_id = $('#portalForm input[name=quote_items_id]').val();
        var nextRow = $('.trading_quote__trading_productLink table tr[recid=' + record_id + ']').next();
        if (nextRow.length > 0) {
            $('#dialog').dialog('destroy');
            $('#dialog').remove();            
            nextRow.find('.editPortalRecord').click();
        }
    },
    
    previousLine: function(e) {
        e.preventDefault();
        var record_id = $('#portalForm input[name=quote_items_id]').val();
        var prevRow = $('.trading_quote__trading_productLink table tr[recid=' + record_id + ']')
                      .prev('[recid]');
        if (prevRow.length > 0) {
            $('#dialog').dialog('destroy');
            $('#dialog').remove();            
            prevRow.find('.editPortalRecord').click();
        }
    }

}