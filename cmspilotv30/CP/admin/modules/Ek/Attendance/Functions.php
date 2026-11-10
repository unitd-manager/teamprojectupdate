<?
class CP_Admin_Modules_Ek_Attendance_Functions
{

    //==================================================================//
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('ek_attendance');
        $modules->registerModule($modObj, array(
            'tableName'   => 'student_attendance'
           ,'keyField'    => 'student_attendance_id'
        ));
    }

    //==================================================================//
    //==================================================================//
    //==================================================================//
    //==================================================================//
    function setMediaArray($mediaArr) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('ek_student', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
}