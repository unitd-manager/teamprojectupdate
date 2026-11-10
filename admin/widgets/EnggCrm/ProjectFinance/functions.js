$(function(){

	  $('#addFinanceProjects').livequery('click', function(){

        var project_id = $("#record_id").val();
        msg = "Do you like to Add Finance Order?";

        if (!confirm(msg)){
            return false;
        }
        else{
            Util.showProgressInd();
            var project_id = $(this).attr('project_id');
                        var quote_id = $(this).attr('quote_id');


            var url = 'index.php?widget=enggCrm_projectFinance&_spAction=GenerateOrderRecords&showHTML=0&project_id=' + project_id + '&quote_id=' + quote_id;

            $.get(url, {project_id: project_id,quote_id: quote_id}, function(html){
                //alert('Quote Record Created Successfully');
                var mgsalert = 'Finance order record created successfully!';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 5000,
                });
                //$('#addQuoteProject').hide();
                window.location.reload(true);
                //projectFinance.reloadQuotePortal(project_id);
            });
            //Util.hideProgressInd();
        }
    });

});

var projectFinance = {

	 reloadQuotePortal: function(project_id){
        var url = 'index.php?widget=enggCrm_projectFinance&_spAction=InvoiceReceiptPortalDetails&showHTML=0';

        Util.showProgressInd();
        $.get(url, {project_id: project_id}, function(html){
            Util.hideProgressInd();
            $('#invoiceReceiptPortalDisplayDiv').html(html);
        });
    }
}