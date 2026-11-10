<?
class CP_Admin_Modules_Pms_ProgramGroup_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('pms_programGroup');
        $modObj['tableName'] = 'program_group';
        $modObj['keyField']  = 'program_group_id';
        $modules->registerModule($modObj, array(
             'hasFlagInList' => 0
            ,'title' => 'Program Group'
        ));
    }

   /**
    *
    */
   function setLinksArray($inst) {
       $linkObj = $inst->getLinksArrayObj('pms_programGroup', 'pms_subsidyDiscountLink');

       $inst->registerLinksArray($linkObj, array(
             'historyTableName'          => 'program_group_subsidy_discount'
            ,'linkingType'               => 'modal'
            ,'historyTableKeyField'      => 'program_group_subsidy_discount_id'
            ,'showLinkPanelInEdit'       => 1
            ,'showAnchorInLinkPortal'    => false
            ,'hasGridEdit'               => 0
       ));
   }    

   /**
    *
    */
    function getPmsProgramGroupPmsSubsidyDiscountLinkAddLinkCallback($program_group_id, $row) {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        
        $program_group_id = $row['program_group_id'];
        
        $SQLProgramGroup = "
        SELECT *
        FROM program_group_subsidy_discount
        WHERE program_group_id = '{$program_group_id}'
        ";
        $resultProgramGroup = $db->sql_query($SQLProgramGroup);
        while ($rowProgramGroup = $db->sql_fetchrow($resultProgramGroup)) {
            $subsidyDiscountId = $rowProgramGroup['subsidy_discount_id'];
            
            $SQLSubsidyDisc = "
            SELECT csh.subsidy_discount_id
                  ,csh.program_group_id
            FROM course_subsidy_history csh
            WHERE csh.program_group_id = '{$program_group_id}'
            AND csh.subsidy_discount_id = '{$subsidyDiscountId}'
            ";
            $resultSubsidyDisc = $db->sql_query($SQLSubsidyDisc);
            $numRowsSubsidyDisc = $db->sql_numrows($resultSubsidyDisc);

            if ($numRowsSubsidyDisc == 0) {
                $SQLCourse = "
                SELECT *
                FROM course
                WHERE program_group_id = '{$program_group_id}'
                ";
                $resultCourse = $db->sql_query($SQLCourse);
                
                while ($rowCourse = $db->sql_fetchrow($resultCourse)) {
                    $fa = array();
                    $fa['course_id']            = $rowCourse['course_id'];
                    $fa['subsidy_discount_id']  = $subsidyDiscountId;
                    $fa['program_group_id']     = $program_group_id;
                    $fa['creation_date']        = date("Y-m-d H:i:s");
                    
                    $SQLCourseSubsidy   = $dbUtil->getInsertSQLStringFromArray($fa, 'course_subsidy_history');
                    $result             = $db->sql_query($SQLCourseSubsidy);
                }
            }
        }
    }

   /**
    *
    */
    function getPmsProgramGroupPmsSubsidyDiscountLinkDeleteLinkCallback($program_group_id, $subsidy_discount_id) {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        
        $SQLProgramGroup = "
        SELECT *
        FROM program_group_subsidy_discount
        WHERE program_group_id = '{$program_group_id}'
        ";
        $resultProgramGroup = $db->sql_query($SQLProgramGroup);
        while ($rowProgramGroup = $db->sql_fetchrow($resultProgramGroup)) {
            
            $SQLSubsidyDisc = "
            SELECT csh.subsidy_discount_id
                  ,csh.program_group_id
            FROM course_subsidy_history csh
            WHERE csh.program_group_id = '{$program_group_id}'
            AND csh.subsidy_discount_id = '{$subsidy_discount_id}'
            ";
            $resultSubsidyDisc = $db->sql_query($SQLSubsidyDisc);
            $numRowsSubsidyDisc = $db->sql_numrows($resultSubsidyDisc);

            if ($numRowsSubsidyDisc > 0) {
                while ($rowSubsidyDisc = $db->sql_fetchrow($resultSubsidyDisc)) {
                    $fa = array();
                    $fa['course_id']            = 0;
                    $fa['subsidy_discount_id']  = 0;
                    $fa['program_group_id']     = 0;
                    $fa['modification_date']    = date("Y-m-d H:i:s");
                    
                    $whereCondition = "
                    WHERE subsidy_discount_id = {$subsidy_discount_id} AND program_group_id = {$program_group_id}
                    ";
                    $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'course_subsidy_history', $whereCondition);
                    $db->sql_query($SQL);
                }
            }
        }
    }

   /**
    *
    */
    function getPmsProgramGroupPmsSubsidyDiscountLinkAddAllLinkCallback($program_group_id) {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        
        $SQLProgramGroup = "
        SELECT *
        FROM program_group_subsidy_discount
        WHERE program_group_id = '{$program_group_id}'
        ";
        $resultProgramGroup = $db->sql_query($SQLProgramGroup);
        while ($rowProgramGroup = $db->sql_fetchrow($resultProgramGroup)) {
            $subsidyDiscountId = $rowProgramGroup['subsidy_discount_id'];
            
            $SQLSubsidyDisc = "
            SELECT csh.subsidy_discount_id
                  ,csh.program_group_id
            FROM course_subsidy_history csh
            WHERE csh.program_group_id = '{$program_group_id}'
            AND csh.subsidy_discount_id = '{$subsidyDiscountId}'
            ";
            $resultSubsidyDisc = $db->sql_query($SQLSubsidyDisc);
            $numRowsSubsidyDisc = $db->sql_numrows($resultSubsidyDisc);

            if ($numRowsSubsidyDisc == 0) {
                $SQLCourse = "
                SELECT *
                FROM course
                WHERE program_group_id = '{$program_group_id}'
                ";
                $resultCourse = $db->sql_query($SQLCourse);
                
                while ($rowCourse = $db->sql_fetchrow($resultCourse)) {
                    $fa = array();
                    $fa['course_id']            = $rowCourse['course_id'];
                    $fa['subsidy_discount_id']  = $subsidyDiscountId;
                    $fa['program_group_id']     = $program_group_id;
                    $fa['creation_date']        = date("Y-m-d H:i:s");
                    
                    $SQLCourseSubsidy   = $dbUtil->getInsertSQLStringFromArray($fa, 'course_subsidy_history');
                    $result             = $db->sql_query($SQLCourseSubsidy);
                }
            }
        }
    }

   /**
    *
    */
    function getPmsProgramGroupPmsSubsidyDiscountLinkRemoveAllLinkCallback($program_group_id) {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        
        $SQLProgramGroup = "
        SELECT *
        FROM program_group_subsidy_discount
        WHERE program_group_id = '{$program_group_id}'
        ";
        $resultProgramGroup = $db->sql_query($SQLProgramGroup);
        while ($rowProgramGroup = $db->sql_fetchrow($resultProgramGroup)) {
            $subsidyDiscountId = $rowProgramGroup['subsidy_discount_id'];

            $SQLSubsidyDisc = "
            SELECT csh.subsidy_discount_id
                  ,csh.program_group_id
            FROM course_subsidy_history csh
            WHERE csh.program_group_id = '{$program_group_id}'
            AND csh.subsidy_discount_id = '{$subsidyDiscountId}'
            ";
            $resultSubsidyDisc = $db->sql_query($SQLSubsidyDisc);
            $numRowsSubsidyDisc = $db->sql_numrows($resultSubsidyDisc);

            if ($numRowsSubsidyDisc > 0) {
                while ($rowSubsidyDisc = $db->sql_fetchrow($resultSubsidyDisc)) {
                    $fa = array();
                    $fa['course_id']            = 0;
                    $fa['subsidy_discount_id']  = 0;
                    $fa['program_group_id']     = 0;
                    $fa['modification_date']    = date("Y-m-d H:i:s");
                    
                    $whereCondition = "
                    WHERE subsidy_discount_id = {$subsidyDiscountId} AND program_group_id = {$program_group_id}
                    ";
                    $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'course_subsidy_history', $whereCondition);
                    $db->sql_query($SQL);
                }
            }
        }
    }
}