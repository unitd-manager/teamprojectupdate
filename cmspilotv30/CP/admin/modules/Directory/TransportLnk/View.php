<?
class CP_Admin_Modules_Directory_TransportLnk_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');
        $ln = Zend_Registry::get('ln');

        $rowCounter = 0;
        $rows = '';

        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['station_exit'])}
            {$listObj->getListDataCell($row['lat_lng'])}
            {$listObj->getListDataCell($row['country_title'])}
            {$listObj->getListDataCell($row['transport_link_id'], 'center')}
            {$listObj->getListRowEnd($row['transport_link_id'])}
            ";
            $rowCounter++;
        }

        $text = "
    	{$listObj->getListHeader()}
        {$listObj->getListHeaderCell($ln->gd('m.directory.transportLnk.lbl.transportLnk'), 'tl.title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.transportLnk.lbl.stationExit'))}
        {$listObj->getListHeaderCell($ln->gd('m.directory.transportLnk.lbl.latLng'))}
        {$listObj->getListHeaderCell($ln->gd('m.directory.lbl.country'), 'c.title')}
        {$listObj->getListHeaderCell($ln->gd('m.lbl.id'), 'tl.transport_link_id', 'headerCenter')}
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
        {$formObj->getTBRow($ln->gd('m.directory.transportLnk.lbl.transportLnk'), 'title')}
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
        $formObj = Zend_Registry::get('formObj');
        $tv = Zend_Registry::get('tv');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        $formObj->mode = $tv['action'];
        $expCountry = array('detailValue' => $row['country_title']);
        $sqlCountry = $fn->getDDSql('common_country');

        $fielset1  = "
        {$formObj->getTBRow($ln->gd('m.directory.transportLnk.lbl.transportLnk'), 'title', $row['title'])}
        {$formObj->getTBRow($ln->gd('m.directory.transportLnk.lbl.stationExit'), 'station_exit', $row['station_exit'])}
        {$formObj->getTBRow($ln->gd('m.directory.transportLnk.lbl.lat'), 'lat', $row['lat'])}
        {$formObj->getTBRow($ln->gd('m.directory.transportLnk.lbl.lng'), 'lng', $row['lng'])}
        {$formObj->getDDRowBySQL($ln->gd('m.directory.socialMedia.lbl.country'), 'country_id', $sqlCountry, $row['country_id'], $expCountry)}
        ";


        $text = "
        {$formObj->getFieldSetWrapped($ln->gd('m.directory.altSpelling.lbl.mainDetails'), $fielset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

	function getRightPanel($row) {
        $media = Zend_Registry::get('media');

        $text = "
		";

        return $text;
    }

    function getQuickSearch() {

        $text = "
        ";

        return $text;
    }
}