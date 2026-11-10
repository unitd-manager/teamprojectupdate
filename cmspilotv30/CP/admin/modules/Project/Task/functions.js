Util.createCPObject('cpm.project.task');

cpm.project.task.init = function(){
    $("a.addToTime").livequery('click', function (e){
        var title = $(this).attr('dialogTitle');

        e.preventDefault();
        var expObj = {
            validate: true,
            callbackOnSuccess: function(){
                document.location = document.location;
            }
        }
        Util.openFormInDialog.call(this, 'portalForm', title, 450, 325, expObj);
    });

    if ($('.task__taskHistory').length > 0){
        var actBtnsPanel = $('.task__taskHistory .actBtns'); 
        var record_id = $('#record_id').val();
        var link = "index.php?module=project_taskHistory&_spAction=bulkAdd&showHTML=0&task_id=" + record_id;
        var bulkBtn = "| <a class='bulkAddTaskHist' href='javascript:void(0);' link='" + link + "'>Bulk Add</a>";
        actBtnsPanel.append(bulkBtn);
    }

    $("a.bulkAddTaskHist").livequery('click', function (e){
        var title = "Bulk Add Task History Items"

        e.preventDefault();
        var expObj = {
            validate: true,
            callbackOnSuccess: function(){
                Util.closeAllDialogs();
                Links.reloadPortalRecords('task#taskHistory');
            }
        }
        Util.openFormInDialog.call(this, 'portalForm', title, 450, 325, expObj);
    });
}

