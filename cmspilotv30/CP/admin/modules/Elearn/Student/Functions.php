<?
class CP_Admin_Modules_ELearn_Student_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('elearn_student');
        $modules->registerModule($modObj, array(
            'hasFlagInList' => 0
        ));
    }

    //==================================================================//
    //==================================================================//
    //==================================================================//
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $cpUtil = Zend_Registry::get('cpUtil');

        $student_id = $fn->getReqParam('student_id');
        $school_id  = $fn->getReqParam('school_id');
        $klass_id   = $fn->getReqParam('klass_id');

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "s.student_id = '{$tv['record_id']}'";

        } else if ($student_id != '') {
            $searchVar->sqlSearchVar[] = "s.student_id = '{$student_id}'";

        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 's.student_id');

            if ($school_id != '') {
                $searchVar->sqlSearchVar[] = "s.school_id = '{$school_id}'";
            }
    
            if ($klass_id != '') {
                $searchVar->sqlSearchVar[] = "s.klass_id = '{$klass_id}'";
            }
    
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                   s.first_name LIKE '%{$tv['keyword']}%'
                OR s.last_name LIKE '%{$tv['keyword']}%'
                )";
            }
        }

    }
    //==================================================================//
    function setMediaArray($mediaArr) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('elearn_student', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

    /**
     *
     * @return <type>
     */
    function setLinksArray($inst) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('elearn_student', 'elearn_bookLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName' => 'student_book'
        ));

    }

    function linkKlassBooksToContact($klass_id, $student_id){
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $SQL = "
        SELECT book_id
        FROM klass_book
        WHERE klass_id = {$klass_id}
        ";
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $fa = array();
            $fa['book_id']       = $row['book_id'];
            $fa['student_id']    = $student_id;
            $fa['creation_date'] = date("Y-m-d H:i:s");

            $SQL2    = $dbUtil->getInsertSQLStringFromArray($fa, "student_book");
            $result2 = $db->sql_query($SQL2);
        }
    }
}