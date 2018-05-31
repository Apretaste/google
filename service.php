<?php

/**
 * Apretaste Google Service
 *
 * @author kumahacker
 * @version 2.0
 */

class Google extends Service
{
	const ENGINE_GOOGLE = 1;
	const ENGINE_DUCKDUCKGO = 2;
	const ENGINE_YANDEX = 3;
	const ENGINE_FAROO = 4;

	/**
	 * Perform a Google search
	 *
	 * @param Request
	 * @return Response
	 */
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
	private function search($q, $engine = Google::ENGINE_GOOGLE)
	{

		$results = [];
		$di = \Phalcon\DI\FactoryDefault::getDefault();
		$wwwroot = $di->get('path')['root'];
		$cacheFile = "$wwwroot/temp/google/" . md5($q);

		// check the cache
		/*if (file_exists($cacheFile) && time() - filemtime($cacheFile) > 100000) {
			$content = file_get_contents($cacheFile);
			$results = json_decode($content);
			return $results;
		}*/

		switch ($engine) {
			case Google::ENGINE_GOOGLE:

				// include lib and create object
				require_once "{$this->pathToService}/lib/CustomSearch.php";
				$cs = new Fogg\Google\CustomSearch\CustomSearch();

				// perform a simple search
				$gresults = null;
				try {
					$gresults = $cs->simpleSearch($q);
				} catch (Exception $e) {
					$error=$e->getMessage();
					$results["error"]=$error;
				}

				// clean if exist results
				if (isset($gresults->items))
					foreach ($gresults->items as $gresult) {
						$results[] = [
							"title" => $this->utils->removeTildes($gresult->htmlTitle),
							"url" => $gresult->link,
							"note" => $this->utils->removeTildes($gresult->htmlSnippet)
						];
					}

				break;

			case Google::ENGINE_DUCKDUCKGO:
				$content = $this->getRemoteContent("https://api.duckduckgo.com/?q=" . urlencode($q) . "&format=json");
				$content = json_decode($content);

				if (isset($content->results))
					foreach ($content->results as $v) {
						if (is_object($v)) $v = get_object_vars($v);
						$v['title'] = $v['Text'];
						$v['url'] = $v['FirstURL'];
						$v['note'] = $v['Result'];
						$results[] = $v;
					}

                if (issert($content->AbstractText))
                    $results[] = [
                        'title' => $content->Heading,
                        'url' => $content->AbstractUrl,
                        'note' => $content->AbstractText
                    ];
				break;

			case Google::ENGINE_YANDEX:
				break;

			case Google::ENGINE_FAROO:

				$config = $this->config['search-api-faroo'];

				// http://www.faroo.com/api?q=cuba&start=1&length=10&l=en&src=web&i=false&f=json&key=G2POOpVSD35690JspEW8SxnI@XI_
				$url = $config['base_url'] . '?' . (empty($query) ? '' : 'q=' . urlencode("$query"));
				$url .= "&start=1";
				$url .= "&length=" . $config['results_length'];
				$url .= "&l=es";
				$url .= "&src=web";
				$url .= "&i=false&f=json&key=" . $config['key'];

				$content = $this->getRemoteContent($url);
				$result = @json_decode($content);

				if (isset($result->results)) if (is_array($result->results)) {
					foreach ($result->results as $k => $v) {
						if (is_object($v)) $v = get_object_vars($v);
						$v['note'] = $v['kwic'];
						$result->results[$k] = $v;
					}
					$results = $result->results;
				}
				break;
		}

		//file_put_contents($cacheFile, json_encode($results));

		return $results;
	}

	/**
	 * Return a remote content
	 *
	 * @param $url
	 * @param array $info
	 * @return mixed
	 */
	private function getRemoteContent($url, &$info = [])
	{
		$url = str_replace("//", "/", $url);
		$url = str_replace("http:/", "http://", $url);
		$url = str_replace("https:/", "https://", $url);

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);

		$default_headers = [
			"Cache-Control" => "max-age=0",
			"Origin" => "{$url}",
			"User-Agent" => "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.125 Safari/537.36",
			"Content-Type" => "application/x-www-form-urlencoded"
		];

		$hhs = [];
		foreach ($default_headers as $key => $val)
			$hhs[] = "$key: $val";

		curl_setopt($ch, CURLOPT_HTTPHEADER, $hhs);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		$html = curl_exec($ch);

		$info = curl_getinfo($ch);

		if ($info['http_code'] == 301)
			if (isset($info['redirect_url']) && $info['redirect_url'] != $url)
				return $this->getUrl($info['redirect_url'], $info);

		curl_close($ch);

		return $html;
	}
}
