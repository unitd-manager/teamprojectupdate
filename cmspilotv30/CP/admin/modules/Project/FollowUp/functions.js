Util.createCPObject('cpm.project.followUp');

cpm.project.followUp = {
    init: function(){
        $("input[name='title']")
        .livequery(cpm.project.followUp.title);

        $('.queueNoTable').livequery('click', function (e){
        	$('.queueNoTable').removeClass('divtoBlink');
        });

        $('#fld_employee_id').change(function(){
            var company_id = $(this).val();
            var events = {
                  url: 'index.php?module=project_followUp&_spAction=eventDetails&showHTML=0',
                  type: 'POST',
                  data: {
                    company_id: company_id
                  }
            }
            $('#calendarOpportunity').fullCalendar('removeEventSource', events);
            $('#calendarOpportunity').fullCalendar('addEventSource', events);
        });

        $('#fld_employee_id_visit').change(function(){
            var employee_id = $(this).val();
            Util.showProgressInd();
            cpm.project.followUp.patientNameFilter(employee_id);
        });

        $("select[name='opportunity_employee_id']").livequery('change', function (e){
            var employee_id = $(this).val();
            var opportunity_id = $('#opportunity_id_appoint').val();

            var url = "index.php?module=project_followUp&_spAction=UpdateFollowUpEventDetails&showHTML=0";
            Util.showProgressInd();
            $.get(url,{employee_id:employee_id, opportunity_id:opportunity_id}, function(html){
                $('#opportunityDetailsEvent').html(html);
                $('#calendarOpportunity').fullCalendar( 'refetchEvents' );
                Util.hideProgressInd();
            });
        });

        $('.notesEditLink').livequery('click', function (e){
            $('.notesFollowup').slideToggle("notesFollowupdisable");
            $('.notesFollowup').slideToggle("notesFollowupdisable");
        });

        $('.m-project_followUp select[name=status]').livequery('change', function(){
            //var parent = $(this).closest('tr');

            var opportunity_id = $('input[name=opportunity_id]').val();
            var status = $(this).val();
            //alert(status);
            //alert(opportunity_id);
            var url = 'index.php?module=project_followUp&_spAction=updateFollowUpStatus&showHTML=0';
            $.get(url, {opportunity_id: opportunity_id, status: status}, function(json){
                cpm.project.followUp.reloadNotesDetails(opportunity_id, status);
            });
        });
        $('.createVisit').livequery('click', function (e){
            var url = 'index.php?module=hms_appointment&_spAction=createVisitRecord&showHTML=0';
            var dr_required    = $(this).attr('dr_required');
            var appointment_id = $(this).attr('appointment_id');

            if(dr_required == ''){
                cpm.project.followUp.updateAppointmentDetails(appointment_id);

            }else{
                $.get(url,{appointment_id:appointment_id}, function(html){
                    Util.closeAllDialogs();
                    var mgsalert='Patient Visit Record Created';
                    var n = noty({
                        text: mgsalert,
                        type: 'confirm',
                        dismissQueue: true,
                        layout: 'topCenter',
                        theme: 'defaultTheme',
                        timeout: 5000,
                    });
                    $('#calendarOpportunity').fullCalendar( 'refetchEvents' );
                    cpm.project.followUp.reloadCompanyDetails();
                    cpm.project.followUp.reloadOpportunityListDetails();
                    cpm.hms.project.followUp.reloadQueueno();
                    setInterval(function () {
					    $(".divtoBlink").css("background-color", function () {
					        this.switch = !this.switch
					        return this.switch ? "#FFC000" : ""
					    });
					}, 1000)
                });
            }
        });
        /* Add Notes Details */
        $('#renewalLinkPortal').livequery('click', function (e){
                var title = "Add Notes";
                var opportunity_id = $(this).attr('opportunity_id');
                
                e.preventDefault();

                var expObj = {
                    validate: true
                   ,callbackOnSuccess: function(){
                        var msg = 'Notes Added Successfully';
                        Util.alert(msg, function(){
                            $('#dialog1').dialog('close');
                            $('#dialog1').dialog('destroy');
                            $('#dialog1').remove();
                            //window.location.reload(true);
                            cpm.project.followUp.reloadNotesListobj(opportunity_id);
                        });
                    }
                }
                Util.openFormInDialog.call(this, 'portalForm', title, 600, 300, expObj);
        });

    /* Edte Follow Up */
    /*$('.EditFollowup').livequery('click', function (e){
        var title = "Edit Followup";
        var comment_id = $(this).attr('comment_id');
        //var record_id = $(this).attr('record_id');
        var opportunity_id = $(this).attr('opportunity_id');
        e.preventDefault();

        var expObj = {
            validate: true
           ,callbackOnSuccess: function(){
                var msg = 'Notes Updated Successfully';
                Util.alert(msg, function(){
                    $('#dialog1').dialog('close');
                    $('#dialog1').dialog('destroy');
                    $('#dialog1').remove();
                    cpm.project.followUp.reloadNotesListobj(opportunity_id);
                });
            }
        }
        Util.openFormInDialog.call(this, 'portalForm', title, 600, 300, expObj);
    });*/

        /* Delete Follow Up */
        /*$('.deletefollowup').livequery('click', function (e){
            var opportunity_id = $(this).attr('opportunity_id');
            msg = "Do you like to delete the Notes?";
            if (!confirm(msg)){
                return false;
            }
            else{
                var comment_id = $(this).attr('comment_id');
                var url = 'index.php?module=project_followUp&_spAction=Deletefollowup&showHTML=0&comment_id=' + comment_id;
                $.get(url, {comment_id: comment_id}, function(html){
                    cpm.project.followUp.reloadNotesListobj(opportunity_id);
                });
            }
        });*/

        $('.evenDetails').livequery('click', function (e){
            var title = "Opportunity Details";
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                Util.closeAllDialogs();
                }
            }
             Util.openDialogForLink.call(this, title, 600, 'auto', expObj);
        });

        $('.cancelPatientVisit').livequery('click', function (e){
            msg = "Do you like to cancel the patient visit?";
            if (!confirm(msg)){
                return false;
            }
            else {
                var url = 'index.php?_topRm=main&module=hms_appointment&_spAction=cancelVisitRecord&showHTML=0';
                Util.showProgressInd();
                var patient_visit_id = $(this).attr('patient_visit_id');
                $.get(url,{patient_visit_id: patient_visit_id}, function(html){
                    var mgsalert='Patient visit Cancelled Succesfully';
                    var n = noty({
                        text: mgsalert,
                        type: 'confirm',
                        dismissQueue: true,
                        layout: 'topCenter',
                        theme: 'defaultTheme',
                        timeout: 5000,
                    });
                    cpm.project.followUp.reloadDoctorDetails();
                    cpm.project.followUp.reloadAppointmentListDetails();
                    cpm.project.followUp.reloadQueueno();
                });
            }
        });

        $('.cancelAppointment').livequery('click', function (e){
            msg = "Do you like to cancel the appointment?";
            if (!confirm(msg)){
                return false;
            }
            else {
                var url = 'index.php?_topRm=main&module=hms_appointment&_spAction=cancelAppointmentRecord&showHTML=0';
                Util.showProgressInd();
                var appointment_id = $(this).attr('appointment_id');
                $.get(url,{appointment_id: appointment_id}, function(html){
                    var mgsalert='Appointment Cancelled Succesfully';
                    var n = noty({
                        text: mgsalert,
                        type: 'confirm',
                        dismissQueue: true,
                        layout: 'topCenter',
                        theme: 'defaultTheme',
                        timeout: 5000,
                    });
                    $('#calendarAppointment').fullCalendar( 'refetchEvents' );
                    cpm.project.followUp.reloadDoctorDetails();
                    cpm.project.followUp.reloadAppointmentListDetails();
                    cpm.project.followUp.reloadQueueno();
                });
            }
        });

    },

    reloadNotesListobj: function(opportunity_id){
             var url = 'index.php?module=project_followUp&_spAction=addFollowupDetail&showHTML=0';
            $.get(url, {opportunity_id: opportunity_id}, function(html){
                $('#AddFollowUpPortal').html(html);
                Util.hideProgressInd();
             });
    },
        //Auto select patient details
    title: function() {
        var titleObj = this;
        $(titleObj).autocomplete({
             source : 'index.php?module=project_followUp&_spAction=searchPatientDetails&showHTML=0'
            ,minLength : 3
            ,selectFirst: true
            ,autoFocus: true
            ,select: function(event, ui) {
                var selectedObj = ui.item;
                var patient_information_id = selectedObj.id
                //alert (patient_information_id);
                $('input[name=patient_information_id]').val(patient_information_id);
                //$(this).after("<input type='hidden' name='patient_information_id' value=" + patient_information_id + ">");
            }
        });
    },

    updateAppointmentDetails: function(opportunity_id){
        var title = "";
        var url   = "index.php?module=project_followUp&_spAction=updateAppointmentDetails&appointment_id="+appointment_id+"&showHTML=0";

        var exp = {
            url: url
           ,validate: true
           ,submitBtnText: 'Submit'
           ,cancelBtnText: 'Close'
           ,callbackOnSuccess: function(){
                $('#dialog1').dialog('close');
                $('#dialog1').dialog('destroy');
                $('#dialog1').remove();
                Util.closeAllDialogs();

                var mgsalert='Patient Visit Record Updated';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 5000,
                });

                var urlVisit = 'index.php?module=hms_appointment&_spAction=createVisitRecord&showHTML=0';
                $.get(urlVisit,{appointment_id:appointment_id}, function(html){
                    var mgsalert2='Patient Visit Record Created';
                    var n = noty({
                        text: mgsalert2,
                        type: 'confirm',
                        dismissQueue: true,
                        layout: 'topCenter',
                        theme: 'defaultTheme',
                        timeout: 5000,
                    });
                    $('#calendarOpportunity').fullCalendar( 'refetchEvents' );
                    cpm.project.followUp.reloadCompanyDetails();
                    cpm.project.followUp.reloadOpportunityListDetails();
                    cpm.project.followUp.reloadQueueno();
                    setInterval(function () {
					    $(".divtoBlink").css("background-color", function () {
					        this.switch = !this.switch
					        return this.switch ? "#FFC000" : ""
					    });
					}, 1000)
                });
            }
        };
        Util.openFormInDialog.call('','portalFormAppointmentUpdate', title,  588, 318, exp);
    },

    patientNameFilter: function(employee_id){
        var url = "index.php?module=hms_appointment&_spAction=appointmentListDetails&showHTML=0";
        $.get(url,{employee_id:employee_id}, function(html){
            $('#appointmentListDetails').html(html);
            Util.hideProgressInd();
        });
    },
}

