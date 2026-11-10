Util.createCPObject('cpm.enggCrm.renewal');

cpm.enggCrm.renewal = {
    init : function(){

         $('#tabs').tabs();

        $('#tabs ul.ui-tabs-nav li:last').livequery(function() {
            $(this).css('border-right', '1px solid #D3D3D3');
        });

            /* Edit Product */
        $('.deleteShopRenewal').live('click', function (e){
            var renewal_id = $(this).attr('renewal_id');
            var shop_renewal_id = $(this).attr('shop_renewal_id');
            var url = 'index.php?module=enggCrm_renewal&_spAction=deleteShopRenewal'
                    + '&showHTML=0';
            $.get(url, {renewal_id:renewal_id, shop_renewal_id:shop_renewal_id} ,function(html){
                alert('Deleted Successfully!');
            	window.location.reload(true);
            });
        });

         $('.m-enggCrm_renewal .showHideCancelledInvoice').livequery('click', function (e){
            var link_text = $(this).html();

            if(link_text == '(+) Click to View Cancelled Invoice(s)'){
                $('.showHideCancelledInvoice').text('(-) Click to Hide Cancelled Invoice(s)');
            }
            else{
                $('.showHideCancelledInvoice').text('(+) Click to View Cancelled Invoice(s)');
            }

            $('.cancelledInvoiceTableOrder').slideToggle();
        });

      
        $('#addQuoteRenewal').livequery('click', function(){
            var renewal_id = $("#record_id").val();
            var opportunity_id = $(this).attr('opportunity_id');
            var category = $(this).attr('category');
            msg = "Do you like to Add Service?";
    
            if (!confirm(msg)){
                return false;
            }
            else{
                Util.showProgressInd();
                var renewal_id = $(this).attr('renewal_id');
                var url = 'index.php?_topRm=main&module=enggCrm_renewal&_spAction=addQuoteFormSubmit&showHTML=0&id=' + renewal_id;
                $.get(url, {renewal_id: renewal_id}, function(html){
                    var mgsalert = 'Service record created successfully!';
                    var n = noty({
                        text: mgsalert,
                        type: 'confirm',
                        dismissQueue: true,
                        layout: 'topCenter',
                        theme: 'defaultTheme',
                        timeout: 5000,
                    });
                    
                   
    
                window.location.reload(true);
            });
            }
        });

        $(".m-enggCrm_renewal a#addServiceMultipleLineItem").livequery('click', function (e){
            var title = "Add Service";
            var renewal_id = $(this).attr('renewal_id');
            var url = 'index.php?_topRm=main&module=enggCrm_renewal&_spAction=addServiceMultipleLineItem'
                    + '&showHTML=0&renewal_id=' + renewal_id;
            var exp = {
                url: url
               ,callbackOnSuccess: function(){
                    Util.closeAllDialogs();
                    var mgsalert = 'Service created successfully!';
                    var n = noty({
                        text: mgsalert,
                        type: 'confirm',
                        dismissQueue: true,
                        layout: 'topCenter',
                        theme: 'defaultTheme',
                        timeout: 5000,
                    });
                  window.location.reload(true);
                }
            };
    
            Util.openFormInDialog.call(this, 'addServiceMultipleLineItemForm', title, 900, 500, exp);
        });


   


      /* Adding row in new Line Item */
      let rowCount = 9;

      // Add new row on "Add Line Item" click
      $(".m-enggCrm_renewal #addServiceMultipleLineItemForm a.addRow").livequery('click', function (e){
        e.preventDefault();
          const renewal_id = $(this).attr('renewal_id');
          const url = 'index.php?_topRm=main&module=enggCrm_renewal&_spAction=addServiceLineItemRecord'
                      + '&showHTML=0&renewal_id=' + renewal_id + '&index=' + rowCount;
      
          $.get(url, '', function(html){
              $('#addServiceMultipleLineItemForm tr:last').after(html);
              rowCount++;  // Increment the counter for the next row
          });
      });


      $(".m-enggCrm_renewal a.clearLineItem").livequery('click', function (e) {
        var scheduleObj = $(this).closest('tr').find('.lineItemSchedule');
        var scheduledateObj = $(this).closest('tr').find('.lineItemScheduleDate');
        var actualdateObj = $(this).closest('tr').find('.lineItemActualDate');
        var servicedueObj = $(this).closest('tr').find('.lineItemServiceDue');
        var remarksObj = $(this).closest('tr').find('.lineItemRemarks');
    
        // Clear text fields
        scheduleObj.val('');
        scheduledateObj.val('');
        actualdateObj.val('');
        remarksObj.val('');
    
        // Clear radio buttons
        servicedueObj.find("input[type='radio']").prop("checked", false);
    });
    
    

        $('.addNewValue').livequery('click', function (e){
            var title = "Add New Value";
            e.preventDefault();
    
            var valuelist_name = $(this).attr('valuelist_name');
    
            var expObj = {
                validate: true
               ,callbackOnSuccess: function(){
                    Util.closeAllDialogs();
                    //window.location.reload(true);
                    //$(".m-manPower_opportunity select[name='valuelist_value']").val(valuelist_value);
    
                    var url = 'index.php?module=enggCrm_renewal&_spAction=valueByValuelistJSON&showHTML=0';
                    $.get(url, {valuelist_name: valuelist_name}, function (data) {
                        if(valuelist_name == 'checklist'){
                            $('#fld_service_included').cp_loadSelect(data);
                        } 
                    }, 'json');
                }
            }
            Util.openFormInDialog.call(this, 'portalForm', title, 400, 300, expObj);
                            //window.location.reload(true);
    
            });

    $('a.editForServiceRenewal').livequery('click', function (e){
        var title = "Edit Service Display";
        var renewal_id = $('#record_id').val();

        e.preventDefault();
        var expObj = {
            validate: true,
            callbackOnSuccess: function(data){
                Util.closeAllDialogs();
                var mgsalert = 'Updated service successfully!';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 5000,
                });

             

                window.location.reload(true);
            }
        }
        Util.openFormInDialog.call(this, 'editForRenewal', title, 800, 500, expObj);
    });

     
    
    $(".m-enggCrm_renewal a#addShopMultipleLineItem").livequery('click', function (e){
        var title = "Add Service";
        var renewal_id = $(this).attr('renewal_id');
        var url = 'index.php?_topRm=main&module=enggCrm_renewal&_spAction=addShopMultipleLineItem'
                + '&showHTML=0&renewal_id=' + renewal_id;
        var exp = {
            url: url
           ,callbackOnSuccess: function(){
                Util.closeAllDialogs();
                var mgsalert = 'Shop created successfully!';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 5000,
                });
              window.location.reload(true);
            }
        };

        Util.openFormInDialog.call(this, 'addShopMultipleLineItemForm', title, 900, 500, exp);
    });





  /* Adding row in new Line Item */
  let rowCounts = 9;

  // Add new row on "Add Line Item" click
  $(".m-enggCrm_renewal #addShopMultipleLineItemForm a.addRow").livequery('click', function (e){
    e.preventDefault();
      const renewal_id = $(this).attr('renewal_id');
      const url = 'index.php?_topRm=main&module=enggCrm_renewal&_spAction=addShopLineItemRecord'
                  + '&showHTML=0&renewal_id=' + renewal_id + '&index=' + rowCount;
  
      $.get(url, '', function(html){
          $('#addShopMultipleLineItemForm tr:last').after(html);
          rowCounts++;  // Increment the counter for the next row
      });
  });


  $(".m-enggCrm_renewal a.clearLineItem").livequery('click', function (e) {
    var shopObj = $(this).closest('tr').find('.lineItemShop');
    var locationObj = $(this).closest('tr').find('.lineItemLocation');


    // Clear text fields
    shopObj.val('');
    locationObj.val('');

  
});


