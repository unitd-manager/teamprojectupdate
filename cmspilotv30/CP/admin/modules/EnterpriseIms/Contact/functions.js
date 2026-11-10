Util.createCPObject('cpm.enterpriseIms.contact');
//portalPvtLinkForm forms are meant for private institute.
cpm.enterpriseIms.contact.init = function(){
    $(window).load(function(){
        /* Populating value for age in Parent # Student Linked New Record */
        $('.m-enterpriseIms_contact #frmEdit #fld_date_of_birth').livequery('change', function (e){
            var date_of_birth = $(this).val();

            var url = 'index.php?module=enterpriseIms_orderLink&_spAction=calculateStudentAge&showHTML=0';
            Util.showProgressInd();
            $.get(url, {date_of_birth: date_of_birth}, function(json){
                var intData = parseInt(json.age);
                $('.m-enterpriseIms_contact #frmEdit #fld_age').val(intData);
                Util.hideProgressInd();
            },'json');
        });

        $('.m-enterpriseIms_contact .button #actBtn_status').livequery('click', function(e){
            var contact_id = $(this).attr('contact_id');
            var title ="Change Status";

            e.preventDefault();
            var expObj = {
                validate: true
               ,callbackOnSuccess: function(json){
                    var urlRedirect = "index.php?_topRm=main&module=enterpriseIms_contact&_action=edit&record_id=" + contact_id;
                    var msg = 'Status Changed Succesfully..';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        document.location = urlRedirect;
                    });
                }
            }
            Util.openFormInDialog.call(this, 'changeStatusForm', title, 500, 300, expObj);
        }),


        $('.m-enterpriseIms_contact .button #actBtn_statusToActive').livequery('click', function(e){
            var contact_id = $(this).attr('contact_id');

            var urlRedirect = "index.php?_topRm=main&module=enterpriseIms_contact&_action=edit&record_id=" + contact_id;
            var url = 'index.php?module=enterpriseIms_contact&_spAction=changeStatusToActive&showHTML=0';
            $.get(url, {contact_id: contact_id}, function(html){
                document.location = urlRedirect;
            });
        });
    });
}