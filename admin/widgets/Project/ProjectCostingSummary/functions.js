$(function(){
    $("a.addCostingSummary").livequery('click', function (e){
        var title = "Costing Summary";
        var project_id    = $(this).attr('project_id');
        var url = 'index.php?widget=project_projectCostingSummary&_spAction=createCostingSummary'
                + '&showHTML=0&project_id='+project_id;
        var exp = {
            url: url
           ,callbackOnSuccess: function(){
                Util.closeAllDialogs();

                var mgsalert = 'Costing summary created successfully!';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 5000,
                });

                projectCostingSummary.reloadCostingSummaryDisplay(project_id);
            }
        };

        Util.openFormInDialog.call(this, 'costingSummaryForm', title, 900, 530, exp);
    });

    $("a.addActualCharges").livequery('click', function (e){
        var project_id    = $(this).attr('project_id');
        var title    = $(this).attr('title');

        var url = 'index.php?widget=project_projectCostingSummary&_spAction=addActualCharges'
                + '&showHTML=0&project_id='+project_id+'&title='+title;
        var exp = {
            url: url
           ,callbackOnSuccess: function(){
                Util.closeAllDialogs();

                var mgsalert = 'Actual charges added successfully!';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 5000,
                });

                projectCostingSummary.reloadCostingSummaryDisplay(project_id);
            }
        };

        Util.openFormInDialog.call(this, 'actualChargesForm', 'Add Actual Charges', 550, 500, exp);
    });

    $("a.clearcostingSummary").livequery('click', function (e){
        //var sketchObj    = $(this).closest('tr').find('.costingSummarySketch');
        var supplierObj  = $(this).closest('tr').find('.costingSummarySupplier');
        var productObj   = $(this).closest('tr').find('.poProductTitle');
        var subConObj    = $(this).closest('tr').find('.subConTitle');
        var qtyObj       = $(this).closest('tr').find('.costingSummaryQuantity');
        var unitPriceObj = $(this).closest('tr').find('.costingSummaryUnitPrice');
        var amountObj    = $(this).closest('tr').find('.costingSummaryAmount');
        var unitObj      = $(this).closest('tr').find('.costingSummaryUnit');
        var productIdObj = $(this).closest('tr').find('.product_id_hidden');
        var subConIdObj  = $(this).closest('tr').find('.sub_con_id_hidden');

        //sketchObj.val('');
        supplierObj.val('');
        productObj.val('');
        subConObj.val('');
        qtyObj.val('');
        unitPriceObj.val('');
        amountObj.val('');
        unitObj.val('');
        productIdObj.val('');
        subConIdObj.val('');
    });

    $("a.editCostingSummary").livequery('click', function (e){
        var title = "Edit Costing Summary";
        var project_id = $(this).attr('project_id');
        var costing_summary_id = $(this).attr('costing_summary_id');
        var url = 'index.php?widget=project_projectCostingSummary&_spAction=editCostingSummary'
                + '&showHTML=0&project_id='+project_id+'&costing_summary_id='+costing_summary_id;
        var exp = {
            url: url
           ,callbackOnSuccess: function(){
                Util.closeAllDialogs();

                var mgsalert = 'Costing summary updated successfully!';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 5000,
                });

                projectCostingSummary.reloadCostingSummaryDisplay(project_id);
            }
        };

        Util.openFormInDialog.call(this, 'costingSummaryForm', title, 900, 530, exp);
    });

    $("#costingSummaryForm select.costingSummarySupplier").livequery('change', function (e){
        var parent      = $(this).closest('tr');

        $("input.subConTitle", parent).val("");
        $("input.sub_con_id_hidden", parent).val("");
    });

    /* Adding row in new Line Item */
    $("#costingSummaryForm a.addRow").livequery('click', function (e){
        var project_id = $(this).attr('project_id');
        var url = 'index.php?widget=project_projectCostingSummary&_spAction=addLineItemRecord'
                + '&showHTML=0&project_id=' + project_id;

        $.get(url, '' ,function(html){
            $('#costingSummaryForm tr:last').after(html);
        });
    });

    $('.addMoreDetailsCostingRow').livequery('click', function (e){
        var link_text = $(this).html();

        if(link_text == '(+) Add More Details'){
            $('.addMoreDetailsCostingRow').text('(-) Hide More Details');
        }
        else{
            $('.addMoreDetailsCostingRow').text('(+) Add More Details');
        }

        $('.hideMoreCostingDetails').slideToggle();
    });

    $("#costingSummaryForm .costingSummaryQuantity").livequery('change', function (e){
        var quantity     = $(this).val();
        var amountObj    = $(this).closest('tr').find('.costingSummaryUnitPrice');
        var amount       = amountObj.val();
        var totalCostObj = $(this).closest('tr').find('.costingSummaryAmount');

        if (quantity > 0 && amount > 0) {
            var total_cost = quantity * amount;
            var total_cost_formatted = parseFloat(total_cost).toFixed(3);
            totalCostObj.val(total_cost_formatted);
        }

        if (quantity == "" && amount > 0) {
            var total_cost = amount;
            var total_cost_formatted = parseFloat(total_cost).toFixed(3);
            totalCostObj.val(total_cost_formatted);
        }

        var total_material_price = parseFloat(0);
        $("#costingSummaryForm input.costingSummaryAmount").each(function() {
            if(isNaN($(this).val()) || $(this).val() == undefined || $(this).val() == "") {
                total_material_price += parseFloat(0);
            } else {
                total_material_price += parseFloat($(this).val());                    
            }
        });

        $('.totalCostField input[name=total_material_price]').attr('value', total_material_price.toFixed(3));
        projectCostingSummary.populateTotalAmount();
    });

    $("#costingSummaryForm .costingSummaryUnitPrice").livequery('change', function (e){
        var amount       = $(this).val();
        var quantityObj  = $(this).closest('tr').find('.costingSummaryQuantity');
        var quantity     = quantityObj.val();
        var totalCostObj = $(this).closest('tr').find('.costingSummaryAmount');

        if (quantity > 0 && amount > 0) {
            var total_cost = quantity * amount;
            var total_cost_formatted = parseFloat(total_cost).toFixed(3);
            totalCostObj.val(total_cost_formatted);
        }

        if (quantity == "" && amount > 0) {
            var total_cost = amount;
            var total_cost_formatted = parseFloat(total_cost).toFixed(3);
            totalCostObj.val(total_cost_formatted);
        }

        var total_material_price = parseFloat(0);
        $("#costingSummaryForm input.costingSummaryAmount").each(function() {
            if(isNaN($(this).val()) || $(this).val() == undefined || $(this).val() == "") {
                total_material_price += parseFloat(0);
            } else {
                total_material_price += parseFloat($(this).val());                    
            }
        });

        $('.totalCostField input[name=total_material_price]').attr('value', total_material_price.toFixed(3));
        projectCostingSummary.populateTotalAmount();
    });

    $("#costingSummaryForm input.costingSummaryAmount").livequery('change', function (e){
        var total_material_price = parseFloat(0);
        $("#costingSummaryForm input.costingSummaryAmount").each(function() {
            if(isNaN($(this).val()) || $(this).val() == undefined || $(this).val() == "") {
                total_material_price += parseFloat(0);
            } else {
                total_material_price += parseFloat($(this).val());                    
            }
        });

        $('.totalCostField input[name=total_material_price]').attr('value', total_material_price.toFixed(3));
        projectCostingSummary.populateTotalAmount();
    });

    $("#costingSummaryForm .totalCostField input").livequery('change', function (e){
        var total_amount = parseFloat(0);
        $("#costingSummaryForm .totalCostField input").each(function() {
            if(isNaN($(this).val()) || $(this).val() == undefined || $(this).val() == "") {
                total_amount += parseFloat(0);
            } else {
                total_amount += parseFloat($(this).val());                    
            }
        });

        projectCostingSummary.populateTotalAmount();
    });

    $("#costingSummaryForm input[name=po_price]").livequery('change', function (e){
        var po_price = $(this).val();
        $("#costingSummaryForm input[name=po_price]").attr('value', parseFloat(po_price).toFixed(3));
        var gst_percentage = $("input[name=gst_percentage]").val();
        po_price_gst = parseFloat(po_price) + ((parseFloat(po_price) * parseFloat(gst_percentage)) / 100);

        $('input[name=invoiced_price]').attr('value', parseFloat(po_price).toFixed(3));
        $('input[name=invoiced_price_with_gst]').attr('value', po_price_gst.toFixed(3));
        $('input[name=po_price_with_gst]').attr('value', po_price_gst.toFixed(3));

        projectCostingSummary.populateTotalAmount();
    });

    $("#costingSummaryForm input[name=transport_charges]").livequery('change', function (e){
        var transport_charges = $(this).val();
        $("#costingSummaryForm input[name=transport_charges]").attr('value', parseFloat(transport_charges).toFixed(3));

        projectCostingSummary.populateTotalAmount();
    });

    $("#costingSummaryForm input[name=no_of_worker_used]").livequery('change', function (e){
        var no_of_worker_used    = $("#costingSummaryForm input[name=no_of_worker_used]").val();
        var no_of_days_worked    = $("#costingSummaryForm input[name=no_of_days_worked]").val();
        var labour_rates_per_day = $("#costingSummaryForm input[name=labour_rates_per_day]").val();
        
        var totalLabourCharges = parseFloat(0);

        if(isNaN(no_of_worker_used) || no_of_worker_used == undefined || no_of_worker_used == "") {
            no_of_worker_used = parseFloat(0);
        }
        
        if(isNaN(no_of_days_worked) || no_of_days_worked == undefined || no_of_days_worked == "") {
            no_of_days_worked = parseFloat(0);
        }

        if(isNaN(labour_rates_per_day) || labour_rates_per_day == undefined || labour_rates_per_day == "") {
            labour_rates_per_day = parseFloat(0);
        }
        
        totalLabourCharges = parseFloat(no_of_worker_used) * parseFloat(no_of_days_worked) * parseFloat(labour_rates_per_day);
        
        $("#costingSummaryForm input[name=total_labour_charges]").attr('value', totalLabourCharges.toFixed(3));
        projectCostingSummary.populateTotalAmount();
    });

    $("#costingSummaryForm input[name=no_of_days_worked]").livequery('change', function (e){
        var no_of_worker_used    = $("#costingSummaryForm input[name=no_of_worker_used]").val();
        var no_of_days_worked    = $("#costingSummaryForm input[name=no_of_days_worked]").val();
        var labour_rates_per_day = $("#costingSummaryForm input[name=labour_rates_per_day]").val();
        
        var totalLabourCharges = parseFloat(0);

        if(isNaN(no_of_worker_used) || no_of_worker_used == undefined || no_of_worker_used == "") {
            no_of_worker_used = parseFloat(0);
        }
        
        if(isNaN(no_of_days_worked) || no_of_days_worked == undefined || no_of_days_worked == "") {
            no_of_days_worked = parseFloat(0);
        }

        if(isNaN(labour_rates_per_day) || labour_rates_per_day == undefined || labour_rates_per_day == "") {
            labour_rates_per_day = parseFloat(0);
        }
        
        totalLabourCharges = parseFloat(no_of_worker_used) * parseFloat(no_of_days_worked) * parseFloat(labour_rates_per_day);
        
        $("#costingSummaryForm input[name=total_labour_charges]").attr('value', totalLabourCharges.toFixed(3));
        projectCostingSummary.populateTotalAmount();    
    });

    $("#costingSummaryForm input[name=labour_rates_per_day]").livequery('change', function (e){
        var no_of_worker_used    = $("#costingSummaryForm input[name=no_of_worker_used]").val();
        var no_of_days_worked    = $("#costingSummaryForm input[name=no_of_days_worked]").val();
        var labour_rates_per_day = $("#costingSummaryForm input[name=labour_rates_per_day]").val();
        
        var totalLabourCharges = parseFloat(0);

        if(isNaN(no_of_worker_used) || no_of_worker_used == undefined || no_of_worker_used == "") {
            no_of_worker_used = parseFloat(0);
        }
        
        if(isNaN(no_of_days_worked) || no_of_days_worked == undefined || no_of_days_worked == "") {
            no_of_days_worked = parseFloat(0);
        }

        if(isNaN(labour_rates_per_day) || labour_rates_per_day == undefined || labour_rates_per_day == "") {
            labour_rates_per_day = parseFloat(0);
        }
        
        totalLabourCharges = parseFloat(no_of_worker_used) * parseFloat(no_of_days_worked) * parseFloat(labour_rates_per_day);
        
        $("#costingSummaryForm input[name=total_labour_charges]").attr('value', totalLabourCharges.toFixed(3));
        projectCostingSummary.populateTotalAmount();
    });

    $("#costingSummaryForm input[name=salesman_commission_percentage]").livequery('change', function (e){
        var salesman_commission_percentage = $(this).val();

        var invoiced_price   = $("#costingSummaryForm input[name=invoiced_price]").val();
        if(isNaN(invoiced_price) || invoiced_price == undefined || invoiced_price == "") {
            invoiced_price = parseFloat(0);
        }

        if(isNaN(salesman_commission_percentage) || salesman_commission_percentage == undefined || salesman_commission_percentage == "") {
            salesman_commission_percentage = parseFloat(0);
        }

        salesman_commission = (parseFloat(invoiced_price) * parseFloat(salesman_commission_percentage)) / parseFloat(100);
        if(isNaN(salesman_commission) || salesman_commission == undefined || salesman_commission == "") {
            salesman_commission = parseFloat(0);
        }

        $("#costingSummaryForm input[name=salesman_commission]").attr('value', salesman_commission.toFixed(3));
        projectCostingSummary.populateTotalAmount();
    });

    $("#costingSummaryForm input[name=finance_charges_percentage]").livequery('change', function (e){
        var finance_charges_percentage = $(this).val();

        var invoiced_price   = $("#costingSummaryForm input[name=invoiced_price]").val();
        if(isNaN(invoiced_price) || invoiced_price == undefined || invoiced_price == "") {
            invoiced_price = parseFloat(0);
        }

        if(isNaN(finance_charges_percentage) || finance_charges_percentage == undefined || finance_charges_percentage == "") {
            finance_charges_percentage = parseFloat(0);
        }

        finance_charges     = (parseFloat(invoiced_price) * parseFloat(finance_charges_percentage)) / parseFloat(100);
        if(isNaN(finance_charges) || finance_charges == undefined || finance_charges == "") {
            finance_charges = parseFloat(0);
        }

        $("#costingSummaryForm input[name=finance_charges]").attr('value', finance_charges.toFixed(3));
        projectCostingSummary.populateTotalAmount();
    });
    
    $("#costingSummaryForm input[name=office_overheads_percentage]").livequery('change', function (e){
        var office_overheads_percentage = $(this).val();

        var invoiced_price   = $("#costingSummaryForm input[name=invoiced_price]").val();
        if(isNaN(invoiced_price) || invoiced_price == undefined || invoiced_price == "") {
            invoiced_price = parseFloat(0);
        }

        if(isNaN(office_overheads_percentage) || office_overheads_percentage == undefined || office_overheads_percentage == "") {
            office_overheads_percentage = parseFloat(0);
        }

        office_overheads    = (parseFloat(invoiced_price) * parseFloat(office_overheads_percentage)) / parseFloat(100);
        if(isNaN(office_overheads) || office_overheads == undefined || office_overheads == "") {
            office_overheads = parseFloat(0);
        }
        
        $("#costingSummaryForm input[name=office_overheads]").attr('value', office_overheads.toFixed(3));
        projectCostingSummary.populateTotalAmount();
    });

    $("#costingSummaryForm input[name=transport_charges_percentage]").livequery('change', function (e){
        var transport_charges_percentage = $(this).val();
        $("#costingSummaryForm input[name=transport_charges_percentage]").attr('value', transport_charges_percentage);
        projectCostingSummary.populateTotalAmount();
    });

    $("#costingSummaryForm input[name=invoiced_price]").livequery('change', function (e){
        var invoiced_price = $(this).val();
        var gst_percentage = $("input[name=gst_percentage]").val();
        invoiced_price_gst = parseFloat(invoiced_price) + ((parseFloat(invoiced_price) * parseFloat(gst_percentage)) / 100);

        $('input[name=invoiced_price_with_gst]').val(invoiced_price_gst.toFixed(3));
    });

    $("#costingSummaryForm input[name=profit_percentage]").livequery('change', function (e){
        var profit_percentage = $(this).val();
        var po_price = $("input[name=po_price]").val();
        profit = (parseFloat(po_price) * parseFloat(profit_percentage)) / 100;

        $('input[name=profit]').val(profit.toFixed(3));
    });

    $("input[name='sub_con_name[]']").livequery(projectCostingSummary.subConName);

    $('#costingSummaryForm .addNewSupplierPopup').livequery('click', function (e){
        var title = "Add Supplier";
        e.preventDefault();

        var expObj = {
            validate: true
           ,callbackOnSuccess: function(){
                var supplierObj = $("#costingSummaryForm .costingSummarySupplier");
                var url = $('#scopeRootAlias').val() + 'index.php?widget=project_projectCostingSummary&_spAction=supplierByJSON&showHTML=0';
                $.ajax({
                    type: "GET",
                    url: url,
                    async: false,
                    dataType: 'json',
                    success: function(json){
                        supplierObj.empty();
                        $.each(json, function() {
                            supplierObj.append(new Option(this.caption, this.value));
                        });
                    }
                });

                $('#dialog1').dialog('close');
                $('#dialog1').dialog('destroy');
                $('#dialog1').remove();
            }
        }

        Util.openFormInDialog.call(this, 'AddNewSupplierPortalForm', title, 530, 532, expObj);
    });

    $("#costingSummaryForm input[name='product_title[]']").livequery(projectCostingSummary.poProductTitle);
});

