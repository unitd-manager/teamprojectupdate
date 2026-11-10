$(function(){
    /*
	var colWidth = $('#wd_project_taskAllocation .col').outerWidth(true);
	var totCols = $('#wd_project_taskAllocation .col').length;
	$('#wd_project_taskAllocation .inner').css('width', totCols*colWidth + 'px');
	*/

	$('input[name=current],input[name=task_now]').livequery('click', function(){
		var container = $(this).closest('.c50l')

		var task_id = $(this).attr('task_id');
		var staff_id = container.attr('staff_id');
		var currentVal = $(this).attr('checked') ? 1 : 0;
		var fldName = $(this).attr('name');

		var url = 'index.php?widget=project_taskAllocation&_spAction=updateCurrentStatus';

        Util.showProgressInd();

        $('.inner', container).html('');
        $.get(url, {fldName: fldName, task_id: task_id, staff_id: staff_id, currentVal: currentVal}, function(data){
            $('.inner', container).html(data)
            Util.hideProgressInd();
        });
	});
});

$(function(){
    Globals.task_id = '';
    $("a.editTaskHistory").livequery('click', function (e){
        var title = $(this).attr('dialogTitle');
        Globals.task_id = $(this).attr('task_id');

        e.preventDefault();
        var expObj = {
            beforeCloseFn: function(){
                Util.closeAllDialogs();
                Util.alert('Changes made successfully..');
            }
        }
        Util.openDialogForLink.call(this, title, 800, 600, expObj);
    });

    $("select[name=staff_current_status]").livequery('click', function (e){
		var staff_id = $(this).attr('staff_id');
		var status = $(this).val();
		var url = 'index.php?widget=project_taskAllocation&_spAction=updateStaffCurrentStatus';

        $.get(url, {staff_id: staff_id, status: status}, function(){
            Util.hideProgressInd();
        });
    });

    $(".task__taskHistory").livequery(function(){
        var actBtnsPanel = $('.actBtns', $(this)); 
        var record_id = Globals.task_id;
        var link = "index.php?module=project_taskHistory&_spAction=bulkAdd&showHTML=0&task_id=" + record_id;
        var bulkBtn = "| <a class='bulkAddTaskHist' href='javascript:void(0);' link='" + link + "'>Bulk Add</a>";
        actBtnsPanel.append(bulkBtn);
    });

    $("a.bulkAddTaskHist").livequery('click', function (e){
        var title = "Bulk Add Task History Items"
        e.preventDefault();
        var expObj = {
            validate: true,
            callbackOnSuccess: function(){
                $('#dialog1').dialog('close');
                $('#dialog1').dialog('destroy');
                Links.reloadPortalRecords('task#taskHistory', 'task', Globals.task_id, 'edit');
                Globals.task_id = '';
            }
        }
        Util.openFormInDialog.call(this, 'portalForm', title, 450, 325, expObj);
    });

    /* Adding new task for task history in dashboard */
    $("a.addNewTask").livequery('click', function (e){
        var title = $(this).attr('dialogTitle');

        e.preventDefault();
        var expObj = {
            validate: true,
            callbackOnSuccess: function(){
                Util.closeAllDialogs();
                Util.alert('Task Created Succesfully..');
                Task.reloadTaskHistory();
            }
        }
        Util.openFormInDialog.call(this, 'addTaskForm', title, 600, 500, expObj);
    });

    /* Sending task mail in dashboard */
    $("a.taskMail").livequery('click', function (e){
        var title = $(this).attr('dialogTitle');

        e.preventDefault();
        var expObj = {
            validate: true,
            callbackOnSuccess: function(){
                Util.closeAllDialogs();
                Util.alert('Email Sent successfully..');
            }
        }
        Util.openFormInDialog.call(this, 'sendTaskEmail', title, 300, 200, expObj);
    });

    /* Loading related values for Task with reference to Project in new task */
    $('#addTaskForm select#fld_project_id').livequery('change', function() {
        var url = 'index.php?module=project_task&_spAction=taskJsonByProId&showHTML=0';
        var project_id = $(this).val();
        $.get(url, {project_id: project_id}, function (data) {
            $('#fld_task_id').cp_loadSelect(data);
        }, 'json');
    });
    
    /* Task history detail summary view */
    $("a.timeSheetDetail").livequery('click', function (e){
        var task_history_id = $(this).attr('task_history_id');
        var url = 'index.php?widget=project_taskAllocation&_spAction=timeSheetDetails'
                + '&task_history_id=' + task_history_id
                + '&showHTML=0';
        var exp = {
            url: url
        };
        Util.openDialogForLink('Detail',  900, 500, 0, exp);
    });

    /* Task history edit */
    $("a.timeSheetEdit").livequery('click', function (e){
        var title = "Update Description";

        e.preventDefault();
        var expObj = {
            validate: true,
            callbackOnSuccess: function(){
                Util.closeAllDialogs();
                Util.alert('Updated successfully..');
                Task.reloadTaskHistory();
            }
        }
        Util.openFormInDialog.call(this, 'timeSheetEdit', title, 600, 350, expObj);
    });

    /* Send update email */
    $("a.sendEmail").livequery('click', function (e){
        var title = $(this).attr('dialogTitle');

        e.preventDefault();
        var expObj = {
            validate: true,
            callbackOnSuccess: function(){
                Util.closeAllDialogs();
                Util.alert('Email Sent successfully..');
            }
        }
        Util.openFormInDialog.call(this, 'addSendEmail', title, 600, 450, expObj);
    });

    /* Checking the checkbox for updating working status */
	$('input[name=task_history_now]').livequery('click', function(){
		var container = $(this).closest('.c50l')
 		var parent = $(this).closest('tr');
		var task_history_id = $(this).attr('task_history_id');
		var staff_id = $(this).attr('staff_id');
		//var staff_id = container.attr('staff_id');
		var task_history_now = $(this).attr('checked') ? 1 : 0;
		var fldName = $(this).attr('name');

		var url = 'index.php?widget=project_taskAllocation&_spAction=updateTaskHistoryNowByStaff';

        Util.showProgressInd();

        $('.inner', container).html('');
        $.get(url, {fldName: fldName, task_history_id: task_history_id, staff_id: staff_id, task_history_now: task_history_now}, function(data){
            $('.inner', container).html(data)
             if(task_history_now == 1){
			 	$(parent).css('background-color', 'pink')           	
			 } else {
				$(parent).css('background-color', '')
			 }
				
				/*if ($staff_id == 11) {
					$(parent).css('background-color', 'red')           	
				}else{
					$(parent).css('background-color', '')
				}*/

            Util.hideProgressInd();

        });
	});
});

