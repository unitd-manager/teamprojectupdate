<?
class CP_Admin_Modules_Ek_BookChapter_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('ek_bookChapter');
        $modObj['tableName'] = 'book_chapter';
        $modObj['keyField']  = 'book_chapter_id';
        $modules->registerModule($modObj, array(
            'title'         => 'Book Chapter'
        ));

    }
    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('ek_bookChapter', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
}