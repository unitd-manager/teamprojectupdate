<?
class CP_Admin_Modules_ELearn_School_View extends CP_Common_Lib_ModuleViewAbstract
{
    //==================================================================//
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $db = Zend_Registry::get('db');
        $pager = Zend_Registry::get('pager');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');
        $mediaArray = Zend_Registry::get('mediaArray');

        $rows  = "";
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['school_name'])}
            {$listObj->getListDataCell($row['edu_authority'])}
            {$listObj->getListDataCell($row['city'])}
            {$listObj->getListDataCell($row['address_country'])}
            {$listObj->getListDataCell($row['school_id'], 'center')}
            {$listObj->getListRowEnd($row['school_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('School Name', 's.school_name')}
        {$listObj->getListHeaderCell('Education Authority', 's.edu_authority')}
        {$listObj->getListHeaderCell('City', 's.city')}
        {$listObj->getListHeaderCell('Country', 's.address_country')}
        {$listObj->getListHeaderCell('ID', 's.school_id' , 'headerCenter')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    //==================================================================//
    function getNew(){
        $formObj = Zend_Registry::get('formObj');

        $fieldset = "
        {$formObj->getTBRow('School Name', 'school_name')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }

    //========================================================//
    //==================================================================//
    //==================================================================//
    //==================================================================//
    function getEdit($row) {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $am = Zend_Registry::get('am');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $formObj = Zend_Registry::get('formObj');

        $formObj->mode = $tv['action'];
        
        $fnMod = includeCPClass('ModuleFns', 'eLearn_school');
        $sqlLanguage = $fnMod->getLanguagesArray();
        
        $fielset1 = "
        {$formObj->getTBRow('School Name', 'school_name', $row['school_name'])}
        {$formObj->getTBRow('Education Authority', 'edu_authority', $row['edu_authority'])}
        {$formObj->getTBRow('Contact name', 'contact_name', $row['contact_name'])}
        {$formObj->getTBRow('Contact Phone', 'contact_phone', $row['contact_phone'])}
        {$formObj->getTBRow('Contact Email', 'contact_email', $row['contact_email'])}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
        {$formObj->getDDRowByArr('Language', 'language', $sqlLanguage, $row['language'], array('useKey' => 1))}
		";

        $fielset2 = "
        {$formObj->getTBRow('Address Line 1', 'address_1', $row['address_1'])}
        {$formObj->getTBRow('Address Line 2', 'address_2', $row['address_2'])}
        {$formObj->getTBRow('City', 'city', $row['city'])}
        {$formObj->getTBRow('State', 'state', $row['state'])}
        {$formObj->getTBRow('Country', 'address_country', $row['address_country'])}
		";

        $text = "
        {$formObj->getFieldSetWrapped('School Details', $fielset1)}
        {$formObj->getFieldSetWrapped('Address', $fielset2)}
        ";

        return $text;
    }

    //========================================================//
    //==================================================================//
    //==================================================================//
    function getRightPanel($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');

        $text ="
        {$displayLinkData->getLinkPortalMain("elearn_school", "elearn_klassLink", "Classes Linked", $row)}
        ";
        return $text;
    }

    //==================================================================//
    //==================================================================//


    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $am = Zend_Registry::get('am');
        $fn = Zend_Registry::get('fn');

        $text = "
        ";
        
        
        return $text;
    }
}