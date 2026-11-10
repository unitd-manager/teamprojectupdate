Util.createCPObject('cpm.agileIms.batch');
cpm.agileIms.batch.init = function(){
}

$("#bulkActionEvaluate").livequery('click', function (e){
    var title = "Bulk Action";

    e.preventDefault();
    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            var msg = 'Updated successfully';
            Util.alert(msg, function(){
                Util.closeAllDialogs();
                Links.reloadPortalRecords('agileIms_batch#agileIms_contactLink', 'agileIms_batch');
            });
        }
    }
    Util.openFormInDialog.call(this, 'portalForm', title, 500, 500, expObj);        
});