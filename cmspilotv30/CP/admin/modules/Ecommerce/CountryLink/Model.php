<?
class CP_Admin_Modules_Ecommerce_CountryLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'country_id');
        $fa = $fn->addToFieldsArray($fa, 'stock');
        $fa = $fn->addToFieldsArray($fa, 'price');
        $fa = $fn->addToFieldsArray($fa, 'special_price');
        $fa = $fn->addToFieldsArray($fa, 'special_offer');
        $fa = $fn->addToFieldsArray($fa, 'published');

        return $fa;
    }

    /**
     *
     */
    function getAddNewGridItem(){
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $fa = $this->getFields();
        $fa['product_id'] = $tv['srcRoomId'];
        $id = $fn->addRecord($fa);
    }

    /**
     *
     */
    function getSaveGridItem(){
        $fn = Zend_Registry::get('fn');

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa);
    }

    /**
     */
    function getEcommerceCountryLinkEcommerceCityLinkSQL($id) {
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');

        $flds = ($formObj->mode == 'detail') ? 'c.title' : 'b.city_id';

        $SQL = "
        SELECT b.product_city_id
              ,{$flds}
              ,b.stock
              ,b.stock_threshold
              ,b.published
        FROM `product_city` b
        LEFT JOIN city c ON (b.city_id = c.city_id)
        WHERE b.product_country_id = {$id}
        ORDER BY b.product_city_id
        ";

        return $SQL;
    }

}