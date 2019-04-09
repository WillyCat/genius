<?php

// a very simple implementation of Genius Lyrics API
//
// Usage:

// see also
// https://github.com/simivar/Genius-PHP
// http://simivar.github.io/Genius-PHP/

/*
Date        Version  Change
----------  -------  ----------------------------------------------------
2019-01-28      0.1  First draft
2019-04-09      0.2  Code refactored with new classes, added cache management
                     added getArtist(), getArtistSongs()
*/

class geniusCache
{
	private $body;
	private $filename;
	public $source;

	public function
	__construct (tinyUrl $u, string $fn)
	{
		$this -> url = $u;
		$this -> filename = 'cache/' . $fn;
		$this -> source = 'none';
	}

	private function
	fetchBody(bool $cache_allowed = true)
	{
		if ($cache_allowed && $this -> isCacheValid())
			$this -> fetchDataFromCache();
		else
			$this -> fetchDataFromGenius();

		return $this -> body;
	}

	private function
	fetchJSON(bool $cache_allowed = true): array
	{
		$this -> fetchBody($cache_allowed);

		$answer = json_decode ($this -> body, true);
		$meta = $answer['meta'];
		$status = $meta['status'];
		if ($status != 200)
			throw new Exception ($meta['message']);
		return $answer;
	}

	public function
	fetch(): array
	{
		try
		{
			$answer = $this -> fetchJSON(true);
		} catch (Exception $e) {

			$answer = $this -> fetchJSON(false);
		}

		$response = $answer['response'];
		return $response;
	}

	private function
	fetchDataFromGenius()
	{
		$h = new tinyHttp ($this -> url);
		//$h -> setHeader ('Authorization', 'Bearer ' . $this -> token);
		try
		{
			$r = $h -> send();
		} catch (Exception $e) {
			die ('ERROR' . $e -> getMessage());
		}

		/*
{"meta":{"status":401,"message":"This call requires an access_token. Please see: https://genius.com/developers"}}
{"meta":{"status":403,"message":"Action forbidden for current scope"}}
		*/
		$this -> body = $r -> getBody();
		$fp = fopen ($this -> filename, 'w+');
		fwrite ($fp, $this -> body);
		fclose ($fp);

		$this -> source = 'api';
	}

	private function
	fetchDataFromCache (): void
	{
		$this -> body = file_get_contents ($this -> filename);
		$this -> source = 'cache';
	}

	private function
	isCacheValid (): bool
	{
		return file_exists ($this -> filename);
	}

	public function
	getSource(): string
	{
		return $this -> source;
	}
}

class genius
{
	private $client_id = 'd9wCE5A4msq8hcJRiUUGXfkgpNWkLjmnUQ8xNfHOFvJ8m5Mn96BVFoj5UsaxXk40';
	private $client_secret = 'x1xZL3fgpQxMSFeUTiIVYbpCxa03D2vbckArrz_9w5sXiAAOm91B7zD8ZkFtvF41DCIkPtyKXTkdDgsbn0_vkQ';
	private $token = 'AcHV2JFqTwFT99tqyZyIppOLvMsD3HZrooKtH6716MFcE0TdBkJZajDzC1Nu_qL_'; // create one on https://genius.com/api-clients
	private $cache;
	public $source;
	private $fn;
	private $u;

	public function
	__construct ()
	{
		$this -> fn = '';
		$this -> u = null;
	}

	public function
	search(string $q): array
	{
		$this -> buildGeniusUrl ([ 'search' ], [  'q' => $q ]);

		$this -> cache = new geniusCache ($this -> u, $this -> fn);
		$response = $this -> cache -> fetch();
		return $response;
	}

	private function
	buildGeniusUrl(array $pathparts, array $queryparms = null): void
	{
		$this -> fn = implode ('.' , $pathparts) . '.json';

		$this -> u = new tinyUrl ();
		$url = implode ('/' , $pathparts);
		$this -> u -> setUrl ('https://api.genius.com/' . $url);
		$query['access_token'] = $this->token;
		$query['text_format'] = 'plain';
		if ($queryparms != null)
			foreach ($queryparms as $name => $value)
				$query[$name] = $value;
		$this -> u -> setQuery($query);
	}

	public function
	getArtistSongs (int $id, int $pageno = 1, int $npp = 10): array
	{
		if ($pageno < 1)
			throw new Exception ('Invalid page number');

		$this -> buildGeniusUrl ([ 'artists', $id, 'songs' ], [ 'per_page' => $npp, 'page' => $page ]);

		$this -> cache = new geniusCache ($this -> u, $this -> fn);
		$response = $this -> cache -> fetch();
		return $response;
	}

	public function
	getArtist (int $id): array
	{
		$this -> buildGeniusUrl ([ 'artists', $id ]);

		$this -> cache = new geniusCache ($this -> u, $this -> fn);
		$response = $this -> cache -> fetch();
		return $response;
	}

	public function
	getSource (): string
	{
		return $this -> cache -> getSource();
	}
}

?>
