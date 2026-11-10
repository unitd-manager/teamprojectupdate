Util.createCPObject('cpm.pms.batch');
cpm.pms.batch.init = function(){
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
                Links.reloadPortalRecords('pms_batch#pms_contactLink', 'pms_batch');
            });
        }
    }
    Util.openFormInDialog.call(this, 'portalForm', title, 500, 500, expObj);        
});