/* Refreshing task history after adding a new task */
var Task = {
	reloadTaskHistory: function(){
	    var url = 'index.php?widget=project_taskAllocation&_spAction=tasksUpdateByStaff';
	    $.get(url,  function(html){
	        $('#taskHistory').html(html);
	        Util.hideProgressInd();
	    });
	}    
}

/* Filtering task history according to staff */
$('#wd_project_taskAllocation #allTask select[name=staff_id]').livequery('change', function(){
    var staff_id = $(this).val();
    var status   = $(".w-project-taskAllocation select[name='status']").val();
    var sort_by  = $(".w-project-taskAllocation select[name='sort_by']").val();
    
	var url = 'index.php?widget=project_taskAllocation&_spAction=tasksUpdateByStaff';
    Util.showProgressInd();
    $.get(url,{staff_id: staff_id, status: status, sort_by: sort_by}, function(html){
        $('#wd_project_taskAllocation table').html(html);
        Util.hideProgressInd();
    });
});

/* Filtering task history according to status */
$('#wd_project_taskAllocation #taskHistory select[name=status]').livequery('change', function(){
    var status      = $(this).val();
    var staff_id    = $(".w-project-taskAllocation select[name='staff_id']").val();
    var sort_by     = $(".w-project-taskAllocation select[name='sort_by']").val();

	var url = 'index.php?widget=project_taskAllocation&_spAction=tasksUpdateByStaff';
    Util.showProgressInd();
    $.get(url,{status: status, staff_id: staff_id, sort_by: sort_by}, function(html){
        $('#wd_project_taskAllocation table').html(html);
        Util.hideProgressInd();
    });
});

