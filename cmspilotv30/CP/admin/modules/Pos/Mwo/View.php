<?
class CP_Admin_Modules_Pos_Mwo_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');

        $rows  = "";
        $value = "";

        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['code'])}
            {$listObj->getListDataCell($row['cost_value'])}
            {$listObj->getListDataCell($row['currency'])}
            {$listObj->getListDataCell($row['mwo_id'], 'center')}
            {$listObj->getListRowEnd($row['mwo_id'])}
            ";
            $rowCounter++;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Code', 'm.code')}
        {$listObj->getListHeaderCell('Cost Value', 'm.cost_value')}
        {$listObj->getListHeaderCell('Currency', 'm.currency')}
        {$listObj->getListHeaderCell('ID', 'm.mwo_id', 'headerCenter')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');

        $fieldset = "
        {$formObj->getTBRow('Code', 'code')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row){
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $formObj->mode = $tv['action'];
        
        $sqlCurrency = getCPModuleObj('pos_currency')->model->getCurrencyCodeSQL();

        $fielset1 = "
        {$formObj->getTBRow('Code', 'code', $row['code'])}
        {$formObj->getTBRow('Cost Value', 'cost_value', $row['cost_value'])}
        {$formObj->getDDRowBySQL('Currency', 'currency', $sqlCurrency, $row['currency'], array('sqlType' => 'OneField'))}
        {$formObj->getDDRowByVL('Operator', 'operator', 'operator', $row['operator'])}
        {$formObj->getTBRow('Meal Allowance', 'meal_allowance_days', $row['meal_allowance_days'])}
        {$formObj->getTBRow('Day Off Allowance', 'day_off_allowance_days', $row['day_off_allowance_days'])}
        {$formObj->getTBRow('Working Time', 'working_time', $row['working_time'])}
        {$formObj->getTBRow('Meal Day', 'meal_day', $row['meal_day'])}
        {$formObj->getTBRow('No. of days off', 'no_of_day_off', $row['no_of_day_off'])}
		";

        $text = "
        {$formObj->getFieldSetWrapped('MWO Details', $fielset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
    }

    /**
     *
     */
    function getQuickSearch() {
    }
}