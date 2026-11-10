Util.createCPObject('cpm.labsg.dutyRoster');

cpm.labsg.dutyRoster = {
    init: function(){
        $("select[name='roster_type']").livequery('change', function (e){
            var roster_type = $(this).val();
            if (roster_type == 'Daily') {
                $('.WeekendsExclude').removeClass('WeekendsExcludeDisable');
            }else{
                $('.WeekendsExclude').addClass('WeekendsExcludeDisable');
            }

            if (roster_type == 'Weekly') {
                $('.WeekdaysSelect').removeClass('WeekdaysSelectDisable');
            }else{
                $('.WeekdaysSelect').addClass('WeekdaysSelectDisable');
            }
        });

        $(".previous_dutyRoster_icon").livequery('click', function (e){
            work_date = $(this).attr('prev_date');
            cpm.labsg.dutyRoster.reloadDoctorDetails(work_date);
        });

        $(".next_dutyRoster_icon").livequery('click', function (e){
            work_date = $(this).attr('next_date');
            cpm.labsg.dutyRoster.reloadDoctorDetails(work_date);
        });

        $(".today_dutyRoster_icon").livequery('click', function (e){
            work_date = $(this).attr('today');
            cpm.labsg.dutyRoster.reloadDoctorDetails(work_date);
        });


        $('#fld_employee_id').change(function(){
            var doctor_id = $(this).val();
            var events = {
                  url: 'index.php?module=labsg_dutyRoster&_spAction=eventDetails&showHTML=0',
                  type: 'POST',
                  data: {
                    doctor_id: doctor_id
                  }
            }
            $('#calendarDutyRoster').fullCalendar('removeEventSource', events);
            $('#calendarDutyRoster').fullCalendar('addEventSource', events);
        });

        $(".addTimeinOutLine").livequery('click', function (e){
            var url = 'index.php?module=labsg_dutyRoster&_spAction=addMoreWorkingTime1'
                    + '&showHTML=0';

            $.get(url, '' ,function(html){
                $('.addTimeinOutLine').after(html);
                $('.secondWorkTimeDuty').removeClass('secondWorkTimeDutyDisable');
                $('.addTimeinOutLine').addClass('secondWorkTimeDutyDisable');
                $('.addTimeinOutLine2').removeClass('secondWorkTimeDutyDisable');
            });
        });

        $(".addTimeinOutLine2").livequery('click', function (e){
            $('.secondWorkTimeDuty').remove();
            $('.secondWorkTimeDuty').addClass('secondWorkTimeDutyDisable');
            $('.addTimeinOutLine').removeClass('secondWorkTimeDutyDisable');
            $('.addTimeinOutLine2').addClass('secondWorkTimeDutyDisable');
        });

        $(".addTimeinOutLine3").livequery('click', function (e){
            var url = 'index.php?module=labsg_dutyRoster&_spAction=addMoreWorkingTime2'
                    + '&showHTML=0';

            $.get(url, '' ,function(html){
                $('.secondWorkTimeDuty').after(html);
                $('.secondWorkTimeDuty2').removeClass('secondWorkTimeDutyDisable2');
                $('.addTimeinOutLine4').removeClass('secondWorkTimeDutyDisable2');
                $('.addTimeinOutLine3').addClass('secondWorkTimeDutyDisable');
                $('.addTimeinOutLine2').addClass('secondWorkTimeDutyDisable');
            });
        });

        $(".addTimeinOutLine4").livequery('click', function (e){
            $('.secondWorkTimeDuty2').remove();
            $('.secondWorkTimeDuty2').addClass('secondWorkTimeDutyDisable2');
            $('.addTimeinOutLine4').addClass('secondWorkTimeDutyDisable2');
            $('.addTimeinOutLine3').removeClass('secondWorkTimeDutyDisable');
            $('.addTimeinOutLine2').removeClass('secondWorkTimeDutyDisable');
        });

        $("a.printDutyRosterLink").livequery('click', function (e){
            var title = "Print Duty Roster";
            var url   = "index.php?module=labsg_dutyRoster&_spAction=printDutyRosterForm&showHTML=0"; 
            var expObj = {
                url: url
               ,validate: true
               ,submitBtnText: 'Submit'
               ,cancelBtnText: 'Close'
               ,callbackOnSuccess: function(){
                    Util.showProgressInd();
                    var year        = $('#fld_duty_year').val();
                    var month       = $('#fld_duty_Month').val();
                    month           = cpm.labsg.dutyRoster.pad2(month);
                    var employee_id = $('select[name="employee_id_pdf"]').val();
                    var convertUrl = "index.php?_topRm=utils&module=labsg_dutyRoster&_spAction=printDutyRosterPdf&year=" + year + "&month=" + month + "&employee_id=" + employee_id;
                    document.location = convertUrl;
                    Util.closeAllDialogs();
                    Util.hideProgressInd();
                }
            };
            Util.openFormInDialog.call('', 'portalFormPrintDutyRoster', title, 525, 'auto', expObj);
        });

        $(".addAppointmentLinkBtn").livequery('click', function (e){
            var local = new Date();
            var currentdate = new Date();
            local.setMinutes(currentdate.getMinutes() - currentdate.getTimezoneOffset());
            var SelectedTime = currentdate.getHoursTwoDigits() + ":" + currentdate.getMinutesTwoDigits() + ":" + currentdate.getSecondsTwoDigits();
            var selectedDate = local.toJSON().slice(0, 10);
            cpm.labsg.dutyRoster.addDutyRosterDetails(SelectedTime, selectedDate);
        });

        $(".editDutyRoasterDetails").livequery('click', function (e){
            var title = "Duty Roster Edit";
            var duty_roster_id = $(this).attr('duty_roster_id');
            var url   = "index.php?module=labsg_dutyRoster&_spAction=dutyRosterEdit&duty_roster_id="+duty_roster_id+"&showHTML=0"; 
            var expObj = {
                url: url
               ,validate: true
               ,submitBtnText: 'Submit'
               ,cancelBtnText: 'Close'
               ,callbackOnSuccess: function(){
                    var msg = 'Duty Roster Updated';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        $('#calendarDutyRoster').fullCalendar( 'refetchEvents' );
                    });
                }
            };
            Util.openFormInDialog.call('', 'portalFormDutyRosterEdit', title, 525, 'auto', expObj);
        });

        $(".eventEditDetails").livequery('click', function (e){
            var title = "Duty Roster Edit";
            var duty_roster_id = $(this).attr('duty_roster_id');
            var url   = "index.php?module=labsg_dutyRoster&_spAction=dutyRosterEdit&duty_roster_id="+duty_roster_id+"&showHTML=0"; 
            var expObj = {
                url: url
               ,validate: true
               ,submitBtnText: 'Submit'
               ,cancelBtnText: 'Close'
               ,callbackOnSuccess: function(){
                    var msg = 'Duty Roster Updated';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        $('#calendarDutyRoster').fullCalendar( 'refetchEvents' );
                    });
                }
            };
            Util.openFormInDialog.call('', 'portalFormDutyRosterEdit', title, 525, 'auto', expObj);
        });
    }
}

