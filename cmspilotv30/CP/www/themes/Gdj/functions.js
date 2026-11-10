Util.createCPObject('cpt.gdj');

cpt.gdj.init = function(){
    $(function(){
        $('.productList ul li:nth-child(4n)').css('margin-right', 0);
        $('.diamondList table.thinlist tr:even').addClass('even');
    });
}
