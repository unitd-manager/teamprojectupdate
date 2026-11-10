<?
class CPL_Admin_Modules_EnggCrm_Employee_Functions Extends CP_Admin_Modules_EnggCrm_Employee_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('enggCrm_employee');
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('new', 'import')
           ,'actBtnsEdit'   => array('save')
           ,'relatedTables' => array('media')
           ,'titleField'    => "CONCAT_WS(' ', first_name, last_name)"
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('enggCrm_employee', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        $mediaObj = $mediaArr->getMediaObj('enggCrm_employee', 'workPermit', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        $mediaObj = $mediaArr->getMediaObj('enggCrm_employee', 'wsq', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
}