cpm.labsg.dutyRoster.run = function(exp){
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
        //,editable: true
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
                cpm.labsg.dutyRoster.addDutyRosterDetails(SelectedTime, selectedDate);
            }else{
                //alert('Clicked on the slot: ' + date);
                var local = new Date(date);
                local.setMinutes(date.getMinutes() - date.getTimezoneOffset());
                var SelectedTime = date.getHoursTwoDigits() + ":" + date.getMinutesTwoDigits() + ":" + date.getSecondsTwoDigits();
                //var SelectedTime = date.formatAMPM();
                var selectedDate = local.toJSON().slice(0, 10);
                cpm.labsg.dutyRoster.addDutyRosterDetails(SelectedTime, selectedDate);
            }

            //alert('Coordinates: ' + jsEvent.pageX + ',' + jsEvent.pageY);

            //alert('Current view: ' + view.name);

            // change the day's background color just for fun
            //$(this).css('background-color', 'red');

        }
       ,eventClick: function(event, jsEvent, view) {
            // To avoid navigating to the url after the modal window display.
            if (event.url) {
                cpm.labsg.dutyRoster.dutyRosterEditForm.call(this);
                return false;
            }
        }
       ,eventMouseover: function(event, jsEvent, view) {
           //alert('Event: ' + event.title);
           //cpw.aceIms.calendarDisplay.showBatchDetails.call(this);
        }
       ,eventDrop: function(event, delta, revertFunc, jsEvent, ui, view) {
            var view = $('#calendarAppointment').fullCalendar('getView');
            
            if (!confirm("Are you sure about this change?")) {
                $('#' + handle).fullCalendar( 'refetchEvents' );
            }else {

                if (view.name == 'agendaDay' || view.name == 'agendaWeek') {
                    var local = new Date(event.start);
                    local.setMinutes(event.start.getMinutes() - event.start.getTimezoneOffset());
                    var SelectedTime = event.start.getHoursTwoDigits() + ":" + event.start.getMinutesTwoDigits() + ":" + event.start.getSecondsTwoDigits();
                    var selectedDate = local.toJSON().slice(0, 10);
                    var url = 'index.php?_topRm=main&module=labsg_appointment&_spAction=changeAppointmentByDrag&showHTML=0';
                    var duty_roster_id = event.duty_roster_id;
                    var check_up_time  = SelectedTime;
                    var check_up_date  = selectedDate;
                    
                    $.get(url,{duty_roster_id: duty_roster_id, check_up_time: check_up_time, check_up_date: check_up_date}, function(){
                        $('#' + handle).fullCalendar( 'refetchEvents' );
                    });

                }else{
                    var local = new Date(event.start);
                    local.setMinutes(event.start.getMinutes() - event.start.getTimezoneOffset());
                    var selectedDate = local.toJSON().slice(0, 10);
                    var url = 'index.php?_topRm=main&module=labsg_appointment&_spAction=changeAppointmentByDrag&showHTML=0';
                    var duty_roster_id = event.duty_roster_id;
                    var check_up_date  = selectedDate;
                    var viewName       = view.name;

                    $.get(url,{duty_roster_id: duty_roster_id, check_up_date: check_up_date, viewName: viewName}, function(){
                        $('#' + handle).fullCalendar( 'refetchEvents' );
                    });
                }

            }

        }
        ,eventRender: function(event, element, view){ 
            $(element).find('.fc-event-time').append("<br/>" + event.doctor_name);
           
        }
        ,eventAfterRender: function(event, element, view) {
            if (view.name == 'agendaDay'){
                $(element).css('width','200px');
            }
        }
    });

    $('#' + handle).fullCalendar('changeView', 'agendaDay');
    
    cpm.labsg.dutyRoster.dutyRosterEditForm = function(){
        var title = "Duty Roster Edit";
        var work_date = '';
        
        var expObj = {
            validate: true
           ,submitBtnText: 'Submit'
           ,cancelBtnText: 'Close'
           ,callbackOnSuccess: function(){
                var msg = 'Duty Roster Updated';
                Util.alert(msg, function(){
                    Util.closeAllDialogs();
                    $('#' + handle).fullCalendar( 'refetchEvents' );
                    cpm.labsg.dutyRoster.reloadDoctorDetails(work_date);
                });
            }
        };
        Util.openFormInDialog.call(this, 'portalFormDutyRosterEdit', title, 525, 'auto', expObj);        
    },

    cpm.labsg.dutyRoster.pad2 = function(number) {
        return (number < 10 ? '0' : '') + number
    },

    cpm.labsg.dutyRoster.addDutyRosterDetails = function(SelectedTime, selectedDate){
        var title = "Add Duty Roster";
        var url   = "index.php?module=labsg_dutyRoster&_spAction=addDutyRosterDetails&work_date="+selectedDate+"&work_from_time="+SelectedTime+"&showHTML=0"; 
        var work_date = '';
        var exp = {
            url: url
           ,validate: true
           ,submitBtnText: 'Submit'
           ,cancelBtnText: 'Close'
           ,callbackOnSuccess: function(){
                var msg = 'Duty Roster Created';
                Util.alert(msg, function(){
                    Util.closeAllDialogs();
                    $('#' + handle).fullCalendar( 'refetchEvents' );
                    cpm.labsg.dutyRoster.reloadDoctorDetails(work_date);
                });
            }
        };
        Util.openFormInDialog.call('','portalFormDutyRoster', title, 656, 'auto', exp);
    },

    cpm.labsg.dutyRoster.reloadDoctorDetails = function(work_date){
        var url = 'index.php?module=labsg_dutyRoster&_spAction=doctorDetails&showHTML=0';
        $.get(url, {work_date:work_date} ,function(html){
            $('#doctorDetails').html(html);
            Util.hideProgressInd();
        });
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