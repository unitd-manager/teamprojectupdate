Util.createCPObject('cpm.labsg.patientInformation');

cpm.labsg.patientInformation = {
    init: function(){
        $('select[name=company_id]').livequery('change', function(){
            var company_id = $(this).val();

            var url = 'index.php?module=labsg_patientInformation&_spAction=updateCompanyDetails&showHTML=0';
            $.get(url, {company_id: company_id}, function(json){
            	$('#fld_company_phone').html(json.phone);
            	$('#fld_company_address_flat').html(json.address_flat);
            	$('#fld_company_address_street').html(json.address_street);
            	$('#fld_company_address_town').html(json.address_town);
            	$('#fld_company_address_state').html(json.address_state);
            	$('#fld_company_address_country').html(json.address_country);
            });
        });
	}
}