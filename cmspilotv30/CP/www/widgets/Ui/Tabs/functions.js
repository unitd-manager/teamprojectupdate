Util.createCPObject('cpw.ui.tabs');

cpw.ui.tabs.run = function(exp){
    var handle = exp.handle;
    var panesId = exp.panesId;
	$('ul#' + handle).tabs('div#' + panesId + ' > div', {
	     effect: 'fade'
    });
}