$('a.editForShopRenewal').livequery('click', function (e){
    var title = "Edit Shop Display";
    var renewal_id = $('#record_id').val();

    e.preventDefault();
    var expObj = {
        validate: true,
        callbackOnSuccess: function(data){
            Util.closeAllDialogs();
            var mgsalert = 'Updated shop successfully!';
            var n = noty({
                text: mgsalert,
                type: 'confirm',
                dismissQueue: true,
                layout: 'topCenter',
                theme: 'defaultTheme',
                timeout: 5000,
            });

         

            window.location.reload(true);
        }
    }
    Util.openFormInDialog.call(this, 'editForShopRenewal', title, 800, 500, expObj);
});

		
		$("a.addActualCharge").livequery('click', function (e){
            var renewal_id = $(this).attr('renewal_id');

            var url = 'index.php?_topRm=main&module=enggCrm_renewal&_spAction=addActualCharge'
                    + '&showHTML=0&renewal_id='+renewal_id;
            var exp = {
                url: url
               ,callbackOnSuccess: function(){
                    Util.closeAllDialogs();

                    var mgsalert = 'Fuel charges added successfully!';
                    var n = noty({
                        text: mgsalert,
                        type: 'confirm',
                        dismissQueue: true,
                        layout: 'topCenter',
                        theme: 'defaultTheme',
                        timeout: 5000,
                    });
                }
            };

            Util.openFormInDialog.call(this, 'actualChargeForm', 'Add Fuel', 550, 500, exp);
        });

        $(".renewalDetailRow input[name='monthly']").livequery('click', function(e){
        var title   = $(this).val();
        var cboxObj   = $(this);
        var cbObj = $('input[type=checkbox]');
        var checked = cbObj.is(":checked") ? true : false;
        var url = 'index.php?module=enggCrm_renewal&_spAction=addMonthly&showHTML=0';
       var parent       = $(this).closest(".classSubjectCheckbox1");
        var urldelete = 'index.php?module=enggCrm_renewal&_spAction=deleteMonthly&showHTML=0';
        var renewal_chechlist_history_id = $("input[name='renewal_chechlist_history_id']", parent).val();
        var renewal_id = $("input[name='renewal_id']").val();
        Util.showProgressInd();
        if (!cboxObj.attr('checked')){
            $.get(urldelete,{renewal_id:renewal_id, renewal_chechlist_history_id:renewal_chechlist_history_id}, function(){
                //Util.alert('Removed Successfully!');
                var mgsalert2 = 'Removed Successfully!';
                var n = noty({
                    text: mgsalert2,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 3000,
                });
                Util.hideProgressInd();
                cpm.enggCrm.renewal.courseSubjectReload(renewal_id);
            });
        } else {
            $.get(url,{renewal_id:renewal_id, renewal_chechlist_history_id:renewal_chechlist_history_id}, function(){
                //Util.alert('Added Successfully!');
                var mgsalert2 = 'Added Successfully!';
                var n = noty({
                    text: mgsalert2,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 3000,
                });
                Util.hideProgressInd();
                cpm.enggCrm.renewal.courseSubjectReload(renewal_id);

            });
        }
    });


          $(".renewalDetailRow input[name='quaterly']").livequery('click', function(e){
        var title   = $(this).val();
        var cboxObj   = $(this);
        var cbObj = $('input[type=checkbox]');
        var checked = cbObj.is(":checked") ? true : false;
        var url = 'index.php?module=enggCrm_renewal&_spAction=addQuaterly&showHTML=0';
       var parent       = $(this).closest(".classSubjectCheckbox1");
        var urldelete = 'index.php?module=enggCrm_renewal&_spAction=deleteQuaterly&showHTML=0';
        var renewal_chechlist_history_id = $("input[name='renewal_chechlist_history_id']", parent).val();
        var renewal_id = $("input[name='renewal_id']").val();
        Util.showProgressInd();
        if (!cboxObj.attr('checked')){
            $.get(urldelete,{renewal_id:renewal_id, renewal_chechlist_history_id:renewal_chechlist_history_id}, function(){
                //Util.alert('Removed Successfully!');
                var mgsalert2 = 'Removed Successfully!';
                var n = noty({
                    text: mgsalert2,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 3000,
                });
                Util.hideProgressInd();
                cpm.enggCrm.renewal.courseSubjectReload(renewal_id);
            });
        } else {
            $.get(url,{renewal_id:renewal_id, renewal_chechlist_history_id:renewal_chechlist_history_id}, function(){
                //Util.alert('Added Successfully!');
                var mgsalert2 = 'Added Successfully!';
                var n = noty({
                    text: mgsalert2,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 3000,
                });
                Util.hideProgressInd();
                cpm.enggCrm.renewal.courseSubjectReload(renewal_id);

            });
        }
    });


           $(".renewalDetailRow input[name='annually']").livequery('click', function(e){
        var title   = $(this).val();
        var cboxObj   = $(this);
        var cbObj = $('input[type=checkbox]');
        var checked = cbObj.is(":checked") ? true : false;
        var url = 'index.php?module=enggCrm_renewal&_spAction=addAnually&showHTML=0';
       var parent       = $(this).closest(".classSubjectCheckbox1");
        var urldelete = 'index.php?module=enggCrm_renewal&_spAction=deleteAnually&showHTML=0';
        var renewal_chechlist_history_id = $("input[name='renewal_chechlist_history_id']", parent).val();
        var renewal_id = $("input[name='renewal_id']").val();
        Util.showProgressInd();
        if (!cboxObj.attr('checked')){
            $.get(urldelete,{renewal_id:renewal_id, renewal_chechlist_history_id:renewal_chechlist_history_id}, function(){
                //Util.alert('Removed Successfully!');
                var mgsalert2 = 'Removed Successfully!';
                var n = noty({
                    text: mgsalert2,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 3000,
                });
                Util.hideProgressInd();
                cpm.enggCrm.renewal.courseSubjectReload(renewal_id);
            });
        } else {
            $.get(url,{renewal_id:renewal_id, renewal_chechlist_history_id:renewal_chechlist_history_id}, function(){
                //Util.alert('Added Successfully!');
                var mgsalert2 = 'Added Successfully!';
                var n = noty({
                    text: mgsalert2,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 3000,
                });
                Util.hideProgressInd();
                cpm.enggCrm.renewal.courseSubjectReload(renewal_id);

            });
        }
    });





            $(".renewalDetailRow input[name='remarks']").livequery('change', function(e){
        var url = 'index.php?module=enggCrm_renewal&_spAction=addRemarks&showHTML=0';
       var parent       = $(this).closest(".classSubjectCheckbox1");
        var renewal_chechlist_history_id = $("input[name='renewal_chechlist_history_id']", parent).val();
        var remarks = $("input[name='remarks']", parent).val();
        var renewal_id = $("input[name='renewal_id']").val();
        Util.showProgressInd();
        
        
            $.get(url,{renewal_id:renewal_id,remarks:remarks, renewal_chechlist_history_id:renewal_chechlist_history_id}, function(){
                //Util.alert('Added Successfully!');
                var mgsalert2 = 'Added Successfully!';
                var n = noty({
                    text: mgsalert2,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 3000,
                });
                Util.hideProgressInd();
                cpm.enggCrm.renewal.courseSubjectReload(renewal_id);

            });
        
    });



        $("a.addRenewalDate").livequery('click', function (e){
            var renewal_id = $(this).attr('renewal_id');

            var url = 'index.php?_topRm=main&module=enggCrm_renewal&_spAction=addRenewalDate'
                    + '&showHTML=0&renewal_id='+renewal_id;
            var exp = {
                url: url
               ,callbackOnSuccess: function(){
                    Util.closeAllDialogs();

                    var mgsalert = 'Insurance added successfully!';
                    var n = noty({
                        text: mgsalert,
                        type: 'confirm',
                        dismissQueue: true,
                        layout: 'topCenter',
                        theme: 'defaultTheme',
                        timeout: 5000,
                    });
                }
            };

            Util.openFormInDialog.call(this, 'actualChargeForm', 'Add Insurance', 550, 500, exp);
        });

        $("a.newContactLink").livequery('click', function (e){
            //alert(urlNew);
            var company_id = $('select[name=company_id]').val();
            var url = $(this).attr('link');
            var urlNew = 'index.php?_spAction=new&lnkRoom=enggCrm_contactLink&showHTML=0&company_id=' + company_id;

            $(this).attr('link', urlNew);
            

        });

        $('select#fld_company_id').change(function() {
            var company_id = $(this).val();

            var url = 'index.php?module=enggCrm_contact&_spAction=contactByCompanyJSON&showHTML=0';
            $.get(url, {company_id: company_id}, function (data) {
                $('#fld_contact_id').cp_loadSelect(data);
            }, 'json');

            var url = $('a.newContactLink').attr('link');
            var urlNew = 'index.php?_spAction=new&lnkRoom=enggCrm_contactLink&showHTML=0&company_id=' + company_id;
            $('a.newContactLink').attr('link', urlNew);
        });


        $("a.addService").livequery('click', function (e){
            var renewal_id = $(this).attr('renewal_id');

            var url = 'index.php?_topRm=main&module=enggCrm_renewal&_spAction=addService'
                    + '&showHTML=0&renewal_id='+renewal_id;
            var exp = {
                url: url
               ,callbackOnSuccess: function(){
                    Util.closeAllDialogs();

                    var mgsalert = 'Service added successfully!';
                    var n = noty({
                        text: mgsalert,
                        type: 'confirm',
                        dismissQueue: true,
                        layout: 'topCenter',
                        theme: 'defaultTheme',
                        timeout: 5000,
                    });
                }
            };

            Util.openFormInDialog.call(this, 'actualChargeForm', 'Add Service', 550, 500, exp);
        });

	}
}

