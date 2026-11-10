$(function(){
    $('#markAttendanceRecords').livequery('click', function (e){
        var title = "Mark Absent Records";
        e.preventDefault();
        
        var expObj = {
            validate: true
           ,callbackOnSuccess: function(){
                var msg = 'Updated successfully';
                Util.alert(msg, function(){
                    Util.closeAllDialogs();
                    window.location.reload(true);
                });
            }
        }
        Util.openFormInDialog.call(this, 'portalForm', title, 400, 400, expObj);        
    });

        $('.invoiceSummaryfilter select[name=duration]').livequery('change', function(){
            var barChartId = $("div#wd_project_invoiceSummaryChart .tableOuter").attr('id');
            var duration = $(this).val();
            if(duration != "") {
                $.ajax({
                    url: 'index.php?widget=project_invoiceSummaryChart&_spAction=widgetDataJSON&duration='+duration+'&showHTML=0',
                        dataType: 'json',
                        success: function (json) {
                            Util.hideProgressInd();
                            var data = new google.visualization.DataTable();
                            data.addColumn('string', 'Invoice');
                            data.addColumn('number', 'Value');
                            data.addRows(json);

                            var chart = new google.visualization.PieChart(document.getElementById(barChartId));
                            chart.draw(data, {is3D: true, title: 'Invoice Summary'});
                        }
                });
            } else {
                var msg = "Please select duration";
                var n = noty({
                    text: msg,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 3000,
                });
            }
        });

        $('#wd_enggCrm_projectReport select[name=category]').livequery('change', function(){
            var category = $(this).val();
            var status   = $(".w-enggCrm-projectReport select[name='status']").val();
            
            var url = 'index.php?widget=enggCrm_projectReport&_spAction=projectSummaryDisplay';
            Util.showProgressInd();
            $.get(url,{category: category, status: status}, function(html){
                $('#wd_enggCrm_projectReport #projectSummaryDisplay').html(html);
                Util.hideProgressInd();
            });
        });

        $('#wd_enggCrm_projectReport select[name=status]').livequery('change', function(){
            var status = $(this).val();
            var category   = $(".w-enggCrm-projectReport select[name='category']").val();
            
            var url = 'index.php?widget=enggCrm_projectReport&_spAction=projectSummaryDisplay';
            Util.showProgressInd();
            $.get(url,{category: category, status: status}, function(html){
                $('#wd_enggCrm_projectReport #projectSummaryDisplay').html(html);
                Util.hideProgressInd();
            });
        });
});