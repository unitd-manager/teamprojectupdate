<?
class CP_Admin_Modules_ManPower_OpportunityCandidate_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('manPower_opportunityCandidate');
        $modObj['tableName'] = 'opportunity_candidate';
        $modObj['keyField']  = 'opportunity_candidate_id';
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array()
           ,'title'         => 'Opp/Candidate'
           ,'actBtnsEdit'   => array('save', 'apply', 'delete')
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_opportunityCandidate', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));

       //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_opportunityCandidate', 'attachment1', 'attachment1');

        $mediaArr->registerMedia($mediaObj, array(
        ));

       //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_opportunityCandidate', 'attachment2', 'attachment2');

        $mediaArr->registerMedia($mediaObj, array(
        ));

       //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_opportunityCandidate', 'attachment3', 'attachment3');

        $mediaArr->registerMedia($mediaObj, array(
        ));

       //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_opportunityCandidate', 'attachment4', 'attachment4');

        $mediaArr->registerMedia($mediaObj, array(
        ));

       //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_opportunityCandidate', 'attachment5', 'attachment5');

        $mediaArr->registerMedia($mediaObj, array(
        ));

       //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_opportunityCandidate', 'attachment6', 'attachment6');

        $mediaArr->registerMedia($mediaObj, array(
        ));

       //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_opportunityCandidate', 'attachment7', 'attachment7');

        $mediaArr->registerMedia($mediaObj, array(
        ));

       //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_opportunityCandidate', 'attachment8', 'attachment8');

        $mediaArr->registerMedia($mediaObj, array(
        ));

       //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_opportunityCandidate', 'attachment9', 'attachment9');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
    
}