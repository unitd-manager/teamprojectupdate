Util.createCPObject('cpm.common.broadcast');

cpm.common.broadcast.init = function(){
    $('.smsDetailWrap').hide();
    $('.emailDetailWrap').hide();
    $('select#fld_record_type').change(function(){
        var recType = $(this).val();

        if (recType == 'SMS') {
            $('.smsDetailWrap').slideDown();
            $('.emailDetailWrap').slideUp();
            $('#fld_from_email').val('');
        } else if (recType == '') {
            $('.smsDetailWrap').slideUp();
            $('.emailDetailWrap').slideUp();
            $('#fld_from_no').val('');
            $('#fld_message').val('');
            $('#fld_from_email').val('');
        } else {
            $('.smsDetailWrap').slideUp();
            $('.emailDetailWrap').slideDown();
            $('#fld_from_no').val('');
            $('#fld_message').val('');
        }
    });
}

var Broadcast = {
    sendBroadcast: function(topRoom){
        if (!confirm("Are you sure to send this mail to all the recipients?")){
            return;
        }
    
        var broadcast_id = document.getElementById('record_id').value;
    
        if (topRoom == undefined || topRoom == ''){
            topRoom = "main";
        }
    
        url = "index.php?_topRm=" + topRoom + "&module=common_broadcast&_spAction=sendBroadcast&broadcast_id=" + broadcast_id;
        w = 700;
        h = 600;
        windowString = "height=" + h + ",width=" + w + ",scrollbars=yes," +
        "resizable=yes,left=" + (screen.width-w)/2 + ",top=" +
        (screen.height-h)/2
        wind = window.open( url , "sendTestBroadcast", windowString);
    },
    
    sendTestBroadcast: function(topRoom){
    
        if (!confirm("Are you sure to send this mail to all the test recipients?")){
            return;
        }
    
        if (topRoom == undefined || topRoom == ''){
            topRoom = "main";
        }
    
        var broadcast_id = document.getElementById('record_id').value;
    
        url = "index.php?_topRm=" + topRoom + "&module=common_broadcast&_spAction=sendTestBroadcast&broadcast_id=" + broadcast_id;
        w = 700;
        h = 600;
        windowString = "height=" + h + ",width=" + w + ",scrollbars=yes," +
        "resizable=yes,left=" + (screen.width-w)/2 + ",top=" +
        (screen.height-h)/2
        wind = window.open( url , "sendTestBroadcast", windowString);
    }
}
