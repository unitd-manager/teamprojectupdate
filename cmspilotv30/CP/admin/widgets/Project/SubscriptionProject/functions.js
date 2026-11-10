Util.createCPObject('cpm.project.subscriptionProject');

/*cpm.project.subscriptionProject = {
    init: function(){*/
/*$(function(){
        $('.createInvoice').livequery('click', function (e){
            var project_id = $(this).attr('project_id');
            alert(project_id);
            var url = "index.php?widget=project_subscriptionProject&_spAction=raiseInvoice&project_id=" + project_id;
            $.get(url,{project_id: project_id}, function(html){
            });
        });
});*/
$(function(){
        $('.createInvoice').livequery('click', function (e){
            var project_id = $(this).attr('project_id');
            var url = 'index.php?widget=project_subscriptionProject&_spAction=raiseInvoice&showHTML=0';

            msg = "Do you like to create invoice?";

            if (!confirm(msg)){
                return false;
            }
            else{
               Util.showProgressInd();
               $.get(url, {project_id: project_id}, function(){
                   Util.hideProgressInd();
                   Util.alert('Invoice Created Succesfully!');
                   window.location.reload(true);
               });
            }

        });
});

    //},


//}


