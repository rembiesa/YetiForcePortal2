{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-Base-Widget-RelatedModuleContent -->
	<div class="table-responsive">
		<table class="table table-bordered">
			<thead class="thead-light">
				{foreach from=$WIDGET->getHeaders() key=HEADER_NAME item=HEADER_LABEL}
					<th>{\App\Purifier::encodeHTML($HEADER_LABEL)}</th>
				{/foreach}
			</thead>
			<tbody>
				{foreach from=$WIDGET->getEntries() item=RECORD_MODEL}
					<tr class="js-row" data-url="{$RECORD_MODEL->getDetailViewUrl()}">
						{foreach from=$WIDGET->getHeaders() key=HEADER_NAME item=HEADER_LABEL}
							<td>{$RECORD_MODEL->getDisplayValue($HEADER_NAME)}</td>
						{/foreach}
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
	<div class="widget-footer">
		{if $WIDGET->isMorePages()}

		{/if}
	</div>
<!-- /tpl-Base-Widget-RelatedModuleContent -->
{/strip}
