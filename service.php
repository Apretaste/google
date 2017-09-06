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

		// include lib
		require_once "{$this->pathToService}/lib/CustomSearch.php";

		//Initialize the search class
		$cs = new Fogg\Google\CustomSearch\CustomSearch();

		//Perform a simple search
		$gresults = $cs->simpleSearch($request->query);

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
		if (empty($results)) $template = "empty.tpl";
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
