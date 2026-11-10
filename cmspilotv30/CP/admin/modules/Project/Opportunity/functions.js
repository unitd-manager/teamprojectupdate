Util.createCPObject('cpm.project.opportunity');

cpm.project.opportunity.init = function(){
    $('button#convertToProject, #actBtn_convertOppToProject').click(function(e) {
        e.preventDefault();
        var msg = 'Are you sure to convert this opportunity to project?';
        Util.confirm(msg, function(){
            var opportunity_id = $('#record_id').val();
            var hasQuotingModule = $('#hasQuotingModule').val();

            var convertUrl = "index.php?_topRm=project&module=project_project&_spAction=convertOppToProject&opportunity_id=" + opportunity_id;

            if (hasQuotingModule == 0){
                document.location = convertUrl;
            } else {
                var url = 'index.php?module=project_opportunity&_spAction=confirmedQuoteIDJSON&showHTML=0';
                $.get(url, {opportunity_id: opportunity_id}, function (json) {
                    if(json.quote_id > 0){
                        var url = "index.php?_topRm=project&module=project_project&_spAction=convertOppToProject&opportunity_id=" + opportunity_id;
                        document.location = convertUrl;
                    } else {
                        var msg = 'The opportunity should have a confirmed quote before it is getting converetd to project';
                        Util.alert(msg)
                    }
                }, 'json');
            }
        });
    });

    /* Add Company Linked*/
    $('#AddCompanyLinked').live('click', function (e){
        var title = "Add Company";
        var opportunity_id = $(this).attr('opportunity_id');
        e.preventDefault();
        var expObj = {
            validate: true
           ,callbackOnSuccess: function(json){
                var msg = 'Company Added Successfully';
                $("input[name='company_name']").val(json.returnUrl['company_name']);
                $("input[name='company_id']").val(json.returnUrl['company_id']);
                $("a#AddCompanyLinked").addClass("displayNone");
                $("a#AddContactLinked").removeClass("displayNone");

                Util.alert(msg, function(){
                    Util.closeAllDialogs();
                    cpm.project.opportunity.reloadCompanyLinked(opportunity_id);
                    cpm.project.opportunity.reloadContactLinked(json.returnUrl['company_id']);
                });
            }
        }

        Util.openFormInDialog.call(this, 'AddCompanyLinkedForm', title, 750, 482, expObj);
    });

    /* Edit Company Linked*/
    $('.EditCompanyLinked').live('click', function (e){
        var title = "Edit Company";
        var company_id     = $(this).attr('company_id');
        var opportunity_id = $(this).attr('opportunity_id');
        e.preventDefault();
        var expObj = {
            validate: true
           ,callbackOnSuccess: function(){
                var msg = 'Company Updated Successfully';
                Util.alert(msg, function(){
                    Util.closeAllDialogs();
                    cpm.project.opportunity.reloadCompanyLinked(opportunity_id);
                });
            }
        }

        Util.openFormInDialog.call(this, 'EditCompanyLinkedForm', title, 750, 482, expObj);
    });

    /* Delete Company Linked */
    $('.deleteCompanyLinked').live('click', function (e){
        var opportunity_company_id = $(this).attr('opportunity_company_id');
        var company_id             = $(this).attr('company_id');
        var opportunity_id         = $(this).attr('opportunity_id');

        var msg = "Are you sure to delete?";
        if (confirm(msg)){
            Util.showProgressInd();
            $("a#AddCompanyLinked").removeClass("displayNone");
            $("a#AddContactLinked").addClass("displayNone");
            var url = 'index.php?module=project_opportunity&_spAction=deleteCompanyLinked'
                    + '&showHTML=0';
            $.get(url, {opportunity_company_id:opportunity_company_id, company_id:company_id}, function(html){
                cpm.project.opportunity.reloadCompanyLinked(opportunity_id);
                var urlContact = 'index.php?module=project_opportunity&_spAction=deleteContactLinkedAll'
                        + '&showHTML=0';
                $.get(urlContact, {company_id: company_id}, function(html){
                    var urlContMain = 'index.php?module=project_opportunity&_spAction=contactLinked&showHTML=0';
                    var companyId = $("input[name='company_id']").val();
                    $.get(urlContMain, {company_id: company_id}, function(html){
                        $('#contactLinkPortal').html(html);
                        window.location.reload(true);
                        if(company_id == companyId) {
                            var urlContact = 'index.php?module=project_opportunity&_spAction=contactByCompanyJSON&showHTML=0';
                            $.get(urlContact, {company_id: company_id}, function (data) {
                                $('#fld_contact_id').cp_loadSelect(data);
                            }, 'json');
                        }
                        $(".project_opportunity_contactLink .actBtns").remove();
                    });
                    
                });

                alert('Company Deleted Successfully');
            });
        }
    });

    /* Add Contact Linked*/
    $('#AddContactLinked').live('click', function (e){
        var title = "Add Contact";
        var company_id = $('input[name=company_id]').val();
        var url = "index.php?module=project_opportunity&_spAction=AddContactLinked&company_id="+ company_id +"&showHTML=0";
        e.preventDefault();
        var expObj = {
            url: url
           ,validate: true
           ,callbackOnSuccess: function(json){
                var msg = 'Contact Added Successfully';
                var urlContact = 'index.php?module=project_opportunity&_spAction=contactByCompanyJSON&showHTML=0';
                $.get(urlContact, {company_id: company_id}, function (data) {
                    $('#fld_contact_id').cp_loadSelect(data);
                }, 'json');

                Util.alert(msg, function(){
                    Util.closeAllDialogs();
                    cpm.project.opportunity.reloadContactLinked(company_id);
                });
            }
        }

        Util.openFormInDialog.call(this, 'AddContactLinkedForm', title, 750, 482, expObj);
    });

    /* Edit Contact Linked*/
    $('.EditContactLinked').live('click', function (e){
        var title = "Edit Contact";
        var company_id     = $(this).attr('company_id');
        e.preventDefault();
        var expObj = {
            validate: true
           ,callbackOnSuccess: function(){
                var msg = 'Contact Updated Successfully';
                Util.alert(msg, function(){
                    Util.closeAllDialogs();
                    cpm.project.opportunity.reloadContactLinked(company_id);
                });
            }
        }

        Util.openFormInDialog.call(this, 'EditContactLinkedForm', title, 750, 482, expObj);
    });

    /* Delete Contact Linked */
    $('.deleteContactLinked').live('click', function (e){
        var company_id = $(this).attr('company_id');
        var contact_id = $(this).attr('contact_id');

        var msg = "Are you sure to delete?";
        if (confirm(msg)){
            Util.showProgressInd();
            var url = 'index.php?module=project_opportunity&_spAction=deleteContactLinked'
                    + '&showHTML=0';
            $.get(url, {contact_id: contact_id}, function(html){
                Util.hideProgressInd();
                alert('Contact Deleted Successfully');
                cpm.project.opportunity.reloadContactLinked(company_id);
            });
        }
    });
}

