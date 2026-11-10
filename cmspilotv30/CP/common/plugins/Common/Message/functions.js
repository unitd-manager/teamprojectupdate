Util.createCPObject('cpp.common.message');

cpp.common.message.init = function(){
    $(function(){
        Util.setUpAjaxFormGeneral('frmMessage', function(){
            cpp.common.message.reload();
        });
            
        $('#toggleMessages, #cancelMessage').live('click', function(){
            $('#messageForm').toggle();
        });

        $('a.editMessage').live('click', function(e) {
            var record_id = $(this).attr('record_id');
            var scopeRootAlias = $('#scopeRootAlias').val();
            var url  = scopeRootAlias + "index.php?_spAction=edit" + "&record_id=" + record_id + "&plugin=common_message&showHTML=0" ;
            extraPar = {
                validate: true
               ,url: url
               ,callbackOnSuccess: function(){
                    $('#dialog').dialog('close');
                    $('#dialog').dialog('destroy');
                    cpp.common.message.reload();
               }
            };

            Util.openFormInDialog('editMessage', 'Edit Notes', 300, 300, extraPar)
        });

        $('a.deleteMessage').live('click', function(e) {
            e.preventDefault();

            var record_id = $(this).attr('record_id');
            var contact_module = $(this).attr('contact_module');
            msg = "Are you sure you want to delete this record?\nYou cannot undo this action!";

            Util.confirm(msg, function(){
                var url = "index.php?_spAction=delete" +
                "&record_id=" + record_id + '&contact_module=' + contact_module + "&plugin=common_message&showHTML=0" ;
                $.get(url, function(){
                    cpp.common.message.reload();
                });
            });
        });

        $('#addMessage').livequery('click', function(e){
            e.preventDefault();
            $('#frmMessage').submit();
        });
        
        $('.star').livequery(function(){
            //$(this).rating();
        });
        
    });
}

cpp.common.message.reload = function(){
    var url = $('input[name=p-common-message_url]').val();
    $.get(url, function(data){
        $('.p-common-message').addClass('to-remove');
        $(data).insertAfter('.p-common-message');
        $('.p-common-message.to-remove').remove();
        $('#frmMessage').resetForm();
        $('#messageForm').hide('slow');
    });
}

$(function(){
    cpp.common.message.init();
});
