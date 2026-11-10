Util.createCPObject('cpm.manPower.agent');
cpm.manPower.agent.init = function(){

    $('.m-manPower_agent input[name=country]').livequery('click', function (e){

        var agent_id = $(this).attr('agent_id');
        var candidate_country_id = $(this).attr('candidate_country_id');
        classCountry = '.agentCountry_' + candidate_country_id;

        if($(classCountry).is(':checked')){
            var country = 1;            
        } else {
            var country = 0;
        }

        var url = 'index.php?_topRm=admin&module=manPower_agent&_spAction=candidateCountrySubmit&showHTML=0';
        
        Util.showProgressInd();
        $.get(url,{agent_id: agent_id, candidate_country_id: candidate_country_id, country: country}, function(html){
            if(html == 'Yes'){
                Util.alert('Country added to the Agent Successfully');
            } else {
                Util.alert('Country removed from the Agent Successfully');
            }
        });
        Util.hideProgressInd();
    });

    $('.m-manPower_agent input[name=candidatePass]').livequery('click', function (e){

        var agent_id = $(this).attr('agent_id');
        var candidate_pass_id = $(this).attr('candidate_pass_id');
        classCountry = '.agentPass_' + candidate_pass_id;

        if($(classCountry).is(':checked')){
            var country = 1;            
        } else {
            var country = 0;
        }

        var url = 'index.php?_topRm=admin&module=manPower_agent&_spAction=candidatePassSubmit&showHTML=0';
        
        Util.showProgressInd();
        $.get(url,{agent_id: agent_id, candidate_pass_id: candidate_pass_id, country: country}, function(html){
            if(html == 'Yes'){
                Util.alert('Candidate Pass added to the Agent Successfully');
            } else {
                Util.alert('Candidate Pass removed from the Agent Successfully');
            }
        });
        Util.hideProgressInd();
    });

    $('.m-manPower_agent input[name=documents]').livequery('click', function (e){

        var agent_id = $(this).attr('agent_id');
        var documents_id = $(this).attr('documents_id');
        classDocuments = '.agentDocument_' + documents_id;
        if($(classDocuments).is(':checked')){
            var documents = 1;            
        } else {
            var documents = 0;
        }

        Util.showProgressInd();
        var url = 'index.php?_topRm=admin&module=manPower_agent&_spAction=agentDocumentSubmit&showHTML=0';
        
        $.get(url,{agent_id: agent_id, documents_id: documents_id, documents: documents}, function(html){
            if(html != ''){
                Util.alert('Document added to the Agent Successfully');
            } else {
                Util.alert('Document removed from the Agent Successfully');
            }
        });
        Util.hideProgressInd();
    });

}

