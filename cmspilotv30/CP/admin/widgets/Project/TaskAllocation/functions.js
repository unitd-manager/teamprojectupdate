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
    $("a.addToTime").livequery('click', function (e){
        var title = $(this).attr('dialogTitle');

        e.preventDefault();
        var expObj = {
            validate: true,
            callbackOnSuccess: function(){
                Util.closeAllDialogs();
                Util.alert('Changes made successfully..');
            }
        }
        Util.openFormInDialog.call(this, 'portalForm', title, 450, 325, expObj);
    });

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
});
