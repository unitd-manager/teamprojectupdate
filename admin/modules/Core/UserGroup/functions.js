Util.createCPObject('cpm.core.userGroup');

cpm.core.userGroup = {
    init: function(){
        $('.click-all-top .check-all').click(cpm.core.userGroup.checkAllCol);
        $('.click-all-top .uncheck-all').click(cpm.core.userGroup.uncheckAllCol);
        $('.click-all-side .check-all').click(cpm.core.userGroup.checkAllRow);
        $('.click-all-side .uncheck-all').click(cpm.core.userGroup.uncheckAllRow);
    },
    
    checkAllCol: function(e){
        e.preventDefault();
        var colPos = $(this).parent().index();
        $('.room-actions-table tr').each(function(rowIndex, trObj) {
            if (rowIndex > 1) {
                var checkbox = $(trObj).find('td:eq(' + colPos + ') input');
                checkbox.attr('checked', 'checked'); 
            }
        });
    },
    
    uncheckAllCol: function(e){
        e.preventDefault();
        var colPos = $(this).parent().index();
        $('.room-actions-table tr').each(function(rowIndex, trObj) {
            if (rowIndex > 1) {
                var checkbox = $(trObj).find('td:eq(' + colPos + ') input');
                checkbox.removeAttr('checked'); 
            }
        });
    },

    checkAllRow: function(e){
        e.preventDefault();
        var rowPos = $(this).parent().parent().index();
        $('.room-actions-table tr:eq(' + rowPos + ') td input').attr('checked', 'checked'); 
    },

    uncheckAllRow: function(e){
        e.preventDefault();
        var rowPos = $(this).parent().parent().index();
        $('.room-actions-table tr:eq(' + rowPos + ') td input').removeAttr('checked'); 
    },

}
