Util.createCPObject('cpw.common.site');

cpw.common.site.init = function(){
    $(function () {
        $('.site_menu .title').click(function () {
            $('.site_menu ul').slideToggle('medium');
        });

        $('.w-common-site .site_menu ul li a').click(function (e) {
            e.preventDefault();
            cid = $(this).attr('cid');
            var url = $(this).attr('href');

            var url2 = '/index.php?widget=common_site&_spAction=siteIdInSession&showHTML=0';
            $.get(url2, {cp_site_id: cid}, function(){
                document.location = url;
            })
        });
    });
}
