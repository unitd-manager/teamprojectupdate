Util.createCPObject('cpm.payroll.reports');
 
cpm.payroll.reports = {
    init: function(){
        $('#header, #nav, #footer').slideUp(1000, function(){
            $('#header, #nav, #footer').remove();
            LoadReady.makeScrollableTable();
        });
        cpm.payroll.reports.setSearchForm();
        
        $('.search select[name=report]').change(function(){
            var report = $(this).val();
            $('#reportSearchPanel').slideUp();
            if (report == ''){
                return;
            }
            var url = 'index.php?_topRm=reports&module=payroll_reports&_spAction=search&showHTML=0&report=' + report;
            $.get(url, function(html){
                $('#reportSearchPanel').html(html).slideDown();
                $('#reportContainer').html('');
            });
        });

        $('.m-payroll_reports .search select[name=employee_status]').livequery('change', function(e){
            cpm.payroll.reports.loadEmployeeNameDropdown.call(this);
        });
    },

    loadEmployeeNameDropdown: function(){
        $(this).each(function(){
            employee_status = $(this).val();
            var url = $('#scopeRootAlias').val() + 'index.php?module=payroll_reports&_spAction=employeeByEmployeeStatus&showHTML=0'

            $.getJSON(url, {employee_status: employee_status}, function(data) {
                $('.m-payroll_reports .search select[name=employee_id]').cp_loadSelect(data);
            });
        });
    },

    setSearchForm: function(){
        $('table.search input.button').livequery('click', function(e) {
            e.preventDefault();
            Util.showProgressInd();
            $('#reportContainer').addClass('reportLoading');
            var allVars = $('table.search').serializeAnything();
            var url = 'index.php?_topRm=reports&module=payroll_reports&_spAction=displayReport&showHTML=0' + allVars;
            $.ajax({
                type: "GET",
                url: url,
                dataType: "html",
                success: function(html) {
                    $('#reportContainer').html(html);
                    $('#reportContainer').removeClass('reportLoading');
                    if($('#reportName').val() == 'liquiditySummary'){
                        drawChart();
                    }
                    Util.hideProgressInd();
                }
            });
        });
    }
}