Util.createCPObject('cpm.pms.reports');
 
cpm.pms.reports = {
    init: function(){
        $('#header, #nav, #footer').slideUp(1000, function(){
            $('#header, #nav, #footer').remove();
            LoadReady.makeScrollableTable();
        });
        cpm.pms.reports.setSearchForm();
        
        $('.search select[name=report]').change(function(){
            var report = $(this).val();

            if (report == 'enrollmentBySummaryReport' || report == 'attendanceSummaryReport') {
                $("#reportSearchPanel select[name='site_id']").livequery('change', function(){
                    var courseId = $("#reportSearchPanel select[name='course_id']").val();
                    
                    if (courseId) {
                        cpm.pms.reports.populateSession();
                    }
                });
            }
            
            $('#reportSearchPanel').slideUp();
            if (report == ''){
                return;
            }
            var url = 'index.php?_topRm=reports&module=pms_reports&_spAction=search&showHTML=0&report=' + report;
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
            var url = 'index.php?_topRm=reports&module=pms_reports&_spAction=displayReport&showHTML=0' + allVars;
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

$("#reportSearchPanel select[name='course_id']").livequery('change', function(){
    cpm.pms.reports.populateSession();
});

cpm.pms.reports.populateSession = function(){
    Util.showProgressInd('Populating related Sessions.... Please wait');

    var siteId = $("#reportSearchPanel select[name='site_id']").val();
    var courseId = $("#reportSearchPanel select[name='course_id']").val();
    var batchObj = $("#reportSearchPanel select[name='batch_id']");
    var url = $('#scopeRootAlias').val() + 'index.php?module=pms_batchLink&_spAction=batchValueForDropDownReport&showHTML=0';
    $.ajax({
        type: "POST",
        url: url,
        async: false,
        dataType: 'json',
        success: function(json){
            batchObj.empty();
            $.each(json, function() {
                batchObj.append(new Option(this.caption, this.value));
            });
        },
        data: {siteFld: 'site_id', siteValue: siteId, courseFld: 'course_id', courseValue: courseId}
    });
    
    Util.hideProgressInd();
}
