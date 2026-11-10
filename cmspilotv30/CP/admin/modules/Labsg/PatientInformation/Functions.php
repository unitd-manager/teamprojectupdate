<?
class CP_Admin_Modules_Labsg_PatientInformation_Functions {
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('labsg_patientInformation');
        $modObj['tableName'] = 'patient_information';
        $modObj['keyField']  = 'patient_information_id';
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('new','import')
           ,'actBtnsDetail' => array('edit')
           ,'actBtnsEdit'   => array('save', 'apply')
           ,'relatedTables' => array('media')
           ,'title'         => 'Patient Information'
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        $mediaObj = $mediaArr->getMediaObj('labsg_patientInformation', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
}
