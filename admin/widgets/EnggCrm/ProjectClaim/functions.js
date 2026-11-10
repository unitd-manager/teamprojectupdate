$(function(){
        $('a.editForClaim').livequery('click', function (e){
            var title      = "Edit Claim Display";
            var project_id = $('#record_id').val();

            e.preventDefault();
            var expObj = {
                validate: true,
                callbackOnSuccess: function(data){
                    Util.closeAllDialogs();
                    var mgsalert = 'Updated claim successfully!';
                    var n = noty({
                        text: mgsalert,
                        type: 'confirm',
                        dismissQueue: true,
                        layout: 'topCenter',
                        theme: 'defaultTheme',
                        timeout: 5000,
                    });

                    projectClaim.reloadClaimPortal(project_id);
                }
            }

            Util.openFormInDialog.call(this, 'editForClaim', title, 800, 500, expObj);
        });

        $("a.addMultipleClaimItem").livequery('click', function (e){
            var title            = "Add Line Item";
            var project_id       = $(this).attr('project_id');
            var project_claim_id = $(this).attr('project_claim_id');

            var url = 'index.php?widget=enggCrm_projectClaim&_spAction=addMultipleClaimItem'
                    + '&showHTML=0&project_id=' + project_id + '&project_claim_id=' + project_claim_id;

            var exp = {
                url: url
               ,callbackOnSuccess: function(){
                    Util.closeAllDialogs();
                    var mgsalert = 'Progress claim items created successfully!';
                    var n = noty({
                        text: mgsalert,
                        type: 'confirm',
                        dismissQueue: true,
                        layout: 'topCenter',
                        theme: 'defaultTheme',
                        timeout: 5000,
                    });

                    projectClaim.reloadClaimPortal(project_id);
                }
            };

            Util.openFormInDialog.call(this, 'addMultipleClaimItemForm', title, 900, 500, exp);
        });

        $("a.editClaimLineItem").livequery('click', function (e){
            var title            = "Edit Line Item";
            var project_id       = $(this).attr('project_id');
            var project_claim_id = $(this).attr('project_claim_id');

            var url = 'index.php?widget=enggCrm_projectClaim&_spAction=editClaimLineItem'
                    + '&showHTML=0&project_id=' + project_id + '&project_claim_id=' + project_claim_id;

            var exp = {
                url: url
               ,callbackOnSuccess: function(){
                    Util.closeAllDialogs();
                    var mgsalert = 'Progress claim updated successfully!';
                    var n = noty({
                        text: mgsalert,
                        type: 'confirm',
                        dismissQueue: true,
                        layout: 'topCenter',
                        theme: 'defaultTheme',
                        timeout: 5000,
                    });

                    projectClaim.reloadClaimPortal(project_id);
                }
            };

            Util.openFormInDialog.call(this, 'editMultipleClaimItemForm', title, 900, 500, exp);
        });

        $("a.editClaimPaymentLineItem").livequery('click', function (e){
            var title            = "Edit Line Item";
            var project_id       = $(this).attr('project_id');
            var project_claim_id = $(this).attr('project_claim_id');
            var claim_seq        = $(this).attr('claim_seq');

            var url = 'index.php?widget=enggCrm_projectClaim&_spAction=editClaimPaymentLineItem'
                    + '&showHTML=0&project_id=' + project_id + '&project_claim_id=' + project_claim_id + '&claim_seq=' + claim_seq;

            var exp = {
                url: url
               ,callbackOnSuccess: function(){
                    Util.closeAllDialogs();
                    var mgsalert = 'Progress claim items updated successfully!';
                    var n = noty({
                        text: mgsalert,
                        type: 'confirm',
                        dismissQueue: true,
                        layout: 'topCenter',
                        theme: 'defaultTheme',
                        timeout: 5000,
                    });

                    projectClaim.reloadClaimPortal(project_id);
                }
            };

            Util.openFormInDialog.call(this, 'editMultipleClaimPaymentItemForm', title, 900, 500, exp);
        });

        $("a.addNewClaim").livequery('click', function (e){
            var title            = "Add New Claim";
            var project_id       = $(this).attr('project_id');
            var project_claim_id = $(this).attr('project_claim_id');

            var url = 'index.php?widget=enggCrm_projectClaim&_spAction=addClaimPaymentLineItem'
                    + '&showHTML=0&project_id=' + project_id + '&project_claim_id=' + project_claim_id;

            var exp = {
                url: url
               ,callbackOnSuccess: function(){
                    Util.closeAllDialogs();
                    var mgsalert = 'Progress claim created successfully!';
                    var n = noty({
                        text: mgsalert,
                        type: 'confirm',
                        dismissQueue: true,
                        layout: 'topCenter',
                        theme: 'defaultTheme',
                        timeout: 5000,
                    });

                    projectClaim.reloadClaimPortal(project_id);
                }
            };

            Util.openFormInDialog.call(this, 'addMultipleClaimPaymentItemForm', title, 900, 600, exp);
        });

        $(".addMultipleClaimItemForm input.thisMonthAmount").livequery('change', function (e){
            var thisMonthAmount  = $(this).val();
            var parent           = $(this).closest('tr');
            var claimAmount      = $("input.claimAmount", parent).val();
            var balanceAmount    = parseFloat(0);

            if(parseFloat(thisMonthAmount) > parseFloat(claimAmount)) {
                Util.alert("Please enter current month claim amount less than or equal to "+parseFloat(claimAmount).toFixed(3));
                $("input.thisMonthAmount", parent).val(parseFloat(0).toFixed(3));
                $("input.thisMonthAmount", parent).focus();
                thisMonthAmount = parseFloat(0).toFixed(3);
            }
            
            if(thisMonthAmount == "NaN" || thisMonthAmount == undefined || thisMonthAmount == "") {
                thisMonthAmount = parseFloat(0);
            }
            
            balanceAmount = parseFloat(claimAmount) - parseFloat(thisMonthAmount);

            if(balanceAmount == "NaN" || balanceAmount == undefined || balanceAmount == "") {
                balanceAmount = parseFloat(0);
            }

            $("td.cumAmount", parent).html(parseFloat(thisMonthAmount).toFixed(3));
        });

        $(".addMultipleClaimItemForm input.claimAmount").livequery('change', function (e){
            var claimAmount     = $(this).val();
            var parent          = $(this).closest('tr');
            var thisMonthAmount = $("input.thisMonthAmount", parent).val();
            var balanceAmount   = parseFloat(0);

            if(parseFloat(claimAmount) < parseFloat(thisMonthAmount)) {
                Util.alert("Please enter contract amount greater or equal to "+parseFloat(thisMonthAmount).toFixed(3));
                $("input.claimAmount", parent).val(parseFloat(thisMonthAmount).toFixed(3));
                $("input.claimAmount", parent).focus();
                claimAmount = parseFloat(thisMonthAmount).toFixed(3);
            }

            if(thisMonthAmount == "NaN" || thisMonthAmount == undefined || thisMonthAmount == "") {
                thisMonthAmount = parseFloat(0);
            }

            if(claimAmount == "NaN" || claimAmount == undefined || claimAmount == "") {
                claimAmount = parseFloat(0);
            }

            balanceAmount = parseFloat(claimAmount) - parseFloat(thisMonthAmount);
            
            if(balanceAmount == "NaN" || balanceAmount == undefined || balanceAmount == "") {
                balanceAmount = parseFloat(0);
            }

            $("td.cumAmount", parent).html(parseFloat(thisMonthAmount).toFixed(3));
        });

        $(".editMultipleClaimItemForm input.claimAmount").livequery('change', function (e){
            var claimAmount             = $(this).val();
            var parent                  = $(this).closest('tr');
            var totalClaimAmount        = $("input.totalClaimAmount", parent).val();
            var overallClaimTotalAmount = $("input.overallClaimTotalAmount", parent).val();

            if(parseFloat(claimAmount) < parseFloat(overallClaimTotalAmount)) {
                Util.alert("Please enter contract amount greater or equal to "+parseFloat(overallClaimTotalAmount).toFixed(3));
                $("input.claimAmount", parent).val(parseFloat(overallClaimTotalAmount).toFixed(3));
                $("input.claimAmount", parent).focus();
            }
            
            /*if(totalClaimAmount == "NaN" || totalClaimAmount == undefined || totalClaimAmount == "") {
                totalClaimAmount = parseFloat(0);
            }

            var balanceAmount    = parseFloat(0);
            balanceAmount        = parseFloat(claimAmount) - parseFloat(totalClaimAmount);

            if(balanceAmount == "NaN" || balanceAmount == undefined || balanceAmount == "") {
                balanceAmount = parseFloat(0);
            }
            
            $("td.cumAmount", parent).html(balanceAmount.toFixed(3));*/
        });

        $(".editMultipleClaimItemForm select.claimStatusDropdown").livequery('change', function (e){
            var status                  = $(this).val();
            var parent                  = $(this).closest('tr');
            var claimAmount             = $("input.claimAmount", parent).val();
            var overallClaimTotalAmount = $("input.overallClaimTotalAmount", parent).val();
            var claimItemStatus         = $("input.claimItemStatus", parent).val();

            if(status == "Work Completed") {
                if(parseFloat(overallClaimTotalAmount) < parseFloat(claimAmount)) {
                    Util.alert("Not claimed full amount");
                    $("select.claimStatusDropdown", parent).val(claimItemStatus);
                    $("select.claimStatusDropdown", parent).focus();
                }
            }
        });

        $(".addMultipleClaimPaymentItemForm input.thisMonthAmount").livequery('change', function (e){
            var thisMonthAmount       = $(this).val();
            var parent                = $(this).closest('tr');
            var prevClaimAmount       = $("input.prevClaimAmount", parent).val();
            var balanceContractAmount = $("input.balanceAmount", parent).val();
            var cumAmount             = parseFloat(0);

            if(parseFloat(thisMonthAmount) > parseFloat(balanceContractAmount)) {
                Util.alert("Please enter current month claim amount less than or equal to "+parseFloat(balanceContractAmount).toFixed(3));
                $("input.thisMonthAmount", parent).val(parseFloat(0).toFixed(3));
                thisMonthAmount = parseFloat(0);
            }
            
            if(thisMonthAmount == "NaN" || thisMonthAmount == undefined || thisMonthAmount == "") {
                thisMonthAmount = parseFloat(0);
            }
            
            cumAmount = parseFloat(prevClaimAmount) + parseFloat(thisMonthAmount);

            if(cumAmount == "NaN" || cumAmount == undefined || cumAmount == "") {
                cumAmount = parseFloat(0);
            }

            $("td.cumAmount", parent).html(cumAmount.toFixed(3));
        });

        $(".editMultipleClaimPaymentItemForm input.thisMonthAmount").livequery('change', function (e){
            var thisMonthAmount         = $(this).val();
            var parent                  = $(this).closest('tr');
            var balanceClaimAmount      = $("input.prevClaimAmount", parent).val();
            var balancePrevAmount       = $("input.balancePrevAmount", parent).val();
            var contractAmount          = $("input.contractAmount", parent).val();
            var overallClaimAmount      = $("input.overallClaimTotalAmount", parent).val();
            var currentMonthClaimAmount = $("input.currentMonthClaimAmount", parent).val();
            var balanceAmount           = parseFloat(0);

            if(overallClaimAmount == "NaN" || overallClaimAmount == undefined || overallClaimAmount == "") {
                overallClaimAmount = parseFloat(0);
            }

            var netAmount = parseFloat(contractAmount) - parseFloat(overallClaimAmount);  
            if(parseFloat(thisMonthAmount) > parseFloat(netAmount)) {
                Util.alert("Please enter current month claim amount less than or equal to "+parseFloat(netAmount).toFixed(3));
                $("input.thisMonthAmount", parent).val(parseFloat(currentMonthClaimAmount).toFixed(3));
                thisMonthAmount = parseFloat(currentMonthClaimAmount);
            }
            
            if(thisMonthAmount == "NaN" || thisMonthAmount == undefined || thisMonthAmount == "") {
                thisMonthAmount = parseFloat(0);
            }
            
            balanceAmount = parseFloat(balanceClaimAmount) + parseFloat(thisMonthAmount);

            if(balanceAmount == "NaN" || balanceAmount == undefined || balanceAmount == "") {
                balanceAmount = parseFloat(0);
            }

            $("td.cumAmount", parent).html(balanceAmount.toFixed(3));
        });

        $("#addMultipleClaimItemForm a.addSingleClaimRow").livequery('click', function (e){
            var project_id = $(this).attr('project_id');
            var url = 'index.php?widget=enggCrm_projectClaim&_spAction=addSingleClaimRow'
                    + '&showHTML=0&project_id=' + project_id;

            $.get(url, '' ,function(html){
                $('#addMultipleClaimItemForm tr:last').after(html);
            });
        });

        $("#addMultipleClaimPaymentItemForm a.addSingleClaimRow").livequery('click', function (e){
            var project_id = $(this).attr('project_id');
            var url = 'index.php?widget=enggCrm_projectClaim&_spAction=addSingleClaimRow'
                    + '&showHTML=0&project_id=' + project_id;

            $.get(url, '' ,function(html){
                $('#addMultipleClaimPaymentItemForm tr:last').after(html);
            });
        });

        $("#editMultipleClaimItemForm a.addSingleClaimEditRow").livequery('click', function (e){
            var project_id = $(this).attr('project_id');
            var url = 'index.php?widget=enggCrm_projectClaim&_spAction=addSingleClaimEditRow'
                    + '&showHTML=0&project_id=' + project_id;

            $.get(url, '' ,function(html){
                $('#editMultipleClaimItemForm tr:last').after(html);
            });
        });

        $('.claimLayoutShow').livequery('click', function (e){
            var link_text = $(this).html();
            var parent    = $(this).closest('.claimDetailRow');

            if(link_text == 'View Line Items'){
                $('.claimLayoutShow', parent).text('Hide Line Items');
            }
            else{
                $('.claimLayoutShow', parent).text('View Line Items');
            }

            $('.showAddLineRow', parent).slideToggle();
        });

        $('.claimPaymentDetailsShow').livequery('click', function (e){
            var link_text = $(this).html();
            var parent    = $(this).closest('.claimActionsDetails');
            var parent2   = $(this).closest('tr').next('tr');

            if(link_text == 'View PC Items'){
                $('.claimPaymentDetailsShow', parent).text('Hide PC Items');
                $(parent2).removeClass('claimPaymentHide');
            }
            else {
                $('.claimPaymentDetailsShow', parent).text('View PC Items');
                $(parent2).addClass('claimPaymentHide');
            }
        });

        $("a.clearClaimItem").livequery('click', function (e){
            //var titleObj       = $(this).closest('tr').find('.claimTitle');
            //var descriptionObj = $(this).closest('tr').find('.claimItemDescription');
            //var amountObj      = $(this).closest('tr').find('.claimAmount');
            //var prevAmountObj  = $(this).closest('tr').find('.prev_amount');
            var thisAmountObj  = $(this).closest('tr').find('.thisMonthAmount');
            //var cumAmountObj   = $(this).closest('tr').find('.cumAmount');
            var remarksObj     = $(this).closest('tr').find('.claimRemarks');

            //titleObj.val('');
            //descriptionObj.val('');
            //amountObj.val('');
            //prevAmountObj.html('');
            thisAmountObj.val('');
            //cumAmountObj.html('');
            remarksObj.val('');
        });

        $('#addClaimProject').livequery('click', function(){
            var project_id = $("#record_id").val();
            msg = "Do you like to Add Claim?";

            if (!confirm(msg)){
                return false;
            }
            else{
                Util.showProgressInd();
                var project_id = $(this).attr('project_id');
                var url = 'index.php?widget=enggCrm_projectClaim&_spAction=addClaimFormSubmit&showHTML=0&id=' + project_id;
                $.get(url, {project_id: project_id}, function(html){
                    var mgsalert = 'Claim record created successfully!';
                    var n = noty({
                        text: mgsalert,
                        type: 'confirm',
                        dismissQueue: true,
                        layout: 'topCenter',
                        theme: 'defaultTheme',
                        timeout: 5000,
                    });

                    projectClaim.reloadClaimPortal(project_id);
                });
            }
        });});

var projectClaim = {
    reloadClaimPortal: function(project_id){
        var url = 'index.php?widget=enggCrm_projectClaim&_spAction=addClaimPortalListView&showHTML=0';
        Util.showProgressInd();
        $.get(url, {project_id: project_id}, function(html){
            Util.hideProgressInd();
            $('#claimLinkedPortal').html(html);
        });
    },
}