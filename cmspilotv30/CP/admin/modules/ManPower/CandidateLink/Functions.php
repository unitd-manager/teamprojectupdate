<?
class CP_Admin_Modules_ManPower_CandidateLink_Functions
{
    function setModuleArray($modules){
        $tv = Zend_Registry::get('tv');

        $modObj = $modules->getModuleObj('manPower_candidateLink');
        if (($tv['srcRoom'] == 'manPower_opportunity' && $tv['spAction'] == 'save') ||
        ($tv['srcRoom'] == 'manPower_opportunity' && $tv['spAction'] == 'add')) {
            $modules->registerModule($modObj, array(
                'tableName' => 'opportunity_candidate'
               ,'keyField'  => 'opportunity_candidate_id'
            ));
        } else {
            $modules->registerModule($modObj, array(
                'tableName' => 'candidate'
               ,'keyField'  => 'candidate_id'
            ));
        }
    }

    /**
     *
     */
    function setLinksArray($inst) {

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('manPower_candidateLink', 'manPower_candidate');

        $inst->registerLinksArray($linkObj, array(
            'displayTitleFieldName' => 'a.first_name'
           ,'historyTableName'      => 'candidate'
           ,'linkMultiple'          => 0
           ,'keyFieldForHistory'    => 'candidate_id'
        ));
    }
}
