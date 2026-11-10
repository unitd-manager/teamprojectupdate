Util.createCPObject('cpm.core.adminTranslation');

cpm.core.adminTranslation.init = function(){
}

cpm.core.adminTranslation.enableEditFunctions = function(){
    $(function(){
        $('td.translation').dblclick(function(){
            var tdCell    = $(this);
            if ($('textarea', tdCell).length > 0){
                return;
            }
            
            $(tdCell).addClass('progress');
            var langKey   = $(this).attr('langKey');
            var rec_id    = $(this).attr('rec_id');
            var hidCell   = "prev__" + rec_id + "__" + langKey;
            var prevValue = $('#' + hidCell).val();

            $(tdCell).append("<textarea>" + prevValue + "</textarea>");
            $(tdCell).removeClass('progress');
            $('.btns', tdCell).show();
            $('.display', tdCell).hide();
        });

        $('button.saveTrans, button.cancelTrans').click(function(){
            var tdCell    = $(this).closest('td');
            var langKey   = $(tdCell).attr('langKey');
            var rec_id    = $(tdCell).attr('rec_id');
            var hidCell   = "prev__" + rec_id + "__" + langKey;
            var prevValue = $('#' + hidCell).val();
            
            if($(this).hasClass('cancelTrans')){
                $('textarea', tdCell).remove();
            } else {
                var newValue = $('textarea', tdCell).val();
                var url = 'index.php?module=core_adminTranslation&_spAction=updateValue&showHTML=0';

                $(tdCell).addClass('progress');

                $.post(url, {
                    langKey: langKey,
                    rec_id: rec_id,
                    value: newValue
                },
                function(data){
                    var displayVal = data.displayVal;
                    var newValue = data.value;
                    $('.display', tdCell).html(displayVal);
                    $('#' + hidCell).val(newValue);
                    $('textarea', tdCell).remove();
                    $(tdCell).removeClass('progress');
                }
                , 'json');
            }

            $('.btns', tdCell).hide();
            $('.display', tdCell).show();
        });

    });
}
