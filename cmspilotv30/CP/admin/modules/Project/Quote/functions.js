Util.createCPObject('cpm.project.quote');

cpm.project.quote.init = function(){
    $("a.arrowRight").livequery('click', function (e){
        e.preventDefault();
        $(this).toggleClass("arrowDown");
        var parent = $(this).closest('.quote');
        var nextTR = $(parent).next();
        $('.quoteCategories', nextTR).slideToggle();
    });
    
    /* Creation of Quote */

    $("a#raiseNewQuote").livequery('click', function (e){
        e.preventDefault();
        var expObj = {
            validate: true,
            callbackOnSuccess: function(){
                $('#dialog').dialog('close');
                Quote.reloadQuotesPortal();
            }
        }
        Util.openFormInDialog.call(this, 'quoteForm', 'Raise New Quote', 800, 600, expObj);
    });

    $("a#raiseNewQuoteFromTemplate").livequery('click', function (e){
        e.preventDefault();
        var expObj = {
            validate: true,
            callbackOnSuccess: function(){
                $('#dialog').dialog('close');
                Quote.reloadQuotesPortal();
            }
        }
        Util.openFormInDialog.call(this, 'quoteForm', 'Raise New Quote From Template', 400, 200, expObj);
    });

    $("#fld_quote_type").livequery('change', function (){
        var selVal = $(this).val();
        
        if (selVal == 'other $'){
            $('#fld_currency_item').parent().show();
        } else {
            $('#fld_currency_item').parent().hide();
        }
    });

    $("a.editQuote").livequery('click', function (e){
        e.preventDefault();
        Globals.currentQuoteID = $(this).closest('tr.quote').attr('quote_id');
        var expObj = {
            validate: true,
            callbackOnSuccess: function(json){
                if(json.returnText.refreshPage){
                    window.location.reload();
                } else {
                    $('#dialog').dialog('close');
                    Quote.reloadQuotesPortal();
                }
            }
        }

        Util.openFormInDialog.call(this, 'quoteForm', "Edit Quote", 800, 600, expObj);
    });

    $("a.deleteQuote").livequery('click', function (e){
        e.preventDefault();
        var url = $(this).attr('href');
        var msg = "Are you sure to delete this quote?";
        if (confirm(msg)){
            Util.showProgressInd();
            $.get(url, function(){
                Quote.reloadQuotesPortal();
                Util.hideProgressInd();
            });
        }
    });

    $("a.duplicateQuote").livequery('click', function (e){
        e.preventDefault();
        var url = $(this).attr('href');
        var msg = "Are you sure to duplicate this quote?";
        if (confirm(msg)){
            Util.showProgressInd();
            $.get(url, function(){
                Quote.reloadQuotesPortal();
                Util.hideProgressInd();
            });
        }
    });

    $("a.printQuote").livequery('click', function (e){
        e.preventDefault();
        Util.openDialogForLink.call(this, 'Print Quote Options', 450, 450);
    });

    /* Creation of Quote Category */

    $("a.addQuoteCat").livequery('click', function (e){
        e.preventDefault();
        Globals.currentQuoteID = $(this).closest('tr.quote').attr('quote_id');
        var expObj = {
            validate: true,
            callbackOnSuccess: function(){
                $('#dialog').dialog('close');
                Quote.reloadQuotesPortal();
            }
        }
        Util.openFormInDialog.call(this, 'quoteForm', "New Quote Category", 700, 300, expObj);

    });

    $("a.editCategory").livequery('click', function (e){
        e.preventDefault();
        Globals.currentQuoteID = $(this).closest('div.quoteCategories').attr('quote_id');

        var expObj = {
            validate: true,
            callbackOnSuccess: function(){
                $('#dialog').dialog('close');
                Quote.reloadQuotesPortal();
            }
        }
        Util.openFormInDialog.call(this, 'quoteForm', "Edit Category", 700, 300, expObj);
    });

    $("a.deleteCategory").livequery('click', function (e){
        e.preventDefault();
        Globals.currentQuoteID = $(this).closest('div.quoteCategories').attr('quote_id');
        var url = $(this).attr('href');
        var msg = "Are you sure to delete this category?";
        if (confirm(msg)){
            Util.showProgressInd();
            $.get(url, function(){
                Quote.reloadQuotesPortal();
                Util.hideProgressInd();
            });
        }
    });

    /* Creation of Quote Item */

    $("a.addLineItem").livequery('click', function (e){
        e.preventDefault();
        Globals.currentQuoteID = $(this).closest('div.quoteCategories').attr('quote_id');

        var expObj = {
            validate: true,
            callbackOnSuccess: function(){
                $('#dialog').dialog('close');
                Quote.reloadQuotesPortal();
            }
        }
        Util.openFormInDialog.call(this, 'quoteForm', "New Item", 700, 350, expObj);
    });

    $("a.editLineItem").livequery('click', function (e){
        e.preventDefault();
        Globals.currentQuoteID = $(this).closest('div.quoteCategories').attr('quote_id');

        var expObj = {
            validate: true,
            callbackOnSuccess: function(){
                $('#dialog').dialog('close');
                Quote.reloadQuotesPortal();
            }
        }
        Util.openFormInDialog.call(this, 'quoteForm', "Edit Item", 700, 350, expObj);
    });

    $("a.deleteLineItem").livequery('click', function (e){
        e.preventDefault();
        Globals.currentQuoteID = $(this).closest('div.quoteCategories').attr('quote_id');
        var url = $(this).attr('href');
        var msg = "Are you sure to delete this item?";
        if (confirm(msg)){
            Util.showProgressInd();
            $.get(url, function(){
                Quote.reloadQuotesPortal();
                Util.hideProgressInd();
            });
        }
    });
}

Globals.currentQuoteID = 0;

var Quote = {
    reloadQuotesPortal: function(){
        var section_name   = $('#cpRoom').val();

        if (section_name == 'project_project'){
            var recId   = $('#project_id').val();
            var recType = 'proj';
        } else if (section_name == 'project_quoteTemplate'){
            var recId   = $('#record_id').val();
            var recType = 'quoteTemplate';
        } else {
            var recId = $('#opportunity_id').val();
            var recType = 'opp';
        }
        
        url = "index.php?module=project_quote&_spAction=quotesPortal&recType=" + recType + "&recId=" + recId + "&showHTML=0";

        $.get(url, function(data){
            $('#quotesOuter').hide();
            $('#quotesOuter').html(data);
            $('#quotesOuter').slideDown('2000');
            var quote_id = Globals.currentQuoteID;
            if (quote_id > 0){
                $("td a.arrowRight', 'tr[quote_id=' + quote_id + ']'").livequery(function(e){
                    $(this).toggleClass("arrowDown");
                    var parent = $(this).closest('.quote');
                    var nextTR = $(parent).next();
                    $('.quoteCategories', nextTR).slideDown(2000);
                });
            }

            if(section_name == 'project'){
                Quote.reloadProjectTotals();
            } else {
                url = "index.php?module=project_quote&_spAction=confirmedQuoteValue&opportunity_id=" + recId + "&showHTML=0";
                $.get(url, {}, function(data){
                    //$('#confirmed_quote_price').html(data.total_value);
                    Util.hideProgressInd();
                }, 'json');
            }
        });
    },

    reloadProjectTotals: function(){
        var project_id = $('#record_id').val();
        url = "index.php?module=project_project&_spAction=projectValuesTable&project_id=" + project_id + "&showHTML=0";
        $.get(url, {}, function(data){
            $('#projectValues').hide();
            $('#projectValues').html(data);
            $('#projectValues').slideDown(2000);
            Util.hideProgressInd();
       });
   }

}