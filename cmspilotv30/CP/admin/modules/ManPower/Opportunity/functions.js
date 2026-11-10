Util.createCPObject('cpm.manPower.opportunity');

cpm.manPower.opportunity.init = function(){

    window.onload = blink();
    function blink() {
        $('.opportunityAlertText').animate({
            opacity: '0'
        }, function(){
            $(this).animate({
                opacity: '1'
            }, blink);
        });
    }

    $('select#fld_company_id').change(function() {
        var url = 'index.php?module=manPower_candidate&_spAction=candidateByCompanyJSON&showHTML=0';
        var company_id = $(this).val();
        $.get(url, {company_id: company_id}, function (data) {
            $('#fld_candidate_id').cp_loadSelect(data);
        }, 'json');
    });

    $('.m-manPower_opportunity .candidateLink #fld_candidate_id').livequery('change', function() {
        var candidate_id = $(this).val();
        var url = 'index.php?module=manPower_opportunity&_spAction=populateCandidatePassport&showHTML=0';

        $.get(url, {candidate_id: candidate_id}, function(data) {
            $('.m-manPower_opportunity .candidateLink #fld_passport_no').html(data);
        });

        var url1 = 'index.php?module=manPower_opportunity&_spAction=populateRelatedAgent&showHTML=0';
        $.get(url1, {candidate_id: candidate_id}, function(data) {
            $('.m-manPower_opportunity .candidateLink #fld_agent_name').html(data);
        });
    });

    $('#addCandidateNew').livequery('click', function (e){
    var title = "New CandidateLink";
    var opportunity_id = $(this).attr('opportunity_id');
    e.preventDefault();

    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
        Util.closeAllDialogs();
        reloadCandidateList.reloadCandidateListobj(opportunity_id);
        }
    }
    Util.openFormInDialog.call(this, 'portalForm', title, 600, 400, expObj);
    });

    $('.deleteCandidateRecord').livequery('click', function (){
    var opportunity_id = $(this).attr('opportunity_id');
    var opportunity_candidate_id = $(this).attr('opportunity_candidate_id');
    var url ='index.php?module=manPower_opportunity&_spAction=deleteCandidateRecord&showHTML=0'
        $.get(url, {opportunity_id:opportunity_id, opportunity_candidate_id:opportunity_candidate_id}, function(html){
            reloadCandidateList.reloadCandidateListobj(opportunity_id);
        });
    });

    $('.editCandidate').livequery('click', function (e){
    var title = "Edit CandidateLink";
    var opportunity_id = $(this).attr('opportunity_id');
    e.preventDefault();

    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
        Util.closeAllDialogs();
        reloadCandidateList.reloadCandidateListobj(opportunity_id);
        }
    }
    Util.openFormInDialog.call(this, 'portalForm', title, 638, 512, expObj);
    });

    $('.addNewValue').livequery('click', function (e){
    var title = "Add New Value";
    e.preventDefault();

    //var valuelist_value = $('.addNewDropdownValueForm #fld_valuelist_value').val();
    var valuelist_name = $(this).attr('valuelist_name');

    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            Util.closeAllDialogs();
            //window.location.reload(true);
            //$(".m-manPower_opportunity select[name='valuelist_value']").val(valuelist_value);

            var url = 'index.php?module=manPower_opportunity&_spAction=valueByValuelistJSON&showHTML=0';
            $.get(url, {valuelist_name: valuelist_name}, function (data) {
                if(valuelist_name == 'projectCategory'){
                    $('#fld_category').cp_loadSelect(data);
                } else if(valuelist_name == 'callRegistryIndustry'){
                    $('#fld_industry').cp_loadSelect(data);
                } else if(valuelist_name == 'opportunityPosition'){
                    $('#fld_position').cp_loadSelect(data);
                }
            }, 'json');
        }
    }
    Util.openFormInDialog.call(this, 'portalForm', title, 400, 300, expObj);
    });

    $('select[name=position]').livequery('change', function(){
        var position = $(this).val();
        $('.m-manPower_opportunity #fld_title').val(position);
    });

    $('.convertToProjectClass').livequery('click', function (){
    var candidate_id   = $(this).attr('candidate_id');
    var opportunity_id = $(this).attr('opportunity_id');
    var project_id     = $(this).attr('project_id');
    msg = "Do you like to convert to Project?";
    projectmsg = 'Project Converted Succesfully!\n\n Please click the link Goto Project!';
            if (!confirm(msg)){
                return false;
            }
            else{
    //alert(project_id);
            var url ='index.php?module=manPower_opportunity&_spAction=convertOppToProject&showHTML=0'
                $.get(url, {opportunity_id:opportunity_id, candidate_id:candidate_id}, function(html){
                    alert(projectmsg);
                    reloadCandidateList.reloadCandidateListobj(opportunity_id);
                });
            }
    });

    $('button#convertToProject, #actBtn_convertOppToProject').click(function(e) {
        e.preventDefault();
        var msg = 'Are you sure to convert this opportunity to project?';
        Util.confirm(msg, function(){
            var opportunity_id = $('#record_id').val();
            var hasQuotingModule = $('#hasQuotingModule').val();

            var convertUrl = "index.php?_topRm=opportunity&module=manPower_opportunity&_spAction=convertOppToProject&opportunity_id=" + opportunity_id;

            if (hasQuotingModule == 0){
                document.location = convertUrl;
            } else {
                var url = 'index.php?module=manPower_opportunity&_spAction=confirmedQuoteIDJSON&showHTML=0';
                $.get(url, {opportunity_id: opportunity_id}, function (json) {
                    if(json.quote_id > 0){
                        var url = "index.php?_topRm=opportunity&module=manPower_opportunity&_spAction=convertOppToProject&opportunity_id=" + opportunity_id;
                        document.location = convertUrl;
                    } else {
                        var msg = 'The opportunity should have a confirmed quote before it is getting converetd to project';
                        Util.alert(msg)
                    }
                }, 'json');
            }
        });
    });

    $('.modal_manPower_opportunity__manPower_candidateLink .candidateDetails').livequery('click', function (e){
        var candidate_id = $(this).attr('candidate_id');
        var url = 'index.php?module=manPower_opportunity&_spAction=showCandidateDetails&candidate_id=' + candidate_id + '&showHTML=0';
        var exp = {
            url: url
        };
        Util.openDialogForLink('Detail',  900, 500, 0, exp);
});

}

