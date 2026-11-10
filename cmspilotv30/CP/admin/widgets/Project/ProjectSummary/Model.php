<?
class CP_Admin_Widgets_Project_ProjectSummary_Model extends CP_Common_Lib_WidgetModelAbstract
{
    //==================================================================//
    function getTotalValueOfWIPProjects() {
        $db = Zend_Registry::get('db');

        $SQL = "
        SELECT CONCAT('{$this->getCurPfx()}', FORMAT(SUM(project_value{$this->getFldSfx()}), 0)) AS total
        FROM project p
        WHERE LOWER(p.status) = 'wip' 
        ";
        $result  = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        
        return $row['total'];
    }

    //==================================================================//
    function getTotalValueOfStillToBill() {
        $db = Zend_Registry::get('db');

        $invSQL = "(
        SELECT SUM(invoice_amount{$this->getFldSfx()})
        FROM invoice i
        WHERE i.project_id = p.project_id
          AND LOWER(i.status) != 'cancelled' 
        )
        ";

        $SQL = "
        SELECT CONCAT('{$this->getCurPfx()}', 
            FORMAT(SUM(p.project_value{$this->getFldSfx()} - IF(ISNULL({$invSQL}),0, {$invSQL})), 0)
        ) AS total
        FROM project p
        WHERE LOWER(p.status) = 'wip' 
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        
        return $row['total'];
    }

    //==================================================================//
    function getTotalSalesThisMonth() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        
        $thisMonth = date('Y-m');
        
        $SQL = "
        SELECT SUM(project_value{$this->getFldSfx()}) AS total
        FROM project p
        WHERE DATE_FORMAT(start_date, '%Y-%m') = '{$thisMonth}'
          AND LOWER(p.status) != 'cancelled' 
        ";

        $result  = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        
        return $row['total'];
    }

    //==================================================================//
    function getTotalSalesLastMonth() {
        $db = Zend_Registry::get('db');
        
        $lastMonth = date('Y-m',mktime (0,0,0,date("m")-1,date("d"), date("Y")));
        
        $SQL = "
        SELECT SUM(project_value{$this->getFldSfx()}) AS total
        FROM project p
        WHERE DATE_FORMAT(start_date, '%Y-%m') = '{$lastMonth}'
          AND LOWER(p.status) != 'cancelled' 
        ";

        $result  = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        
        return $row['total'];
    }

    //==================================================================//
    function getTotalSalesThisYear() {
        $db = Zend_Registry::get('db');
        
        $thisYear = date('Y');
        
        $SQL = "
        SELECT SUM(project_value{$this->getFldSfx()}) AS total
        FROM project p
        WHERE DATE_FORMAT(start_date, '%Y') = '{$thisYear}'
          AND LOWER(p.status) != 'cancelled' 
        ";

        $result  = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        
        return $row['total'];
    }

    //==================================================================//
    function getFldSfx() {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        if ($cpCfg['m.project.hasMultiCurrency'] == 1){
            return '_base';
        }
    }

    //==================================================================//
    function getCurPfx() {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        if ($cpCfg['m.project.hasMultiCurrency'] == 1){
            return $cpCfg['baseCurrency'] . ' ';
        } else {
            return '$';
        }
    }
}