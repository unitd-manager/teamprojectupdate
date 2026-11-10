/* Filtering Renewals with respect to values chosen */
$('#wd_project_domainHostingSummary select[name=duration]').livequery('change', function(){
    var duration = $(this).val();

    var url = 'index.php?widget=project_domainHostingSummary&_spAction=renewalListInDashboard';
    Util.showProgressInd();
    $.get(url,{duration: duration}, function(html){
        $('#wd_project_domainHostingSummary table').html(html);
        Util.hideProgressInd();
    });
});

