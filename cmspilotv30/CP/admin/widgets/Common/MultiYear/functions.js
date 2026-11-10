Util.createCPObject('cpw.common.multiYear');

cpw.common.multiYear = {

    init: function(){
       $('.w-common-multiYear select[name=cp_year]').change(this.changeYear);
    },

    changeYear: function() {
        var url = 'index.php?widget=common_multiYear&_spAction=changeYear&showHTML=0';
        var cp_year = $(this).val();
        $.get(url, {cp_year: cp_year}, function(){
            var topRm = $('#cpTopRm').val();
            var cpRoom = $('#cpRoom').val();
            var urlRedirect = "index.php?_topRm=" + topRm +
                      "&module=" + cpRoom;
            document.location = urlRedirect;
        })
    }


}
