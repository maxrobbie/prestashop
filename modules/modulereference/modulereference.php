<?php
/**
 * 2007-2020 PrestaShop SA and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

class ModuleReference extends Module implements WidgetInterface
{
    /**
     * @var string Name of the module running on PS 1.6.x. Used for data migration.
     */
    private $templateFile;

    public function __construct()
    {
        $this->name = 'modulereference';
        $this->author = 'Nethues';
        $this->version = '1.0.0';
        $this->need_instance = 0;
		$this->controllers   = array('refer','autofill');	
        parent::__construct();

        $this->displayName = $this->trans('Reference Search Module' , [], 'Modules.Searchbar.Admin');
        $this->description = $this->trans('Reference Search Module.', [], 'Modules.Searchbar.Admin');

        $this->ps_versions_compliancy = ['min' => '1.7.1.0', 'max' => _PS_VERSION_];

        $this->templateFile = 'module:modulereference/modulereference.tpl';
    }

    public function install() 
    {
        if (!file_exists(_PS_THEME_DIR_ .'templates/catalog/listing/refer.tpl')){
			Tools::Copy (_PS_MODULE_DIR_ .'modulereference/views/refer.tpl',_PS_THEME_DIR_ .'templates/catalog/listing/refer.tpl');
		}
		if (!file_exists(_PS_THEME_DIR_ .'templates/catalog/listing/refer-listing.tpl')){
			Tools::Copy (_PS_MODULE_DIR_ .'modulereference/views/refer-listing.tpl',_PS_THEME_DIR_ .'templates/catalog/listing/refer-listing.tpl');
		}
		return parent::install()
            && $this->registerHook('displayTop')
            && $this->registerHook('topRefernce')
            && $this->registerHook('displayReferSearch')
            && $this->registerHook('header')
        ;
    }
	public function uninstall() 
	{
		return parent::uninstall();
	}
    public function hookHeader()
    {
        $this->context->controller->addJqueryUI('ui.autocomplete');
        $this->context->controller->registerJavascript('modules-reference', 'modules/' . $this->name . '/modulereference.js', ['position' => 'bottom', 'priority' => 150]);
    }

    public function getWidgetVariables($hookName, array $configuration = [])
    {
        $widgetVariables = [
           'search_controller_url' => $this->context->link->getPageLink('refer', null, null, null, false, null, true),
            //'search_controller_url' => $this->context->link->getModuleLink('modulereference','refer', [], true),
        ];

        /** @var array $templateVars */
        $templateVars = $this->context->smarty->getTemplateVars();
        if (is_array($templateVars) && !array_key_exists('search_string', $templateVars)) {
            $widgetVariables['search_string'] = '';
        }

        return $widgetVariables;
    }

    public function renderWidget($hookName, array $configuration = [])
    {
        $this->smarty->assign($this->getWidgetVariables($hookName, $configuration));

        return $this->fetch($this->templateFile);
    }
	public function hookDisplayReferSearch($params)
    {
        $this->smarty->assign(array(
					'search_controller_url' => $this->context->link->getPageLink('refer', null, null, null, false, null, true)
				)
			);  
		return $this->fetch($this->templateFile,'modulereference');
    }
}
