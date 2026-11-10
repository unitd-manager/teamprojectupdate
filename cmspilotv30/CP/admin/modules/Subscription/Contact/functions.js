Util.createCPObject('cpm.subscription.contact');
//portalPvtLinkForm forms are meant for private institute.
cpm.subscription.contact.init = function(){
    $(window).load(function(){
        /* Populating value for age in Parent # Student Linked New Record */
        $('.m-subscription_contact #frmEdit #fld_date_of_birth').livequery('change', function (e){
            var date_of_birth = $(this).val();
        
            var url = 'index.php?module=subscription_orderLink&_spAction=calculateStudentAge&showHTML=0';
            Util.showProgressInd();
            $.get(url, {date_of_birth: date_of_birth}, function(json){
                var intData = parseInt(json.age);
                $('.m-subscription_contact #frmEdit #fld_age').val(intData);
                Util.hideProgressInd();
            },'json');
        });

        $('.m-subscription_contact .button #actBtn_status').livequery('click', function(e){
            var contact_id = $(this).attr('contact_id');
            var title ="Change Status";
    
            e.preventDefault();
            var expObj = {
                validate: true
               ,callbackOnSuccess: function(json){
                    var urlRedirect = "index.php?_topRm=main&module=subscription_contact&_action=edit&record_id=" + contact_id;                    
                    var msg = 'Status Changed Succesfully..';
                    Util.alert(msg, function(){
                        Util.closeAllDialogs();
                        document.location = urlRedirect;
                    });
                }
            }
            Util.openFormInDialog.call(this, 'changeStatusForm', title, 500, 300, expObj);
        }),
        

        $('.m-subscription_contact .button #actBtn_statusToActive').livequery('click', function(e){
            var contact_id = $(this).attr('contact_id');

            var urlRedirect = "index.php?_topRm=main&module=subscription_contact&_action=edit&record_id=" + contact_id;
            var url = 'index.php?module=subscription_contact&_spAction=changeStatusToActive&showHTML=0';
            $.get(url, {contact_id: contact_id}, function(html){
                document.location = urlRedirect;
            });
        });
    });

        $(".subscriptionReceiptCheckBox").livequery('click', function(e){
                e.preventDefault();
              alert ("Hello")  ;
	});

        $('.m-subscription_contact #addSubscriptionForm').livequery('click', function (e){
            /*msg = "Do you like to Update Markup?";
            if (!confirm(msg)){
                return false;
            }
            else{*/
                var title = "Add Subscription";
                e.preventDefault();
                var expObj = {
                    validate: true
                   ,callbackOnSuccess: function(){
                        Util.closeAllDialogs();
                        window.location.reload(true);
                    }
                }
                Util.openFormInDialog.call(this, 'portalForm', title, 400, 400, expObj);
            //}
        });

}