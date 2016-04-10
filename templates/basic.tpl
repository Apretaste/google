<h1>Resultado de buscar con Google</h1>
<p>Resultados para <b>{$query|capitalize}</b></p>
{foreach from=$responses item=response}
    <h3>{link href="NAVEGAR {$response['url']}" caption="{$response['title']}"}</h3>
	<cite style="color: #006621;font-size: small;">{$response['url']}</cite> <br/>
	<small>{link href="WEB {$response['url']}" caption="Obtener como PDF"}</small>
	<p align="justify" style="color: #545454;font-size: small;">{$response['note']}</p>
	{space5}
{/foreach}
