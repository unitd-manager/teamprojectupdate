Util.createCPObject('cpm.accountsg.accHead');

cpm.account.accHead.init = function(){
    $('.m-accountsg_accHead.v-edit #fld_company_id').livequery('change', function(){
        Util.loadDropdownByJSON('company_id', $(this).val(), 'fld_contact_id', 'account_contact');
    });
}
