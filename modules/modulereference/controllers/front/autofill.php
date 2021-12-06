<?php class ModulereferenceAutofillModuleFrontController extends ModuleFrontController{
	public function displayAjaxGetRefer(){
		if(isset($_POST['search'])){
			$query = "SELECT * FROM "._DB_PREFIX_."product_numbers WHERE article_number like '%".$_POST['search']."%' GROUP BY article_number";
			$aNumbers = Db::getInstance()->ExecuteS($query);
			$response = array();
			foreach ($aNumbers as $aNumber) { 
			  $response[] = array("label"=> $aNumber['article_number']);
		   }
			echo json_encode($response);
		}
		exit; 
	}
}