cpm.project.opportunity.loadContactsByCompany = function(){
    var url = 'index.php?module=project_contact&_spAction=contactByCompanyJSON&showHTML=0';
    var company_id = $('input[name=company_id]').val();
    $.get(url, {company_id: company_id}, function (data) {
        $('#fld_contact_id').cp_loadSelect(data);
    }, 'json');
}

cpm.project.opportunity.afterNewCompany = function(){
    Util.closeAllDialogs();
    Util.alert('New company successfully created.', function(){
        $('#fld_company_name').focus();
        $('#fld_company_name').select();
    });
}

cpm.project.opportunity.afterNewContact = function(){
    Util.closeAllDialogs();
    Util.alert('New contact successfully created.', function(){
        cpm.project.opportunity.loadContactsByCompany();
    });
}

cpm.project.opportunity.reloadCompanyLinked = function(opportunity_id){
    var url = 'index.php?module=project_opportunity&_spAction=companyLinked&showHTML=0';
    $.get(url, {opportunity_id: opportunity_id}, function(html){
        $('#companyLinkPortal').html(html);
    });
}

cpm.project.opportunity.reloadContactLinked = function(company_id){
    var url = 'index.php?module=project_opportunity&_spAction=contactLinked&showHTML=0';
    var companyId = $("input[name='company_id']").val();
    $.get(url, {company_id: company_id}, function(html){
        $('#contactLinkPortal').html(html);
        if(company_id == companyId) {
            var urlContact = 'index.php?module=project_opportunity&_spAction=contactByCompanyJSON&showHTML=0';
            $.get(urlContact, {company_id: company_id}, function (data) {
                $('#fld_contact_id').cp_loadSelect(data);
            }, 'json');
        }
    });
} 