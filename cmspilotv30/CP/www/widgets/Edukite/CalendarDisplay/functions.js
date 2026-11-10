Util.createCPObject('cpw.edukite.calendarDisplay');

cpw.edukite.calendarDisplay.run = function(exp){
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
                //alert (event.url);
                $.get(event.url, function(html){
                    $('.homeLeft .inner').html(html);

                    $('.summaryLink').show();

                    $('.homeLeft .inner .jqGalleriaSlider').each(function(){
                        var galId = $(this).attr('id');

                        exp = {
                             handle: galId
                            ,width: '287'
                            ,height: '200'
                            ,autoplay: ''
                            ,speed: '5'
                            ,zoom: ''
                            ,showCaption: ''
                            ,thumbnails: ''
                        }
                        cpw.media.relatedImages.run(exp);
                    });
                    Util.hideProgressInd();
                });

                return false;
            }
        }
       ,eventMouseover: function(event, jsEvent, view) {
           //alert('Event: ' + event.title);
           //cpw.edukite.calendarDisplay.showBatchDetails.call(this);
        }
    });

    cpw.edukite.calendarDisplay.reloadDailyDairy = function(){
    }
}