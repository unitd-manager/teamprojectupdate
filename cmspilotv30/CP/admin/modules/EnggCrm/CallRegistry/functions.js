Util.createCPObject('cpm.enggCrm.callRegistry');

cpm.enggCrm.callRegistry.init = function(){
/*
    $('select#fld_company_id').change(function() {
        var company_id = $(this).val();
        
        var url = 'index.php?module=enggCrm_contact&_spAction=contactByCompanyJSON&showHTML=0';
        $.get(url, {company_id: company_id}, function (data) {
            $('#fld_contact_id').cp_loadSelect(data);
        }, 'json');
        
        var faxObj = $('.m-enggCrm_callRegistry #fld_fax');
        var mobileObj = $('.m-enggCrm_callRegistry #fld_mobile');
        var emailObj = $('.m-enggCrm_callRegistry #fld_email');
        var industryObj = $('.m-enggCrm_callRegistry #fld_industry');
        var companyAddressObj = $('.m-enggCrm_callRegistry #fld_company_address');
        
        var urlCompany = 'index.php?module=enggCrm_callRegistry&_spAction=companyDetailsJSON&showHTML=0';
        $.get(urlCompany, {company_id: company_id}, function (json) {
            faxObj.html(json.fax);
            mobileObj.html(json.mobile);
            emailObj.html(json.email);
            industryObj.html(json.industry);
            companyAddressObj.html(json.companyAddress);
        }, 'json');   
    });

    $('#frmEdit select#fld_company_id').livequery('change', function(){
       Util.loadCategoryDropdown.call(this);
    });

    $('#frmEdit select#fld_candidate_id').livequery('change', function(){
       Util.loadSubCategoryDropdown.call(this);
    });
    */
    $('select#fld_category').change(function() {
        var category = $(this).val();

        var url = 'index.php?module=enggCrm_callRegistry&_spAction=statusByCategoryJSON&showHTML=0';
        $.get(url, {category: category}, function (data) {
            $('#fld_status').cp_loadSelect(data);
        }, 'json');        
    });

    /* Create Record in Client */
    $('.m-enggCrm_callRegistry #createClientRec').livequery('click', function (e){

	      var company_name = $("input[name='company_name']").val();
	      var contact_name = $("input[name='contact_name']").val();
	      var phone = $("input[name='phone']").val();
	      var email = $("input[name='email']").val();

        
        if (company_name != '') {
            msg = "Do you like to create a record in Client?";
            if (!confirm(msg)){
                return false;
            }
            else{
                Util.showProgressInd();
                var call_registry_id = $(this).attr('call_registry_id');
                var url = 'index.php?module=enggCrm_callRegistry&_spAction=createClientRec&showHTML=0';

			          $.get(url, {company_name: company_name, contact_name: contact_name, phone: phone, email: email}, function (json) {
			          	 if (json.message != '') {
 														Util.hideProgressInd();
                            Util.alert(json.message);
                            return;
                      }
			          });
            }
        } else {
            msg = "Please enter the Company Name.";
            alert(msg);
        }
    });

    /* Convert to Opportunity */
    $('.m-enggCrm_callRegistry #convertToOpportunity').livequery('click', function (e){
        msg = "Do you like to convert to Opportunity?";
        if (!confirm(msg)){
            return false;
        }
        else{
            Util.showProgressInd();
            var call_registry_id = $(this).attr('call_registry_id');
            var url = 'index.php?module=enggCrm_callRegistry&_spAction=convertToOpportunity&showHTML=0&call_registry_id=' + call_registry_id;
            document.location = url;
        }
    });

    /* Duplicate Record */
    $('.m-enggCrm_callRegistry #duplicate').livequery('click', function (e){
        
        /*msg = "Do you like to Duplicate?";
        if (!confirm(msg)){
            return false;
        }
        else{
            Util.showProgressInd();
            var call_registry_id = $(this).attr('call_registry_id');
            var url = 'index.php?module=enggCrm_callRegistry&_spAction=duplicate&showHTML=0&call_registry_id=' + call_registry_id;
            document.location = url;
        }*/

        var title ="Call Registry Record Duplicate.";

        e.preventDefault();
        var expObj = {
            validate: true
           ,callbackOnSuccess: function(json){
                var msg = 'Call Registry Record Duplicated Succesfully..';
                Util.alert(msg, function(){
                    Util.closeAllDialogs();
                    document.location = json.returnUrl;
                });
            }
        }
        Util.openFormInDialog.call(this, 'duplicateCallDate', title, 300, 200, expObj);
    });

    $('.m-enggCrm_callRegistry #addNewCompany').livequery('click', function (e){
        var title = "Create New Company";
        e.preventDefault();
        
        var expObj = {
            validate: true
           ,callbackOnSuccess: function(){
                var msg = 'Company created successfully';
                Util.alert(msg, function(){
                    Util.closeAllDialogs();
                    window.location.reload(true);
                });
            }
        }
        Util.openFormInDialog.call(this, 'portalForm', title, 500, 500, expObj);        
    });

    /*
    $(".m-enggCrm_callRegistry input[name='company_name']").livequery(function(){
        var titleObj = this;
    	$(titleObj).autocomplete({
             source : 'index.php?module=enggCrm_callRegistry&_spAction=searchCompanyName&showHTML=0'
            ,minLength : 2
    		,select: function(event, ui) {
                var selectedObj = ui.item;
    			var company_id = selectedObj.id
    			//alert (company_id);
    			$(this).after("<input type='hidden' name='company_id' value=" + company_id + ">");
                //--------------------------------------------
                var companyNameObj = $('.m-enggCrm_callRegistry #fld_company_name');
                var contactObj = $('.m-enggCrm_callRegistry #fld_contact_name');
                var phoneObj = $('.m-enggCrm_callRegistry #fld_phone');
                var emailObj = $('.m-enggCrm_callRegistry #fld_email');
                var url = 'index.php?module=enggCrm_callRegistry&_spAction=updateCompanyDetails&showHTML=0';
                $.get(url, {company_id: company_id}, function(json){
                    companyNameObj.val(json.company_name);
                    contactObj.val(json.contact_person);
                    phoneObj.val(json.phone);
                    emailObj.val(json.email);
                }); 
    		}
    	});
    });
    */

    $('.m-enggCrm_callRegistry select[name=industry]').livequery('change', function (e){
        var title = $(this).val();
        if(title == 'Others'){
            $('.m-enggCrm_callRegistry .otherIndustry').show();
        } else {
            $('.m-enggCrm_callRegistry .otherIndustry').hide();
        }
    });

    $('.m-enggCrm_callRegistry select[name=status]').livequery('change', function (e){
        status = $(this).val();
        if(status == 'Follow up'){
            $('.m-enggCrm_callRegistry .reminderDateDisplay').show();
        } else {
            $('.m-enggCrm_callRegistry .reminderDateDisplay').hide();
        }
    });

    $('.m-enggCrm_callRegistry select[name=status]').livequery('change', function (e){
        status = $(this).val();
        if(status == 'High Win Ratio'){
            $('.m-enggCrm_callRegistry .noOfCandidate').show();
        } else {
            $('.m-enggCrm_callRegistry .noOfCandidate').hide();
        }
    });

    $('#sendProfileToClient').livequery('click', function(e){
        var title = "Send Profile To Client";
        e.preventDefault();
        
        var expObj = {
            validate: true
           ,callbackOnSuccess: function(){
                var msg = 'Profile sent to client';
                Util.alert(msg, function(){
                    Util.closeAllDialogs();
                });
            }
        }
        Util.openFormInDialog.call(this, 'portalForm', title, 700, 600, expObj);        
    });
}