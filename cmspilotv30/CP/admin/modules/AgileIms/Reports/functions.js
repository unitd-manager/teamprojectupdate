Util.createCPObject('cpm.agileIms.reports');

cpm.agileIms.reports = {
    init: function(){
        $('#header, #nav, #footer').slideUp(1000, function(){
            $('#header, #nav, #footer').remove();
            LoadReady.makeScrollableTable();
        });
        cpm.agileIms.reports.setSearchForm();

        $('.search select[name=report]').change(function(){
            var report = $(this).val();
            $('#reportSearchPanel').slideUp();
            if (report == ''){
                return;
            }
            var url = 'index.php?_topRm=reports&module=agileIms_reports&_spAction=search&showHTML=0&report=' + report;
            $.get(url, function(html){
                $('#reportSearchPanel').html(html).slideDown();
                $('#reportContainer').html('');
            });
        });

        $('.m-agileIms_reports .search select[name=enrollment_type]').livequery('change', function(e){
            cpm.agileIms.reports.loadCompanyContactDropdown.call(this);
        });

    },

    loadCompanyContactDropdown: function(){
        $(this).each(function(){
            enrollment_type = $(this).val();
            var url = $('#scopeRootAlias').val() + 'index.php?module=agileIms_reports&_spAction=companyContactSqlByEnrollmentType&showHTML=0'

            $.getJSON(url, {enrollment_type: enrollment_type}, function(data) {
                $('.m-agileIms_reports .search select[name=company_contact_id]').cp_loadSelect(data);
            });
        });
    },

    setSearchForm: function(){
        $('table.search input.button').livequery('click', function(e) {
            e.preventDefault();
            Util.showProgressInd();
            $('#reportContainer').addClass('reportLoading');
            var allVars = $('table.search').serializeAnything();
            var url = 'index.php?_topRm=reports&module=agileIms_reports&_spAction=displayReport&showHTML=0' + allVars;
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