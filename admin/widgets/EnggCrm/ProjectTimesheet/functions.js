$(function(){
    $(document).ready(function () {
    });

    /* Adding Hours for the project employee */
    $("a.addTimesheetForProjectEmployee").livequery('click', function (e){
        var title = "Add Timesheet";
        var project_id = $(this).attr('project_id');
        var url = 'index.php?widget=enggCrm_projectTimesheet&_spAction=addHoursProjectEmployee'
                + '&showHTML=0&project_id=' + project_id;

        var exp = {
            url: url,
            beforeCloseFn: function(){
                //window.location.reload(true);
                projectTimesheet.reloadTimesheetPortal(project_id);
            }
        };
        Util.openDialogForLink.call(this, title,  1228, 500, 1, exp);
    });

    /* Edit Hours for the project employee */
    $("a.editTimesheetForProjectEmployee").livequery('click', function (e){
        var title = "Edit Timesheet";
        var project_id = $(this).attr('project_id');
        var month = $(this).attr('month');
        var year = $(this).attr('year');
        var url = 'index.php?widget=enggCrm_projectTimesheet&_spAction=editHoursProjectEmployee'
                + '&showHTML=0&project_id='+project_id+'&month='+month+'&year='+year;

        var exp = {
            url: url,
            beforeCloseFn: function(){
                //window.location.reload(true);
                projectTimesheet.reloadTimesheetPortal(project_id);
            }
           /*,callbackOnSuccess: function(){
                var msg = 'Timesheet Updated successfully';
                Util.alert(msg, function(){
                    Util.closeAllDialogs();
                    window.location.reload(true);
                });
            }*/
        };

        Util.openDialogForLink.call(this, title,  1228, 500, 1, exp);
        //Util.openFormInDialog.call(this, 'addMultipleHoursEmployeeForm', title, 1228, 500, exp);
    });


    // Year change in timesheet
    $("select[name=project_time_year]").livequery("change", function() {
        var project_id     = $("input[name=project_id]").val();
        var selected_year  = $(this).val();
        var selected_month = $("select[name=project_time_month]").val();
        Util.showProgressInd();
        projectTimesheet.reloadDaysInTimesheet(project_id, selected_year, selected_month);
    });

    // Month change in timesheet
    $("select[name=project_time_month]").livequery("change", function() {
        var project_id     = $("input[name=project_id]").val();
        var selected_year  = $("select[name=project_time_year]").val();
        var selected_month = $(this).val();
        Util.showProgressInd();
        projectTimesheet.reloadDaysInTimesheet(project_id, selected_year, selected_month);
    });

    // Staff change in timesheet for 1-15 days timesheet
    $("form.addMultipleHoursEmployeeForm select[name=sign_staff_id_1]").livequery('change', function(){
        var sign_staff_id_1 = $(this).val();
        var project_id      = $("form.addMultipleHoursEmployeeForm input[name=project_id]").val();
        var year            = $("form.addMultipleHoursEmployeeForm input[name=project_time_year]").val();
        var month           = $("form.addMultipleHoursEmployeeForm input[name=project_time_month]").val();
        var timesheet_type  = $("form.addMultipleHoursEmployeeForm input[name=timesheet_type]").val();
        
        Util.showProgressInd();
        var url = 'index.php?widget=enggCrm_projectTimesheet&_spAction=updateTimeSheetSignStaff&showHTML=0';
        $.get(url, {sign_staff_id_1: sign_staff_id_1, project_id: project_id, year: year, month: month, timesheet_type: timesheet_type}, function(html){
            Util.hideProgressInd();
        });
    });

    // Staff change in timesheet for 16-31 days timesheet
    $("form.addMultipleHoursEmployeeForm select[name=sign_staff_id_2]").livequery('change', function(){
        var sign_staff_id_2 = $(this).val();
        var project_id      = $("form.addMultipleHoursEmployeeForm input[name=project_id]").val();
        var year            = $("form.addMultipleHoursEmployeeForm input[name=project_time_year]").val();
        var month           = $("form.addMultipleHoursEmployeeForm input[name=project_time_month]").val();
        var timesheet_type  = $("form.addMultipleHoursEmployeeForm input[name=timesheet_type]").val();
        
        Util.showProgressInd();
        var url = 'index.php?widget=enggCrm_projectTimesheet&_spAction=updateTimeSheetSignStaff&showHTML=0';
        $.get(url, {sign_staff_id_2: sign_staff_id_2, project_id: project_id, year: year, month: month, timesheet_type: timesheet_type}, function(html){
            Util.hideProgressInd();
        });
    });

    // Staff change in timesheet for 1-31 days timesheet
    $("form.addMultipleHoursEmployeeForm select[name=sign_staff_id]").livequery('change', function(){
        var sign_staff_id  = $(this).val();
        var project_id     = $("form.addMultipleHoursEmployeeForm input[name=project_id]").val();
        var year           = $("form.addMultipleHoursEmployeeForm input[name=project_time_year]").val();
        var month          = $("form.addMultipleHoursEmployeeForm input[name=project_time_month]").val();
        var timesheet_type = $("form.addMultipleHoursEmployeeForm input[name=timesheet_type]").val();
        
        Util.showProgressInd();
        var url = 'index.php?widget=enggCrm_projectTimesheet&_spAction=updateTimeSheetSignStaff&showHTML=0';
        $.get(url, {sign_staff_id: sign_staff_id, project_id: project_id, year: year, month: month, timesheet_type: timesheet_type}, function(html){
            Util.hideProgressInd();
        });
    });

    // Enter normal rate in timesheet for employee
    $(".addMultipleHoursEmployeeForm input.timeSheetDaysRatePerHr").livequery('change', function(){
        var ratePerHR      = $(this).val(); 
        var employee_id    = $(this).attr('employee_id');
        var project_id     = $(this).attr('project_id');
        var year           = $(this).attr('year');
        var month          = $(this).attr('month');
        var timesheet_type = $("form.addMultipleHoursEmployeeForm input[name=timesheet_type]").val();

        Util.showProgressInd();
        var url = 'index.php?widget=enggCrm_projectTimesheet&_spAction=updateDetailsProjectTimeSheetDetails&showHTML=0';
        $.get(url, {employee_id: employee_id, project_id: project_id, ratePerHR: ratePerHR, year: year, month: month, timesheet_type: timesheet_type}, function(html){
            Util.hideProgressInd();
        });
    });

    // Enter overtime rate in timesheet for employee
    $(".addMultipleHoursEmployeeForm input.timeSheetDaysOTRatePerHr").livequery('change', function(){
        var oTRatePerHR    = $(this).val(); 
        var employee_id    = $(this).attr('employee_id');
        var project_id     = $(this).attr('project_id');
        var year           = $(this).attr('year');
        var month          = $(this).attr('month');
        var timesheet_type = $("form.addMultipleHoursEmployeeForm input[name=timesheet_type]").val();

        Util.showProgressInd();
        var url = 'index.php?widget=enggCrm_projectTimesheet&_spAction=updateDetailsProjectTimeSheetDetails&showHTML=0';
        $.get(url, {employee_id: employee_id, project_id: project_id, oTRatePerHR: oTRatePerHR, year: year, month: month, timesheet_type: timesheet_type}, function(html){
            Util.hideProgressInd();
        });
    });

    // Enter sunday / public holiday rate in timesheet for employee
    $(".addMultipleHoursEmployeeForm input.timeSheetDaysPHRatePerHr").livequery('change', function(){
        var pHRatePerHR    = $(this).val(); 
        var employee_id    = $(this).attr('employee_id');
        var project_id     = $(this).attr('project_id');
        var year           = $(this).attr('year');
        var month          = $(this).attr('month');
        var timesheet_type = $("form.addMultipleHoursEmployeeForm input[name=timesheet_type]").val();

        Util.showProgressInd();
        var url = 'index.php?widget=enggCrm_projectTimesheet&_spAction=updateDetailsProjectTimeSheetDetails&showHTML=0';
        $.get(url, {employee_id: employee_id, project_id: project_id, pHRatePerHR: pHRatePerHR, year: year, month: month, timesheet_type: timesheet_type}, function(html){
            Util.hideProgressInd();
        });
    });

    // Enter normal hours in timesheet for employee
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

    // Enter overtime hours in timesheet for employee
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

    // Enter sunday / public holiday hours in timesheet for employee
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

    // Enter normal hours in timesheet for employee
    $(".timeSheetDaysNormalInput").live("keydown", function (e) {
        var keyCode = e.keyCode ? e.keyCode : e.which;
        var parent_th = $(this).parent('th');

        if (keyCode == 13) {
            parent_th.find('input.timeSheetDaysOTInput').focus();
        }
    });

    // Enter overtime hours in timesheet for employee
    $(".timeSheetDaysOTInput").live("keydown", function (e) {
        var keyCode = e.keyCode ? e.keyCode : e.which;
        var parent_th = $(this).parent('th');

        if (keyCode == 13) {
            parent_th.find('input.timeSheetDaysPHInput').focus();
        }
    });

    // Enter sunday / public holiday hours in timesheet for employee
    $(".timeSheetDaysPHInput").live("keydown", function (e) {
        var keyCode = e.keyCode ? e.keyCode : e.which;
        var parent_th = $(this).parent('th');

        if (keyCode == 13) {
            //parent_th.next('th').find('input.timeSheetDaysNormalInput').focus();
            $(':input:eq(' + ($(':input').index(this) + 1) +')').focus();
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

    $(".timeSheetDaysInput").live("keydown", function (e) {
        var keyCode = e.keyCode ? e.keyCode : e.which;
        var parent_th = $(this).parent('th');

        if (keyCode == 13) {
            parent_th.next('th').find('input').focus();
        }
    });

    $(".timesheetDaysTdRate input").live("keydown", function (e) {
        var keyCode = e.keyCode ? e.keyCode : e.which;
        var employee_id = $(this).attr('employee_id');
        if (keyCode == 13) {
            $(':input:eq(' + ($(':input').index(this) + 1) +')').focus();
        }
    });

    //New timesheet - Enter normal hours in timesheet for employee
    $(".addFormTimeSheetRightPanelPopupTh input.timeSheetDaysNormalInput").livequery('change', function (){
        var parent                = $(this).closest('th');
        var normalHours           = $(this).val();
        var oTHours               = $("input.timeSheetDaysOTInput", parent).val();
        var pHHours               = $("input.timeSheetDaysPHInput", parent).val();
        var employee_id           = $("input[name=projectTimeSheetHiddenValues]", parent).attr('employee_id');
        var project_id            = $("input[name=projectTimeSheetHiddenValues]", parent).attr('project_id');
        var employee_timesheet_id = $("input[name=projectTimeSheetHiddenValues]", parent).attr('employee_timesheet_id');
        var timeSheetDate         = $("input[name=projectTimeSheetHiddenValues]", parent).attr('timeSheetDate');
        var month                 = $("input[name=projectTimeSheetHiddenValues]", parent).attr('month');
        var year                  = $("input[name=projectTimeSheetHiddenValues]", parent).attr('year');
        var parent2               = $(this).closest('.timesheetTableProjReltab');
        var ratePerHR             = $("input.timeSheetDaysRatePerHr", parent2).val();
        var oTRatePerHR           = $("input.timeSheetDaysOTRatePerHr", parent2).val();
        var pHRatePerHR           = $("input.timeSheetDaysPHRatePerHr", parent2).val();
        var admin_charges         = $("input.adminChargesEmployee", parent2).val();
        var transport_charges     = $("input.transportChargesEmployee", parent2).val();
        var timesheet_type        = $("form.addMultipleHoursEmployeeForm input[name=timesheet_type]").val();
        var sign_staff_id_1       = $("form.addMultipleHoursEmployeeForm select[name=sign_staff_id_1]").val();
        var sign_staff_id_2       = $("form.addMultipleHoursEmployeeForm select[name=sign_staff_id_2]").val();
        var sign_staff_id         = $("form.addMultipleHoursEmployeeForm select[name=sign_staff_id]").val();

        var countCheck = parseInt(0);
        /*
        if (timesheet_type == 'Fortnightly') {
            if(sign_staff_id_1 == "" || sign_staff_id_2 == "") {
                alert("Please select timesheet staff for sign");
                countCheck = parseInt(countCheck) + parseInt(1);
                $("input.timeSheetDaysNormalInput", parent).val("");
            }
        } else {
            if(sign_staff_id == "") {
                alert("Please select timesheet staff for sign");
                countCheck = parseInt(countCheck) + parseInt(1);
                $("input.timeSheetDaysNormalInput", parent).val("");
            }
        }
        */

        if(parseInt(countCheck) == 0) {
            Util.showProgressInd();
            var url = 'index.php?widget=enggCrm_projectTimesheet&_spAction=createUpdateEmployeeTimesheetRecordEdit&showHTML=0';
            $.get(url, {normalHours: normalHours, oTHours: oTHours, pHHours: pHHours, employee_id: employee_id, project_id: project_id, employee_timesheet_id: employee_timesheet_id, timeSheetDate: timeSheetDate, month: month, year: year, ratePerHR: ratePerHR, oTRatePerHR: oTRatePerHR, pHRatePerHR: pHRatePerHR, admin_charges: admin_charges, transport_charges: transport_charges, timesheet_type: timesheet_type, sign_staff_id_1: sign_staff_id_1, sign_staff_id_2: sign_staff_id_2, sign_staff_id: sign_staff_id}, function(html){
                Util.hideProgressInd();
                $("input[name=projectTimeSheetHiddenValues]", parent).attr('employee_timesheet_id', html);
            });
        }
    });

    //New timesheet - Enter overtime hours in timesheet for employee
    $(".addFormTimeSheetRightPanelPopupTh input.timeSheetDaysOTInput").livequery('change', function (){
        var parent                = $(this).closest('th');
        var normalHours           = $("input.timeSheetDaysNormalInput", parent).val();
        var oTHours               = $(this).val();
        var pHHours               = $("input.timeSheetDaysPHInput", parent).val();
        var employee_id           = $("input[name=projectTimeSheetHiddenValues]", parent).attr('employee_id');
        var project_id            = $("input[name=projectTimeSheetHiddenValues]", parent).attr('project_id');
        var employee_timesheet_id = $("input[name=projectTimeSheetHiddenValues]", parent).attr('employee_timesheet_id');
        var timeSheetDate         = $("input[name=projectTimeSheetHiddenValues]", parent).attr('timeSheetDate');
        var month                 = $("input[name=projectTimeSheetHiddenValues]", parent).attr('month');
        var year                  = $("input[name=projectTimeSheetHiddenValues]", parent).attr('year');
        var parent2               = $(this).closest('.timesheetTableProjReltab');
        var ratePerHR             = $("input.timeSheetDaysRatePerHr", parent2).val();
        var oTRatePerHR           = $("input.timeSheetDaysOTRatePerHr", parent2).val();
        var pHRatePerHR           = $("input.timeSheetDaysPHRatePerHr", parent2).val();
        var admin_charges         = $("input.adminChargesEmployee", parent2).val();
        var transport_charges     = $("input.transportChargesEmployee", parent2).val();         
        var timesheet_type        = $("form.addMultipleHoursEmployeeForm input[name=timesheet_type]").val();
        var sign_staff_id_1       = $("form.addMultipleHoursEmployeeForm select[name=sign_staff_id_1]").val();
        var sign_staff_id_2       = $("form.addMultipleHoursEmployeeForm select[name=sign_staff_id_2]").val();
        var sign_staff_id         = $("form.addMultipleHoursEmployeeForm select[name=sign_staff_id]").val();

        var countCheck = parseInt(0);
        /*
        if (timesheet_type == 'Fortnightly') {
            if(sign_staff_id_1 == "" || sign_staff_id_2 == "") {
                alert("Please select timesheet staff for sign");
                countCheck = parseInt(countCheck) + parseInt(1);
                $("input.timeSheetDaysNormalInput", parent).val("");
            }
        } else {
            if(sign_staff_id == "") {
                alert("Please select timesheet staff for sign");
                countCheck = parseInt(countCheck) + parseInt(1);
                $("input.timeSheetDaysNormalInput", parent).val("");
            }
        }
        */

        if(parseInt(countCheck) == 0) {
            Util.showProgressInd();
            var url = 'index.php?widget=enggCrm_projectTimesheet&_spAction=createUpdateEmployeeTimesheetRecordEdit&showHTML=0';
            $.get(url, {normalHours: normalHours, oTHours: oTHours, pHHours: pHHours, employee_id: employee_id, project_id: project_id, employee_timesheet_id: employee_timesheet_id, timeSheetDate: timeSheetDate, month: month, year: year, ratePerHR: ratePerHR, oTRatePerHR: oTRatePerHR, pHRatePerHR: pHRatePerHR, admin_charges: admin_charges, transport_charges: transport_charges, timesheet_type: timesheet_type, sign_staff_id_1: sign_staff_id_1, sign_staff_id_2: sign_staff_id_2, sign_staff_id: sign_staff_id}, function(html){
                Util.hideProgressInd();
                $("input[name=projectTimeSheetHiddenValues]", parent).attr('employee_timesheet_id', html);
            });
        }
    });
    
    //New timesheet - Enter sunday / public holiday hours in timesheet for employee
    $(".addFormTimeSheetRightPanelPopupTh input.timeSheetDaysPHInput").livequery('change', function (){
        var parent                = $(this).closest('th');
        var normalHours           = $("input.timeSheetDaysNormalInput", parent).val();
        var oTHours               = $("input.timeSheetDaysOTInput", parent).val();
        var pHHours               = $(this).val();
        var employee_id           = $("input[name=projectTimeSheetHiddenValues]", parent).attr('employee_id');
        var project_id            = $("input[name=projectTimeSheetHiddenValues]", parent).attr('project_id');
        var employee_timesheet_id = $("input[name=projectTimeSheetHiddenValues]", parent).attr('employee_timesheet_id');
        var timeSheetDate         = $("input[name=projectTimeSheetHiddenValues]", parent).attr('timeSheetDate');
        var month                 = $("input[name=projectTimeSheetHiddenValues]", parent).attr('month');
        var year                  = $("input[name=projectTimeSheetHiddenValues]", parent).attr('year');
        var parent2               = $(this).closest('.timesheetTableProjReltab');
        var ratePerHR             = $("input.timeSheetDaysRatePerHr", parent2).val();
        var oTRatePerHR           = $("input.timeSheetDaysOTRatePerHr", parent2).val();
        var pHRatePerHR           = $("input.timeSheetDaysPHRatePerHr", parent2).val();
        var admin_charges         = $("input.adminChargesEmployee", parent2).val();
        var transport_charges     = $("input.transportChargesEmployee", parent2).val();
        var timesheet_type        = $("form.addMultipleHoursEmployeeForm input[name=timesheet_type]").val();
        var sign_staff_id_1       = $("form.addMultipleHoursEmployeeForm select[name=sign_staff_id_1]").val();
        var sign_staff_id_2       = $("form.addMultipleHoursEmployeeForm select[name=sign_staff_id_2]").val();
        var sign_staff_id         = $("form.addMultipleHoursEmployeeForm select[name=sign_staff_id]").val();

        var countCheck = parseInt(0);
        /*
        if (timesheet_type == 'Fortnightly') {
            if(sign_staff_id_1 == "" || sign_staff_id_2 == "") {
                alert("Please select timesheet staff for sign");
                countCheck = parseInt(countCheck) + parseInt(1);
                $("input.timeSheetDaysNormalInput", parent).val("");
            }
        } else {
            if(sign_staff_id == "") {
                alert("Please select timesheet staff for sign");
                countCheck = parseInt(countCheck) + parseInt(1);
                $("input.timeSheetDaysNormalInput", parent).val("");
            }
        }
        */

        if(parseInt(countCheck) == 0) {
            Util.showProgressInd();
            var url = 'index.php?widget=enggCrm_projectTimesheet&_spAction=createUpdateEmployeeTimesheetRecordEdit&showHTML=0';
            $.get(url, {normalHours: normalHours, oTHours: oTHours, pHHours: pHHours, employee_id: employee_id, project_id: project_id, employee_timesheet_id: employee_timesheet_id, timeSheetDate: timeSheetDate, month: month, year: year, ratePerHR: ratePerHR, oTRatePerHR: oTRatePerHR, pHRatePerHR: pHRatePerHR, admin_charges: admin_charges, transport_charges: transport_charges, timesheet_type: timesheet_type, sign_staff_id_1: sign_staff_id_1, sign_staff_id_2: sign_staff_id_2, sign_staff_id: sign_staff_id}, function(html){
                Util.hideProgressInd();
                $("input[name=projectTimeSheetHiddenValues]", parent).attr('employee_timesheet_id', html);
            });
        }
    });

    //Edit timesheet - Enter normal hours in timesheet for employee
    $(".editFormTimeSheetRightPanelPopupTh input.timeSheetDaysNormalInput").livequery('change', function (){
        var parent                = $(this).closest('th');
        var normalHours           = $(this).val();
        var oTHours               = $("input.timeSheetDaysOTInput", parent).val();
        var pHHours               = $("input.timeSheetDaysPHInput", parent).val();
        var employee_id           = $("input[name=projectTimeSheetHiddenValues]", parent).attr('employee_id');
        var project_id            = $("input[name=projectTimeSheetHiddenValues]", parent).attr('project_id');
        var employee_timesheet_id = $("input[name=projectTimeSheetHiddenValues]", parent).attr('employee_timesheet_id');
        var timeSheetDate         = $("input[name=projectTimeSheetHiddenValues]", parent).attr('timeSheetDate');
        var month                 = $("input[name=projectTimeSheetHiddenValues]", parent).attr('month');
        var year                  = $("input[name=projectTimeSheetHiddenValues]", parent).attr('year');
        var payroll_management_id = $("input[name=projectTimeSheetHiddenValues]", parent).attr('payroll_management_id');
        var parent2               = $(this).closest('.timesheetTableProjReltab');
        var ratePerHR             = $("input.timeSheetDaysRatePerHr", parent2).val();
        var oTRatePerHR           = $("input.timeSheetDaysOTRatePerHr", parent2).val();
        var pHRatePerHR           = $("input.timeSheetDaysPHRatePerHr", parent2).val();
        var admin_charges         = $("input.adminChargesEmployee", parent2).val();
        var transport_charges     = $("input.transportChargesEmployee", parent2).val();
        var timesheet_type        = $("form.addMultipleHoursEmployeeForm input[name=timesheet_type]").val();
        var sign_staff_id_1       = $("form.addMultipleHoursEmployeeForm select[name=sign_staff_id_1]").val();
        var sign_staff_id_2       = $("form.addMultipleHoursEmployeeForm select[name=sign_staff_id_2]").val();
        var sign_staff_id         = $("form.addMultipleHoursEmployeeForm select[name=sign_staff_id]").val();

        var countCheck = parseInt(0);
        /*
        if (timesheet_type == 'Fortnightly') {
            if(sign_staff_id_1 == "" || sign_staff_id_2 == "") {
                alert("Please select timesheet staff for sign");
                countCheck = parseInt(countCheck) + parseInt(1);
                $("input.timeSheetDaysNormalInput", parent).val("");
            }
        } else {
            if(sign_staff_id == "") {
                alert("Please select timesheet staff for sign");
                countCheck = parseInt(countCheck) + parseInt(1);
                $("input.timeSheetDaysNormalInput", parent).val("");
            }
        }
        */

        if(parseInt(countCheck) == 0) {
            Util.showProgressInd();
            var url = 'index.php?widget=enggCrm_projectTimesheet&_spAction=createUpdateEmployeeTimesheetRecordEdit&showHTML=0';
            $.get(url, {normalHours: normalHours, oTHours: oTHours, pHHours: pHHours, employee_id: employee_id, project_id: project_id, employee_timesheet_id: employee_timesheet_id, timeSheetDate: timeSheetDate, month: month, year: year, ratePerHR: ratePerHR, oTRatePerHR: oTRatePerHR, pHRatePerHR: pHRatePerHR, admin_charges: admin_charges, transport_charges: transport_charges, timesheet_type: timesheet_type, sign_staff_id_1: sign_staff_id_1, sign_staff_id_2: sign_staff_id_2, sign_staff_id: sign_staff_id, payroll_management_id: payroll_management_id}, function(html){
                Util.hideProgressInd();
                $("input[name=projectTimeSheetHiddenValues]", parent).attr('employee_timesheet_id', html);
            });
        }
    });

    //Edit timesheet - Enter overtime hours in timesheet for employee
    $(".editFormTimeSheetRightPanelPopupTh input.timeSheetDaysOTInput").livequery('change', function (){
        var parent                = $(this).closest('th');
        var normalHours           = $("input.timeSheetDaysNormalInput", parent).val();
        var oTHours               = $(this).val();
        var pHHours               = $("input.timeSheetDaysPHInput", parent).val();
        var employee_id           = $("input[name=projectTimeSheetHiddenValues]", parent).attr('employee_id');
        var project_id            = $("input[name=projectTimeSheetHiddenValues]", parent).attr('project_id');
        var employee_timesheet_id = $("input[name=projectTimeSheetHiddenValues]", parent).attr('employee_timesheet_id');
        var timeSheetDate         = $("input[name=projectTimeSheetHiddenValues]", parent).attr('timeSheetDate');
        var month                 = $("input[name=projectTimeSheetHiddenValues]", parent).attr('month');
        var year                  = $("input[name=projectTimeSheetHiddenValues]", parent).attr('year');
        var payroll_management_id = $("input[name=projectTimeSheetHiddenValues]", parent).attr('payroll_management_id');
        var parent2               = $(this).closest('.timesheetTableProjReltab');
        var ratePerHR             = $("input.timeSheetDaysRatePerHr", parent2).val();
        var oTRatePerHR           = $("input.timeSheetDaysOTRatePerHr", parent2).val();
        var pHRatePerHR           = $("input.timeSheetDaysPHRatePerHr", parent2).val();
        var admin_charges         = $("input.adminChargesEmployee", parent2).val();
        var transport_charges     = $("input.transportChargesEmployee", parent2).val();         
        var timesheet_type        = $("form.addMultipleHoursEmployeeForm input[name=timesheet_type]").val();
        var sign_staff_id_1       = $("form.addMultipleHoursEmployeeForm select[name=sign_staff_id_1]").val();
        var sign_staff_id_2       = $("form.addMultipleHoursEmployeeForm select[name=sign_staff_id_2]").val();
        var sign_staff_id         = $("form.addMultipleHoursEmployeeForm select[name=sign_staff_id]").val();

        var countCheck = parseInt(0);
        /*
        if (timesheet_type == 'Fortnightly') {
            if(sign_staff_id_1 == "" || sign_staff_id_2 == "") {
                alert("Please select timesheet staff for sign");
                countCheck = parseInt(countCheck) + parseInt(1);
                $("input.timeSheetDaysNormalInput", parent).val("");
            }
        } else {
            if(sign_staff_id == "") {
                alert("Please select timesheet staff for sign");
                countCheck = parseInt(countCheck) + parseInt(1);
                $("input.timeSheetDaysNormalInput", parent).val("");
            }
        }
        */

        if(parseInt(countCheck) == 0) {
            Util.showProgressInd();
            var url = 'index.php?widget=enggCrm_projectTimesheet&_spAction=createUpdateEmployeeTimesheetRecordEdit&showHTML=0';
            $.get(url, {normalHours: normalHours, oTHours: oTHours, pHHours: pHHours, employee_id: employee_id, project_id: project_id, employee_timesheet_id: employee_timesheet_id, timeSheetDate: timeSheetDate, month: month, year: year, ratePerHR: ratePerHR, oTRatePerHR: oTRatePerHR, pHRatePerHR: pHRatePerHR, admin_charges: admin_charges, transport_charges: transport_charges, timesheet_type: timesheet_type, sign_staff_id_1: sign_staff_id_1, sign_staff_id_2: sign_staff_id_2, sign_staff_id: sign_staff_id, payroll_management_id: payroll_management_id}, function(html){
                Util.hideProgressInd();
                $("input[name=projectTimeSheetHiddenValues]", parent).attr('employee_timesheet_id', html);
            });
        }
    });
    
    //Edit timesheet - Enter sunday / public holiday hours in timesheet for employee
    $(".editFormTimeSheetRightPanelPopupTh input.timeSheetDaysPHInput").livequery('change', function (){
        var parent                = $(this).closest('th');
        var normalHours           = $("input.timeSheetDaysNormalInput", parent).val();
        var oTHours               = $("input.timeSheetDaysOTInput", parent).val();
        var pHHours               = $(this).val();
        var employee_id           = $("input[name=projectTimeSheetHiddenValues]", parent).attr('employee_id');
        var project_id            = $("input[name=projectTimeSheetHiddenValues]", parent).attr('project_id');
        var employee_timesheet_id = $("input[name=projectTimeSheetHiddenValues]", parent).attr('employee_timesheet_id');
        var timeSheetDate         = $("input[name=projectTimeSheetHiddenValues]", parent).attr('timeSheetDate');
        var month                 = $("input[name=projectTimeSheetHiddenValues]", parent).attr('month');
        var year                  = $("input[name=projectTimeSheetHiddenValues]", parent).attr('year');
        var payroll_management_id = $("input[name=projectTimeSheetHiddenValues]", parent).attr('payroll_management_id');
        var parent2               = $(this).closest('.timesheetTableProjReltab');
        var ratePerHR             = $("input.timeSheetDaysRatePerHr", parent2).val();
        var oTRatePerHR           = $("input.timeSheetDaysOTRatePerHr", parent2).val();
        var pHRatePerHR           = $("input.timeSheetDaysPHRatePerHr", parent2).val();
        var admin_charges         = $("input.adminChargesEmployee", parent2).val();
        var transport_charges     = $("input.transportChargesEmployee", parent2).val();
        var timesheet_type        = $("form.addMultipleHoursEmployeeForm input[name=timesheet_type]").val();
        var sign_staff_id_1       = $("form.addMultipleHoursEmployeeForm select[name=sign_staff_id_1]").val();
        var sign_staff_id_2       = $("form.addMultipleHoursEmployeeForm select[name=sign_staff_id_2]").val();
        var sign_staff_id         = $("form.addMultipleHoursEmployeeForm select[name=sign_staff_id]").val();

        var countCheck = parseInt(0);
        /*
        if (timesheet_type == 'Fortnightly') {
            if(sign_staff_id_1 == "" || sign_staff_id_2 == "") {
                alert("Please select timesheet staff for sign");
                countCheck = parseInt(countCheck) + parseInt(1);
                $("input.timeSheetDaysNormalInput", parent).val("");
            }
        } else {
            if(sign_staff_id == "") {
                alert("Please select timesheet staff for sign");
                countCheck = parseInt(countCheck) + parseInt(1);
                $("input.timeSheetDaysNormalInput", parent).val("");
            }
        }
        */

        if(parseInt(countCheck) == 0) {
            Util.showProgressInd();
            var url = 'index.php?widget=enggCrm_projectTimesheet&_spAction=createUpdateEmployeeTimesheetRecordEdit&showHTML=0';
            $.get(url, {normalHours: normalHours, oTHours: oTHours, pHHours: pHHours, employee_id: employee_id, project_id: project_id, employee_timesheet_id: employee_timesheet_id, timeSheetDate: timeSheetDate, month: month, year: year, ratePerHR: ratePerHR, oTRatePerHR: oTRatePerHR, pHRatePerHR: pHRatePerHR, admin_charges: admin_charges, transport_charges: transport_charges, timesheet_type: timesheet_type, sign_staff_id_1: sign_staff_id_1, sign_staff_id_2: sign_staff_id_2, sign_staff_id: sign_staff_id, payroll_management_id: payroll_management_id}, function(html){
                Util.hideProgressInd();
                $("input[name=projectTimeSheetHiddenValues]", parent).attr('employee_timesheet_id', html);
            });
        }
    });
});

var projectTimesheet = {
    reloadTimesheetPortal: function(project_id){
        var url = 'index.php?widget=enggCrm_projectTimesheet&_spAction=employmentTimeSheetNewAllView&showHTML=0';
        Util.showProgressInd();
        $.get(url, {project_id: project_id}, function(html){
            Util.hideProgressInd();
            $('.timesheetData').html(html);
        });
    },

    reloadDaysInTimesheet: function(project_id, selected_year, selected_month){
        var url = 'index.php?widget=enggCrm_projectTimesheet&_spAction=addDaysRowHeadTimesheet&showHTML=0';

        $.get(url, {project_id: project_id, selected_year: selected_year, selected_month:selected_month}, function(html){
            $('.timesheetTableProjRel').html(html);
            Util.hideProgressInd();
        });
    },	
}