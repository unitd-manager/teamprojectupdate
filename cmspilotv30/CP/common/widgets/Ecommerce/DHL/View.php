<?
class CP_Common_Widgets_Ecommerce_DHL_View extends CP_Common_Lib_WidgetViewAbstract
{
     /**
      * Get XML to generate the label 
      */
     function getShipmentValidate(){
         return $this->model->getShipmentValidate();
     }
     
     /**
      *
      * @return type 
      */
     function getCapability(){
         return $this->model->getCapability();
     }
   
}
