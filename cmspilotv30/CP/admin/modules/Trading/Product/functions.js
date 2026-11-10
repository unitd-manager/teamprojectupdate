Util.createCPObject('cpm.trading.product');

cpm.trading.product = {
    init: function(){
        $('#fld_dimension_h, #fld_dimension_w, #fld_dimension_d')
        .change(cpm.trading.product.calculateCBM);

        $('.m-trading_product .editCostBreakdown')
        .click(cpm.trading.product.editCostBreakdown);

        $('input.select-rfq').live('click', cpm.trading.product.selectConfirmedRfq);

    },

    calculateCBM: function(){
        var dimension_h = $('#fld_dimension_h').val();
        var dimension_w = $('#fld_dimension_w').val();
        var dimension_d = $('#fld_dimension_d').val();

        var cbm = dimension_h * dimension_w * dimension_d;
        $('#fld_cbm_per_pc').val(cbm);
    },

    editCostBreakdown: function(e){
        e.preventDefault();
        var product_id = $('#record_id').val();
        var url = 'index.php?module=trading_product&_spAction=editCostBreakdown'
                + '&product_id=' + product_id + '&showHTML=0'
        var exp = {
            url: url
           ,afterOpen: function() {
                $('.btnSaveCostBreakdown').click(cpm.trading.product.saveCostBreakdown);
                $('#costBreakdown .costs input, #costBreakdown .costs select')
                .change(cpm.trading.product.calculateProductCosting);

                $('#fld_pt_other_costs_1_label').unbind('change');
                $('#fld_pt_other_costs_2_label').unbind('change');
                $('#fld_pt_other_costs_3_label').unbind('change');

                $('#costBreakdown .markups input')
                .change(cpm.trading.product.calculateProductMarkup);
            }
        };
        Util.openDialogForLink('Edit Cost Breakdown',  750, 650, 0, exp);
    },

    calculateProductCosting: function(e){
        var url = "index.php?module=trading_product"
                + "&_spAction=calculateProductCosting"
                + "&showHTML=0";
        var values = $('#costBreakdown input, #costBreakdown select').serialize();

        Util.showProgressInd();
        $.post(url, values, function(json) {
            Util.hideProgressInd();
            $('#txt_pt_buy_unit_price_base').html(json.pt_buy_unit_price_base);
            $('#fld_pt_buy_unit_price_base').val(json.pt_buy_unit_price_base);
            $('#txt_pt_other_costs_1_base').html(json.pt_other_costs_1_base);
            $('#fld_pt_other_costs_1_base').val(json.pt_other_costs_1_base);
            $('#txt_pt_other_costs_2_base').html(json.pt_other_costs_2_base);
            $('#fld_pt_other_costs_2_base').val(json.pt_other_costs_2_base);
            $('#txt_pt_other_costs_3_base').html(json.pt_other_costs_3_base);
            $('#fld_pt_other_costs_3_base').val(json.pt_other_costs_3_base);
            $('#txt_pt_sell_unit_price_total_net_cost_base').html(json.pt_sell_unit_price_total_net_cost_base);
            $('#fld_pt_sell_unit_price_total_net_cost_base').val(json.pt_sell_unit_price_total_net_cost_base);
            $('#txt_pt_agent_comm_base').html(json.pt_agent_comm_base);
            $('#fld_pt_agent_comm_base').val(json.pt_agent_comm_base);
            $('#txt_pt_qc_comm_base').html(json.pt_qc_comm_base);
            $('#fld_pt_qc_comm_base').val(json.pt_qc_comm_base);
            $('#txt_pt_sell_unit_price_ex_fact_base').html(json.pt_sell_unit_price_ex_fact_base);
            $('#fld_pt_sell_unit_price_ex_fact_base').val(json.pt_sell_unit_price_ex_fact_base);
            $('#txt_pt_local_charges_base').html(json.pt_local_charges_base);
            $('#fld_pt_local_charges_base').val(json.pt_local_charges_base);
            $('#txt_pt_sell_unit_price_fob_base').html(json.pt_sell_unit_price_fob_base);
            $('#fld_pt_sell_unit_price_fob_base').val(json.pt_sell_unit_price_fob_base);
            $('#txt_pt_shipping_cost_base').html(json.pt_shipping_cost_base);
            $('#fld_pt_shipping_cost_base').val(json.pt_shipping_cost_base);
            $('#txt_pt_insurance_cost_base').html(json.pt_insurance_cost_base);
            $('#fld_pt_insurance_cost_base').val(json.pt_insurance_cost_base);
            $('#txt_pt_sell_unit_price_cif_base').html(json.pt_sell_unit_price_cif_base);
            $('#fld_pt_sell_unit_price_cif_base').val(json.pt_sell_unit_price_cif_base);
            $('#txt_pt_tax_amount_base').html(json.pt_tax_amount_base);
            $('#fld_pt_tax_amount_base').val(json.pt_tax_amount_base);

            var dataArr = json.markupArr;
            cpm.trading.product.setProductMarkup(dataArr);

        }, 'json');

    },

    calculateProductMarkup: function(e){
        var product_id = $('#record_id').val();

        var url = "index.php?module=trading_product"
                + "&_spAction=calculateProductMarkup"
                + '&product_id=' + product_id + '&showHTML=0'
        var values = $('#costBreakdown input, #costBreakdown select').serialize();
        Util.showProgressInd();
        $.post(url, values, function(dataArr) {
            Util.hideProgressInd();
            cpm.trading.product.setProductMarkup(dataArr);
        }, 'json');
    },

    setProductMarkup: function(dataArr){
        for (pricing_type_id in dataArr) {
            var row = dataArr[pricing_type_id];
            var rowSel = $('#costBreakdown .pricing-type table tr.pt_' + pricing_type_id);
            rowSel.find('.ex_fact_markup span').html(row.ex_fact_markup);
            rowSel.find('.fob_markup span').html(row.fob_markup);
            rowSel.find('.cif_markup span').html(row.cif_markup);
            rowSel.find('.calculated_cost').html(row.calculated_cost);
            //for RRP with VAT calculated cost and sell unit price base is the same
            if (row.record_type == 'has_tax') {
                rowSel.find('.sales_unit_price_base div').html(row.calculated_cost);
                rowSel.find('.sales_unit_price_base input').val(row.calculated_cost);
            }

        }
    },

    saveCostBreakdown: function(e) {
        e.preventDefault();
        var product_id = $('#record_id').val();
        var url = "index.php?module=trading_product"
                + "&_spAction=saveCostBreakdown"
                + '&product_id=' + product_id + '&showHTML=0'
        var values = $('#costBreakdown input, #costBreakdown select').serialize();

        $.post(url, values, function(json) {
            Util.alert(json.html, function() {
                $('#dialog').dialog('destroy');
                $('#dialog').remove();
                Links.reloadPortalRecords('trading_product#trading_pricingTypeLink');
            });
        }, 'json');
    },

    selectConfirmedRfq: function() {
        var quote_request_items_id = $(this).attr('quote_request_items_id');
        var product_id = $('#record_id').val();

        var checked = $(this).attr('checked') ? 'checked' : '';
        var checkedVal = checked == 'checked' ? 1 : 0;

        Util.showProgressInd();
        var url = 'index.php?module=trading_product&_spAction=chooseConfirmedRFQForProduct&showHTML=0'
                  + '&product_id=' + product_id
                  + '&quote_request_items_id=' + quote_request_items_id
                  + '&checked=' + checkedVal;
        $.getJSON(url, function(json) {
            Util.alert('RFQ selection saved', function() {
                document.location = document.location;
            });
        });
    }
}