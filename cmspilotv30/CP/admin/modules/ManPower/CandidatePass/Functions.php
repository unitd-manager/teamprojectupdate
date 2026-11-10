<?
class CP_Admin_Modules_ManPower_CandidatePass_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('manPower_candidatePass');
        $modObj['tableName'] = 'candidate_pass';
        $modObj['keyField']  = 'candidate_pass_id';
        $modules->registerModule($modObj, array(
           'title'          => 'Candidate Pass'
          ,'actBtnsEdit'    => array('save', 'apply', 'delete')
        ));
    }

    /**
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('manPower_candidatePass', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
                
    }

}