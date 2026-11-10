<?
class CP_Admin_Modules_ManPower_StaffCommissionLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{
    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');
        
        /* Finding projects related to staff from staff commission table */
        $sqlPro = "
        SELECT p.project_id FROM project p
        LEFT JOIN (staff_commission sc) ON (p.project_id = sc.project_id)
        WHERE sc.staff_id = {$tv['srcRoomId']}
        ";
        $resultPro = $db->sql_query($sqlPro);  
        $numRows = $db->sql_numrows($resultPro);
        $present_project_id = '';
        $count = 1;
        while ($rowPro = $db->sql_fetchrow($resultPro)) {
            if ($count == $numRows) {
                $present_project_id .= $rowPro['project_id'];
            } else {
                $present_project_id .= $rowPro['project_id'] . ', ';
            }
            $count++;
        }
        
        /* Finding the projects related to staff excluding already created projects for the staff from staff commission table */
        $sqlProject = "
        SELECT p.project_id, p.title
        FROM project p
        LEFT JOIN (project_staff ps) ON (p.project_id = ps.project_id)
        WHERE p.status = 'WIP'
          AND ps.staff_id = {$tv['srcRoomId']}
          AND p.title != ''
          AND p.project_id NOT IN ('{$present_project_id}')
        ";

        $sqlStatus = $fn->getValueListSQL('staffCommissionStatus');
        $exp = array('sqlType' => 'OneField');
        $current_date = date('Y-m-d');

        $formAction = "index.php?_spAction=add&lnkRoom={$tv['lnkRoom']}&showHTML=0";

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getDDRowBySQL('Project', 'project_id', $sqlProject)}
                {$formObj->getDateRow('Date', 'date', $current_date)}
                {$formObj->getTBRow('Amount', 'amount')}
                {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, '', $exp)}
            </fieldset>
            <input type='hidden' name='{$fn->getSrcRoomKeyFldName()}' value='{$tv['srcRoomId']}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        
        $id  = $fn->getReqParam('id');
        $row = $fn->getRecordRowByID('staff_commission', 'staff_commission_id', $id);

        $sqlStatus = $fn->getValueListSQL('staffCommissionStatus');
        $exp = array('sqlType' => 'OneField');
        $expNoEdit = array('isEditable' => 0);        
        $rowProject = $fn->getRecordRowByID('project', 'project_id', $row['project_id']);

        $formAction = "index.php?_spAction=save&lnkRoom={$tv['lnkRoom']}&showHTML=0";

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getTBRow('Project', 'project_id', $rowProject['title'], $expNoEdit)}
                {$formObj->getDateRow('Date', 'date', $row['date'])}
                {$formObj->getTBRow('Amount', 'amount', $row['amount'])}
                {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $row['status'], $exp)}
            </fieldset>
            <input type='hidden' name='staff_commission_id' value='{$id}' />
        </form>
        ";
        return $text;
    }
}
