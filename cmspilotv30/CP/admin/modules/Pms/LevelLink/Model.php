<?
class CP_Admin_Modules_Pms_LevelLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    /**
     *
     */
    function getSQL($linkRecType) {
        $modObj = getCPModuleObj('pms_level');
        return $modObj->model->getSQL($linkRecType);
    }


    /**
     *
     */
    function setSearchVar($linkRecType) {
        $searchVar = Zend_Registry::get('searchVar');
        $modObj = getCPModuleObj('pms_level');
        $modObj->model->setSearchVar($linkRecType);
    }
    

    /**
     *
     */
    function getSQLForPager() {
    }      

    /**
     *
     */
    function getLevelValueForDropDown() {
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $fn     = Zend_Registry::get('fn');
        $ln     = Zend_Registry::get('ln');
        $modulesArr = Zend_Registry::get('modulesArr');
        
        $module   = $fn->getReqParam('room');
        $srcFld   = $fn->getReqParam('srcFld', '', true);
        $srcValue = $fn->getReqParam('srcValue', '', true);
        $category_type   = $fn->getReqParam('category_type');
        //we are setting the course in session here to use in all our further functions, this is unset in orderlink view getCourseTraineeSearch
        $_SESSION['selectedCourse']  = $srcValue;        

        $json = array();

        if ($srcValue == ''){
            $json[] = array('value' => '', 'caption' => $ln->gd('cp.form.lbl.pleaseSelect'));
            return json_encode($json);
        }

        $SQL  = "
        SELECT l.level_id
              ,l.title
        FROM level l
        LEFT JOIN (course_level cl) ON (l.level_id = cl.level_id)
        WHERE cl.course_id = {$_SESSION['selectedCourse']}
        ";
        $result = $db->sql_query($SQL);

        $json[] = array('value' => '', 'caption' => $ln->gd('cp.form.lbl.pleaseSelect'));
        while ($row = $db->sql_fetchrow($result)) {
            $json[] = array("value" => $row[0], "caption" => $row[1]);
        }

        return json_encode($json);
    }
}
