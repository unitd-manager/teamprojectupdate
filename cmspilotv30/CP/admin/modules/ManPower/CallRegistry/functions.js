Util.createCPObject('cpm.manPower.callRegistry');

cpm.manPower.callRegistry.init = function(){
    $('select#fld_category').change(function() {
        var category = $(this).val();

        var url = 'index.php?module=manPower_callRegistry&_spAction=statusByCategoryJSON&showHTML=0';
        $.get(url, {category: category}, function (data) {
            $('#fld_status').cp_loadSelect(data);
        }, 'json');
    });

    /* Create Record in Client */
    $('.m-manPower_callRegistry #createClientRec').livequery('click', function (e){

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
                var url = 'index.php?module=manPower_callRegistry&_spAction=createClientRec&showHTML=0';

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
    $('.m-manPower_callRegistry #convertToOpportunity').livequery('click', function (e){
        var no_of_candidates = $('.m-manPower_callRegistry #fld_no_of_candidates').val();

        if (no_of_candidates != '') {
            msg = "Do you like to convert to Opportunity?";
            if (!confirm(msg)){
                return false;
            }
            else{
                Util.showProgressInd();
                var call_registry_id = $(this).attr('call_registry_id');
                var url = "index.php?module=manPower_callRegistry&_spAction=convertToOpportunity&call_registry_id="+call_registry_id+"&showHTML=0";
                window.open(url,'_blank');
                window.location.reload();
            }
        } else {
            msg = "Please enter no of canidates.";
            alert(msg);
        }
    });

    /* Duplicate Record */
    $('.m-manPower_callRegistry #duplicate').livequery('click', function (e){

        /*msg = "Do you like to Duplicate?";
        if (!confirm(msg)){
            return false;
        }
        else{
            Util.showProgressInd();
            var call_registry_id = $(this).attr('call_registry_id');
            var url = 'index.php?module=manPower_callRegistry&_spAction=duplicate&showHTML=0&call_registry_id=' + call_registry_id;
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

    $('.addNewValue').livequery('click', function (e){
    var title = "Add New Value";
    e.preventDefault();

    var valuelist_value = $('.addNewDropdownValueForm #fld_valuelist_value').val();
    var expObj = {
        validate: true
       ,callbackOnSuccess: function(){
            Util.closeAllDialogs();
            window.location.reload(true);
            $(".m-manPower_callRegistry select[name='valuelist_value']").val(valuelist_value);
        }
    }
    Util.openFormInDialog.call(this, 'portalForm', title, 400, 300, expObj);
    });

    $('.m-manPower_callRegistry #addNewCompany').livequery('click', function (e){
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

    $(".m-manPower_callRegistry input[name='company_name']").livequery(function(){
        var titleObj = this;
    	$(titleObj).autocomplete({
             source : 'index.php?module=manPower_callRegistry&_spAction=searchCompanyName&showHTML=0'
            ,minLength : 2
    		,select: function(event, ui) {
                var selectedObj = ui.item;
    			var company_id = selectedObj.id
    			//alert (company_id);
    			$(this).after("<input type='hidden' name='company_id' value=" + company_id + ">");
                //--------------------------------------------
                var companyNameObj = $('.m-manPower_callRegistry #fld_company_name');
                var phoneObj = $('.m-manPower_callRegistry #fld_phone');
                var emailObj = $('.m-manPower_callRegistry #fld_email');
                var url = 'index.php?module=manPower_callRegistry&_spAction=updateCompanyDetails&showHTML=0';
                $.get(url, {company_id: company_id}, function(json){
                    companyNameObj.val(json.company_name);
                    phoneObj.val(json.phone);
                    emailObj.val(json.email);
                });
    		}
    	});
    });

    $('.m-manPower_callRegistry select[name=industry]').livequery('change', function (e){
        var title = $(this).val();
        if(title == 'Others'){
            $('.m-manPower_callRegistry .otherIndustry').show();
        } else {
            $('.m-manPower_callRegistry .otherIndustry').hide();
        }
    });

    $('.m-manPower_callRegistry select[name=status]').livequery('change', function (e){
        status = $(this).val();
        if(status == 'Follow up'){
            $('.m-manPower_callRegistry .reminderDateDisplay').show();
        } else {
            $('.m-manPower_callRegistry .reminderDateDisplay').hide();
        }
    });

    $('.m-manPower_callRegistry select[name=status]').livequery('change', function (e){
        status = $(this).val();
        if(status == 'High Win Ratio'){
            $('.m-manPower_callRegistry .noOfCandidate').show();
        } else {
            $('.m-manPower_callRegistry .noOfCandidate').hide();
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

    /* Convert to Enquiry */
    $('.m-manPower_callRegistry #convertToEnquiry').livequery('click', function (e){

        msg = "Do you like to convert to Enquiry?";
        if (!confirm(msg)){
            return false;
        }
        else{
            Util.showProgressInd();
            var call_registry_id = $(this).attr('call_registry_id');
            var url = 'index.php?module=manPower_callRegistry&_spAction=convertToEnquiry&showHTML=0&call_registry_id=' + call_registry_id;
            document.location = url;
        }
    });

}