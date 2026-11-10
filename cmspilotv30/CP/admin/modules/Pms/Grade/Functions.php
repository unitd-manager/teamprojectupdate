<?
class CP_Admin_Modules_Pms_Grade_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');

        if ($cpCfg['cp.forAceIms']) {
            $modObj = $modules->getModuleObj('pms_grade');
            $modules->registerModule($modObj, array(
            ));
        } else {
            $modObj = $modules->getModuleObj('pms_grade');
            $modules->registerModule($modObj, array(
                'actBtnsList' => array()
               ,'tableName' => 'student_grade'
               ,'keyField'  => 'student_grade_id'
            ));
        }
    }

}