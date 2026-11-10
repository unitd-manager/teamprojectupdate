Util.createCPObject('cpm.enggCrm.reports');
 
cpm.enggCrm.reports = {
    init: function(){
        $('.w-enggCrm-balanceSheetReport .expenseDetails').livequery('click', function(){
            var parent = $(this).closest('.expenseDetailsHead');
            $('.subTitles', parent).slideToggle();
        }); 

        $('.w-enggCrm-balanceSheetReport .invoiceDetails').livequery('click', function(){
            var parent = $(this).closest('.invoiceDetailsHead');
            $('.invoiceTitles', parent).slideToggle();
        }); 

        $('#header, #nav, #footer').slideUp(1000, function(){
            $('#header, #nav, #footer').remove();
            LoadReady.makeScrollableTable();
        });
        cpm.enggCrm.reports.setSearchForm();
        
        $('.search select[name=report]').change(function(){
            var report = $(this).val();
            $('#reportSearchPanel').slideUp();
            if (report == ''){
                return;
            }
            var url = 'index.php?_topRm=reports&module=enggCrm_reports&_spAction=search&showHTML=0&report=' + report;
            $.get(url, function(html){
                $('#reportSearchPanel').html(html).slideDown();
                $('#reportContainer').html('');
            });
        });
    
        $('.w-enggCrm-opportunityQuotation .quoteVal').livequery('click', function(){
            var parent = $(this).closest('.opportunityQuotationSummary');
            $('.quoteDetails', parent).slideToggle();
        });

        $('.w-enggCrm-employeeReport .employeeVal').livequery('click', function(){
            var parent = $(this).closest('.employeeSummary');
            $('.employeeDetails', parent).slideToggle();
        });

        $('.w-enggCrm-employeeReport .employeehrsVal').livequery('click', function(){
            var parent = $(this).closest('.employeehrsSummary');
            $('.employeehrsDetails', parent).slideToggle();
        });

        $('.w-project-salesByMonthReports .projectAmt').livequery('click', function(){
            var parent = $(this).closest('.projectSalesSummary');
            $('.projectDetails', parent).slideToggle();
        }); 
},

    setSearchForm: function(){
        $('table.search input.button').livequery('click', function(e) {
            e.preventDefault();
            Util.showProgressInd();
            $('#reportContainer').addClass('reportLoading');
            var allVars = $('table.search').serializeAnything();
            var url = 'index.php?_topRm=reports&module=enggCrm_reports&_spAction=displayReport&showHTML=0' + allVars;
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