<?
class CP_Admin_Modules_ManPower_Candidate_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('manPower_candidate');
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('new','export')
           ,'relatedTables' => array('media')
           ,'titleField'    => "CONCAT_WS(' ', first_name, last_name)"
           ,'actBtnsEdit'   => array('save', 'apply', 'delete')
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_candidate', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));

       //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_candidate', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));

       //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_candidate', 'attachment1', 'attachment1');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_candidate', 'attachment2', 'attachment2');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_candidate', 'attachment3', 'attachment3');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_candidate', 'attachment4', 'attachment4');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_candidate', 'attachment5', 'attachment5');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_candidate', 'attachment6', 'attachment6');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_candidate', 'attachment7', 'attachment7');

        $mediaArr->registerMedia($mediaObj, array(
        ));

    }
    
    /**
     *
     */
    function setLinksArray($inst) {

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('manPower_candidate', 'project_companyLink');

        $inst->registerLinksArray($linkObj, array(
            'displayTitleFieldName' => 'a.company_name'
           ,'historyTableName'      => 'candidate'
           ,'linkMultiple'          => 0
           ,'keyFieldForHistory'    => 'company_id'
        ));
    }
}