Util.createCPObject('cpm.hms.labsSupplier');

cpm.hms.labsSupplier = {
    init: function(){
		$('#AddSupplierCategory').livequery('click', function (e){
            var title = "Supplier Category Link";
            var labs_supplier_id = $(this).attr('labs_supplier_id');
            e.preventDefault();

            var exp = {
                onCloseFn: function(){
                	Util.showProgressInd();
                    cpm.hms.labsSupplier.reloadCategoryPortal(labs_supplier_id);
                }
            }
             Util.openDialogForLink.call(this, title, 500, 500, true, exp);
        });

        /* Add Category */
        $('.addCategoryToSupplier').live('click', function (e){
            var labs_suppliercategory_id = $(this).attr('labs_suppliercategory_id');
            var labs_supplier_id         = $(this).attr('labs_supplier_id');
            var removeCategory           = 0;
            var url = 'index.php?module=hms_labsSupplier&_spAction=AddRemoveSupplierCategoryLink'
                    + '&showHTML=0';
            Util.showProgressInd();
            $.get(url, {labs_suppliercategory_id:labs_suppliercategory_id, labs_supplier_id:labs_supplier_id} ,function(html){
                cpm.hms.labsSupplier.reloadAddCategoryLink(labs_supplier_id);
            });
        });

        /* Add Category */
        $('.removeCategoryToSupplier').live('click', function (e){
            var labs_suppliercategory_id = $(this).attr('labs_suppliercategory_id');
            var labs_supplier_id         = $(this).attr('labs_supplier_id');
            var removeCategory           = 1;
            var url = 'index.php?module=hms_labsSupplier&_spAction=AddRemoveSupplierCategoryLink'
                    + '&showHTML=0';
            Util.showProgressInd();
            $.get(url, {labs_suppliercategory_id:labs_suppliercategory_id, labs_supplier_id:labs_supplier_id, removeCategory:removeCategory} ,function(html){
                cpm.hms.labsSupplier.reloadAddCategoryLink(labs_supplier_id);
            });
        });

        $('.addCategoryAll').live('click', function (e){
            var labs_supplier_id         = $(this).attr('labs_supplier_id');
            var removeCategory           = 0;
            var url = 'index.php?module=hms_labsSupplier&_spAction=AddRemoveCategoryAll'
                    + '&showHTML=0';
            Util.showProgressInd();
            $.get(url, {labs_supplier_id:labs_supplier_id, removeCategory:removeCategory} ,function(html){
                cpm.hms.labsSupplier.reloadAddCategoryLink(labs_supplier_id);
            });
        });

        $('.removeCategoryAll').live('click', function (e){
            var labs_supplier_id         = $(this).attr('labs_supplier_id');
            var removeCategory           = 1;
            var url = 'index.php?module=hms_labsSupplier&_spAction=AddRemoveCategoryAll'
                    + '&showHTML=0';
            Util.showProgressInd();
            $.get(url, {labs_supplier_id:labs_supplier_id, removeCategory:removeCategory} ,function(html){
                cpm.hms.labsSupplier.reloadAddCategoryLink(labs_supplier_id);
            });
        });
	},

	reloadAddCategoryLink: function(labs_supplier_id){
        var url = 'index.php?module=hms_labsSupplier&_spAction=AddCategoryLinkPortal&showHTML=0';
        $.get(url, {labs_supplier_id: labs_supplier_id}, function(html){
            $('#AddSupplierCategoryLinkTable').html(html);
            Util.hideProgressInd();
        });
    },

    reloadCategoryPortal: function(labs_supplier_id){
        var url = 'index.php?module=hms_labsSupplier&_spAction=AddCategory&showHTML=0';
        $.get(url, {labs_supplier_id: labs_supplier_id}, function(html){
            $('#categoryLinkPortal').html(html);
            Util.hideProgressInd();
            Util.closeAllDialogs();
        });
    },
}

