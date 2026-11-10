Util.createCPObject('cpm.project.company');

cpm.project.company.init = function(){
	$('#addRenewalNew').livequery('click', function (e){
	    var title = "New RenewalLink";
	    var company_id = $(this).attr('company_id');
	    e.preventDefault();

	    var expObj = {
	        validate: true
	        ,callbackOnSuccess: function(){
	        Util.closeAllDialogs();
	        reloadRenewalList.reloadRenewalListobj(company_id);
	        }
	    }
	    Util.openFormInDialog.call(this, 'portalForm', title, 600, 500, expObj);
    });

	$('.extendRenewal').livequery('click', function (e){
	    var title = "Extend RenewalLink";
	    var company_id = $(this).attr('company_id');
	    e.preventDefault();

	    var expObj = {
	        validate: true
	        ,callbackOnSuccess: function(){
	        Util.closeAllDialogs();
	        reloadRenewalList.reloadRenewalListobj(company_id);
	        }
	    }
	    Util.openFormInDialog.call(this, 'portalForm', title, 600, 500, expObj);
    });

    $('.editRenewal').livequery('click', function (e){
	    var title = "Edit RenewalLink";
	    var company_id = $(this).attr('company_id');
	    e.preventDefault();

	    var expObj = {
	        validate: true
	       ,callbackOnSuccess: function(){
	        Util.closeAllDialogs();
	        reloadRenewalList.reloadRenewalListobj(company_id);
	        }
	    }
	    Util.openFormInDialog.call(this, 'portalForm', title, 600, 500, expObj);
	});

	$('.deleteRenewalRecord').livequery('click', function (){
    var company_id = $(this).attr('company_id');
    var renewal_id = $(this).attr('renewal_id');
    var url ='index.php?module=project_company&_spAction=deleteRenewalRecord&showHTML=0'
        $.get(url, {company_id:company_id, renewal_id:renewal_id}, function(html){
            reloadRenewalList.reloadRenewalListobj(company_id);
        });
    });
}

	var reloadRenewalList ={
		reloadRenewalListobj: function(company_id){
		         var url = 'index.php?module=project_company&_spAction=renewalDisplay&showHTML=0';
		        $.get(url, {company_id: company_id}, function(html){
		            $('#renewalLinkPortal').html(html);
		            Util.hideProgressInd();
		         });
		}
	}