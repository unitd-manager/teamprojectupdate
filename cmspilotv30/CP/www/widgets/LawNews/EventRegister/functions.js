Util.createCPObject('cpw.lawNews.eventRegister');

cpw.lawNews.eventRegister = {
    init:function(){
        $(function(){
            $("input[name='currency']").livequery('change', function(e){
                cpw.lawNews.eventRegister.currencyChange.call(this, e);
            });
        });
    },

    currencyChange: function(e){
        e.preventDefault();
        var url = '/index.php?widget=lawNews_eventRegister&_spAction=eventItem&showHTML=0';

        var currency = $(this).val();
        var event_id = $("form input[name='event_id']").val();
        var event_item_id = $("form input[name='event_item_id']:checked").val();

        var widgetHiddenData = $("input[name^='w-event-eventItem_']").serializeArray();
        var dataArray = new Array(
              {'name': 'event_id', 'value': event_id}
             ,{'name': 'currency', 'value': currency}
             ,{'name': 'event_item_id', 'value': event_item_id}
        );

        dataArray = $.merge(dataArray, widgetHiddenData);
        $('#event-eventItem-widget').load(url,dataArray);
    }
}