/* Filtering task history according to sort by */
$('#wd_project_taskAllocation #taskHistory select[name=sort_by]').livequery('change', function(){
    var sort_by     = $(this).val();
    var staff_id    = $(".w-project-taskAllocation select[name='staff_id']").val();
    var status      = $(".w-project-taskAllocation select[name='status']").val();

	var url = 'index.php?widget=project_taskAllocation&_spAction=tasksUpdateByStaff';
    Util.showProgressInd();
    $.get(url,{sort_by: sort_by, staff_id: staff_id, status: status}, function(html){
        $('#wd_project_taskAllocation table').html(html);
        Util.hideProgressInd();
    });
});

/* Changing staff in task history from dashboard list */
$('#wd_project_taskAllocation #taskHistory select[id=fld_staff_name]').livequery('change', function(){
    var staff_id = $(this).val();
    var task_history_id = $(this ).closest('td').attr('id');
    
	var url = 'index.php?widget=project_taskAllocation&_spAction=updateTaskHistoryStaffIdByStaff';
    Util.showProgressInd();
    $.get(url,{staff_id: staff_id, task_history_id: task_history_id}, function(html){
        Util.hideProgressInd();
    });
});

/* Changing priority in task history from dashboard list */
$('#wd_project_taskAllocation #taskHistory select[id=fld_priority]').livequery('change', function(){
    var priority = $(this).val();
    var task_history_id = $(this ).closest('td').attr('id');

	var url = 'index.php?widget=project_taskAllocation&_spAction=updateTaskHistoryPriorityByStaff';
    Util.showProgressInd();
    $.get(url,{priority: priority, task_history_id: task_history_id}, function(html){
        Util.hideProgressInd();
    });
});

/* Changing progress percentage in task history from dashboard list */
$('#wd_project_taskAllocation #taskHistory select[id=fld_progress_percent]').livequery('change', function(){
    var progress_percent = $(this).val();
    var task_history_id = $(this ).closest('td').attr('id');

	var url = 'index.php?widget=project_taskAllocation&_spAction=updateTaskHistoryProgressPercentByStaff';
    Util.showProgressInd();
    $.get(url,{progress_percent: progress_percent, task_history_id: task_history_id}, function(html){
        Util.hideProgressInd();
    });
});

/* Changing status in task history from dashboard list */
$('#wd_project_taskAllocation #taskHistory select[id=fld_Status]').livequery('change', function(){
    var status = $(this).val();
    var task_history_id = $(this ).closest('td').attr('id');

	var url = 'index.php?widget=project_taskAllocation&_spAction=updateTaskHistoryStatusByStaff';
    Util.showProgressInd();
    $.get(url,{status: status, task_history_id: task_history_id}, function(html){
        Util.hideProgressInd();
    });
});

/* Updating Estimated hours in task history from dashboard list */
$('#wd_project_taskAllocation #taskHistory input[id=fld_estd_hrs]').livequery('change', function(){
    var estd_hrs = $(this).val();
    var task_history_id = $(this ).closest('td').attr('id');

	var url = 'index.php?widget=project_taskAllocation&_spAction=updateTaskHistoryEstimatedHoursByStaff';
    Util.showProgressInd();
    $.get(url,{estd_hrs: estd_hrs, task_history_id: task_history_id}, function(html){
        Util.hideProgressInd();
    });
});

