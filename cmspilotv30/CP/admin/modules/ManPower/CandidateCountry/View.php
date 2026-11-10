<?
class CP_Admin_Modules_ManPower_CandidateCountry_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');

        $rows  = '';
        $rowCounter = 0;

        foreach ($dataArray as $row){

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}                            
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}           
            {$listObj->getListDataCell($row['country_code'])}                      
            {$listObj->getListDataCell($row['candidate_country_id'], 'center')}
            {$listObj->getListPublishedImage($row['published'], $row['candidate_country_id'])}
            {$listObj->getListRowEnd($row['candidate_country_id'])}                              
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}                                                  
        {$listObj->getListHeaderCell('Title', 'c.title')}
        {$listObj->getListHeaderCell('Country Code', 'c.country_code')}
        {$listObj->getListHeaderCell('Country ID', 'c.candidate_country_id' , 'headerCenter')}
        {$listObj->getListHeaderCell('Published', 'c.published', 'headerCenter')}
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
        $ln = Zend_Registry::get('ln');

        $fieldset = "
        {$formObj->getTBRow('Country Name', 'title')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row) {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');

        $formObj->mode = $tv['action'];
        $text = '';
        
        $fieldset1 = "
        {$formObj->getTBRow('Country Name', 'title', $row['title'])}
        {$formObj->getTBRow('Country Code', 'country_code', $row['country_code'])}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
        ";

        $fieldset2 = "
        {$formObj->getHTMLEditor('Description', 'description', $ln->gfv($row, 'description', '0'))}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Country Details', $fieldset1)}
        {$formObj->getFieldSetWrapped('Descriptions', $fieldset2)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $ln = Zend_Registry::get('ln');

        $text ="
        ";
        
        return $text;
    }
}