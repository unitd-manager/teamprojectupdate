<?
class CP_Admin_Modules_Hms_FollowUpPatient_Functions {

    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('hms_followUpPatient');
        $modObj['tableName'] = 'follow_up_patient';
        $modObj['keyField']  = 'follow_up_patient_id';
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('new')
           ,'actBtnsDetail' => array('edit', 'delete')
           ,'actBtnsEdit'   => array('save', 'apply', 'delete')
           ,'relatedTables' => array('media')
           ,'title'         => 'Follow Up Patient'
        ));
    }

    /**
     *
     */

    function setMediaArray($mediaArr) {

        $mediaObj = $mediaArr->getMediaObj('hms_followUpPatient', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }


}
