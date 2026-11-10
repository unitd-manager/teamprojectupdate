Util.createCPObject('cpm.labsg.followUpPatient');

cpm.labsg.followUpPatient = {
    init: function(){
        $("input[name='patient_name']")
        .livequery(cpm.labsg.followUpPatient.patientName);

        $('.queueNoTable').livequery('click', function (e){
        	$('.queueNoTable').removeClass('divtoBlink');
        });

        $('#fld_employee_id').change(function(){
            var doctor_id = $(this).val();
            var events = {
                  url: 'index.php?module=labsg_followUpPatient&_spAction=eventDetails&showHTML=0',
                  type: 'POST',
                  data: {
                    doctor_id: doctor_id
                  }
            }
            $('#calendarFollowUpPatient').fullCalendar('removeEventSource', events);
            $('#calendarFollowUpPatient').fullCalendar('addEventSource', events);
        });

        $('#fld_employee_id_appointment').change(function(){
            var employee_id = $(this).val();
            Util.showProgressInd();
            cpm.labsg.followUpPatient.patientNameFilter(employee_id);
        });

        $('.notesEditLink').livequery('click', function (e){
            $('.notesFollowup').slideToggle("notesFollowupdisable");
            $('.notesFollowupDefault').slideToggle("notesFollowupdisable");
        });

        $('.followUpNotesUpdate').livequery('click', function (e){
            var follow_up_patient_id = $(this).attr('follow_up_patient_id');
            var notes = $('#fld_description').val();

            var url = 'index.php?_topRm=main&module=labsg_followUpPatient&_spAction=updateFollowUpNotes&showHTML=0';
            Util.showProgressInd();

            $.get(url,{follow_up_patient_id: follow_up_patient_id, notes:notes}, function(html){
                cpm.labsg.followUpPatient.reloadNotesDetails(follow_up_patient_id, notes);
                var mgsalert='Notes Updated';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 5000,
                });
            });
        });

        $('.createAppointment').livequery('click', function (e){
            var follow_up_patient_id = $(this).attr('follow_up_patient_id');
            var employee_id          = $(this).attr('employee_id');
            var url = 'index.php?module=labsg_followUpPatient&_spAction=createAppointmentRecord&follow_up_patient_id='
                      +follow_up_patient_id+'&showHTML=0';
            var title = "Create Appointment";

            var exp = {
                url: url
               ,validate: true
               ,submitBtnText: 'Submit'
               ,cancelBtnText: 'Close'
               ,callbackOnSuccess: function(){
                    Util.closeAllDialogs();
                    $('#calendarFollowUpPatient').fullCalendar( 'refetchEvents' );
                    cpm.labsg.followUpPatient.reloadDoctorDetails();
                    cpm.labsg.followUpPatient.reloadFollowUpListDetails();
                    var mgsalert='Appointment Record Created';
                    $('#dialog1').dialog('close');
                    $('#dialog1').dialog('destroy');
                    $('#dialog1').remove();
                    var n = noty({
                        text: mgsalert,
                        type: 'confirm',
                        dismissQueue: true,
                        layout: 'topCenter',
                        theme: 'defaultTheme',
                        timeout: 5000,
                    });
                }
            };

            Util.openFormInDialog.call('','portalFormAppointment', title, 460, 300, exp);

        });

        $('.evenDetails').livequery('click', function (e){
            var title = "Follow Up Details";
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                Util.closeAllDialogs();
                }
            }
             Util.openDialogForLink.call(this, title, 990, 290, expObj);
        });

        $('.cancelFollowUp').livequery('click', function (e){
            msg = "Do you like to cancel the follow up?";
            if (!confirm(msg)){
                return false;
            }
            else {
                var url = 'index.php?_topRm=main&module=labsg_followUpPatient&_spAction=cancelFollowUpRecord&showHTML=0';
                Util.showProgressInd();
                var follow_up_patient_id = $(this).attr('follow_up_patient_id');
                var appointment_id       = $(this).attr('appointment_id');
                $.get(url,{follow_up_patient_id: follow_up_patient_id, appointment_id: appointment_id}, function(html){
                    Util.closeAllDialogs();
                    Util.hideProgressInd();
                    var mgsalert='Follow Up Cancelled Succesfully';
                    var n = noty({
                        text: mgsalert,
                        type: 'confirm',
                        dismissQueue: true,
                        layout: 'topCenter',
                        theme: 'defaultTheme',
                        timeout: 5000,
                    });
                    $('#calendarFollowUpPatient').fullCalendar( 'refetchEvents' );
                    cpm.labsg.followUpPatient.reloadDoctorDetails();
                    cpm.labsg.followUpPatient.reloadFollowUpListDetails();
                });
            }
        });

    },

    //Auto select patient details
    patientName: function() {
        var titleObj = this;
        $(titleObj).autocomplete({
             source : 'index.php?module=labsg_followUpPatient&_spAction=searchPatientDetails&showHTML=0'
            ,minLength : 2
            ,selectFirst: true
            ,autoFocus: true
            ,select: function(event, ui) {
                var selectedObj = ui.item;
                var patient_information_id = selectedObj.id
                $('input[name=patient_information_id]').val(patient_information_id);
            }
        });
    },

    patientNameFilter: function(employee_id){
        var url = "index.php?module=labsg_followUpPatient&_spAction=followUpListDetails&showHTML=0";
        $.get(url,{employee_id:employee_id}, function(html){
            $('#followUpListDetails').html(html);
            Util.hideProgressInd();
        });
    },
}

