<?
class CP_Admin_Modules_EnggCrm_ThirdPartyCost_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('enggCrm_thirdPartyCost');
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('export')
           ,'actBtnsDetail' => array('delete')
           ,'title'         => 'Third Party Cost'
           ,'tableName'     => 'third_party_cost'
           ,'keyField'      => 'third_party_cost_id'
           ,'hasEditInList' => 0
        ));
    }

    /**
     *
     */
    function refreshValuesBasedOnThirdPartyCosts($third_party_cost_id, $excludeCurRec = 0){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rec = $fn->getRecordRowByID('third_party_cost', 'third_party_cost_id', $third_party_cost_id);
        
        if (!is_array($rec)){
            return;
        }

        /**** record excluded if it is going to be deleted ********/
        $append = ($excludeCurRec == 1) ? "AND tp.third_party_cost_id != {$third_party_cost_id}" : '';

        if ($rec['project_id'] > 0 && $cpCfg['m.enggCrm.hasQuotingModule'] == 0){
            $SQL = "
            UPDATE project p SET p.budget_third_party  = (
                SELECT SUM(budget_amount) AS total_cost 
                FROM third_party_cost tp
                WHERE tp.project_id = p.project_id
                {$append}
            )
            WHERE p.project_id = {$rec['project_id']}
            ";
            $db->sql_query($SQL);

            $SQL = "
            UPDATE project p SET p.used_third_party  = (
                SELECT SUM(actual_amount) AS total_cost 
                FROM third_party_cost tp
                WHERE tp.project_id = p.project_id
                {$append}
            )
            WHERE p.project_id = {$rec['project_id']}
            ";
            $db->sql_query($SQL);

            $SQL = "
            UPDATE project p SET p.budget_inhouse = (
                p.project_value
                    - IF(ISNULL(p.budget_third_party),0, p.budget_third_party)
                    - IF(ISNULL(p.project_commission),0, p.project_commission)
            )
            WHERE p.project_id = {$rec['project_id']}
            ";
            $db->sql_query($SQL);

        } else if ($rec['project_id'] > 0){

            $SQL = "
            UPDATE project p SET p.net_third_party  = (
                (
                SELECT SUM(actual_amount) AS total_cost 
                FROM third_party_cost tp
                WHERE tp.project_id = p.project_id
                {$append}
                ) + 
                IF(ISNULL(p.project_commission), 0, p.project_commission) +
                IF(ISNULL(p.used_third_party)  , 0, p.used_third_party) 
            )
            WHERE p.project_id = {$rec['project_id']}
            ";
            $db->sql_query($SQL);
        }
    }

    /**
     *
     */
    function beforeDeleteHandler($third_party_cost_id){
        $this->refreshValuesBasedOnThirdPartyCosts($third_party_cost_id, 1);
    }

}