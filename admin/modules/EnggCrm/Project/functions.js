Util.createCPObject('cpm.enggCrm.project');

cpm.enggCrm.project = {
    init : function(){
        //initialize tabs
        $('#tabs').tabs();

        $('#tabs ul.ui-tabs-nav li:last').livequery(function() {
            $(this).css('border-right', '1px solid #D3D3D3');
        });

        var firstTabId = $('div.desktopTabMenuToggle ul.ui-tabs-nav li:first-child a').attr('href');
        if(firstTabId != undefined && firstTabId != "") {
            $('div.desktopTabMenuToggle ul.ui-tabs-nav li:first-child').addClass('active');
            $('div.desktopTabMenuToggle ul.ui-tabs-nav li:first-child a').attr("aria-expanded", true)
            $('div'+firstTabId).addClass('active in');
        }
        
        var firstMobileTabId = $('div.desktopTabMenuMainDiv div.dropdown-menu a.dropdown-item:first-child').attr('href');
        if(firstMobileTabId != undefined && firstMobileTabId != "") {
            $('div'+firstMobileTabId).addClass('active in');
        }
        
        //$("a.editForQuote").livequery('click', function (e){

        $(".creationModificationDetails").livequery('click', function (e){
            var record_id  = $(this).attr('record_id');
            var table_name = $(this).attr('table_name');
            var field_name = $(this).attr('field_name');

            Util.showProgressInd();

            var url = "index.php?module=enggCrm_project&_spAction=creationModificationDetailsPopup&record_id="+record_id+"&table_name="+table_name+"&field_name="+field_name+"&showHTML=0";
            var exp = {
                url: url
            };

            Util.openDialogForLink('Updated By',  500, 200, 0, exp);
        });

        $('.saveQty').livequery('click', function(){
            var mgsalert = 'Quantity updated successfully!';
            var n = noty({
                text: mgsalert,
                type: 'confirm',
                dismissQueue: true,
                layout: 'topCenter',
                theme: 'defaultTheme',
                timeout: 5000,
            });
        });

        $('select#fld_company_id').change(function() {
            var company_id = $(this).val();

            var url = 'index.php?module=enggCrm_contact&_spAction=contactByCompanyJSON&showHTML=0';
            $.get(url, {company_id: company_id}, function (data) {
                $('#fld_contact_id').cp_loadSelect(data);
            }, 'json');
        });       

        $("#generateInvoiceForm input[name=discount]").livequery('change', function (e){
            var discount = $(this).val();

            var total_amount = 0;
            var total_Price = document.getElementsByClassName('invoiceItemAmount');
            for (var i = 0; i < total_Price.length; ++i) {
                if (!isNaN(parseInt(total_Price[i].value)) ){
                    total_amount += parseInt(total_Price[i].value);
                }
            }

            if(total_amount == "NaN") {
                total_amount = parseInt(0);
            } else {
                if(parseFloat(discount) > 0) {
                    total_amount = parseFloat(total_amount) - parseFloat(discount);
                }
            }

            $('.totalInvoiceAmountLabel .totalInvoiceAmount').html(total_amount.toFixed(3));
        });

        $("input[name='product_title[]']").livequery(cpm.enggCrm.project.poProductTitle);

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
                    //window.location.reload(true);
                    //cpm.enggCrm.project.reloadConfirmedQuoteDisplay(project_id);
                    projectQuote.reloadQuotePortal(project_id);
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

                $("#timeSheetTotalHours_"+employee_id).val(totalHours.toFixed(3));
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

                $("#timeSheetTotalHours_"+employee_id).val(totalHours.toFixed(3));
            }
        });

        $("#editInvoicePortalForm .invoiceQuantity").livequery('change', function (e){
            var quantity = parseFloat($(this).val());
            var unitPrice = parseFloat($(this).closest('tr').find('.invoiceUnitPrice').val());
            var totalCostObj = $(this).closest('tr').find('.invoiceAmount');
            
            if (!isNaN(quantity) && !isNaN(unitPrice) && quantity >= 0 && unitPrice >= 0) {
                var totalCost = quantity * unitPrice;
                var totalCostFormatted = totalCost.toFixed(2); // Ensure two decimal places
                totalCostObj.val(totalCostFormatted); // Assuming invoiceAmount is an input field
            }
        });
        
        $("#editInvoicePortalForm .invoiceUnitPrice").livequery('change', function (e){
            var unitPrice = parseFloat($(this).val());
            var quantity = parseFloat($(this).closest('tr').find('.invoiceQuantity').val());
            var totalCostObj = $(this).closest('tr').find('.invoiceAmount');
            
            if (!isNaN(quantity) && !isNaN(unitPrice) && quantity >= 0 && unitPrice >= 0) {
                var totalCost = quantity * unitPrice;
                var totalCostFormatted = totalCost.toFixed(2); // Ensure two decimal places
                totalCostObj.val(totalCostFormatted); // Assuming invoiceAmount is an input field
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

                $("#timeSheetOTTotalHours_"+employee_id).val(totalOTHours.toFixed(3));
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

                $("#timeSheetPHTotalHours_"+employee_id).val(totalPHHours.toFixed(3));
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
                    var mgsalert='Employee removed from project!';
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
                    var mgsalert='Employee added to project!';
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

        $('.showAddLineRow').hide();

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

        $("a.addToHours").livequery('click', function (e){
            var title = $(this).attr('dialogTitle');
            var project_id = $(this).attr('project_id');


           var title = "Add Employee List";
            e.preventDefault();
            var expObj = {
                validate: true,
                callbackOnSuccess: function(){
                    Util.closeAllDialogs();
                    Util.alert('Employee hours created successfully..');
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

        $('.row-enggCrm_project__enggCrm_employeeLink td.employeecategory select[name=category_type]').livequery('change', function (e){
            var category_type       = $(this).val();
            var project_employee_id = $(this).attr('project_employee_id');
            
            Util.showProgressInd();
            var url = 'index.php?module=enggCrm_project&_spAction=updateEmployeeCategoryType&showHTML=0';
            $.get(url, {project_employee_id: project_employee_id, category_type: category_type}, function(html){
                Util.hideProgressInd();
            });
        });

        $('.row-enggCrm_project__enggCrm_employeeLink td.employeecategory select[name=category_type]').each(function(){
            var checkedVal = $(this).attr('category_type');
            $(this).val(checkedVal);
        });
 
        /* Adding multiple rows in invoice */
        $(".m-enggCrm_project a.generateInvoice").livequery('click', function (e){
            var title = "Generate Invoice";
            var order_id    = $(this).attr('order_id');
            var record_type = $(this).attr('record_type');
            var url = 'index.php?module=enggCrm_invoice&_spAction=generateInvoiceForm1'
                    + '&showHTML=0&order_id='+order_id+'&record_type='+record_type;
            var exp = {
                url: url
               ,callbackOnSuccess: function(){
                    Util.closeAllDialogs();

                    var mgsalert = 'Invoice created successfully!';
                    var n = noty({
                        text: mgsalert,
                        type: 'confirm',
                        dismissQueue: true,
                        layout: 'topCenter',
                        theme: 'defaultTheme',
                        timeout: 5000,
                    });
                    
                    cpm.enggCrm.project.reloadInvoicePortalDisplay(order_id);
                }
            };

            Util.openFormInDialog.call(this, 'generateInvoiceForm', title, 900, 530, exp);
        });

        $("a.generateReceipt").livequery('click', function (e){
            var title = "Generate Receipt";
            e.preventDefault();

            $("select[name='mode_of_payment']").livequery('change', function (e){
                cpm.enggCrm.project.populatePaymentMode.call(this);
            });

            var order_id = $(this).attr('order_id');
            var url = 'index.php?module=enggCrm_receipt&_spAction=generateReceiptForm'
                    + '&showHTML=0&order_id=' + order_id;
            var exp = {
                url: url
               ,callbackOnSuccess: function(){
                    Util.closeAllDialogs();

                    var mgsalert = 'Receipt created successfully!';
                    var n = noty({
                        text: mgsalert,
                        type: 'confirm',
                        dismissQueue: true,
                        layout: 'topCenter',
                        theme: 'defaultTheme',
                        timeout: 5000,
                    });

                    cpm.enggCrm.project.reloadReceiptPortalDisplay(order_id);
                    cpm.enggCrm.project.reloadInvoicePortalDisplay(order_id);
                }
            };

            Util.openFormInDialog.call(this, 'portalForm', title, 700, 500, exp);
        });

        

        /* Adding row in invoice */
        $("#generateInvoiceForm a.addRow").livequery('click', function (e){
            var url = 'index.php?module=enggCrm_invoice&_spAction=addInvoiceItemRecord'
                    + '&showHTML=0';

            $.get(url, '', function(html){
                $('#generateInvoiceForm table.thinlist tr:last').after(html);
            });
        });

        $("#generateInvoiceForm .invoiceItemQuantity").livequery('change', function (e){
            //cpm.enggCrm.project.triggerCalcForQuantity.call(this);
            var discount     = $("#generateInvoiceForm input[name=discount]").val();
            var quantity     = $(this).val();
            var amountObj    = $(this).closest('tr').find('.invoiceItemUnitPrice');
            var amount       = amountObj.val();
            var totalCostObj = $(this).closest('tr').find('.invoiceItemAmount');

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

            var total_amount = 0;
            var total_Price = document.getElementsByClassName('invoiceItemAmount');
            for (var i = 0; i < total_Price.length; ++i) {
                if (!isNaN(parseInt(total_Price[i].value)) ){
                    total_amount += parseInt(total_Price[i].value);
                }
            }

            if(total_amount == "NaN") {
                total_amount = parseInt(0);
            }

            if(parseFloat(discount) > 0) {
                total_amount = parseFloat(total_amount) - parseFloat(discount);
            }

            $('.totalInvoiceAmountLabel .totalInvoiceAmount').html(total_amount.toFixed(3));
        });

       
        $("#generateInvoiceForm .invoiceItemAmount").livequery('change', function (e){
            var total_amount = 0;
            var total_Price = document.getElementsByClassName('invoiceItemAmount');
            for (var i = 0; i < total_Price.length; ++i) {
                if (!isNaN(parseInt(total_Price[i].value)) ){
                    total_amount += parseInt(total_Price[i].value);
                }
            }

            if(total_amount == "NaN") {
                total_amount = parseInt(0);
            }

            var discount = $("#generateInvoiceForm input[name=discount]").val();
            if(parseFloat(discount) > 0) {
                total_amount = parseFloat(total_amount) - parseFloat(discount);
            }

            $('.totalInvoiceAmountLabel .totalInvoiceAmount').html(total_amount.toFixed(3));
        });

        $("#generateInvoiceForm .invoiceItemUnitPrice").livequery('change', function (e){
            var amount       = $(this).val();
            var quantityObj  = $(this).closest('tr').find('.invoiceItemQuantity');
            var quantity     = quantityObj.val();
            var totalCostObj = $(this).closest('tr').find('.invoiceItemAmount');

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

            var total_amount = 0;
            var total_Price = document.getElementsByClassName('invoiceItemAmount');
            for (var i = 0; i < total_Price.length; ++i) {
                if (!isNaN(parseInt(total_Price[i].value)) ){
                    total_amount += parseInt(total_Price[i].value);
                }
            }

            if(total_amount == "NaN") {
                total_amount = parseInt(0);
            }

            var discount = $("#generateInvoiceForm input[name=discount]").val();
            if(parseFloat(discount) > 0) {
                total_amount = parseFloat(total_amount) - parseFloat(discount);
            }

            $('.totalInvoiceAmountLabel .totalInvoiceAmount').html(total_amount.toFixed(3));
        });

        $("a.clearInvoiceItemEdit").livequery('click', function (e){
            var titleObj       = $(this).closest('tr').find('#title');
            var descriptionObj = $(this).closest('tr').find('#description');

            titleObj.val('');
            descriptionObj.val('');
        });

        $("a.clearInvoiceItem").livequery('click', function (e){
            var titleObj       = $(this).closest('tr').find('.invoiceItemTitleFull');
            var quantityObj    = $(this).closest('tr').find('.invoiceItemQuantity');
            var unitObj        = $(this).closest('tr').find('.invoiceItemUnit');
            var amountObj      = $(this).closest('tr').find('.invoiceItemAmount');
            var unitPriceObj   = $(this).closest('tr').find('.invoiceItemUnitPrice');
            //var totalCostObj   = $(this).closest('tr').find('.totalCost');
            var descriptionObj = $(this).closest('tr').find('.invoiceItemDescription');
            //var remarksObj = $(this).closest('tr').find('.invoiceItemRemarks');

            titleObj.val('');
            quantityObj.val('');
            unitObj.val('');
            amountObj.val('');
            unitPriceObj.val('');
            descriptionObj.val('');
            //remarksObj.val('');

            var total_Price  = document.getElementsByClassName('invoiceItemAmount');
            var total_amount = 0;
            for (var i = 0; i < total_Price.length; ++i) {
                if (!isNaN(parseInt(total_Price[i].value)) ){
                    total_amount += parseInt(total_Price[i].value);
                }
            }

            if(total_amount == "NaN") {
                total_amount = parseInt(0);
            }

            var discount = $("#generateInvoiceForm input[name=discount]").val();
            if(parseFloat(discount) > 0) {
                total_amount = parseFloat(total_amount) - parseFloat(discount);
            }

            $('.totalInvoiceAmountLabel .totalInvoiceAmount').html(total_amount.toFixed(3));
        });


        $('.m-enggCrm_project .cancelReceipt1').livequery('click', function (e){
            msg = "Do you like to cancel the Receipt?";
            if (!confirm(msg)){
                return false;
            }
            else {
                var url = 'index.php?_topRm=finance&module=enggCrm_order&_spAction=cancelReceipt&showHTML=0';
                Util.showProgressInd();
                var receipt_id = $(this).attr('receipt_id');
                $.get(url,{receipt_id: receipt_id}, function(html){
                    alert ('Receipt Cancelled Succesfully');
                    Util.hideProgressInd();
                    window.location.reload(true);
                });
            }
        });


        
$('.m-enggCrm_project .cancelInvoice1').livequery('click', function (e){
    var invoice_status = $(this).attr('invoice_status');

    if (invoice_status != 'Paid') {
        msg = "Do you want to cancel the Invoice?";
        if (!confirm(msg)){
            return false;
        }
        else {
            var url = 'index.php?_topRm=finance&module=enggCrm_order&_spAction=cancelInvoice&showHTML=0';
            Util.showProgressInd();
            var invoice_code = $(this).attr('invoice_code');
            var invoice_id = $(this).attr('invoice_id');
            $.get(url,{invoice_code: invoice_code, invoice_id:invoice_id}, function(html){

                /* Checking for one or more receipt for the invoice */
                if (html == 'Cannot cancel') {
                    Util.alert ('Cancel the related receipts and then proceed canceling the invoice');
                    Util.hideProgressInd();
                } else {
                    alert ('Invoice Cancelled Succesfully');
                    Util.hideProgressInd();
                    window.location.reload(true);
                }
            });
        }
    } else {
        msg = "Please cancel the receipt and then try canceling the Invoice";
        if (!confirm(msg)){
            return false;
        } else {
            return false;
        }
    }
});

        $('.m-enggCrm_project .editInvoice1').livequery('click', function (e){
            var title    = "Edit Invoice1";
            var order_id = $(this).attr('order_id');
            
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Invoice updated successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        cpm.enggCrm.project.reloadInvoicePortalDisplay(order_id);
                        //window.location.reload(true);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'editInvoicePortalForm', title, 700, 500, expObj);
        });

        $('.m-enggCrm_project input.invoiceCode').livequery('click', function (e){
            Util.showProgressInd();
            invoice_code = $(this).val();
            var checked    = $(this).attr('checked') ? 'checked' : '';
            var checkedVal = checked == 'checked' ? 1 : 0;
            var order_id   = $(this).attr('order_id');

            var url = 'index.php?_topRm=finance&module=enggCrm_receipt&_spAction=populateReceiptAmount&showHTML=0';
            $.get(url,{invoice_code: invoice_code ,checkedVal: checkedVal, order_id: order_id}, function(html){
                $('input[id=fld_amount]').val(html);
                Util.hideProgressInd();
            });
        });

        $('.cancelInvoice').livequery('click', function (e){
            var invoice_status = $(this).attr('invoice_status');
            var order_id = $(this).attr('order_id');

            if (invoice_status != 'Paid') {
                msg = "Do you want to cancel the Invoice?";
                if (!confirm(msg)){
                    return false;
                }
                else {
                    var url = 'index.php?_topRm=finance&module=enggCrm_order&_spAction=cancelInvoice&showHTML=0';
                    Util.showProgressInd();
                    var invoice_code = $(this).attr('invoice_code');
                    var invoice_id = $(this).attr('invoice_id');
                    $.get(url,{invoice_code: invoice_code, invoice_id:invoice_id}, function(html){

                        /* Checking for one or more receipt for the invoice */
                        if (html == 'Cannot cancel') {
                            Util.alert ('Cancel the related receipts and then proceed canceling the invoice');
                            Util.hideProgressInd();
                        } else {
                            alert ('Invoice Cancelled Succesfully');
                            Util.hideProgressInd();
                            cpm.enggCrm.project.reloadInvoicePortalDisplay(order_id);
                        }
                    });
                }
            } else {
                msg = "Please cancel the receipt and then try canceling the Invoice";
                if (!confirm(msg)){
                    return false;
                } else {
                    return false;
                }
            }
        });

        $('.cancelReceipt').livequery('click', function (e){
            var order_id = $(this).attr('order_id');
            var msg = "Do you like to cancel the Receipt?";
            if (!confirm(msg)){
                return false;
            }
            else {
                var url = 'index.php?_topRm=finance&module=enggCrm_order&_spAction=cancelReceipt&showHTML=0';
                Util.showProgressInd();
                var receipt_id = $(this).attr('receipt_id');
                $.get(url,{receipt_id: receipt_id}, function(html){
                    alert ('Receipt Cancelled Succesfully');
                    Util.hideProgressInd();
                    cpm.enggCrm.project.reloadReceiptPortalDisplay(order_id);
                    cpm.enggCrm.project.reloadInvoicePortalDisplay(order_id);
                });
            }
        });

        $('.m-enggCrm_project .showHideCancelledInvoice').livequery('click', function (e){
            var link_text = $(this).html();

            if(link_text == '(+) Click to View Cancelled Invoice(s)'){
                $('.showHideCancelledInvoice').text('(-) Click to Hide Cancelled Invoice(s)');
            }
            else{
                $('.showHideCancelledInvoice').text('(+) Click to View Cancelled Invoice(s)');
            }

            $('.cancelledInvoiceTableOrder').slideToggle();
        });

        $('.addMoreDetailsInvoiceRow').livequery('click', function (e){
            var link_text = $(this).html();

            if(link_text == '(+) Add More Details'){
                $('.addMoreDetailsInvoiceRow').text('(-) Hide More Details');
            }
            else{
                $('.addMoreDetailsInvoiceRow').text('(+) Add More Details');
            }

            $('.hideMoreInvoiceDetails').slideToggle();
        });

        $('.addMoreDetailsInvoiceRow1').livequery('click', function (e){
            var link_text = $(this).html();

            if(link_text == '(+) Add More Details'){
                $('.addMoreDetailsInvoiceRow1').text('(-) Hide More Details');
            }
            else{
                $('.addMoreDetailsInvoiceRow1').text('(+) Add More Details');
            }

            $('.hideMoreInvoiceDetails1').slideToggle();
        });

        $("input[name='product_title[]']").livequery('change', function(){
            var parent     = $(this).closest('tr');
            var product_id = $("input.product_id_hidden", parent).val();
            
            if(product_id == "") {
                $("input.poProductTitle", parent).val("");
                $("select[name='category[]']", parent).val("");
                $("td.productType", parent).html("");
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

    poProductTitle: function() {
        var titleObj = this;
        $(titleObj).autocomplete({
            source: function(request, response) {
                $.ajax({
                  url: 'index.php?widget=enggCrm_projectPurchaseOrder&_spAction=searchProductTitle&showHTML=0',
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
                var category     = selectedObj.category;
                var product_type = selectedObj.product_type;
                var stock        = selectedObj.stock;
                var parent       = $(this).closest('tr');

                $("input[name='product_id[]']", parent).val(product_id);
                $("select[name='category[]']", parent).val(category);
                $("td.productType", parent).html(product_type);
            }
        });
    },

    poProductTitleEdit: function() {
        var titleObj = this;
        $(titleObj).autocomplete({
             source : 'index.php?module=enggCrm_project&_spAction=searchProductTitle&showHTML=0'
            ,minLength : 1
            ,selectFirst: true
            ,autoFocus: true
            ,select: function(event, ui) {
                var selectedObj = ui.item;
                var product_id  = selectedObj.id;

                $("#editForPoLineItem input[name='product_id']").val(product_id);
            }
        });
    },

    projectTitle: function() {
        var titleObj = this;
        $(titleObj).autocomplete({
             source : 'index.php?module=enggCrm_project&_spAction=searchProjectTitle&showHTML=0'
            ,minLength : 2
            ,selectFirst: true
            ,autoFocus: true
            ,select: function(event, ui) {
                var selectedObj    = ui.item;
                var to_project_id     = selectedObj.id;
                var company_name   = selectedObj.company_name;
                var parent         = $(this).closest('tr');
                var project_id = $("#record_id").val();
                var product_id = $("input[name='product_id']").val();

                $("input[name='to_project_id']", parent).val(to_project_id);
                $(".clientName", parent).html(company_name);
                //$(this).after("<input type='hidden' name='product_id[]' value=" + product_id + ">");
                var url = 'index.php?module=enggCrm_project&_spAction=createStockTransfer&showHTML=0';
                $.get(url, {project_id: project_id, to_project_id:to_project_id, product_id:product_id}, function(html){
                    $("input[name='stock_transfer_id']", parent).val(html);
                }); 
            }
        });
    },

    clientName: function() {
        var titleObj = this;
        $(titleObj).autocomplete({
             source : 'index.php?module=enggCrm_project&_spAction=searchClientName&showHTML=0'
            ,minLength : 2
            ,selectFirst: true
            ,autoFocus: true
            ,select: function(event, ui) {
                var selectedObj   = ui.item;
                //var to_project_id = selectedObj.id;
                var company_id  = selectedObj.id;
                var parent        = $(this).closest('tr');
                var project_id    = $("#record_id").val();
                var product_id    = $("input[name='product_id']").val();

                $("input[name='company_id']", parent).val(company_id);
                /*var url = 'index.php?module=enggCrm_project&_spAction=createStockTransfer&showHTML=0';
                $.get(url, {project_id: project_id, to_project_id:to_project_id, product_id:product_id}, function(html){
                    $("input[name='stock_transfer_id']", parent).val(html);
                });*/ 
                var url = 'index.php?module=enggCrm_project&_spAction=projectByCompanyJSON&showHTML=0';
                $.get(url, {company_id: company_id}, function (data) {
                    $("select[name='to_project_id']", parent).cp_loadSelect(data);
                }, 'json');
            }
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
    },
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

/*cpm.enggCrm.project.reloadConfirmedQuoteDisplay = function(project_id){
    var url = 'index.php?_topRm=project&module=enggCrm_project&_spAction=confirmedQuoteDetails&showHTML=0';
    Util.showProgressInd();
    $.get(url,{project_id: project_id}, function(html){
        $('.confirmedQuoteDisplayDiv').html(html);
        Util.hideProgressInd();
    });
}*/

cpm.enggCrm.project.reloadInvoiceReceiptMainPortalDisplay = function(project_id){
    var url = 'index.php?widget=enggCrm_projectFinance&_spAction=invoiceReceiptPortalDetails&showHTML=0';
    Util.showProgressInd();
    $.get(url,{project_id: project_id}, function(html){
        $('.invoiceReceiptPortalDisplayDiv').html(html);
        Util.hideProgressInd();
    });
}

cpm.enggCrm.project.reloadInvoicePortalDisplay = function(order_id){
    var url = 'index.php?_topRm=finance&module=enggCrm_order&_spAction=invoicePortalDisplay&showHTML=0';
    Util.showProgressInd();
    $.get(url,{order_id: order_id}, function(html){
        $('.invoicePortalDisplayDiv').html(html);
        Util.hideProgressInd();
    });
}
    
cpm.enggCrm.project.reloadReceiptPortalDisplay = function(order_id){
    var url = 'index.php?_topRm=finance&module=enggCrm_order&_spAction=receiptPortalDisplay&showHTML=0';
    Util.showProgressInd();
    $.get(url,{order_id: order_id}, function(html){
        $('.receiptPortalDisplayDiv').html(html);
        Util.hideProgressInd();
    });
}

cpm.enggCrm.project.triggerCalcForQuantity = function(){
    var quantity = $(this).val();
    var amountObj = $(this).closest('tr').find('.invoiceItemAmount');
    var amount = amountObj.val();
    var totalCostObj = $(this).closest('tr').find('.totalCost');
    var qtyBalanceVal = $(this).closest('tr').find('input[name=qty_balance]');
    var qtyBalanceObj = $(this).closest('tr').find('.invoiceItemBalanceQuantity');
    var qtyBalance = qtyBalanceVal.val();

    if (quantity != '' && amount != '') {
        var total_cost = quantity * amount;
        var total_cost_formatted = parseFloat(total_cost).toFixed(3);
        totalCostObj.html(total_cost_formatted);
        qtyBalanceObj.html((qtyBalance - quantity).toFixed(3));
    }
}

cpm.enggCrm.project.triggerCalcForAmount = function(){
    var amount = $(this).val();
    var quantityObj = $(this).closest('tr').find('.invoiceItemQuantity');
    var quantity = quantityObj.val();
    var totalCostObj = $(this).closest('tr').find('.totalCost');

    if (quantity != '' && amount != '') {
        var total_cost = quantity * amount;
        var total_cost_formatted = parseFloat(total_cost).toFixed(3);
        totalCostObj.html(total_cost_formatted);
    } else if (amount != '' && quantity == '') {
        var total_cost_formatted = amount;
        totalCostObj.html(total_cost_formatted);
    }
}

cpm.enggCrm.project.populatePaymentMode = function(){
    var paymentMode = $(this).val();
    if (paymentMode == 'Cheque') {
        Util.showProgressInd();
        $('form.receiptForm .row_cheque_no').removeClass('hideme');
        $('form.receiptForm .row_cheque_date').removeClass('hideme');
        $('form.receiptForm .row_bank_name').removeClass('hideme');
        Util.hideProgressInd();
    } else {
        Util.showProgressInd();
        $('form.receiptForm .row_cheque_no').addClass('hideme');
        $('form.receiptForm .row_cheque_date').addClass('hideme');
        $('form.receiptForm .row_bank_name').addClass('hideme');
        Util.hideProgressInd();
    }
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