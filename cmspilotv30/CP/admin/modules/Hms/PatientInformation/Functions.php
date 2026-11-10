<?
class CP_Admin_Modules_Hms_PatientInformation_Functions {

    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('hms_patientInformation');
        $modObj['tableName'] = 'patient_information';
        $modObj['keyField']  = 'patient_information_id';
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('new','import')
           ,'actBtnsDetail' => array('edit')
           ,'actBtnsEdit'   => array('apply','save')
           ,'relatedTables' => array('media')
           ,'title'         => 'Patient Information'
        ));
    }

    /**
     *
     */

    function setMediaArray($mediaArr) {

        $mediaObj = $mediaArr->getMediaObj('hms_patientInformation', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
    /**
     *
     */
    function setLinksArray($inst) {

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('hms_patientInformation', 'hms_patientInformationLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'patient_relationinfo'
           ,'displayTitleFieldName'  => "CONCAT_WS(' ', a.first_name, a.last_name)"
           ,'fieldlabel'             => array('Name', 'Nric')
           ,'showAnchorInLinkPortal' => 0
           ,'openExpanded'           => 1
        ));
    }

}
