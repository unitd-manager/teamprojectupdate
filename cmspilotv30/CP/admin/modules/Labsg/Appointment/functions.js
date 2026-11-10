Util.createCPObject('cpm.labsg.appointment');

cpm.labsg.appointment = {
    init: function(){
        $("input[name='patient_name']")
        .livequery(cpm.labsg.appointment.patientName);

        $('.queueNoTable').livequery('click', function (e){
        	$('.queueNoTable').removeClass('divtoBlink');
        });

        $('#fld_employee_id').change(function(){
            var doctor_id = $(this).val();
            var events = {
                  url: 'index.php?module=labsg_appointment&_spAction=eventDetails&showHTML=0',
                  type: 'POST',
                  data: {
                    doctor_id: doctor_id
                  }
            }
            $('#calendarAppointment').fullCalendar('removeEventSource', events);
            $('#calendarAppointment').fullCalendar('addEventSource', events);
        });

        $('#fld_employee_id_visit').change(function(){
            var employee_id = $(this).val();
            Util.showProgressInd();
            cpm.labsg.appointment.patientNameFilter(employee_id);
        });

        $("select[name='appointment_employee_id']").livequery('change', function (e){
            var employee_id = $(this).val();
            var appointment_id = $('#appointment_id_appoint').val();

            var url = "index.php?module=labsg_appointment&_spAction=updateAppointmentEventDetails&showHTML=0";
            Util.showProgressInd();
            $.get(url,{employee_id:employee_id, appointment_id:appointment_id}, function(html){
                $('#appointmentDetailsEvent').html(html);
                $('#calendarAppointment').fullCalendar( 'refetchEvents' );
                Util.hideProgressInd();
            });
        });

        $('.notesEditLink').livequery('click', function (e){
            $('.notesAppointment').slideToggle("notesAppointmentdisable");
            $('.notesAppointmentDefault').slideToggle("notesAppointmentdisable");
        });

        $('.appointmentNotesUpdate').livequery('click', function (e){
            var appointment_id = $(this).attr('appointment_id');
            var notes = $('#fld_description').val();

            var url = 'index.php?_topRm=main&module=labsg_appointment&_spAction=updateAppointmentNotes&showHTML=0';
            Util.showProgressInd();

            $.get(url,{appointment_id: appointment_id, notes:notes}, function(html){
                cpm.labsg.appointment.reloadNotesDetails(appointment_id, notes);
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

        $('.createVisit').livequery('click', function (e){
            var url = 'index.php?module=labsg_appointment&_spAction=createVisitRecord&showHTML=0';
            var dr_required    = $(this).attr('dr_required');
            var appointment_id = $(this).attr('appointment_id');

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
                $('#calendarAppointment').fullCalendar( 'refetchEvents' );
                cpm.labsg.appointment.reloadAppointmentListDetails();
                //cpm.labsg.appointment.reloadQueueno();
                setInterval(function () {
				    $(".divtoBlink").css("background-color", function () {
				        this.switch = !this.switch
				        return this.switch ? "#FFC000" : ""
				    });
				}, 1000)
            });
        });

        $('.evenDetails').livequery('click', function (e){
            var title = "Appointment Details";
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                Util.closeAllDialogs();
                }
            }
             Util.openDialogForLink.call(this, title, 'auto', 'auto', expObj);
        });

        $('.cancelPatientVisit').livequery('click', function (e){
            msg = "Do you like to cancel the patient visit?";
            if (!confirm(msg)){
                return false;
            }
            else {
                var url = 'index.php?_topRm=main&module=labsg_appointment&_spAction=cancelVisitRecord&showHTML=0';
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
                    cpm.labsg.appointment.reloadAppointmentListDetails();
                    //cpm.labsg.appointment.reloadQueueno();
                });
            }
        });

        $('.cancelAppointment').livequery('click', function (e){
            msg = "Do you like to cancel the appointment?";
            if (!confirm(msg)){
                return false;
            }
            else {
                var url = 'index.php?_topRm=main&module=labsg_appointment&_spAction=cancelAppointmentRecord&showHTML=0';
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
                    cpm.labsg.appointment.reloadAppointmentListDetails();
                    //cpm.labsg.appointment.reloadQueueno();
                });
            }
        });

    },

    //Auto select patient details
    patientName: function() {
        var titleObj = this;
        $(titleObj).autocomplete({
             source : 'index.php?module=labsg_appointment&_spAction=searchPatientDetails&showHTML=0'
            ,minLength : 2
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

    updateAppointmentDetails: function(appointment_id){
        var title = "";
        var url   = "index.php?module=labsg_appointment&_spAction=updateAppointmentDetails&appointment_id="+appointment_id+"&showHTML=0";

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

                var urlVisit = 'index.php?module=labsg_appointment&_spAction=createVisitRecord&showHTML=0';
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
                    $('#calendarAppointment').fullCalendar( 'refetchEvents' );
                    cpm.labsg.appointment.reloadAppointmentListDetails();
                    //cpm.labsg.appointment.reloadQueueno();
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
        var url = "index.php?module=labsg_appointment&_spAction=appointmentListDetails&showHTML=0";
        $.get(url,{employee_id:employee_id}, function(html){
            $('#appointmentListDetails').html(html);
            Util.hideProgressInd();
        });
    },
}

cpm.labsg.appointment.run = function(exp){
    var handle = exp.handle;
    var eventAction = exp.eventAction;
    var headerObj = exp.headerObj ? exp.headerObj : null;
    var timeFormatObj = exp.timeFormatObj;
    var minTime = exp.minTime;
    var maxTime = exp.maxTime;

    $('#' + handle).fullCalendar({
         eventSources: [eventAction]
        ,header: headerObj
        ,timeFormat:timeFormatObj
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
                cpm.labsg.appointment.addAppointmentDetails(SelectedTime, selectedDate);
            }else{
                //alert('Clicked on the slot: ' + date);
                var local = new Date(date);
                local.setMinutes(date.getMinutes() - date.getTimezoneOffset());
                var SelectedTime = date.getHoursTwoDigits() + ":" + date.getMinutesTwoDigits() + ":" + date.getSecondsTwoDigits();
                //var SelectedTime = date.formatAMPM();
                var selectedDate = local.toJSON().slice(0, 10);
                cpm.labsg.appointment.addAppointmentDetails(SelectedTime, selectedDate);
            }

            //alert('Coordinates: ' + jsEvent.pageX + ',' + jsEvent.pageY);

            //alert('Current view: ' + view.name);

            // change the day's background color just for fun
            //$(this).css('background-color', 'red');

        }
       ,eventClick: function(event, jsEvent, view) {
            // To avoid navigating to the url after the modal window display.
            if (event.url) {
                cpm.labsg.appointment.showAppointmentDetails.call(this);
                return false;
            }
        }
       ,eventMouseover: function(event, jsEvent, view) {
           //alert('Event: ' + event.title);
           //cpw.aceIms.calendarDisplay.showBatchDetails.call(this);
        }
       ,eventDrop: function(event, delta, revertFunc, jsEvent, ui, view) {
            var view = $('#calendarAppointment').fullCalendar('getView');

            if (!confirm("Please note for this action the appointment time will be changed")) {
                $('#calendarAppointment').fullCalendar( 'refetchEvents' );
            }else {

                if (view.name == 'agendaDay' || view.name == 'agendaWeek') {
                    var local = new Date(event.start);
                    local.setMinutes(event.start.getMinutes() - event.start.getTimezoneOffset());
                    var SelectedTime = event.start.getHoursTwoDigits() + ":" + event.start.getMinutesTwoDigits() + ":" + event.start.getSecondsTwoDigits();
                    var selectedDate = local.toJSON().slice(0, 10);
                    var url = 'index.php?_topRm=main&module=labsg_appointment&_spAction=changeAppointmentByDrag&showHTML=0';
                    var appointment_id = event.appointment_id;
                    var check_up_time  = SelectedTime;
                    var check_up_date  = selectedDate;

                    $.get(url,{appointment_id: appointment_id, check_up_time: check_up_time, check_up_date: check_up_date}, function(){
                        $('#calendarAppointment').fullCalendar( 'refetchEvents' );
                    });

                }else{
                    var local = new Date(event.start);
                    local.setMinutes(event.start.getMinutes() - event.start.getTimezoneOffset());
                    var selectedDate = local.toJSON().slice(0, 10);
                    var url = 'index.php?_topRm=main&module=labsg_appointment&_spAction=changeAppointmentByDrag&showHTML=0';
                    var appointment_id = event.appointment_id;
                    var check_up_date  = selectedDate;
                    var viewName       = view.name;

                    $.get(url,{appointment_id: appointment_id, check_up_date: check_up_date, viewName: viewName}, function(){
                        $('#calendarAppointment').fullCalendar( 'refetchEvents' );
                    });
                }

            }

        }
        ,eventRender: function(event, element, view){
            $(element).find('.fc-event-time').append("<br/>" + event.patient_name + "<br/>" + event.createVisit + event.cancelAppointment);
        }
        ,eventAfterRender: function(event, element, view) {
            if (view.name == 'agendaDay'){
                $(element).css('width','135px');
                $(element).css('height','70px');
            }
        }
    });

    $('#calendarAppointment').fullCalendar('changeView', 'agendaDay');

    cpm.labsg.appointment.reloadNotesDetails = function(appointment_id, notes){
        var url = 'index.php?module=labsg_appointment&_spAction=appointmentNotes&appointment_id='+appointment_id+'&notes='+notes+'&showHTML=0';
        $.get(url,  function(html){
            $('#notesAppointmentTd').html(html);
            Util.hideProgressInd();
        });
    }

    cpm.labsg.appointment.reloadAppointmentListDetails = function(){
        var url = "index.php?module=labsg_appointment&_spAction=appointmentListDetails&showHTML=0";
        $.get(url,  function(html){
            $('#appointmentListDetails').html(html);
            Util.hideProgressInd();
        });
    }

    cpm.labsg.appointment.reloadAppointmentDetails = function(){
        var url = 'index.php?module=labsg_appointment&_spAction=appointmentDetails&showHTML=0';
        $.get(url,  function(html){
            $('.appointmentvisitScroll').html(html);
        });
    }

    cpm.labsg.appointment.addAppointmentDetails = function(SelectedTime, selectedDate){
        var title = "Add Appointment";
        var url   = "index.php?module=labsg_appointment&_spAction=addAppointmentDetails&check_up_date="+selectedDate+"&check_up_time="+SelectedTime+"&showHTML=0";

        var exp = {
            url: url
           ,validate: true
           ,submitBtnText: 'Submit'
           ,cancelBtnText: 'Close'
           ,callbackOnSuccess: function(){
                var msg = 'Appointment Created';
                Util.alert(msg, function(){
                    Util.closeAllDialogs();
                    //window.location.reload(true);
                    $('#calendarAppointment').fullCalendar( 'refetchEvents' );
                    cpm.labsg.appointment.reloadAppointmentListDetails();
                    //cpm.labsg.appointment.reloadQueueno();
                });
            }
        };
        Util.openFormInDialog.call('','portalFormAppointment', title, 545, 386, exp);
    }

    cpm.labsg.appointment.reloadQueueno = function(){
        var url = 'index.php?_theme=matrix&_spAction=patientQueueNo&showHTML=0';
        $.get(url,  function(html){
            $('.queueNumberDisplay').html(html);
        });
    }

    cpm.labsg.appointment.showAppointmentDetails = function(){
        var title = "Appointment Details";

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