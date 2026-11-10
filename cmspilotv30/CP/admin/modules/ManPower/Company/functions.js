Util.createCPObject('cpm.manPower.company');
cpm.manPower.company.init = function(){

    $('.m-manPower_company input[name=documents]').livequery('click', function (e){

        var company_id = $(this).attr('company_id');
        var documents_id = $(this).attr('documents_id');
        companyDocuments = '.companyDocument_' + documents_id;
        if($(companyDocuments).is(':checked')){
            var documents = 1;            
        } else {
            var documents = 0;
        }

        var url = 'index.php?_topRm=admin&module=manPower_company&_spAction=companyDocumentSubmit&showHTML=0';
        
        $.get(url,{company_id: company_id, documents_id: documents_id, documents: documents}, function(html){
            if(html != ''){
                Util.alert('Document added to the Client Successfully');
            } else {
                Util.alert('Document removed from the Client Successfully');
            }
        });
    });

}

