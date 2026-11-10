Util.createCPObject('cpm.labsg.company');

cpm.labsg.company = {
    init: function(){

        /* Add Treatment */
        $('#AddTreatment').livequery('click', function (e){
                var title = "Add Treatment";
                var company_id = $(this).attr('company_id');
                e.preventDefault();

                var expObj = {
                    validate: true
                   ,callbackOnSuccess: function(){
                        var msg = 'Treatment Added Successfully';
                        Util.alert(msg, function(){
                            Util.closeAllDialogs();
                            Util.showProgressInd();
                            cpm.labsg.company.reloadTreatmentLinked(company_id);
                        });
                    }
                }
                Util.openFormInDialog.call(this, 'portalForm', title, 600, 300, expObj);
        });

        /* Edit treatment */
        $('.EditTreatment').livequery('click', function (e){
            var title = "Edit Treatment";
            var company_treatment_id = $(this).attr('company_treatment_id');
            var company_id = $(this).attr('company_id');

            e.preventDefault();
    
            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    var msg = 'Treatment Updated Successfully';
                      Util.alert(msg, function(){
                      Util.closeAllDialogs();
                      Util.showProgressInd();
                      cpm.labsg.company.reloadTreatmentLinked(company_id);
                    });
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 600, 500, expObj);
        });

            /* Delete  Treatment*/
        $('.deleteTreatment').livequery('click', function (e){
            var company_id   = $(this).attr('company_id');
            var treatment_id = $(this).attr('treatment_id');
            msg = "Do you like to delete the Treatment?";
            if (!confirm(msg)){
                return false;
            }
            else{
                Util.showProgressInd();
                var company_treatment_id = $(this).attr('company_treatment_id');
                var url = 'index.php?module=labsg_company&_spAction=DeleteTreatment&showHTML=0&company_treatment_id=' + company_treatment_id;
                $.get(url, {company_treatment_id: company_treatment_id, treatment_id:treatment_id}, function(html){
                      cpm.labsg.company.reloadTreatmentLinked(company_id);
                });
            }
        });

    },

    reloadTreatmentLinked: function(company_id){
        var url = 'index.php?module=labsg_company&_spAction=addTreatment&showHTML=0';
        $.get(url, {company_id:company_id},function(html){
            $('#treatmentLinkPortal').html(html);
            Util.hideProgressInd();
        });
    },
}
