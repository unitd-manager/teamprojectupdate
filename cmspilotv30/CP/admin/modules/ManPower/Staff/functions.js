Util.createCPObject('cpm.manPower.staff');
cpm.manPower.staff.init = function(){
    $('.m-manPower_staff input[name=documents]').livequery('click', function (e){

        var staff_id = $(this).attr('staff_id');
        var documents_id = $(this).attr('documents_id');
        classDocuments = '.staffDocument_' + documents_id;
        if($(classDocuments).is(':checked')){
            var documents = 1;            
        } else {
            var documents = 0;
        }

        var url = 'index.php?_topRm=admin&module=manPower_staff&_spAction=staffDocumentSubmit&showHTML=0';
        
        $.get(url,{staff_id: staff_id, documents_id: documents_id, documents: documents}, function(html){
            if(html != ''){
                Util.alert('Document added to the Staff Successfully');
            } else {
                Util.alert('Document removed from the Staff Successfully');
            }
        });
    });

}

