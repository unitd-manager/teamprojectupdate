<?
class CP_Admin_Modules_Hms_PatientQueue_Functions {

    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('hms_patientQueue');
        $modObj['tableName'] = 'patient_queue';
        $modObj['keyField']  = 'patient_queue_id';
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('new')
           ,'actBtnsDetail' => array('edit', 'delete')
           ,'actBtnsEdit'   => array('save', 'apply', 'delete')
           ,'relatedTables' => array('media')
           ,'title'         => 'Patient Queue'
        ));
    }

    /**
     *
     */

    function setMediaArray($mediaArr) {

        $mediaObj = $mediaArr->getMediaObj('hms_patientQueue', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

 
}
