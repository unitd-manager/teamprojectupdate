<?
class CP_Admin_Modules_Hms_DutyRoster_Functions {

    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('hms_dutyRoster');
        $modObj['tableName'] = 'duty_roster';
        $modObj['keyField']  = 'duty_roster_id';
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array()
           ,'actBtnsDetail' => array('edit', 'delete')
           ,'actBtnsEdit'   => array('save', 'apply', 'delete')
           ,'relatedTables' => array('media')
           ,'title'         => ' Duty Roster'
        ));
    }

    /**
     *
     */

    function setMediaArray($mediaArr) {

        $mediaObj = $mediaArr->getMediaObj('hms_dutyRoster', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

}
