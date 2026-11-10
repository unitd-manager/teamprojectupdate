<?
class CP_Admin_Modules_Party_PartyReminder_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $SQL = "
        SELECT pr.*
        FROM party_reminder pr
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $searchVar = Zend_Registry::get('searchVar');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $searchVar->mainTableAlias = 'pr';

        $country_id     = $fn->getReqParam('country_id');
        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "pr.party_reminder_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'pr.party_reminder_id');

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                       pr.name   LIKE '%{$tv['keyword']}%'
                    OR pr.email LIKE '%{$tv['keyword']}%'
                    OR pr.event_title LIKE '%{$tv['keyword']}%'
                )";
            }

            if ($start_date != '' && $end_date != '') {
                $searchVar->sqlSearchVar[] = "(pr.event_date BETWEEN '{$start_date}' AND '{$end_date}')";
            } else if ($start_date != '') {
                $searchVar->sqlSearchVar[] = "pr.event_date >= '{$start_date}'";
            }
        }

        $searchVar->sortOrder = "pr.creation_date DESC";
    }

}
