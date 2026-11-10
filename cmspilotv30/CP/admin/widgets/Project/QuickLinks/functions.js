$(function(){
    $("a.newTaskFromDashboard").livequery('click', function (e){
        var title = $(this).attr('dialogTitle');

        e.preventDefault();
        var expObj = {
            validate: true,
            callbackOnSuccess: function(json){
                Util.closeAllDialogs();
                task_id = json.extraParam.task_id;
                task_title = json.extraParam.task_title;
                var title = "Edit Task for the Project: " + task_title;

                /*************************************************************/
                var expObj = {
                    url: 'index.php?lnkRoom=project_task&_spAction=editPortal&showHTML=0&id=' + task_id,
                    validate: true,
                    callbackOnSuccess: function(){
                        document.location = document.location;
                    }
                }
                Util.openFormInDialog.call(this, 'portalForm', title, 800, 650, expObj);
                /*************************************************************/
            }
        }
        Util.openFormInDialog.call(this, 'portalForm', title, 600, 300, expObj);
    });
});