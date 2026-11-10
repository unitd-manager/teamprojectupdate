<?
class CP_Common_Modules_Wine_Producer_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('wine_producer');
        $modules->registerModule($modObj, array(
            'hasMultiLang' => 1
           ,'actBtnsList'   => array('import', 'new','deleteList')
//           ,'actBtnsList'   => array('new','deleteList')
        ));
    }
    
    /**
     *
     */
    function setMediaArray($mediaArr) {

        $mediaObj = $mediaArr->getMediaObj('wine_producer', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }    
}