Util.createCPObject('cpm.enterpriseIms.reports');
 
cpm.enterpriseIms.reports = {
    init: function(){
        $('#header, #nav, #footer').slideUp(1000, function(){
            $('#header, #nav, #footer').remove();
            LoadReady.makeScrollableTable();
        });
        cpm.enterpriseIms.reports.setSearchForm();
        
        $('.search select[name=report]').change(function(){
            var report = $(this).val();
            $('#reportSearchPanel').slideUp();
            if (report == ''){
                return;
            }
            var url = 'index.php?_topRm=reports&module=enterpriseIms_reports&_spAction=search&showHTML=0&report=' + report;
            $.get(url, function(html){
                $('#reportSearchPanel').html(html).slideDown();
                $('#reportContainer').html('');
            });
        });
    },

    setSearchForm: function(){
        $('table.search input.button').livequery('click', function(e) {
            e.preventDefault();
            Util.showProgressInd();
            $('#reportContainer').addClass('reportLoading');
            var allVars = $('table.search').serializeAnything();
            var url = 'index.php?_topRm=reports&module=enterpriseIms_reports&_spAction=displayReport&showHTML=0' + allVars;
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