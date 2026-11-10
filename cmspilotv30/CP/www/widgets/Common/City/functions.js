Util.createCPObject('cpw.common.city');

cpw.common.city.init = function(){
    $(function () {
        $('.city_menu .title').click(function () {
            $('.city_menu ul').slideToggle('medium');
        });

        $('.w-common-city .city_menu ul li a').click(function (e) {
            e.preventDefault();
            cid = $(this).attr('cid');
            var url = $(this).attr('href');

            var url2 = '/index.php?widget=common_city&_spAction=cityIdInSession&showHTML=0';
            $.get(url2, {cp_country_id: cid}, function(){
                document.location = url;
            })
        });
    });
}
