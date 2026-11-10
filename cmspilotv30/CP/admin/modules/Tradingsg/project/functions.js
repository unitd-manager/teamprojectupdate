Util.createCPObject('cpm.tradingsg.project');

cpm.tradingsg.project = {
	$('#AddProjectPrice').live('click', function (e){
                var title = "Add Project Price";
                var project_id = $(this).attr('project_id');
                e.preventDefault();
                var expObj = {
                    validate: true
                   ,callbackOnSuccess: function(){
                        var msg = 'Amount Added Successfully';
                        Util.alert(msg, function(){
                            Util.closeAllDialogs();
                            cpm.tradingsg.project.reloadProjectPriceLink(project_id);
                        });
                    }
                }
            Util.openFormInDialog.call(this, 'AddProjectPriceForm', title, 550, 482, expObj);
        });
		
      reloadProjectPriceLink: function(project_id){
        var url = 'index.php?module=tradingsg_project&_spAction=ProjectPriceDetail&showHTML=0';
        $.get(url, {project_id: project_id}, function(html){
            $('#projectPriceLinkPortal').html(html);
        });
    }
	
	
    init: function(){
        $('.click-all-top .check-all').livequery('click', function(e){
            e.preventDefault();
            cpm.tradingsg.project.checkAllCol.call(this);
        });

        $('.click-all-top .uncheck-all').livequery('click', function(e){
            e.preventDefault();
            cpm.tradingsg.project.uncheckAllCol.call(this);
        });
    },

    checkAllCol: function(e){
        var colPos = $(this).parent().index();
        $('.room-project-table tbody tr').each(function(rowIndex, trObj) {
            var checkbox = $(trObj).find('td:eq(' + colPos + ') input');
            checkbox.attr('checked', 'checked');
        });
        cpm.tradingsg.order.updateInvoiceAmount();
    },

    uncheckAllCol: function(e){
        var colPos = $(this).parent().index();
        $('.room-order-table tbody tr').each(function(rowIndex, trObj) {
            var checkbox = $(trObj).find('td:eq(' + colPos + ') input');
            checkbox.removeAttr('checked');
        });
        $('.invoiceForm input[id=fld_invoice_amount]').val(0);
    }


	 
	
	 
}





