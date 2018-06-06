<?php

/**
 * Apretaste Google Service
 *
 * @author kumahacker
 * @version 2.0
 */

class Google extends Service
{
	
	public function _main(Request $request)
	{	
		// do not allow a blank search
		if (empty($request->query)) {
			$response = new Response();
			$response->setCache();
			$response->setResponseSubject("Que desea buscar en Google?");
			$response->createFromTemplate("home.tpl", array());
			return $response;
		}

		// search
		$results = $this->search($request->query);
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

	/**
	 * Generic searcher
	 *
	 * @author kumahacker
	 * @param string $q
	 * @param integer $engine
	 *
	 * @return array
	 */
	private function search($q)
	{

		$results = [];
		$di = \Phalcon\DI\FactoryDefault::getDefault();
			$key1 = $di->get('config')['bing']['key1'];
			$key2 = $di->get('config')['bing']['key2'];
			$content = $this->BingWebSearch("https://api.cognitive.microsoft.com/bing/v7.0/search",$key1,$q);

				$json=json_decode($content[1]);
				if(isset($json->webPages)){
					foreach($json->webPages->value as $v){
							$v = get_object_vars($v);
							$results[]=["title"=>$v['name'],'url'=>$v['url'],'note'=>$v['snippet']];
						}
					}


		return $results;
	}

	/**
	 * Return a remote content
	 *
	 * @author vilfer
	 * @param  string url
	 * @param integer key
	 *	@param string query
	 *	@return array
	 */
	
	private function BingWebSearch ($url, $key, $query) {
    // Prepare HTTP request
    // NOTE: Use the key 'http' even if you are making an HTTPS request. See:
    // http://php.net/manual/en/function.stream-context-create.php
    $headers = "Ocp-Apim-Subscription-Key: $key\r\n";
    $options = array ('http' => array (
                          'header' => $headers,
                           'method' => 'GET'));

    // Perform the Web request and get the JSON response
    $context = stream_context_create($options);
    $result = file_get_contents($url . "?q=" . urlencode($query), false, $context);

    // Extract Bing HTTP headers
    $headers = array();
    foreach ($http_response_header as $k => $v) {
        $h = explode(":", $v, 2);
        if (isset($h[1]))
            if (preg_match("/^BingAPIs-/", $h[0]) || preg_match("/^X-MSEdge-/", $h[0]))
                $headers[trim($h[0])] = trim($h[1]);
    }

    return array($headers, $result);
}
	
	
}
