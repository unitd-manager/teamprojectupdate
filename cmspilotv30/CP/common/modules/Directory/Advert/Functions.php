<?
class CP_Common_Modules_Directory_Advert_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        
        $modObj = $modules->getModuleObj('directory_advert');
        $modules->registerModule($modObj, array(
        	 'hasFlagInList' => 0
            ,'title' => 'Advertiser'
            ,'keyField' => 'advert_id'
            ,'actBtnsList' => array('new', 'export')
        ));
    }

    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('directory_advert', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
}