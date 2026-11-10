Util.createCPObject('cpw.common.country');

cpw.common.country.init = function(){
    $(function () {
        $('.country_menu .title').click(function () {
    	$('.country_menu ul').slideToggle('medium');
        });

        $('.w-common-country .country_menu ul li a').click(function (e) {
            e.preventDefault();
            cid = $(this).attr('cid');
            var url = $(this).attr('href');
            
            var url2 = '/index.php?widget=common_country&_spAction=countryIdInSession&showHTML=0';
            $.get(url2, {cp_country_id: cid}, function(){
                document.location = url;
            })
        });
    });
}
