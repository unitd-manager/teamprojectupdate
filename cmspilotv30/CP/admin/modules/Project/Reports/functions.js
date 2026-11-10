Util.createCPObject('cpm.project.reports');
 
cpm.project.reports = {
    init: function(){
        $('#header, #nav, #footer').slideUp(1000, function(){
            $('#header, #nav, #footer').remove();
            LoadReady.makeScrollableTable();
        });
        cpm.project.reports.setSearchForm();
        
        $('.search select[name=report]').change(function(){
            var report = $(this).val();
            $('#reportSearchPanel').slideUp();
            if (report == ''){
                return;
            }
            var url = 'index.php?_topRm=reports&module=project_reports&_spAction=search&showHTML=0&report=' + report;
            $.get(url, function(html){
                $('#reportSearchPanel').html(html).slideDown();
                $('#reportContainer').html('');
            });
        });
    
        $('.w-project-salesByMonthReports .projectAmt').livequery('click', function(){
            var parent = $(this).closest('.projectSalesSummary');
            $('.projectDetails', parent).slideToggle();
        }); 

        $('.w-project-marketingDetailReport .viewComments').livequery('click', function(){
            var parent = $(this).closest('.marketingDetailList');
            $('.commentDetails', parent).slideToggle();
        }); 
},

    setSearchForm: function(){
        $('table.search input.button').livequery('click', function(e) {
            e.preventDefault();
            Util.showProgressInd();
            $('#reportContainer').addClass('reportLoading');
            var allVars = $('table.search').serializeAnything();
            var url = 'index.php?_topRm=reports&module=project_reports&_spAction=displayReport&showHTML=0' + allVars;
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
        
        //$('form#searchTop').livequery(function() {
        //    var formName = 'searchTop';
        //    var extraPar = {};
        //    var cpCSRFToken = $('#cpCSRFToken').val();
        //
        //    var additionalData = {
        //        cpCSRFToken: cpCSRFToken
        //    };
        //
        //    var options = {
        //        success: function(html, statusText, xhr, jqFormObj) {
        //            Util.hideProgressInd();
        //            $('#' + formName).unblock();
        //            $('#reportContainer').removeClass('reportLoading');
        //            $('#reportContainer').html(html);
        //        },
        //        beforeSubmit: function(frmData) {
        //            $('#reportContainer').addClass('reportLoading');
        //            Util.clearPrepopulatedTextbox('#' + formName, frmData);
        //            Util.showProgressInd();
        //            $('#' + formName).block({ message: null });
        //        }
        //        ,data: additionalData
        //        ,dataType: 'html'
        //    };
        //
        //    $('#' + formName).ajaxForm(options);
    	//});
    }
}