{**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
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
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 *}
{extends file='../../layouts/layout-full-width.tpl'}

{block name='content'}
  <section id="main">

    {block name='product_list_header'}
      <h2 id="js-product-list-header" class="h2">{$listing.label}11111</h2>
    {/block}

    <section id="products">
      {if $listing.products|count}

        <div>
          {block name='product_list_top'}
            {include file='catalog/_partials/products-top.tpl' listing=$listing}
          {/block}
        </div>

        {block name='product_list_active_filters'}
          <div id="" class="hidden-sm-down">
            {$listing.rendered_active_filters nofilter}
          </div>
        {/block}

        <div>
          {block name='product_list'}
            {include file='catalog/_partials/products.tpl' listing=$listing}
          {/block}
        </div>

        <div id="js-product-list-bottom">
          {block name='product_list_bottom'}
            {include file='catalog/_partials/products-bottom.tpl' listing=$listing}
          {/block}
        </div>

      {else}
       <div id="js-product-list-top"></div>

        <div id="js-product-list">
			<section id="content" class="page-content page-not-found">
			  {block name='page_content'}

				<h4>{l s='Sorry for the inconvenience.' d='Shop.Theme.Global'}</h4>
				<p>{l s='Search again what you are looking for' d='Shop.Theme.Global'}</p>

				{block name='search'}
				  {hook h='displayReferSearch'}
				{/block}

				{block name='hook_not_found'}
				  {hook h='displayNotFound'}
				{/block}

			  {/block}
			</section>
        </div>

        <div id="js-product-list-bottom"></div>
      {/if}
    </section>

    {hook h="displayFooterCategory"}

  </section>
{/block}
