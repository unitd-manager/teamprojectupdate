Util.createCPObject('cpm.tradingsg.enquiry');

cpm.tradingsg.enquiry = {
    init: function(){
        $('#raiseQuote').livequery('click', function(){
            msg = "Do you like to Raise Quote?";

            if (!confirm(msg)){
                return false;
            }
            else{
                var enquiry_id = $(this).attr('enquiry_id');
                var url = 'index.php?module=tradingsg_enquiry&_spAction=raiseQuote&showHTML=0&enquiry_id=' + enquiry_id;
                document.location = url;
                /*$.get(url, {enquiry_id: enquiry_id}, function(html){
                    //window.location.reload(true);
                });*/
            }
        });
    }
}