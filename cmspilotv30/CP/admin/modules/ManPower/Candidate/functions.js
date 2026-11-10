Util.createCPObject('cpm.manPower.candidate');

cpm.manPower.candidate = { 
    init: function(){
    //show hide education details
    $('#btnAddEducation').livequery('click', function(){
        $('#btnAddEducation').css('display', 'none');
        $('#educationDetails').show();
        $('#btnAddEducation1').show();
    });
    $('#btnAddEducation1').livequery('click', function(){
        $('#btnAddEducation1').css('display', 'none');
        $('#educationDetails1').show();
        $('#btnAddEducation2').show();
    });
    $('#btnAddEducation2').livequery('click', function(){
        $('#btnAddEducation2').css('display', 'none');
        $('#educationDetails2').show();
        $('#btnAddEducation3').show();
    });
    $('#btnAddEducation3').livequery('click', function(){
        $('#btnAddEducation3').css('display', 'none');
        $('#educationDetails3').show();
    });

    //show hide employment details
    $('#btnAddEmployment').livequery('click', function(){
        $('#btnAddEmployment').css('display', 'none');
        $('#employmentDetails').show();
        $('#btnAddEmployment1').show();
    });
    $('#btnAddEmployment1').livequery('click', function(){
        $('#btnAddEmployment1').css('display', 'none');
        $('#employmentDetails1').show();
        $('#btnAddEmployment2').show();
    });
    $('#btnAddEmployment2').livequery('click', function(){
        $('#btnAddEmployment2').css('display', 'none');
        $('#employmentDetails2').show();
        $('#btnAddEmployment3').show();
    });
    $('#btnAddEmployment3').livequery('click', function(){
        $('#btnAddEmployment3').css('display', 'none');
        $('#employmentDetails3').show();
    });

    $('.m-manPower_candidate input[name=stayed_in]').livequery('click', function (e){
        stayed_in = $(this).val();
        if(stayed_in == 1){
            $('.m-manPower_candidate .lengthOfStay').show();
        } else {
            $('.m-manPower_candidate .lengthOfStay').hide();
        }
    });

    $('.m-manPower_candidate input[name=visa_by_another_country]').livequery('click', function (e){
        visa_by_another_country = $(this).val();
        if(visa_by_another_country == 1){
            $('.m-manPower_candidate .foreignerIssued').show();
        } else {
            $('.m-manPower_candidate .foreignerIssued').hide();
        }
    });

    $('.m-manPower_candidate input[name=documents]').livequery('click', function (e){

        var candidate_id = $(this).attr('candidate_id');
        var documents_id = $(this).attr('documents_id');
        classDocuments = '.candidateDocument_' + documents_id;
        if($(classDocuments).is(':checked')){
            var documents = 1;            
        } else {
            var documents = 0;
        }

        var url = 'index.php?_topRm=admin&module=manPower_candidate&_spAction=candidateDocumentSubmit&showHTML=0';
        
        $.get(url,{candidate_id: candidate_id, documents_id: documents_id, documents: documents}, function(html){
            if(html != ''){
                Util.alert('Document added to the Candidate Successfully');
            } else {
                Util.alert('Document removed from the Candidate Successfully');
            }
        });
    });
        
    $('.viewComment').livequery('click', function(e){
        var title = "";
        var candidate_id = $(this).attr('candidate_id');
        e.preventDefault();

        var exp = {
            'beforeCloseFn': function(){
            }
        }
        Util.openDialogForLink.call(this, title, 800, 475, true, exp);
    });

    $('.candidateComment').livequery('click', function (e){
        msg = "Do you like to add comment?";
        if (!confirm(msg)){
            return false;
        }
        else{
            var title = "Add Comments";
            e.preventDefault();
            
            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Comments Added Successfully';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                    });
                }
            }
            Util.openFormInDialog.call(this, 'addCommentForm', title, 500, 400, expObj);        
        }
    });
    
    $('.m-manPower_candidate #confirmToStaff').livequery('click', function (e){
        var title = "Email to Staff";
        e.preventDefault();
        var expObj = {
            validate: true
           ,callbackOnSuccess: function(){
                var msg = 'Email sent successfully';
                Util.alert(msg, function(){
                    Util.closeAllDialogs();
                    window.location.reload(true);
                });
            }
        }
        Util.openFormInDialog.call(this, 'portalForm', title, 500, 300, expObj);        
    });
    
    $('#openCandidatePdf').livequery('click', function(e) {
        var actBtn = $("select[name='actionBtn']").val();
        var candidate_id = $(this).attr('candidate_id');
        var url = 'index.php?_topRm=opportunity&module=manPower_candidate&_spAction=printPdfByDropDown&showHTML=0' + actBtn + candidate_id;
        
        $.get(url,{actBtn: actBtn, candidate_id: candidate_id}, function(html){
            if(html != ''){
                Util.alert('Open Pdf');
            }
        });
    });        

    $('.m-manPower_candidate select[name=education_specialisation1]').livequery('click', function (e){
        education_specialisation1 = $(this).val();
        if(education_specialisation1 == 'NONE OF THE ABOVE'){
            $('.m-manPower_candidate .specify').show();
        } else {
            $('.m-manPower_candidate .specify').hide();
        }
    });

    $('.m-manPower_candidate select[name=education_specialisation2]').livequery('click', function (e){
        education_specialisation2 = $(this).val();
        if(education_specialisation2 == 'NONE OF THE ABOVE'){
            $('.m-manPower_candidate .specify').show();
        } else {
            $('.m-manPower_candidate .specify').hide();
        }
    });

    $('.m-manPower_candidate select[name=education_specialisation3]').livequery('click', function (e){
        education_specialisation3 = $(this).val();
        if(education_specialisation3 == 'NONE OF THE ABOVE'){
            $('.m-manPower_candidate .specify').show();
        } else {
            $('.m-manPower_candidate .specify').hide();
        }
    });

    $('.m-manPower_candidate select[name=education_specialisation4]').livequery('click', function (e){
        education_specialisation4 = $(this).val();
        if(education_specialisation4 == 'NONE OF THE ABOVE'){
            $('.m-manPower_candidate .specify').show();
        } else {
            $('.m-manPower_candidate .specify').hide();
        }
    });

    $('.addNewValue').livequery('click', function (e){
    var title = "Add New Value";
    e.preventDefault();

    var valuelist_name = $(this).attr('valuelist_name');

    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            Util.closeAllDialogs();
            var url = 'index.php?module=manPower_candidate&_spAction=valueByValuelistJSON&showHTML=0';
            $.get(url, {valuelist_name: valuelist_name}, function (data) {
                $('#fld_position').cp_loadSelect(data);
            }, 'json');
        }
    }
    Util.openFormInDialog.call(this, 'portalForm', title, 400, 300, expObj);
    });

    $('.positionCheckBox input[type=checkbox]').livequery('click',function(){
        var position   = $(this).val();
        var cboxObj   = $(this);
        var cbObj = $('input[type=checkbox]');
        var checked = cbObj.is(":checked") ? true : false;
        var url = 'index.php?module=manPower_candidate&_spAction=addPositionCandidate&showHTML=0';
        var urldelete = 'index.php?module=manPower_candidate&_spAction=deletePositionCandidate&showHTML=0';
        var candidate_id = $('#candidate_id').val();

        if (!cboxObj.attr('checked')){
            $.get(urldelete,{position:position,candidate_id:candidate_id}, function(){
                Util.alert('Position removed from the candidate!');
            });
        }
        else{
            $.get(url,{position:position,candidate_id:candidate_id}, function(){
                Util.alert('Position added to the candidate!');
            });
        }
    });

    $('.m-manPower_candidate select[name=education_specialisation5]').livequery('click', function (e){
        education_specialisation5 = $(this).val();
        if(education_specialisation5 == 'NONE OF THE ABOVE'){
            $('.m-manPower_candidate .specify').show();
        } else {
            $('.m-manPower_candidate .specify').hide();
        }
    });
}
}
    //cpm.manPower.candidate.setSearchForm();
    
//},

   /* setSearchForm: function(){
        $('table.search input.button').livequery('click', function(e) {
            alert(12345);
            e.preventDefault();
            Util.showProgressInd();
            var allVars = $('table.search').serializeAnything();
            var url = 'index.php?_topRm=opportunity&module=manPower_candidate&_spAction=printPdfByDropDown&showHTML=0' + allVars;
            $.ajax({
                type: "GET",
                url: url,
                dataType: "html",
                success: function(html) {
                    Util.hideProgressInd();
                }
            });
        });        
    } */
//}