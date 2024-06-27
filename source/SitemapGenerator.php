<?php

class SitemapGenerator
{
	/**
	 * Generates a sitemap content based on the specified file type and saves it to the specified file path.
	 * 
	 * @param array $pagesArray An array of pages to include in the sitemap. 
	 * - Each page should be represented as an associative array with at least a 'loc' key, which is a required field. 
	 * - Optional keys for each page include 'lastmod' (valid date format), 'priority' (decimal between 0.0 and 1.0), and 'changefreq' (one of: always, hourly, daily, weekly, monthly, yearly, never). 
	 * - __Additional custom keys can be included as needed.__
	 * 
	 * @param string $fileType The type of file to generate ('xml', 'json', or 'csv').
	 * @param string $filePath The file path where the sitemap content will be saved.
	 *
	 * @throws InvalidArgumentException If the parameters in the pages array or file type are not valid.
	 *     - If an invalid file type is specified, its must be one of: 'xml', 'json', or 'csv'.
	 *     - Each parameter array must contain a valid URI for 'loc' conforming to [RFC 2396](http://www.ietf.org/rfc/rfc2396.txt).
	 *     - If 'lastmod' is provided, it must be a valid date conforming to [W3C DATETIME format](http://www.w3.org/TR/NOTE-datetime).
	 *     - If 'priority' is provided, it must be a decimal between 0.0 and 1.0.
	 *     - If 'changefreq' is provided, it must be one of: always, hourly, daily, weekly, monthly, yearly, never.
	 * 
	 * @throws RuntimeException If any of the following file or directory operations fail:
	 *     - Failed to create the directory.
	 *     - The directory is not writable.
	 *     - The file exists and is not writable.
	 *     - Unable to save content to the file.
	 * 
	 * @return void
	 */
    public static function generate($pagesArray, $fileType, $filePath)
    {
		SitemapGenerator::validateParamsArray($pagesArray);

        switch ($fileType) 
		{
            case 'xml':
                $sitemapContent = SitemapGenerator::generateXMLFromArray($pagesArray);
                break;
            case 'json':
                $sitemapContent = SitemapGenerator::generateJSONFromArray($pagesArray);
                break;
            case 'csv':
                $sitemapContent = SitemapGenerator::generateCSVFromArray($pagesArray);
                break;
            default:
                throw new InvalidArgumentException('Invalid parameter. Invalid file type specified.');
        }

        SitemapGenerator::saveContentIntoFile($sitemapContent, $filePath);
    }

	private static function validateParamsArray($pagesArray) 
	{
	    foreach ($pagesArray as $page) 
		{
	        if (!isset($page['loc']) || !SitemapGenerator::isValidURI($page['loc'])) 
			{
	            throw new InvalidArgumentException('Invalid parameters. Each parameter array must contain a valid URI for loc conforming to RFC 2396.');
	        }
		
	        if (isset($page['lastmod']) && !SitemapGenerator::isValidDate($page['lastmod'])) 
			{
	            throw new InvalidArgumentException('Invalid parameters. lastmod must be a valid date.');
	        }
		
	        if (isset($page['priority']) && !SitemapGenerator::isValidPriority($page['priority'])) 
			{
	            throw new InvalidArgumentException('Invalid parameters. priority must be a decimal between 0.0 and 1.0.');
	        }
		
	        if (isset($page['changefreq']) && !SitemapGenerator::isValidChangeFreq($page['changefreq'])) 
			{
	            throw new InvalidArgumentException('Invalid parameters. changefreq must be one of: always, hourly, daily, weekly, monthly, yearly, never.');
	        }
	    }
	}

	private static function isValidURI($uri) 
	{
	    return filter_var($uri, FILTER_VALIDATE_URL) !== false;
	}

	private static function isValidDate($date)
	{
	    return (bool)strtotime($date);
	}

	private static function isValidPriority($priority) 
	{
	    return is_numeric($priority) && $priority >= 0.0 && $priority <= 1.0;
	}

	private static function isValidChangeFreq($changefreq) 
	{
	    $validFreqs = array("always", "hourly", "daily", "weekly", "monthly", "yearly", "never");
	    return in_array($changefreq, $validFreqs);
	}

	private static function generateXMLFromArray($pagesArray)
	{
	    $doc = new DOMDocument('1.0', 'UTF-8');

	    $urlset = $doc->createElement('urlset');

	    $urlset->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
	    $urlset->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
	    $urlset->setAttribute('xsi:schemaLocation', 'http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd');

	    foreach ($pagesArray as $page) 
		{
	        $url = $doc->createElement('url');

	        $loc = $doc->createElement('loc', $page['loc']);
	        $lastmod = $doc->createElement('lastmod', $page['lastmod']);
	        $priority = $doc->createElement('priority', $page['priority']);
	        $changefreq = $doc->createElement('changefreq', $page['changefreq']);

	        $url->appendChild($loc);
	        $url->appendChild($lastmod);
	        $url->appendChild($priority);
	        $url->appendChild($changefreq);

			foreach ($page as $key => $value) 
			{
				if (!in_array($key, ['loc', 'lastmod', 'priority', 'changefreq'])) 
				{
					$otherTag = $doc->createElement($key, $value);
					$url->appendChild($otherTag);
				}
			}

	        $urlset->appendChild($url);
	    }

	    $doc->appendChild($urlset);

	    return $doc->saveXML();
	}

	private static function generateJSONFromArray($pagesArray)
	{
	    $jsonArray = [];

	    foreach ($pagesArray as $page) 
		{
	        $jsonItem = [
	            'loc' => $page['loc'],
	            'lastmod' => $page['lastmod'],
	            'priority' => $page['priority'],
	            'changefreq' => $page['changefreq']
	        ];

	        foreach ($page as $key => $value) 
			{
	            if (!in_array($key, ['loc', 'lastmod', 'priority', 'changefreq'])) 
				{
	                $jsonItem[$key] = $value;
	            }
	        }

	        $jsonArray[] = $jsonItem;
	    }

	    return json_encode($jsonArray, JSON_PRETTY_PRINT);
	}

	private static function generateCSVFromArray($pagesArray)
	{
	    $csvData = '';
	    $allKeys = [];

	    foreach ($pagesArray as $page) 
	        $allKeys = array_merge($allKeys, array_keys($page));

	    $allKeys = array_unique($allKeys);

	    $csvData .= implode(';', $allKeys) . "\n";

	    foreach ($pagesArray as $page) 
		{
	        $rowData = [];

	        foreach ($allKeys as $key) 
	            $rowData[] = isset($page[$key]) ? $page[$key] : '';

	        $csvData .= implode(';', $rowData) . "\n";
	    }

	    return $csvData;
	}

	private static function saveContentIntoFile($content, $filePath)
	{
		$directory = dirname($filePath);

	    if (!is_dir($directory)) 
		{
	        if (!mkdir($directory, 0777, true))
	            throw new RuntimeException('Failed to create directory.');
	    }

	    if (!is_writable(dirname($filePath)))
	        throw new RuntimeException('Directory is not writable.');

	    if (file_exists($filePath) && !is_writable($filePath))
	        throw new RuntimeException('File exists and is not writable.');

	    if (file_put_contents($filePath, $content) === false)
	        throw new RuntimeException('Unable to save content to file.');
	}
}

?>