var projectCostingSummary = {
    reloadCostingSummaryDisplay: function(project_id){
        var url = 'index.php?widget=project_projectCostingSummary&_spAction=rowsHTML&showHTML=0';
        Util.showProgressInd();
        $.get(url, {project_id: project_id}, function(html){
            Util.hideProgressInd();
            $('#costingSummaryPortal').html(html);
        });
    },

    subConName: function(){
        var titleObj = this;
        $(titleObj).autocomplete({
             source : 'index.php?widget=project_projectCostingSummary&_spAction=searchSubCon&showHTML=0'
            ,minLength : 1
            ,selectFirst: true
            ,autoFocus: true
            ,select: function(event, ui) {
                var selectedObj    = ui.item;
                var sub_con_id     = selectedObj.id;
                var parent         = $(this).closest('tr');

                $("select.costingSummarySupplier", parent).val("");
                //$("input.poProductTitle", parent).val("");
                //$("input.product_id_hidden", parent).val("");
                $("input[name='sub_con_id[]']", parent).val(sub_con_id);
            }
        });
    },

    populateTotalAmount: function(project_id){
        var invoiced_price   = $("#costingSummaryForm input[name=invoiced_price]").val();
        if(isNaN(invoiced_price) || invoiced_price == undefined || invoiced_price == "") {
            invoiced_price = parseFloat(0);
        }
    
        var transport_charges_percentage   = $("#costingSummaryForm input[name=transport_charges_percentage]").val();
        var salesman_commission_percentage = $("#costingSummaryForm input[name=salesman_commission_percentage]").val();
        var finance_charges_percentage     = $("#costingSummaryForm input[name=finance_charges_percentage]").val();
        var office_overheads_percentage    = $("#costingSummaryForm input[name=office_overheads_percentage]").val();

        if(isNaN(salesman_commission_percentage) || salesman_commission_percentage == undefined || salesman_commission_percentage == "") {
            salesman_commission_percentage = parseFloat(0);
        }

        if(isNaN(finance_charges_percentage) || finance_charges_percentage == undefined || finance_charges_percentage == "") {
            finance_charges_percentage = parseFloat(0);
        }

        if(isNaN(office_overheads_percentage) || office_overheads_percentage == undefined || office_overheads_percentage == "") {
            office_overheads_percentage = parseFloat(0);
        }

        salesman_commission = (parseFloat(invoiced_price) * parseFloat(salesman_commission_percentage)) / parseFloat(100);
        if(isNaN(salesman_commission) || salesman_commission == undefined || salesman_commission == "") {
            salesman_commission = parseFloat(0);
        }
        $("#costingSummaryForm input[name=salesman_commission]").attr('value', salesman_commission.toFixed(3));
        
        finance_charges     = (parseFloat(invoiced_price) * parseFloat(finance_charges_percentage)) / parseFloat(100);
        if(isNaN(finance_charges) || finance_charges == undefined || finance_charges == "") {
            finance_charges = parseFloat(0);
        }
        $("#costingSummaryForm input[name=finance_charges]").attr('value', finance_charges.toFixed(3));
        
        office_overheads    = (parseFloat(invoiced_price) * parseFloat(office_overheads_percentage)) / parseFloat(100);
        if(isNaN(office_overheads) || office_overheads == undefined || office_overheads == "") {
            office_overheads = parseFloat(0);
        }
        $("#costingSummaryForm input[name=office_overheads]").attr('value', office_overheads.toFixed(3));
        
        transport_charges = (parseFloat(invoiced_price) * parseFloat(transport_charges_percentage)) / parseFloat(100);
        if(isNaN(transport_charges) || transport_charges == undefined || transport_charges == "") {
            transport_charges = parseFloat(0);
        }
        $("#costingSummaryForm input[name=transport_charges]").attr('value', transport_charges.toFixed(3));

        var total_amount = parseFloat(0);
        $("#costingSummaryForm .totalCostField input").each(function() {
            if(isNaN($(this).val()) || $(this).val() == undefined || $(this).val() == "") {
                total_amount += parseFloat(0);
            } else {
                total_amount += parseFloat($(this).val());                    
            }
        });
        
        var marginPercentage = ((parseFloat(invoiced_price) - parseFloat(total_amount)) / parseFloat(invoiced_price)) * parseFloat(100);
        if(isNaN(marginPercentage) || marginPercentage == undefined || marginPercentage == "" || marginPercentage == "-Infinity") {
            marginPercentage = parseFloat(0);
        }

        var marginAmount     = (parseFloat(invoiced_price) * parseFloat(marginPercentage))/ parseFloat(100);
        if(isNaN(marginAmount) || marginAmount == undefined || marginAmount == "") {
            marginAmount = parseFloat(0);
        }


        $("#costingSummaryForm input[name=profit_percentage]").attr('value', marginPercentage.toFixed(3));
        $("#costingSummaryForm input[name=profit]").attr('value', marginAmount.toFixed(3));
        $('.totalCostValue input').attr('value', total_amount.toFixed(3));
    },

    poProductTitle: function() {
        var titleObj = this;
        $(titleObj).autocomplete({
            source: function(request, response) {
                $.ajax({
                  url: 'index.php?widget=project_projectCostingSummary&_spAction=searchProductTitle&showHTML=0',
                  dataType: "json",
                  data: request,                    
                  success: function (data) {
                    if (data.length == 0) {
                        var parent = titleObj.closest('tr');
                        $("input[name='product_id[]']", parent).val("");
                        response("");
                    } else {
                      response(data);
                    }

                  }
                });
            },

            minLength : 1,
            electFirst: true,
            autoFocus: true,
            select: function(event, ui) {
                var selectedObj  = ui.item;
                var product_id   = selectedObj.id;
                var parent       = $(this).closest('tr');

                //$("input.subConTitle ", parent).val("");
                //$("input.sub_con_id_hidden", parent).val("");
                $("input[name='product_id[]']", parent).val(product_id);
            }
        });
    },

    /*subConName: function() {
        var titleObj = this;
        $(titleObj).autocomplete({
             source : 'index.php?widget=project_projectCostingSummary&_spAction=searchSubCon&showHTML=0'
            ,minLength : 1
            ,selectFirst: true
            ,autoFocus: true
            ,select: function(event, ui) {
                var selectedObj    = ui.item;
                var sub_con_id     = selectedObj.id;
                var parent         = $(this).closest('tr');

                $("input[name='sub_con_id[]']", parent).val(sub_con_id);
            }
        });
    }*/    
}