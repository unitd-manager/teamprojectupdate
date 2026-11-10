<?
class CP_Admin_Modules_Ek_BookPage_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('ek_bookPage');
        $modObj['tableName'] = 'book_page';
        $modObj['keyField']  = 'book_page_id';
        $modules->registerModule($modObj, array(
            'title'         => 'Book Page'
        ));

    }
    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('ek_bookPage', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
}