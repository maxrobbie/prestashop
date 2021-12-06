<?php
/* Smarty version 3.1.34-dev-7, created on 2021-11-29 05:00:12
  from 'module:modulereferencemodulerefe' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.34-dev-7',
  'unifunc' => 'content_61a4a4ac85fe10_39491451',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '316ecf24d7aa8c93e4ee15f08fd24fd636c4ea1b' => 
    array (
      0 => 'module:modulereferencemodulerefe',
      1 => 1634732104,
      2 => 'module',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_61a4a4ac85fe10_39491451 (Smarty_Internal_Template $_smarty_tpl) {
?><!-- begin /home1/lamppp/htdocs/prestashop/modules/modulereference/modulereference.tpl --> <div id="search_refer_widget" class="search-widget">
	<form method="get" action="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['search_controller_url']->value, ENT_QUOTES, 'UTF-8');?>
">
		<input type="hidden" name="controller" value="refer">
		<input type="hidden" name="module" value="modulereference">
		<input type="hidden" name="fc" value="module">
		<input type="text" name="s" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['search_string']->value, ENT_QUOTES, 'UTF-8');?>
" id="autocomplete" placeholder="Search our Reference">
		<button type="submit"><i class="material-icons search"></i><span class="hidden-xl-down">Search</span></button>
	</form>
</div><!-- end /home1/lamppp/htdocs/prestashop/modules/modulereference/modulereference.tpl --><?php }
}
