<?
class CP_Common_Modules_Edukite_TeacherNotice_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('edukite_teacherNotice');
        $modules->registerModule($modObj, array(
            'tableName' => 'teacher_notice'
           ,'keyField'  => 'teacher_notice_id'
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
    }
}