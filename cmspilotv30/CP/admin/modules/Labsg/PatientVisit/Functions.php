<?
class CP_Admin_Modules_Labsg_PatientVisit_Functions {

    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('labsg_patientVisit');
        $modObj['tableName'] = 'patient_visit';
        $modObj['keyField']  = 'patient_visit_id';
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array()
           //,'actBtnsSearchlist' => array('new')
           ,'actBtnsDetail' => array('edit')
           ,'actBtnsEdit'   => array('save', 'apply')
           ,'relatedTables' => array('media')
           ,'title'         => 'Patient Visit'
        ));
    }

    /**
     *
     */

    function setMediaArray($mediaArr) {

        $mediaObj = $mediaArr->getMediaObj('labsg_patientVisit', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

}
