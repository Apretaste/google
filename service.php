<?php
use Goutte\Client;

class Google extends Service
{

    /*
     * Function executed when the service is called
     *
     * @param Request
     * @return Response
     */
    public function _main (Request $request)
    {
		// include lib
		require_once $this->pathToService."/lib/CustomSearch.php";
		
		//Initialize the search class
		$cs = new Fogg\Google\CustomSearch\CustomSearch();

		//Perform a simple search
		$gresults = $cs->simpleSearch($request->query);

		$results = array();
		if (isset($gresults->items))
		foreach ($gresults->items as $gresult){
			$results[] =  array(
                                "title" => $gresult->htmlTitle,
                                "url" => $gresult->link,
                                "note" => $gresult->htmlSnippet
                        );
		}
		
		$responseContent = array(
                "query" => $request->query,
                "responses" => $results
        );
        
        if (empty($results)) {
            $template = "empty.tpl";
        } else {
            $template = "basic.tpl";
        }
        
        // create the response
        $response = new Response();
        $response->setResponseSubject("Buscando en la web con Google");
        $response->createFromTemplate($template, $responseContent);
        return $response;
    }
}
