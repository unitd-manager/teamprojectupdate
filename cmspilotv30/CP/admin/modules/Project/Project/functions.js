Util.createCPObject('cpm.project.project');

cpm.project.project = {
    init : function(){
    
    $('#frmEdit select#fld_company_id').livequery('change', function(){
        var url = 'index.php?module=project_contact&_spAction=contactByCompanyJSON&showHTML=0';
        var company_id = $('#fld_company_id').val();
        $.get(url, {company_id: company_id}, function (data) {
            $('#fld_contact_id').cp_loadSelect(data);
        }, 'json');
    }); 

},

    loadContactDropdown: function(){
        $(this).each(function(){
            comId = $(this).val();
            var url = $('#scopeRootAlias').val() + 'index.php?module=project_contact&_spAction=contactByCompanyJSON&showHTML=0'

            $.getJSON(url, {company_id: comId}, function(data) {
                $('#frmEdit select#fld_contact_id').cp_loadSelect(data);
            });
        });
    }
}

var Company = {
   /*getContactsComboByCompany: function(){
        var url = 'index.php?module=project_contact&_spAction=contactByCompanyJSON&showHTML=0';
        var company_id = $('#fld_company_id').val();
        $.get(url, {company_id: company_id}, function (data) {
            $('#fld_contact_id').cp_loadSelect(data);
        }, 'json');
    } */
        
}

var Project = {
    editFromList: function(project_id){
        url = "index.php?module=project_project"   +
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
        var url = "index.php?_topRm=" + topRoom + "&module=project_project&_spAction=duplicateProject&project_id=" + project_id;
    
        document.location = url;
    }
    
    
}

var Invoice = {
    raiseInvoice: function(topRoom){
        if (!confirm("You like to raise invoice for this project?")){
            return;
        }
    
        var project_id = document.getElementById('record_id').value;
        var url = "index.php?_topRm=" + topRoom + "&module=project_invoice&_spAction=raiseInvoice&project_id=" + project_id;
    
        document.location = url;
    }
}