var Opportunity = {
    editFromList: function(opportunity_id){
        url = "index.php?module=manPower_opportunity"   +
        "&_spAction=editFromList" +
        "&opportunity_id="   + opportunity_id
        a = window.open(url,"","height=150,width=425,scrollbars=no," +
            "resizable=yes" + ",left=" + (screen.width-400)/2 + ",top=" + (screen.height-200)/2);
    },

    duplicateOpportunity: function(topRoom){
        if (!confirm("Are you sure to duplicate this opportunity?")){
            return;
        }

        var opportunity_id = document.getElementById('record_id').value;
        var url = "index.php?_topRm=" + topRoom + "&module=manPower_opportunity&_spAction=duplicate&opportunity_id=" + opportunity_id;

        document.location = url;
    },

    setContactsComboByCompany: function(){
        var url = 'index.php?module=manPower_candidate&_spAction=contactByCompanyJSON&showHTML=0';
        var company_id = $('select#fld_company_id').val();
        $.get(url, {company_id: company_id}, function (data){
            $('#fld_contact_id').cp_loadSelect(data);
        }, 'json');
    }
}

var reloadCandidateList = {
    reloadCandidateListobj: function(opportunity_id){
         var url = 'index.php?module=manPower_opportunity&_spAction=opportunityCandidateDisplay&showHTML=0';
         //alert(opportunity_id);
        $.get(url, {opportunity_id: opportunity_id}, function(html){
            $('#candidateLinkPortal').html(html);
            Util.hideProgressInd();
         });
    }
}

$('a#sendAgentMail').livequery('click', function (e){
    var salary = $('.m-manPower_opportunity #fld_salary').val();

    if (salary != '') {
        msg = "Do you like to send email to Agents?";
        if (!confirm(msg)){
            return false;
        }
        else{
            var title = "Send Agent Email";
            e.preventDefault();

            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Emails Sent to Agents Successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        //window.location.reload(true);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 600, 500, expObj);
        }
    } else {
        msg = "Please enter all the mandatory fields and then proceed sending mail to agent.";
        if (!confirm(msg)){
            return false;
        } else {
            return false;
        }
    }
});

$('a.resendMail').livequery('click', function (e){
    var id = $(this).attr('record_id');
    alert('Hi Folks');
    /*
    var url = 'index.php?module=manPower_opportunity&_spAction=contactByCompanyJSON&showHTML=0';
    $.get(url, {candidate_id: candidate_id}, function(data) {
        $('.m-manPower_opportunity .candidateLink #fld_passport_no').html(data);
    });
    */
});

/*$('input[id=addComment]').livequery('click', function (e){
      	var n = noty({
      		text: 'Note is saved.',
      		type: 'confirm',
            dismissQueue: true,
      		layout: 'topRight',
      		theme: 'defaultTheme',
      		timeout: 8000,
          	});

}); */