cpm.project.followUp.run = function(exp){
    var handle = exp.handle;
    var eventAction = exp.eventAction;
    var headerObj = exp.headerObj ? exp.headerObj : null;
    var timeFormatObj = exp.timeFormatObj;
    var minTime = exp.minTime;
    var maxTime = exp.maxTime;

    $('#' + handle).fullCalendar({
         eventSources: [eventAction]
        ,header: headerObj
        ,timeFormat:'hh:mm tt'
        ,minTime : minTime
        ,maxTime : maxTime
        ,editable: true
        ,views: {
        }
        ,dayClick: function(date, allDay, jsEvent, view) {

            if (allDay) {
                //alert('Clicked on the entire day: ' + date);
                var local = new Date(date);
                var currentdate = new Date();
                local.setMinutes(date.getMinutes() - date.getTimezoneOffset());
                var SelectedTime = currentdate.getHoursTwoDigits() + ":" + currentdate.getMinutesTwoDigits() + ":" + date.getSecondsTwoDigits();
                //var SelectedTime = date.formatAMPM();
                var selectedDate = local.toJSON().slice(0, 10);
                //cpm.project.followUp.addAppointmentDetails(SelectedTime, selectedDate);
            }else{
                //alert('Clicked on the slot: ' + date);
                var local = new Date(date);
                local.setMinutes(date.getMinutes() - date.getTimezoneOffset());
                var SelectedTime = date.getHoursTwoDigits() + ":" + date.getMinutesTwoDigits() + ":" + date.getSecondsTwoDigits();
                //var SelectedTime = date.formatAMPM();
                var selectedDate = local.toJSON().slice(0, 10);
                //cpm.project.followUp.addAppointmentDetails(SelectedTime, selectedDate);
            }

            //alert('Coordinates: ' + jsEvent.pageX + ',' + jsEvent.pageY);

            //alert('Current view: ' + view.name);

            // change the day's background color just for fun
            //$(this).css('background-color', 'red');

        }
       ,eventClick: function(event, jsEvent, view) {
            // To avoid navigating to the url after the modal window display.
            if (event.url) {
                cpm.project.followUp.showFollowUpDetails.call(this);
                return false;
            }
        }
       ,eventMouseover: function(event, jsEvent, view) {
           //alert('Event: ' + event.title);
           //cpw.aceIms.calendarDisplay.showBatchDetails.call(this);
        }
       ,eventDrop: function(event, delta, revertFunc, jsEvent, ui, view) {
            var view = $('#calendarOpportunity').fullCalendar('getView');

            if (!confirm("Please note for this action the appointment time will be changed")) {
                $('#calendarOpportunity').fullCalendar( 'refetchEvents' );
            }else {

                if (view.name == 'agendaDay' || view.name == 'agendaWeek') {
                    var local = new Date(event.start);
                    local.setMinutes(event.start.getMinutes() - event.start.getTimezoneOffset());
                    var SelectedTime = event.start.getHoursTwoDigits() + ":" + event.start.getMinutesTwoDigits() + ":" + event.start.getSecondsTwoDigits();
                    var selectedDate = local.toJSON().slice(0, 10);
                    var url = 'index.php?_topRm=main&module=project_followUp&_spAction=changeAppointmentByDrag&showHTML=0';
                    var opportunity_id = event.opportunity_id;
                    var check_up_time  = SelectedTime;
                    var check_up_date  = selectedDate;

                    $.get(url,{opportunity_id: opportunity_id, check_up_time: check_up_time, check_up_date: check_up_date}, function(){
                        $('#calendarOpportunity').fullCalendar( 'refetchEvents' );
                    });

                }else{
                    var local = new Date(event.start);
                    local.setMinutes(event.start.getMinutes() - event.start.getTimezoneOffset());
                    var selectedDate = local.toJSON().slice(0, 10);
                    var url = 'index.php?_topRm=main&module=project_followUp&_spAction=changeAppointmentByDrag&showHTML=0';
                    var appointment_id = event.appointment_id;
                    var check_up_date  = selectedDate;
                    var viewName       = view.name;

                    $.get(url,{appointment_id: appointment_id, check_up_date: check_up_date, viewName: viewName}, function(){
                        $('#calendarOpportunity').fullCalendar( 'refetchEvents' );
                    });
                }

            }

        }
        ,eventRender: function(event, element, view){
            /*if (view.name == 'agendaDay'){
                $(element).find('.fc-event-time').append("<br/>" + event.title);
                $(element).find('.fc-event-time').append("<br/>" + event.doctor_name);
            }*/

            //if (view.name == 'agendaDay'){
                $(element).find('.fc-event-time').append("<br/>" + event.company_name+ "<br/>" + event.opportunitytitle);
               // $(element).find('.fc-event-time').append(event.createVisit);
               // $(element).find('.fc-event-time').prepend(event.cancelAppointment);
            //}

            //$(element).find('.fc-event-time').append(event.createVisit);
            //$(element).find('.fc-event-time').append(event.cancelAppointment);
            //$(element).find('.fc-event-title').append("<br/>" + event.patient_name);
            //$(element).find('.fc-event-title').append("<br/>" + event.doctor_name);

        }
        ,eventAfterRender: function(event, element, view) {
            if (view.name == 'agendaDay'){
                $(element).css('width','135px');
                $(element).css('height','70px');
            }

            /*if (view.name == 'agendaWeek'){
            	$(element).css('width','auto');
            	$(element).css('height','auto');
            }*/
        }
    });

    $('#calendarOpportunity').fullCalendar('changeView', 'agendaDay');

    cpm.project.followUp.reloadOpportunityDetails = function(){
        var url = 'index.php?module=project_followUp&_spAction=opportunityDetails&showHTML=0';
        $.get(url,  function(html){
            $('#opportunityDetails').html(html);
            Util.hideProgressInd();
        });
    }

    cpm.project.followUp.reloadNotesDetails = function(opportunity_id, notes){
        var url = 'index.php?module=project_followUp&_spAction=followUpNotes&opportunity_id='+opportunity_id+'&notes='+notes+'&showHTML=0';
        $.get(url,  function(html){
            $('#notesFollowUpTd').html(html);
            Util.hideProgressInd();
        });
    }

    cpm.project.followUp.reloadFollowUpListDetails = function(){
        var url = "index.php?module=project_followUp&_spAction=followUpListDetails&showHTML=0";
        $.get(url,  function(html){
            $('#appointmentListDetails').html(html);
            Util.hideProgressInd();
        });
    }

    cpm.project.followUp.reloadFollowUpDetails = function(){
        var url = 'index.php?module=project_followUp&_spAction=followUpDetails&showHTML=0';
        $.get(url,  function(html){
            $('.followUpvisitScroll1').html(html);
        });
    }

    /*cpm.project.followUp.addAppointmentDetails = function(SelectedTime, selectedDate){
        var title = "Add Opportunity";
        var url   = "index.php?module=project_followUp&_spAction=addAppointmentDetails&check_up_date="+selectedDate+"&check_up_time="+SelectedTime+"&showHTML=0";

        var exp = {
            url: url
           ,validate: true
           ,submitBtnText: 'Submit'
           ,cancelBtnText: 'Close'
           ,callbackOnSuccess: function(){
                var msg = 'Opportunity Created';
                Util.alert(msg, function(){
                    Util.closeAllDialogs();
                    //window.location.reload(true);
                    $('#calendarOpportunity').fullCalendar( 'refetchEvents' );
                    cpm.project.followUp.reloadCompanyDetails();
                    cpm.project.followUp.reloadOpportunityListDetails();
                    cpm.project.followUp.reloadQueueno();
                });
            }
        };
        Util.openFormInDialog.call('','portalFormAppointment', title, 460, 380, exp);
    }*/

    cpm.project.followUp.reloadQueueno = function(){
        var url = 'index.php?_theme=matrix&_spAction=patientQueueNo&showHTML=0';
        $.get(url,  function(html){
            $('.queueNumberDisplay').html(html);
        });
    }

    cpm.project.followUp.showOpportunityDetails = function(){
        var title = "Opportunity Details";

        var expObj = {
            validate: false
           ,submitBtnText: 'Submit'
           ,cancelBtnText: 'Close'
        }
        Util.openFormInDialog.call(this, 'portalForm', title, 625, 450, expObj);
        //alert (123);
    }

    Date.prototype.getHoursTwoDigits = function(){
        var retval = this.getHours();
        if (retval < 10)
        {
            return ("0" + retval.toString());
        }
        else
        {
            return retval.toString();
        }
    }

    Date.prototype.getMinutesTwoDigits = function(){
        var retval = this.getMinutes();
        if (retval < 10)
        {
            return ("0" + retval.toString());
        }
        else
        {
            return retval.toString();
        }
    }

    Date.prototype.getSecondsTwoDigits = function(){
        var retval = this.getSeconds();
        if (retval < 10)
        {
            return ("0" + retval.toString());
        }
        else
        {
            return retval.toString();
        }
    }


    Date.prototype.formatAMPM = function() {
      var hours   = this.getHours();
      var minutes = this.getMinutes();
      var ampm = hours >= 12 ? 'pm' : 'am';
      hours = hours % 12;
      hours = hours ? hours : 12; // the hour '0' should be '12'
      minutes = minutes < 10 ? '0'+minutes : minutes;
      var strTime = hours + ':' + minutes + ' ' + ampm;
      return strTime;
    }


}