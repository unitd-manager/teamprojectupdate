<?
class CP_Common_Modules_WebBasic_Section_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getRecordByType($sectionType){
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');

        $multiSitesCond = "";
        if ($cpCfg['cp.hasMultiSites'] && isset($cpCfg['cp.site_id'])){
            $multiSitesCond = "
            AND s.section_id IN (
                SELECT record_id
                FROM site_link
                WHERE module = 'webBasic_section'
                  AND site_id = {$cpCfg['cp.site_id']}
                  AND published = 1
            )
            ";
        }

        $multiUniqueSitesCond = "";
        if ($cpCfg['cp.hasMultiUniqueSites'] && isset($cpCfg['cp.site_id'])){
            $multiUniqueSitesCond = "AND site_id = '{$cpCfg['cp.site_id']}'";
        }

        $SQL = "
        SELECT s.*
        FROM section s
        WHERE s.section_type = '{$sectionType}'
        {$multiUniqueSitesCond}
        {$multiSitesCond}
        ";
        
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if ($numRows == 0) return;

        $row = $db->sql_fetchrow($result);

        return $row;
    }
}