cpm.labsg.followUpPatient.run = function(exp){
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
                cpm.labsg.followUpPatient.addFollowUpDetails(SelectedTime, selectedDate);
            }else{
                //alert('Clicked on the slot: ' + date);
                var local = new Date(date);
                local.setMinutes(date.getMinutes() - date.getTimezoneOffset());
                var SelectedTime = date.getHoursTwoDigits() + ":" + date.getMinutesTwoDigits() + ":" + date.getSecondsTwoDigits();
                //var SelectedTime = date.formatAMPM();
                var selectedDate = local.toJSON().slice(0, 10);
                cpm.labsg.followUpPatient.addFollowUpDetails(SelectedTime, selectedDate);
            }

            //alert('Coordinates: ' + jsEvent.pageX + ',' + jsEvent.pageY);

            //alert('Current view: ' + view.name);

            // change the day's background color just for fun
            //$(this).css('background-color', 'red');

        }
       ,eventClick: function(event, jsEvent, view) {
            // To avoid navigating to the url after the modal window display.
            if (event.url) {
                cpm.labsg.followUpPatient.showFollowUpDetails.call(this);
                return false;
            }
        }
       ,eventMouseover: function(event, jsEvent, view) {
           //alert('Event: ' + event.title);
           //cpw.aceIms.calendarDisplay.showBatchDetails.call(this);
        }
       ,eventDrop: function(event, delta, revertFunc, jsEvent, ui, view) {
            var view = $('#calendarFollowUpPatient').fullCalendar('getView');

            if (!confirm("Please note for this action the follow up time will be changed")) {
                $('#calendarFollowUpPatient').fullCalendar( 'refetchEvents' );
            }else {

                if (view.name == 'agendaDay' || view.name == 'agendaWeek') {
                    var local = new Date(event.start);
                    local.setMinutes(event.start.getMinutes() - event.start.getTimezoneOffset());
                    var SelectedTime = event.start.getHoursTwoDigits() + ":" + event.start.getMinutesTwoDigits() + ":" + event.start.getSecondsTwoDigits();
                    var selectedDate = local.toJSON().slice(0, 10);
                    var url = 'index.php?_topRm=main&module=labsg_followUpPatient&_spAction=changeFollowUpByDrag&showHTML=0';
                    var follow_up_patient_id = event.follow_up_patient_id;
                    var follow_up_time  = SelectedTime;
                    var follow_up_date  = selectedDate;

                    $.get(url,{follow_up_patient_id: follow_up_patient_id, follow_up_time: follow_up_time, follow_up_date: follow_up_date}, function(){
                        $('#calendarFollowUpPatient').fullCalendar( 'refetchEvents' );
                    });

                }else{
                    var local = new Date(event.start);
                    local.setMinutes(event.start.getMinutes() - event.start.getTimezoneOffset());
                    var selectedDate = local.toJSON().slice(0, 10);
                    var url = 'index.php?_topRm=main&module=labsg_followUpPatient&_spAction=changeFollowUpByDrag&showHTML=0';
                    var follow_up_patient_id = event.follow_up_patient_id;
                    var follow_up_date  = selectedDate;
                    var viewName       = view.name;

                    $.get(url,{follow_up_patient_id: follow_up_patient_id, follow_up_date: follow_up_date, viewName: viewName}, function(){
                        $('#calendarFollowUpPatient').fullCalendar( 'refetchEvents' );
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
                $(element).find('.fc-event-time').append("<br/>" + event.doctor_name + "<br/>" + event.patient_name + "<br/>" + event.createAppointment + event.cancelFollowUp);
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

    $('#calendarFollowUpPatient').fullCalendar('changeView', 'agendaDay');

    cpm.labsg.followUpPatient.reloadDoctorDetails = function(){
        var url = 'index.php?module=labsg_followUpPatient&_spAction=doctorDetails&showHTML=0';
        $.get(url,  function(html){
            $('#doctorDetails').html(html);
            Util.hideProgressInd();
        });
    }

    cpm.labsg.followUpPatient.reloadNotesDetails = function(follow_up_patient_id, notes){
        var url = 'index.php?module=labsg_followUpPatient&_spAction=followUpNotes&follow_up_patient_id='+follow_up_patient_id+'&notes='+notes+'&showHTML=0';
        $.get(url,  function(html){
            $('#notesFollowupTd').html(html);
            Util.hideProgressInd();
        });
    }

    cpm.labsg.followUpPatient.reloadFollowUpListDetails = function(){
        var url = "index.php?module=labsg_followUpPatient&_spAction=followUpListDetails&showHTML=0";
        $.get(url,  function(html){
            $('#followUpListDetails').html(html);
            Util.hideProgressInd();
        });
    }

    cpm.labsg.followUpPatient.addFollowUpDetails = function(SelectedTime, selectedDate){
        var title = "Add Follow Up";
        var url   = "index.php?module=labsg_followUpPatient&_spAction=addFollowUpDetails&follow_up_date="+selectedDate+"&follow_up_time="+SelectedTime+"&showHTML=0";

        var exp = {
            url: url
           ,validate: true
           ,submitBtnText: 'Submit'
           ,cancelBtnText: 'Close'
           ,callbackOnSuccess: function(){
                var msg = 'Follow Up Created';
                Util.alert(msg, function(){
                    Util.closeAllDialogs();
                    //window.location.reload(true);
                    $('#calendarFollowUpPatient').fullCalendar( 'refetchEvents' );
                    cpm.labsg.followUpPatient.reloadDoctorDetails();
                    cpm.labsg.followUpPatient.reloadFollowUpListDetails();
                });
            }
        };
        Util.openFormInDialog.call('','portalFormFollowup', title, 460, 380, exp);
    }

    cpm.labsg.followUpPatient.showFollowUpDetails = function(){
        var title = "Follow Up Details";

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