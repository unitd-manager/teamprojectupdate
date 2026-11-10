Util.createCPObject('cpw.ui.tree');

cpw.ui.tree.run = function(exp){
    var handle = exp.handle;
    var treeLoaded = false;
            
    $('#' + handle + ' li a').each(function(){
        var href = $(this).attr('href');
        $(this).closest('li').attr('data', "url: '" + href + "'");
    });

    $('#' + handle).dynatree({
        persist: true,
        
        onPostInit: function(isReloading, isError) {
            this.reactivate();
        },
        
        onActivate: function(node) {
            // Use status functions to find out about the calling context
            var isInitializing = node.tree.isInitializing(); // Tree loading phase
            var isReloading = node.tree.isReloading(); // Loading phase, and reading status from cookies
            
            if (!isReloading && !isInitializing){
		        document.location = node.data.url;
            }
        }
    });
}
