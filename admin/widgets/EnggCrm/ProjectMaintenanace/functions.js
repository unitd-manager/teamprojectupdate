$(function(){
    /* Adding 5 Materials in New window */
    $("a.addMultipleMaintain").livequery('click', function (e){
        var title = "Add Renewal";
        var project_id = $(this).attr('project_id');
        var url = 'index.php?widget=enggCrm_projectMaintenanace&_spAction=addMultipleMaterials'
                + '&showHTML=0&project_id=' + project_id;
        var exp = {
            url: url
           ,callbackOnSuccess: function(){
                Util.closeAllDialogs();
                //Util.alert('Updated successfully..');
                var mgsalert = 'Renewal added successfully!';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 5000,
                });
                //window.location.reload(true);
                projectMaintenanace.reloadRenewalPortal(project_id);
            }
        };
        Util.openFormInDialog.call(this, 'addMultipleRenewalForm', title, 1200, 500, exp);
    });

});

var projectMaintenanace = {
   

    reloadRenewalPortal: function(project_id){
        var url = 'index.php?widget=enggCrm_projectMaintenanace&_spAction=ProjectMaintenanacePortal&showHTML=0';
        Util.showProgressInd();
        $.get(url, {project_id: project_id}, function(html){
            Util.hideProgressInd();
            $('#maintenanaceLinkPortal').html(html);
        });
    }
}