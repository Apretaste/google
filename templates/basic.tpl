<h1>{$query|capitalize}</h1>

{foreach from=$responses item=response name=google}
    <b>{$smarty.foreach.google.index + 1}) {link href="NAVEGAR {$response['url']}" caption="{$response['title']}"}</b><br/>
	<font style="color: #545454;font-size: small;">{$response['note']}</font><br/>
	<small>
		{link href="WEB FULL {$response['url']}" caption="Ver con im&aacute;genes"} {separator}
		{link href="PIZARRA {$response['url']}" caption="Compartir en Pizarra"}
	</small>
	{space15}
{/foreach}
