Util.createCPObject('cpm.enggCrm.project');

cpm.enggCrm.project = {
    init : function(){
        //$("a.editForQuote").livequery('click', function (e){
       // Used in ACS CRM
       $('.viewQuoteLog').live('click', function (e){
            e.preventDefault();
            var expObj = {
                beforeCloseFn: function(){
                    Util.closeAllDialogs();
                }
            }
            Util.openDialogForLink.call(this, 'Quote History', 1100, 550, expObj);
        });

        $('a.editForQuote').livequery('click', cpm.enggCrm.project.quoteEditInProject);

        $('select#fld_company_id').change(function() {
            var company_id = $(this).val();

            var url = 'index.php?module=enggCrm_contact&_spAction=contactByCompanyJSON&showHTML=0';
            $.get(url, {company_id: company_id}, function (data) {
                $('#fld_contact_id').cp_loadSelect(data);
            }, 'json');
        });

        /* Adding 5 Line Items in New window */
        $("a.addMultipleLineItem").livequery('click', function (e){
            var title = "Add Line Item";
            var project_id = $(this).attr('project_id');
            var quote_id = $(this).attr('quote_id');
            var url = 'index.php?module=enggCrm_project&_spAction=addMultipleLineItem'
                    + '&showHTML=0&project_id=' + project_id + '&quote_id=' + quote_id;
            var exp = {
                url: url
               ,callbackOnSuccess: function(){
                    var msg = 'Line Items created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        window.location.reload(true);
                    });
                }
            };

            /*
            var link_text = $(this).closest('.quoteLayoutShow').val();
            var parent = $(this).closest('.quoteDetailRow');
            if(link_text == 'View Line Items'){
                $('.quoteLayoutShow', parent).text('Hide');
            } else {
                $('.quoteLayoutShow', parent).text('View Line Items');
            }
            $('.showAddLineRow', parent).slideToggle();
            */

            Util.openFormInDialog.call(this, 'addMultipleLineItemForm', title, 1100, 500, exp);
        });

        /* Adding row in new Line Item */
        $("a.addRow").livequery('click', function (e){
            var url = 'index.php?module=enggCrm_project&_spAction=addLineItemRecord'
                    + '&showHTML=0';

            $.get(url, '' ,function(html){
                $('#addMultipleLineItemForm tr:last').after(html);
            });
        });

        $("#addMultipleLineItemForm .lineItemQuantity").livequery('change', function (e){
            var quantity = $(this).val();
            var amountObj = $(this).closest('tr').find('.lineItemAmount');
            var amount = amountObj.val();
            var totalCostObj = $(this).closest('tr').find('.totalCost');

            if (quantity > 0 && amount > 0) {
                var total_cost = quantity * amount;
                var total_cost_formatted = (total_cost).toFixed(2);
                totalCostObj.html(total_cost_formatted);
            }
        });

        $("#addMultipleLineItemForm .lineItemAmount").livequery('change', function (e){
            var amount = $(this).val();
            var quantityObj = $(this).closest('tr').find('.lineItemQuantity');
            var quantity = quantityObj.val();
            var totalCostObj = $(this).closest('tr').find('.totalCost');

            if (quantity > 0 && amount > 0) {
                var total_cost = quantity * amount;
                var total_cost_formatted = (total_cost).toFixed(2);
                totalCostObj.html(total_cost_formatted);
            }
        });

        $("a.clearLineItem").livequery('click', function (e){
            var titleObj = $(this).closest('tr').find('.lineItemTitle');
            var quantityObj = $(this).closest('tr').find('.lineItemQuantity');
            var unitObj = $(this).closest('tr').find('.lineItemUnit');
            var amountObj = $(this).closest('tr').find('.lineItemAmount');
            var totalCostObj = $(this).closest('tr').find('.totalCost');
            var descriptionObj = $(this).closest('tr').find('.lineItemDescription');
            var remarksObj = $(this).closest('tr').find('.lineItemRemarks');

            titleObj.val('');
            quantityObj.val('');
            unitObj.val('');
            amountObj.val('');
            totalCostObj.html('');
            descriptionObj.val('');
            remarksObj.val('');
        });

        /* Adding 5 Materials in New window */
        $("a.addMultipleMaterials").livequery('click', function (e){
            var title = "Add Materials";
            var project_id = $(this).attr('project_id');
            var url = 'index.php?module=enggCrm_project&_spAction=addMultipleMaterials'
                    + '&showHTML=0&project_id=' + project_id;
            var exp = {
                url: url
               ,callbackOnSuccess: function(){
                    var msg = 'Materials added successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        window.location.reload(true);
                    });
                }
            };
            Util.openFormInDialog.call(this, 'addMultipleMaterialsForm', title, 1100, 500, exp);
        });

        /* Adding row in new material */
        $("a.addMaterialRow").livequery('click', function (e){
            var url = 'index.php?module=enggCrm_project&_spAction=addMaterialRecord'
                    + '&showHTML=0';

            $.get(url, '' ,function(html){
                $('#addMultipleMaterialsForm tr:last').after(html);
            });
        });

        $('.cancelMaterial').livequery('click', function (e){
            msg = "Are you sure you want to cancel the entry? You cannot undo this action!";
            if (!confirm(msg)){
                return false;
            } else {
                Util.showProgressInd();
                var url = 'index.php?module=enggCrm_project&_spAction=cancelMaterial&showHTML=0';
                var project_materials_id = $(this).attr('project_materials_id');
                $.get(url,{project_materials_id: project_materials_id}, function(html){
                    alert ('Material Cancelled Succesfully');
                    Util.hideProgressInd();
                    window.location.reload(true);
                });
            }
        });

        $('.cancelPoItem').livequery('click', function (e){
            msg = "Are you sure you want to cancel the entry? You cannot undo this action!";
            if (!confirm(msg)){
                return false;
            } else {
                Util.showProgressInd();
                var url = 'index.php?module=enggCrm_project&_spAction=cancelPoItem&showHTML=0';
                var po_product_id = $(this).attr('po_product_id');
                $.get(url,{po_product_id: po_product_id}, function(html){
                    alert ('Item Cancelled Succesfully');
                    Util.hideProgressInd();
                    window.location.reload(true);
                });
            }
        });

        /* Adding 5 Purchase Order rows in New window */
        $("a.addMultiplePurchaseOrder").livequery('click', function (e){
            var title = "Add Purchase Order";
            var project_id = $(this).attr('project_id');
            var url = 'index.php?module=enggCrm_project&_spAction=addMultiplePurchaseOrder'
                    + '&showHTML=0&project_id=' + project_id;
            var exp = {
                url: url
               ,callbackOnSuccess: function(){
                    var msg = 'Purchase Order created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        window.location.reload(true);
                    });
                }
            };
            Util.openFormInDialog.call(this, 'addMultiplePurchaseOrderForm', title, 1200, 500, exp);
        });

        $("#addMultiplePurchaseOrderForm .poQuantity").livequery('change', function (e){
            var quantity = $(this).val();
            var amountObj = $(this).closest('tr').find('.poAmount');
            var amount = amountObj.val();
            var totalCostObj = $(this).closest('tr').find('.totalCost');

            if (quantity > 0 && amount > 0) {
                var total_cost = quantity * amount;
                var total_cost_formatted = (total_cost).toFixed(2);
                totalCostObj.html(total_cost_formatted);
            }
        });

        $("#addMultiplePurchaseOrderForm .poAmount").livequery('change', function (e){
            var amount = $(this).val();
            var quantityObj = $(this).closest('tr').find('.poQuantity');
            var quantity = quantityObj.val();
            var totalCostObj = $(this).closest('tr').find('.totalCost');

            if (quantity > 0 && amount > 0) {
                var total_cost = quantity * amount;
                var total_cost_formatted = (total_cost).toFixed(2);
                totalCostObj.html(total_cost_formatted);
            }
        });

        /* Adding row in new Purchase Order */
        $("a.addSinglePoRow").livequery('click', function (e){
            var url = 'index.php?module=enggCrm_project&_spAction=addSinglePurchaseOrderRecord'
                    + '&showHTML=0';

            $.get(url, '' ,function(html){
                $('#addMultiplePurchaseOrderForm tr:last').after(html);
            });
        });

        /* Clear data in Purchase Order */
        $("a.clearPo").livequery('click', function (e){
            var supplierObj = $(this).closest('tr').find('.poSupplier');
            var supplierId = supplierObj.val();
            var titleObj = $(this).closest('tr').find('.poTitle');
            var quantityObj = $(this).closest('tr').find('.poQuantity');
            var unitObj = $(this).closest('tr').find('.poUnit');
            var amountObj = $(this).closest('tr').find('.poAmount');
            var descriptionObj = $(this).closest('tr').find('.poDescription');
            var totalCostObj = $(this).closest('tr').find('.totalCost');

            supplierObj.val('');
            titleObj.val('');
            quantityObj.val('');
            unitObj.val('');
            amountObj.val('');
            totalCostObj.html('');
            descriptionObj.val('');
        });

        $('#frmEdit select#fld_company_id').livequery('change', function(){
            var url = 'index.php?module=enggCrm_contact&_spAction=contactByCompanyJSON&showHTML=0';
            var company_id = $('#fld_company_id').val();
            $.get(url, {company_id: company_id}, function (data) {
                $('#fld_contact_id').cp_loadSelect(data);
            }, 'json');
        });
        $('.m-enggCrm_project a.duplicateQuote').livequery('click', function(){
            msg = "Would you like to Add Duplicate Quote?";

            if (!confirm(msg)){
                return false;
            }
            else{
                Util.showProgressInd();

                var project_id     = $(this).attr('project_id');
                var quote_id       = $(this).attr('quote_id');
                var quote_items_id = $(this).attr('quote_items_id');

               var url = 'index.php?module=enggCrm_project&_spAction=duplicateQuote&showHTML=0' +
                        '&project_id=' + project_id +
                        '&quote_id=' + quote_id +
                        '&quote_items_id=' + quote_items_id;

                $.get(url, {project_id: project_id, quote_id: quote_id, quote_items_id: quote_items_id}, function(html){
                    alert('Quote Record Created Successfully');
                    window.location.reload(true);

                });
            }

        });

        $('a.generateOrderRecords').livequery('click', function(){
            var link_text = $(this).html();

            if(link_text == 'Update Finance Record'){
                msg = "Would you like to Update Finance records?";
            }else{
                msg = "Would you like to Generate Finance records?";
            }

            if (!confirm(msg)){
                return false;
            } else {
                Util.showProgressInd();
                var project_id = $(this).attr('project_id');
                var quote_id   = $(this).attr('quote_id');

               var url = "index.php?module=enggCrm_project&_spAction=generateOrderRecords&showHTML=0";

                $.get(url, {project_id: project_id, quote_id: quote_id}, function(html){
                    alert('Finance records created successfully');
                    window.location.reload(true);
                });
            }
        });


        $('a.generateManpowerOrderRecords').livequery('click', function(){
            var link_text = $(this).html();

            if(link_text == 'Update Finance Record'){
                msg = "Would you like to Update Finance records?";
            }else{
                msg = "Would you like to Generate Finance records?";
            }

            if (!confirm(msg)){
                return false;
            } else {
                Util.showProgressInd();
                var project_id = $(this).attr('project_id');
                var quote_id   = $(this).attr('quote_id');

               var url = "index.php?module=enggCrm_project&_spAction=generateOrderManpowerRecords&showHTML=0";

                $.get(url, {project_id: project_id, quote_id: quote_id}, function(html){
                    alert('Finance records created successfully');
                    window.location.reload(true);
                });
            }
        });



        /* Adding Hours for the project employee */
        $("a.editTimesheetForProjectEmployee").livequery('click', function (e){
            var title = "Edit Timesheet";
            var project_id = $(this).attr('project_id');
            var month = $(this).attr('month');
            var year = $(this).attr('year');
            var url = 'index.php?module=enggCrm_project&_spAction=editHoursProjectEmployee'
                    + '&showHTML=0&project_id='+project_id+'&month='+month+'&year='+year;

            var exp = {
                url: url
               ,callbackOnSuccess: function(){
                    var msg = 'Timesheet Updated successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        window.location.reload(true);
                    });
                }
            };
            Util.openFormInDialog.call(this, 'addMultipleHoursEmployeeForm', title, 1228, 500, exp);
        });


        /* Adding Hours for the project employee */
        $("a.addTimesheetForProjectEmployee").livequery('click', function (e){
            var title = "Add Timesheet";
            var project_id = $(this).attr('project_id');
            var url = 'index.php?module=enggCrm_project&_spAction=addHoursProjectEmployee'
                    + '&showHTML=0&project_id=' + project_id;

            var exp = {
                url: url
               ,callbackOnSuccess: function(){
                    var msg = 'Timesheet created successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        window.location.reload(true);
                    });
                }
            };
            Util.openFormInDialog.call(this, 'addMultipleHoursEmployeeForm', title, 1228, 500, exp);
        });

        $("input.timeSheetDaysInput").live("keyup", function() {
            var totalHours   = 0;
            var parent_td = $(this).parent('th');
            var totalDays = $(this).attr('totalDays');
            var employee_id = $(this).attr('employee_id');
            var currentInputNo = $(this).attr('currentInputNo');
            var inputval = $(this).val();
            if(inputval != ''){
                for ( var i = 1; i<=totalDays; i++ ){
                    var inputval   = $('#timeSheetDays_'+employee_id+'_'+i).val();

                    if(inputval == undefined){
                       inputval = parseInt(0);
                    }

                    totalHours += Number(inputval);
                }

                $("#timeSheetTotalHours_"+employee_id).val(totalHours.toFixed(2));

            }
        });

        $("input.timeSheetDaysNormalInput").live("keyup", function() {
            var totalHours   = 0;
            var parent_td = $(this).parent('th');
            var totalDays = $(this).attr('totalDays');
            var employee_id = $(this).attr('employee_id');
            var currentInputNo = $(this).attr('currentInputNo');
            var inputval = $(this).val();
            if(inputval != ''){
                for ( var i = 1; i<=totalDays; i++ ){
                    var inputval   = $('#timeSheetDays_'+employee_id+'_'+i).val();

                    if(inputval == undefined){
                       inputval = parseInt(0);
                    }

                    totalHours += Number(inputval);
                }

                $("#timeSheetTotalHours_"+employee_id).val(totalHours.toFixed(2));

            }
        });

        $("input.timeSheetDaysOTInput").live("keyup", function() {
            var totalOTHours = 0;
            var parent_td = $(this).parent('th');
            var totalDays = $(this).attr('totalDays');
            var employee_id = $(this).attr('employee_id');
            var currentInputNo = $(this).attr('currentInputNo');
            var inputval = $(this).val();
            if(inputval != ''){
                for ( var i = 1; i<=totalDays; i++ ){
                    var inputvalOT = $('#timeSheetOTDays_'+employee_id+'_'+i).val();

                    if(inputvalOT == undefined){
                       inputvalOT = parseInt(0);
                    }

                    totalOTHours += Number(inputvalOT);
                }

                $("#timeSheetOTTotalHours_"+employee_id).val(totalOTHours.toFixed(2));

            }
        });

        $("input.timeSheetDaysPHInput").live("keyup", function() {
            var totalPHHours = 0;
            var parent_td = $(this).parent('th');
            var totalDays = $(this).attr('totalDays');
            var employee_id = $(this).attr('employee_id');
            var currentInputNo = $(this).attr('currentInputNo');
            var inputval = $(this).val();
            if(inputval != ''){
                for ( var i = 1; i<=totalDays; i++ ){
                    var inputvalPH = $('#timeSheetPHDays_'+employee_id+'_'+i).val();

                    if(inputvalPH == undefined){
                       inputvalPH = parseInt(0);
                    }

                    totalPHHours += Number(inputvalPH);
                }

                $("#timeSheetPHTotalHours_"+employee_id).val(totalPHHours.toFixed(2));

            }
        });

        $(".timeSheetDaysInput").live("keydown", function (e) {
            var keyCode = e.keyCode ? e.keyCode : e.which;
            var parent_th = $(this).parent('th');

            if (keyCode == 13) {
                parent_th.next('th').find('input').focus();
            }

        });

        $(".timeSheetDaysNormalInput").live("keydown", function (e) {
            var keyCode = e.keyCode ? e.keyCode : e.which;
            var parent_th = $(this).parent('th');

            if (keyCode == 13) {
                parent_th.find('input.timeSheetDaysOTInput').focus();
            }

        });

        $(".timeSheetDaysOTInput").live("keydown", function (e) {
            var keyCode = e.keyCode ? e.keyCode : e.which;
            var parent_th = $(this).parent('th');

            if (keyCode == 13) {
                parent_th.find('input.timeSheetDaysPHInput').focus();
            }

        });

        $(".timeSheetDaysPHInput").live("keydown", function (e) {
            var keyCode = e.keyCode ? e.keyCode : e.which;
            var parent_th = $(this).parent('th');

            if (keyCode == 13) {
                //parent_th.next('th').find('input.timeSheetDaysNormalInput').focus();
                $(':input:eq(' + ($(':input').index(this) + 1) +')').focus();
            }

        });

        $(".timesheetDaysTdRate input").live("keydown", function (e) {
            var keyCode = e.keyCode ? e.keyCode : e.which;
            var employee_id = $(this).attr('employee_id');
            if (keyCode == 13) {
                $(':input:eq(' + ($(':input').index(this) + 1) +')').focus();
            }
         });

        $("select[name=project_Time_year]").livequery("change", function() {
            var project_id     = $("input[name=project_id]").val();
            var selected_year  = $(this).val();
            var selected_month = $("select[name=project_Time_Month]").val();
            Util.showProgressInd();
            cpm.enggCrm.project.reloadDaysInTimesheet(project_id, selected_year, selected_month);
        });

        $("select[name=project_Time_Month]").livequery("change", function() {
            var project_id     = $("input[name=project_id]").val();
            var selected_year  = $("select[name=project_Time_year]").val();
            var selected_month = $(this).val();
            Util.showProgressInd();
            cpm.enggCrm.project.reloadDaysInTimesheet(project_id, selected_year, selected_month);
        });

        $('.employeeListShow').livequery('click', function (e){
            var link_text = $(this).html();
            var parent = $(this).closest('.employeeMonthRow');

            if(link_text == 'View Staff'){
                $('.employeeListShow', parent).text('Hide Staff');
            }
            else{
                $('.employeeListShow', parent).text('View Staff');
            }

            $('.employeeListHide', parent).slideToggle();
        });

        $('.quotecolumnCheckBox input[type=checkbox]').livequery('click',function(){
            var title   = $(this).val();
            var cboxObj   = $(this);
            var cbObj = $('input[type=checkbox]');
            var checked = cbObj.is(":checked") ? true : false;
            var url = 'index.php?module=enggCrm_project&_spAction=addQuoteColumn&showHTML=0';
            var urldelete = 'index.php?module=enggCrm_project&_spAction=deleteQuoteColumn&showHTML=0';
            var project_id = $('#project_id').val();
            Util.showProgressInd();
            if (!cboxObj.attr('checked')){
                $.get(url,{title:title,project_id:project_id}, function(){
                    Util.alert('column is removed!');
                    Util.hideProgressInd();
                });
            }
            else{
                $.get(urldelete,{title:title,project_id:project_id}, function(){
                    Util.alert('column is added!');
                    Util.hideProgressInd();
            });
        }
        });

        $('.project_employee_in').livequery('click',function(){
            var cboxObj   = $(this);
            var cbObj = $('input[type=checkbox]');
            var checked = cbObj.is(":checked") ? true : false;
            var url = 'index.php?module=enggCrm_project&_spAction=addRemoveEmployeeToProject&showHTML=0';
            var project_id = $('#record_id').val();
            var employee_id = $(this).val();

            Util.showProgressInd();
            if (!cboxObj.attr('checked')){
                $.get(url,{employee_id:employee_id, project_id:project_id, active_in_project:0}, function(){
                    Util.hideProgressInd();
                    var mgsalert='Employee Removed From Project!';
                    var n = noty({
                        text: mgsalert,
                        type: 'confirm',
                        dismissQueue: true,
                        layout: 'topCenter',
                        theme: 'defaultTheme',
                        timeout: 2000,
                    });
                });
            }
            else{
                $.get(url,{employee_id:employee_id, project_id:project_id, active_in_project:1}, function(){
                    Util.hideProgressInd();
                    var mgsalert='Employee Added To Project!';
                    var n = noty({
                        text: mgsalert,
                        type: 'confirm',
                        dismissQueue: true,
                        layout: 'topCenter',
                        theme: 'defaultTheme',
                        timeout: 2000,
                    });
                });
            }
        });

        $('.timesheetLayoutShow').livequery('click', function (e){
            var link_text = $(this).html();
            var parent = $(this).closest('.addEmployeeRow2');

            if(link_text == 'View Hours'){
                $('.timesheetLayoutShow',parent).text('Hide Hours');
            }
            else{
                $('.timesheetLayoutShow',parent).text('View Hours');
            }

            parent.next('.timesheetLayoutHide').slideToggle();
        });

        $('#addQuoteProject').livequery('click', function(){
            msg = "Do you like to Add Quote?";

            if (!confirm(msg)){
                return false;
            }
            else{
                Util.showProgressInd();
                var project_id = $(this).attr('project_id');
                var url = 'index.php?module=enggCrm_project&_spAction=addQuoteFormSubmit&showHTML=0&id=' + project_id;
                $.get(url, {project_id: project_id}, function(html){
                    alert('Quote Record Created Successfully');
                    window.location.reload(true);
                });
                //Util.hideProgressInd();
            }
        });

        $('.m-enggCrm_project a.deleteLineItem').livequery('click', function (e){
            msg = "Do you like to delete the Quote Line Item?";
            if (!confirm(msg)){
                return false;
            }
            else{
                Util.showProgressInd();
                var quote_items_id = $(this).attr('quote_items_id');
                var url = 'index.php?module=enggCrm_project&_spAction=deleteLineItem&showHTML=0&quote_items_id=' + quote_items_id;
                $.get(url, {quote_items_id: quote_items_id}, function(html){
                    Util.hideProgressInd();
                    alert ('Add Line Item Deleted Succesfully');
                    window.location.reload(true);
                });
            }
        });

        $('.m-enggCrm_project a.deleteAddQuote').livequery('click', function (e){
            msg = "Do you like to delete the Quote?";

            if (!confirm(msg)){
                return false;
            }
            else{
                Util.showProgressInd();
                var quote_id = $(this).attr('quote_id');
                var url = 'index.php?module=enggCrm_project&_spAction=deleteAddQuote&showHTML=0&quote_id=' + quote_id;
                $.get(url, {quote_id: quote_id}, function(html){
                    Util.hideProgressInd();
                    alert ('Quote Deleted Succesfully');
                    window.location.reload(true);
                });
            }
        });

        $('a.addLineItem').livequery('click', function (e){
            /*msg = "Do you like to Update Markup?";
            if (!confirm(msg)){
                return false;
            }
            else{*/
                var title = "Add Line Item";
                e.preventDefault();
                var expObj = {
                    validate: true
                   ,callbackOnSuccess: function(){
                        Util.closeAllDialogs();
                        alert('Add Line Item Created Successfully');
                        window.location.reload(true);
                    }
                }
                Util.openFormInDialog.call(this, 'portalForm', title, 400, 300, expObj);
            //}
        });

        /* Purchase Order Edit Portal */
        $("a.editForPo").livequery('click', function (e){
            e.preventDefault();
            var exp = {
                callbackOnSuccess: function(){
                    var msg = 'Updated Purchase Order successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        window.location.reload(true);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'editForPoForm', 'Edit Purchase Order', 500, 300, exp);
        });

        $('.quoteLayoutShow').livequery('click', function (e){

            var link_text = $(this).html();
            var parent = $(this).closest('.quoteDetailRow');

            if(link_text == 'View Line Items'){
                $('.quoteLayoutShow', parent).text('Hide');
            }
            else{
                $('.quoteLayoutShow', parent).text('View Line Items');
            }

            $('.showAddLineRow', parent).slideToggle();
        });

        $('.employeeLayoutShow').livequery('click', function (e){

            var link_text = $(this).html();
            var parent = $(this).closest('.employeeDetailRow');

            if(link_text == 'View'){
                $('.employeeLayoutShow', parent).text('Hide');
            }
            else{
                $('.employeeLayoutShow', parent).text('View');
            }

            $('.showAddEmployeeLineRow', parent).slideToggle();
        });
        /* ADD LINE ITEM EDIT */
        $("a.editForLineItem").livequery('click', function (e){
            var title = "Edit Display";

            e.preventDefault();
            var expObj = {
                validate: true,
                callbackOnSuccess: function(){
                    Util.closeAllDialogs();
                    Util.alert('Updated successfully..');
                    window.location.reload(true);
                }
            }
            Util.openFormInDialog.call(this, 'editForLineItem', title, 600, 350, expObj);
        });

        /* MATERIAL USED EDIT */
        $("a.editForMaterialUsed").livequery('click', function (e){
            var title = "Edit Material Used";

            e.preventDefault();
            var expObj = {
                validate: true,
                callbackOnSuccess: function(){
                    Util.closeAllDialogs();
                    Util.alert('Updated successfully..');
                    window.location.reload(true);
                }
            }
            Util.openFormInDialog.call(this, 'editForMaterialUsed', title, 600, 350, expObj);
        });

        $("a.addToHours").livequery('click', function (e){
            var title = $(this).attr('dialogTitle');
            var project_id = $(this).attr('project_id');


           var title = "Add Employee List";
            e.preventDefault();
            var expObj = {
                validate: true,
                callbackOnSuccess: function(){
                    Util.closeAllDialogs();
                    Util.alert('Employee Hours Created successfully..');
                    window.location.reload(true);
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 450, 325, expObj);
        });

        $('.m-enggCrm_project a.deleteEmployeePortal').livequery('click', function (e){
            msg = "Do you like to delete the employee portal?";

            if (!confirm(msg)){
                return false;
            }
            else{
                Util.showProgressInd();
                var employee_id = $(this).attr('employee_id');
                var project_id = $(this).attr('project_id');

                var url = 'index.php?module=enggCrm_project&_spAction=deleteEmployeePortal&showHTML=0&employee_id='+employee_id+'&project_id='+project_id;
                $.get(url, {employee_id: employee_id, project_id: project_id}, function(html){
                    Util.hideProgressInd();
                    alert ('Employee Deleted Succesfully');
                    window.location.reload(true);
                });
            }
        });

        /* Employee ADD LINE ITEM EDIT */
        $("a.editForEmployeeItemView").livequery('click', function (e){
            var title = "Edit Display";

            e.preventDefault();
            var expObj = {
                validate: true,
                callbackOnSuccess: function(){
                    Util.closeAllDialogs();
                    Util.alert('Updated successfully..');
                    window.location.reload(true);
                }
            }
            Util.openFormInDialog.call(this, 'editForEmployeeItemView', title, 600, 350, expObj);
        });

        /* Employee Delete Item */

        $('.m-enggCrm_project a.deleteForEmployeeItemView').livequery('click', function (e){
            msg = "Do you like to delete the Employee Line Item?";
            if (!confirm(msg)){
                return false;
            }
            else{
                Util.showProgressInd();
                var employee_timesheet_id = $(this).attr('employee_timesheet_id');
                var url = 'index.php?module=enggCrm_project&_spAction=deleteEmployeeItem&showHTML=0&employee_timesheet_id=' + employee_timesheet_id;
                $.get(url, {employee_timesheet_id: employee_timesheet_id}, function(html){
                    Util.hideProgressInd();
                    alert ('Employee Item Deleted Succesfully');
                    window.location.reload(true);
                });
            }
        });
    },

    reloadDaysInTimesheet: function(project_id, selected_year, selected_month){
        var url = 'index.php?module=enggCrm_project&_spAction=addDaysRowHeadTimesheet&showHTML=0';

        $.get(url, {project_id: project_id, selected_year: selected_year, selected_month:selected_month}, function(html){
            $('.timesheetTableProjRel').html(html);
            Util.hideProgressInd();
        });
    },

    loadContactDropdown: function(){
        $(this).each(function(){
            comId = $(this).val();
            var url = $('#scopeRootAlias').val() + 'index.php?module=enggCrm_contact&_spAction=contactByCompanyJSON&showHTML=0'

            $.getJSON(url, {company_id: comId}, function(data) {
                $('#frmEdit select#fld_contact_id').cp_loadSelect(data);
            });
        });
    },

    employeeLinkCallBack: function(){
        window.location.reload(true);
    }
}

var Company = {
   /*getContactsComboByCompany: function(){
        var url = 'index.php?module=project_contact&_spAction=contactByCompanyJSON&showHTML=0';
        var company_id = $('#fld_company_id').val();
        $.get(url, {company_id: company_id}, function (data) {
            $('#fld_contact_id').cp_loadSelect(data);
        }, 'json');
    } */

}

var Project = {
    editFromList: function(project_id){
        url = "index.php?module=enggCrm_project"   +
        "&_spAction=editFromList" +
        "&project_id="   + project_id
        a = window.open(url,"","height=250,width=550,scrollbars=no," +
            "resizable=yes" + ",left=" + (screen.width-400)/2 + ",top=" + (screen.height-200)/2);
    },

    printOrderConfirm: function(){
        var record_id = document.getElementById('record_id').value;
        url = "jasper.php?project_id=" + record_id + "&report=orderCofirmation";
        w = 50;
        h = 50;
        windowString = "height=" + h + ",width=" + w + ",scrollbars=yes," +
        "resizable=yes,left=" + (screen.width-w)/2 + ",top=" +
        (screen.height-h)/2
        wind = window.open( url , "printFormToPDF", windowString);
    },

    setContactsComboByCompany: function(){
        var url = 'index.php?module=enggCrm_contact&_spAction=contactByCompanyJSON&showHTML=0';
        var company_id = $('select#fld_company_id').val();
        $.get(url, {company_id: company_id}, function (data){
            $('#fld_contact_id').cp_loadSelect(data);
        }, 'json');
    },

    duplicateProject: function(topRoom){
        if (!confirm("You like to duplicate the Project and related Quote?")){
            return;
        }

        var project_id = document.getElementById('record_id').value;
        var url = "index.php?_topRm=" + topRoom + "&module=enggCrm_project&_spAction=duplicateProject&project_id=" + project_id;

        document.location = url;
    }
}

var Invoice = {
    raiseInvoice: function(topRoom){
        if (!confirm("You like to raise invoice for this project?")){
            return;
        }

        var project_id = document.getElementById('record_id').value;
        var url = "index.php?_topRm=" + topRoom + "&module=enggCrm_invoice&_spAction=raiseInvoice&project_id=" + project_id;

        document.location = url;
    }
}


/* Quote Edit Portal */
//$("a.editForQuote").livequery('click', function (e){
cpm.enggCrm.project.quoteEditInProject = function(e){
    var title = "Edit Quote Display";

    e.preventDefault();
    var expObj = {
        validate: true,
        callbackOnSuccess: function(){
            Util.closeAllDialogs();
            Util.alert('Updated Quote successfully..');
            window.location.reload(true);
        }
    }
    Util.openFormInDialog.call(this, 'editForQuote', title, 500, 300, expObj);
}

/*$('.m-enggCrm_project a.duplicateQuote').livequery('click', function(){
    msg = "Would you like to Add Duplicate Quote?";

    if (!confirm(msg)){
        return false;
    }
    else{
        Util.showProgressInd();

        var project_id      = $(this).attr('project_id');
        var opportunity_id  = $(this).attr('opportunity_id');
        var quote_id        = $(this).attr('quote_id');
        var quote_items_id  = $(this).attr('quote_items_id');

       var url = 'index.php?module=enggCrm_project&_spAction=duplicateQuote&showHTML=0' +
                '&project_id=' + project_id +
                '&opportunity_id=' + opportunity_id +
                '&quote_id='   + quote_id +
                '&quote_items_id=' + quote_items_id;

        $.get(url, {project_id: project_id, opportunity_id: opportunity_id, quote_id: quote_id, quote_items_id: quote_items_id}, function(html){
            alert('Quote Record Created Successfully');
            window.location.reload(true);

        });
    }

});*/