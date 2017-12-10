<?php

class Google extends Service
{
	/**
	 * Perform a Google search
	 *
	 * @param Request
	 * @return Response
	 */
	public function _main (Request $request)
	{
		// do not allow a blank search
		if(empty($request->query))
		{
			$response = new Response();
			$response->setCache();
			$response->setResponseSubject("Que desea buscar en Google?");
			$response->createFromTemplate("home.tpl", array());
			return $response;
		}

		// include lib and create object
		require_once "{$this->pathToService}/lib/CustomSearch.php";
		$cs = new Fogg\Google\CustomSearch\CustomSearch();

		// perform a simple search
		$gresults = null;
		try {
			$gresults = $cs->simpleSearch($request->query);
		} catch(Exception $e){}

		// clean if exist results
		$results = array();
		if (isset($gresults->items))
		foreach ($gresults->items as $gresult){
			$results[] =  array(
				"title" => $this->utils->removeTildes($gresult->htmlTitle),
				"url" => $gresult->link,
				"note" => $this->utils->removeTildes($gresult->htmlSnippet)
			);
		}

		// load empty template if no results
		if (empty($results)) $template = "noresults.tpl";
		else $template = "basic.tpl";

		// create response object
		$responseContent = array(
			"query" => $request->query,
			"responses" => $results
		);

		// create the response
		$response = new Response();
		$response->setCache("day");
		$response->setResponseSubject("Google: " . $request->query);
		$response->createFromTemplate($template, $responseContent);
		return $response;
	}
}
