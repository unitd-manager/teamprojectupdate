Util.createCPObject('cpm.payroll.training');

cpm.payroll.training = {
    init: function(){
        $("select[name='company_id']").change(function() {
            var company_id = $("select[name='company_id']").val();
            var url = 'index.php?module=hms_contact&_spAction=multipleAddress&showHTML=0';
            $.get(url, {company_id: company_id}, function (data) {
                $("select[name='company_address_id']").cp_loadSelect(data);
            }, 'json');
        });

        $('#frmEdit select#fld_company_id').livequery('change', function(){
           Util.loadContactDropdown.call(this);
        });

        $('#portalForm select#fld_product_group_id').livequery('change', function(){
           cpm.payroll.training.loadCategoryDropdown.call(this);
        });        

        //for edit portal product
        $('#portalForm .costs input, #portalForm .costs select')
        .live('change', cpm.payroll.training.calculateProductCosting);

        $('#portalForm #fld_other_costs_1_label').unbind('change');
        $('#portalForm #fld_other_costs_2_label').unbind('change');
        $('#portalForm #fld_other_costs_3_label').unbind('change');

        $('#portalForm a.next').live('click', cpm.payroll.training.nextLine);
        $('#portalForm a.previous').live('click', cpm.payroll.training.previousLine);


        /* Add Training Emplyoee */
        $('#AddTrainingEmplyoee').livequery('click', function (e){
                var title = "Add Training Employee";
                //alert ('Display Training Emplyoee');
                e.preventDefault();

                var expObj = {
                    validate: true
                   ,callbackOnSuccess: function(){
                        var msg = 'Training Employee Added Successfully';
                        Util.alert(msg, function(){
                            Util.closeAllDialogs();
                            window.location.reload(true);
                        });
                    }
                }
                Util.openFormInDialog.call(this, 'portalForm', title, 600, 300, expObj);
        });




        /* Delete Training Emplyoee */
        $('.deleteTrainingEmplyoee').livequery('click', function (e){
            var training_id = $("#record_id").val();

            msg = "Do you like to delete the Training Emplyoee?";
            if (!confirm(msg)){
                return false;
            }
            else{
                Util.showProgressInd();
                var training_staff_id = $(this).attr('training_staff_id');
                var url = 'index.php?module=payroll_training&_spAction=DeleteTrainingEmplyoee&showHTML=0&training_staff_id=' + training_staff_id;
                $.get(url, {training_staff_id: training_staff_id}, function(html){
                    Util.hideProgressInd();
                    cpm.payroll.training.reloadEmployeeLink(training_id);
                });
            }
        });

        $('#linkEmployeeToCourse')
        .livequery('click', cpm.payroll.training.employeeLinkedAdd);

         $('select[name=employee_staff_id]').livequery('change', function (e){
            var parent   = $(this).closest('tr');
            var rec_id   = $(parent).attr('rec_id');
            var staff_id = $(this).val();
            Util.showProgressInd();

            var countCheck = parseInt(0);
            var count      = parseInt(1);
            $('select[name=employee_staff_id]').each(function(){
                if(count > 1) {
                    var checkedVal = $(this).val();
                    if(staff_id == checkedVal){
                        countCheck = parseInt(countCheck) + parseInt(1);
                    }
                }

                count = parseInt(count) + parseInt(1);
            });

            if(countCheck > 0) {
                $('select[name=employee_staff_id]', parent).val("");
                Util.alert('Employee already exists');
                Util.hideProgressInd();
            } else {
                var url = 'index.php?module=payroll_training&_spAction=updateEmployeeId&showHTML=0&training_staff_id=' + rec_id;
                $.get(url, {training_staff_id: rec_id, staff_id:staff_id}, function(html){
                    Util.hideProgressInd();
                    //window.location.reload(true);
                });
            }
        });

        $('.employeeFromDate .hasDatepicker').livequery('change', function(e){
            var parent    = $(this).closest('tr');
            var rec_id    = $(parent).attr('rec_id');
            var from_date = $('input[name=employee_from_date'+rec_id+']').val();
            var url = 'index.php?module=payroll_training&_spAction=UpdateStaffFromDateForEmployeeLink&showHTML=0&training_staff_id=' + rec_id;
            $.get(url, {from_date:from_date, training_staff_id: rec_id}, function(html){
            });
        });

        $('.employeeToDate .hasDatepicker').livequery('change', function(e){
            var parent    = $(this).closest('tr');
            var rec_id    = $(parent).attr('rec_id');
            var to_date   = $('input[name=employee_to_date'+rec_id+']').val();
            var url = 'index.php?module=payroll_training&_spAction=UpdateStaffToDateForEmployeeLink&showHTML=0&training_staff_id=' + rec_id;
            $.get(url, {to_date:to_date, training_staff_id: rec_id}, function(html){
            });
        });

    },



    calculateProductCosting: function(e){
        var url = "index.php?module=hms_product"
                + "&_spAction=calculatedValuesItems"
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





    nextLine: function(e) {
        e.preventDefault();
        var record_id = $('#portalForm input[name=quote_items_id]').val();
        var nextRow = $('.payroll_training__payroll_training_staffLink table tr[recid=' + record_id + ']').next();
        if (nextRow.length > 0) {
            $('#dialog').dialog('destroy');
            $('#dialog').remove();
            nextRow.find('.editPortalRecord').click();
        }
    },

    previousLine: function(e) {
        e.preventDefault();
        var record_id = $('#portalForm input[name=quote_items_id]').val();
        var prevRow = $('.payroll_training__payroll_training_staffLink table tr[recid=' + record_id + ']')
                      .prev('[recid]');
        if (prevRow.length > 0) {
            $('#dialog').dialog('destroy');
            $('#dialog').remove();
            prevRow.find('.editPortalRecord').click();
        }
    },


    employeeLinkedAdd: function(e) {
        e.preventDefault();
        var training_id       = $(this).attr('training_id');
        Util.showProgressInd();
        var url = 'index.php?module=payroll_training&_spAction=linkEmployeeToCourse&training_id=' + training_id + '&showHTML=0';
        $.get(url, {training_id: training_id}, function(){
            cpm.payroll.training.reloadEmployeeLink(training_id);
            Util.hideProgressInd();
        });
    },

    reloadEmployeeLink: function(training_id){
        var url = 'index.php?module=payroll_training&_spAction=AddTrainingEmplyoee&training_id=' + training_id + '&showHTML=0';
        $.get(url, function(html){
            $('#employeeLinkPortal').html(html);
            Util.hideProgressInd();
        });
    },

}

