Util.createCPObject('cpp.common.comment');

cpp.common.comment.init = function(){
    $(function(){
        Util.setUpAjaxFormGeneral('frmComment', function(json){
            cpp.common.comment.reload(json);
        });

        $('#toggleComments, #cancelComment').livequery('click', function(){
            $('#commentForm').toggle();
        });

        $('a.editComment').livequery('click', function(e) {
            var record_id = $(this).attr('record_id');
            var scopeRootAlias = $('#scopeRootAlias').val();
            var url  = scopeRootAlias + "index.php?_spAction=edit" + "&record_id=" + record_id + "&plugin=common_comment&showHTML=0" ;
            extraPar = {
                validate: true
               ,url: url
               ,callbackOnSuccess: function(){
                    $('#dialog').dialog('close');
                    $('#dialog').dialog('destroy');
                    cpp.common.comment.reload();
               }
            };

            Util.openFormInDialog('editComment', 'Edit Notes', 300, 300, extraPar)
        });

        $('a.deleteComment').livequery('click', function(e) {
            e.preventDefault();

            var record_id = $(this).attr('record_id');
            var contact_module = $(this).attr('contact_module');
            msg = "Are you sure you want to delete this record?\nYou cannot undo this action!";

            Util.confirm(msg, function(){
                var url = "index.php?_spAction=delete" +
                "&record_id=" + record_id + '&contact_module=' + contact_module + "&plugin=common_comment&showHTML=0" ;
                $.get(url, function(){
                    cpp.common.comment.reload();
                });
            });
        });

        $('#addComment').livequery('click', function(e){
            e.preventDefault();
            $('#frmComment').submit();
        });

        $('#commentsList .reportAbuse a').livequery('click', function(e){
            e.preventDefault();
            var id = $(this).attr('cid');
            var url = $('#scopeRootAlias').val() + 'index.php?_spAction=reportAbuse&plugin=common_comment&showHTML=0' ;
            $.get(url, {id:id}, function(text){
                cpp.common.comment.reload();
                Util.alert(text);
            });
        });
    });
}

cpp.common.comment.reload = function(){
    var url = $('input[name=p-common-comment_url]').val();
    $.get(url, function(data){
        $('.p-common-comment').addClass('to-remove');
        $(data).insertAfter('.p-common-comment');
        $('.p-common-comment.to-remove').remove();
        $('#frmComment').resetForm();
        $('#commentForm').hide('slow');
        Util.hideProgressInd();
    });
}

$(function(){
    cpp.common.comment.init();
});
