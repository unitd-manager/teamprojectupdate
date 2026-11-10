Util.createCPObject('cpm.ek.bookPage');

cpm.ek.bookPage.init = function(){
    $('#frmEdit select#fld_book_id').livequery('change', function(){
        Util.loadDropdownByJSON('book_id', $(this).val(), 'fld_book_chapter_id', 'ek_bookChapter');
    });
}