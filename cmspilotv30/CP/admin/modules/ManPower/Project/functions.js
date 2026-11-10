Util.createCPObject('cpm.manPower.project');

cpm.manPower.project.init = function(){
    $('select#fld_company_id').livequery('change', function() {
        var url = 'index.php?module=project_contact&_spAction=contactByCompanyJSON&showHTML=0';
        var company_id = $(this).val();
        $.get(url, {company_id: company_id}, function (data) {
            $('#fld_contact_id').cp_loadSelect(data);
        }, 'json');
    });

    $('input:radio[name="apply_commission"]').livequery('click', function (e){
        apply_commission = $(this).val();
        if(apply_commission == 1){
            $('.m-manPower_project .chequeNoDisplay').show();
        } else {
            $('.m-manPower_project .chequeNoDisplay').hide();
        }
    });

    $('select[name=referral_id]').livequery('change', function(){
        company_id = $(this).val();
        var url = 'index.php?module=manPower_project&_spAction=commissionPercent&showHTML=0';
        $.get(url, {company_id: company_id}, function (data) {
             $('#fld_commission_percentage').val(data);
        });
    });
}

var Company = {
    getContactsComboByCompany: function(){
        var url = 'index.php?module=project_contact&_spAction=contactByCompanyJSON&showHTML=0';
        var company_id = $('#fld_company_id').val();
        $.get(url, {company_id: company_id}, function (data) {
            $('#fld_contact_id').cp_loadSelect(data);
        }, 'json');
    }
}

var Project = {
    editFromList: function(project_id){
        url = "index.php?module=manPower_project"   +
        "&_spAction=editFromList" +
        "&project_id="   + project_id
        a = window.open(url,"","height=250,width=550,scrollbars=no," +
            "resizable=yes" + ",left=" + (screen.width-400)/2 + ",top=" + (screen.height-200)/2);
    },
    
    printOrderConfirm: function(){
        var record_id = document.getElementById('record_id').value;
        url = "jasper.php?project_id=" + record_id + "&report=orderCofirmation";
        w = 50;
        h = 50;
        windowString = "height=" + h + ",width=" + w + ",scrollbars=yes," +
        "resizable=yes,left=" + (screen.width-w)/2 + ",top=" +
        (screen.height-h)/2
        wind = window.open( url , "printFormToPDF", windowString);
    },

    setContactsComboByCompany: function(){
        var url = 'index.php?module=project_contact&_spAction=contactByCompanyJSON&showHTML=0';
        var company_id = $('select#fld_company_id').val();
        $.get(url, {company_id: company_id}, function (data){
            $('#fld_contact_id').cp_loadSelect(data);
        }, 'json');
    },
	
    duplicateProject: function(topRoom){
        if (!confirm("You like to duplicate the Project and related Tasks?")){
            return;
        }
    
        var project_id = document.getElementById('record_id').value;
        var url = "index.php?_topRm=" + topRoom + "&module=manPower_project&_spAction=duplicateProject&project_id=" + project_id;
    
        document.location = url;
    }


}

var Invoice = {
    raiseInvoice: function(topRoom){
        if (!confirm("You like to raise invoice for this project?")){
            return;
        }
    
        var project_id = document.getElementById('record_id').value;
        var url = "index.php?_topRm=finance&module=manPower_invoice&_spAction=raiseInvoice&project_id=" + project_id;
    
        document.location = url;
    }
}

    $('#generateOrderRecord').livequery('click', function(){
        msg = "Do you like to Generate Finance Record?";
        var client_hourly_rate    = $(this).attr('client_hourly_rate');
        var candidate_hourly_rate = $(this).attr('candidate_hourly_rate');

        if (!confirm(msg)){
            return false;
        }
        else{

            if(client_hourly_rate == 0 || candidate_hourly_rate == 0){
                var hourly_rate ="Please enter client hourly rate and candidate hourly rate";
                Util.alert(hourly_rate);
                return false;
            }
            else{
                Util.showProgressInd();
                var project_id = $(this).attr('project_id');
                var url = 'index.php?module=manPower_project&_spAction=generateOrderRecord&showHTML=0&id=' + project_id;
                $.get(url, {project_id: project_id}, function(html){
                    alert('Order raised Successfully!!\n\nClick Go To Finance!!');
                    window.location.reload(true);
                });
            }
        }
    });