cpm.enggCrm.renewal.loadContactsByCompany = function(){
    var url = 'index.php?module=enggCrm_contact&_spAction=contactByCompanyJSON&showHTML=0';
    var company_id = $('select[name=company_id]').val();
    $.get(url, {company_id: company_id}, function (data) {
        $('#fld_contact_id').cp_loadSelect(data);
    }, 'json');
}

cpm.enggCrm.renewal.loadCompany = function(){
    var url = 'index.php?module=enggCrm_renewal&_spAction=newCompanyJSON&showHTML=0';
    $.get(url, function (data) {
        $('#fld_company_id').cp_loadSelect(data);
    }, 'json');
}

cpm.enggCrm.renewal.afterNewCompany = function(data){
    Util.closeAllDialogs();
    cpm.enggCrm.renewal.loadCompany();
    var mgsalert = 'New company successfully created!';
    var n = noty({
        text: mgsalert,
        type: 'confirm',
        dismissQueue: true,
        layout: 'topCenter',
        theme: 'defaultTheme',
        timeout: 5000,
    });
}

cpm.enggCrm.renewal.afterNewContact = function(){
    Util.closeAllDialogs();
    cpm.enggCrm.renewal.loadContactsByCompany();
    var mgsalert = 'New contact successfully created!';
    var n = noty({
        text: mgsalert,
        type: 'confirm',
        dismissQueue: true,
        layout: 'topCenter',
        theme: 'defaultTheme',
        timeout: 5000,
    });
}