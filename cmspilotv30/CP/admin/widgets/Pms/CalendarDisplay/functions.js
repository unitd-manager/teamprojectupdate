Util.createCPObject('cpw.pms.calendarDisplay');

cpw.pms.calendarDisplay.run = function(exp){
    var handle = exp.handle;
    var eventAction = exp.eventAction;
    var headerObj = exp.headerObj ? exp.headerObj : null;
    var timeFormatObj = exp.timeFormatObj;
    var minTime = exp.minTime;
    
    $('#' + handle).fullCalendar({
         eventSources: [eventAction]
        ,header: headerObj
        ,timeFormat:timeFormatObj 
        ,minTime : minTime
        ,dayClick: function(date, allDay, jsEvent, view) {

            if (allDay) {
                //alert('Clicked on the entire day: ' + date);
            }else{
                //alert('Clicked on the slot: ' + date);
            }

            //alert('Coordinates: ' + jsEvent.pageX + ',' + jsEvent.pageY);

            //alert('Current view: ' + view.name);

            // change the day's background color just for fun
            //$(this).css('background-color', 'red');

        }
       ,eventClick: function(event, jsEvent, view) {
            // To avoid navigating to the url after the modal window display.
            if (event.url) {
                cpw.pms.calendarDisplay.showBatchDetails.call(this);
                return false;
            }
        }
       ,eventMouseover: function(event, jsEvent, view) {
           //alert('Event: ' + event.title);
           //cpw.pms.calendarDisplay.showBatchDetails.call(this);
        }
    });
    
    cpw.pms.calendarDisplay.showBatchDetails = function(){
        var title = "Batch Details";

        var expObj = {
            validate: false
           ,submitBtnText: ''
           ,cancelBtnText: 'Close'
        }
        Util.openFormInDialog.call(this, 'portalForm', title, 625, 250, expObj);        
        //alert (123);
    }
}