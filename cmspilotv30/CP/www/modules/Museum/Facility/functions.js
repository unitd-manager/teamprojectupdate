Util.createCPObject('cpm.museum.facility');

cpm.museum.facility = {
    init: function(){
        $("a.open").livequery('click', function (e){
            var title = "Booking Form";

            e.preventDefault();
            var expObj = {
                validate: true,
                callbackOnSuccess: function(){
                    Util.closeAllDialogs();
                    $('#calendar').fullCalendar('refetchEvents');
                    Util.alert('Booking successfully submitted.');
                }
            }
            Util.openFormInDialog.call(this, 'bookingForm', title, 600, 500, expObj);
        });
    },
    
    setUpBookingCalendar: function(facility_id){
        var url = "/index.php?widget=museum_booking&_spAction=bookingJSON&showHTML=0&facility_id=" + facility_id;
        $('#calendar').fullCalendar({
                header: {
                    left: 'prev,next',
                    center: 'title',
                    right: 'month,agendaWeek'
                }
                ,editable: false
                ,defaultView: 'agendaWeek'
                ,slotMinutes: 30    
                ,allDaySlot: false
                ,firstHour: 9
                ,minTime: 9
                ,maxTime: 20
                ,axisFormat: "HH:mm"
                ,events: url
        });
    }
}


