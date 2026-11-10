Util.createCPObject('cpm.hms.reports');

cpm.hms.reports = {
    init: function(){

        var config = {
            '.chosen-select'           : {},
            '.chosen-select-deselect'  : {allow_single_deselect:true},
            '.chosen-select-no-single' : {disable_search_threshold:10},
            '.chosen-select-no-results': {no_results_text:'Oops, nothing found!'},
            '.chosen-select-width'     : {width:'95%'}
        }

        for (var selector in config) {
            $(selector).chosen(config[selector]);
        }

        $('#header, #nav, #footer').slideUp(1000, function(){
            $('#header, #nav, #footer').remove();
            LoadReady.makeScrollableTable();
        });
        cpm.hms.reports.setSearchForm();

        $('.search select[name=report]').change(function(){
            var report = $(this).val();
            $('#reportSearchPanel').slideUp();
            if (report == ''){
                return;
            }
            var url = 'index.php?_topRm=reports&module=hms_reports&_spAction=search&showHTML=0&report=' + report;
            $.get(url, function(html){
                $('#reportSearchPanel').html(html).slideDown();
                $('#reportContainer').html('');
            });
        });

        $('.w-tradingsg-summaryPurchaseSalesReport .purchaseVal').livequery('click', function(){
        	var parent = $(this).closest('.purchaseSalesSummary');
            $('.purchaseDetails', parent).slideToggle();
        });

        $('.w-tradingsg-overallSalesSummary .purchaseVal').livequery('click', function(){
            var parent = $(this).closest('.purchaseSalesSummary');
            $('.purchaseDetails', parent).slideToggle();
        });

        $('.w-tradingsg-summaryOfProductSalesPrice .salesVal').livequery('click', function(){
            var parent = $(this).closest('.salesSummaryPrice');
            $('.salesDetails', parent).slideToggle();
        }); 


        $("input[name='company_patient_search']").livequery('keyup', function(){
            var obj = $(this);
            cpm.hms.reports.patientName(obj);
        });

        $('.m-hms_reports .spylonearch select[name=bill_type]').livequery('change', function(){
            $('input[name=company_patient_id]').val('');
            $('input[name=company_patient_search]').val('');
        }); 
    },

    loadCompanyPatientDropdown: function(){
        $(this).each(function(){
            bill_type = $(this).val();
            var url = $('#scopeRootAlias').val() + 'index.php?module=hms_reports&_spAction=companyPatientSqlByBillType1showHTML=0'

            $.getJSON(url, {bill_type: bill_type}, function(data) {
                $('.m-hms_reports .search select[name=company_patient_id]').cp_loadSelect(data);
            });
        });
    },

    patientName: function(obj) {
        var titleObj = obj;
        var bill_type = $('select[name=bill_type]').val();

        $(titleObj).autocomplete({
             source : "index.php?module=hms_reports&_spAction=companyPatientSqlByBillType&bill_type="+bill_type+"&showHTML=0"
            ,minLength : 3
            ,selectFirst: true
            ,autoFocus: true
            ,select: function(event, ui) {
                var selectedObj = ui.item;
                var patient_information_id = selectedObj.id
                $('input[name=company_patient_id]').val(patient_information_id);
            }
        });
    },

    setSearchForm: function(){
        $('table.search input.button').livequery('click', function(e) {
            e.preventDefault();
            Util.showProgressInd();
            $('#reportContainer').addClass('reportLoading');
            var allVars = $('table.search').serializeAnything();
            var url = 'index.php?_topRm=reports&module=hms_reports&_spAction=displayReport&showHTML=0' + allVars;
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