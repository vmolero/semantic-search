<?php

namespace AppBundle\Services;

use anlutro\cURL\cURL;
use DOMDocument;
use DOMNodeList;
use DOMXPath;

/**
 * Description of MorphAdornerService
 *
 * @author Víctor Molero
 */
final class MorphAdornerService
{
    const POSITIVE = 1;
    const NEUTRAL  = 0;
    const NEGATIVE = -1;

    /**
     *
     * @var cURL
     */
    private $curl;

    /**
     *
     * @param cURL $curl
     */
    public function __construct(cURL $curl)
    {
        $this->curl = $curl;
    }
    /**
     * @url http://classify.at.northwestern.edu/maserver/lexiconlookup
     * @access public
     *
     * @param string $word
     * @return array ['lemma' => <lemma>, 'tag' => 'vvb']
     */
    public function lexiconLookup($word)
    {
        return $this->parseLexiconLookup($this->connector('http://classify.at.northwestern.edu/maserver/lexiconlookup', ['corpusConfig' => 'eme', 'media' => 'xml', 'spelling' => $word]));
    }
    /**
     * @url http://classify.at.northwestern.edu/maserver/sentimentanalyzer
     * @access public
     *
     * @param string $text
     * @return array
     */
    public function sentimentAnalyzer($text)
    {
        return $this->parseSentimentAnalyzer($this->connector('http://classify.at.northwestern.edu/maserver/sentimentanalyzer', ['includeInputText' => false, 'media' => 'xml', 'text' => $text]));
    }
    /**
     *
     * @param string $xml
     * @return array array ['lemma' => <lemma>, 'tag' => 'vvb']
     */
    private function parseLexiconLookup($xml)
    {
        $dom      = new DOMDocument();
        $dom->loadXML($xml);
        $xpath    = new DOMXPath($dom);
        $indexes  = $this->XMLtoArray($xpath->query('//lemmata/entry/string[1]/text()'));
        $values   = $this->XMLtoArray($xpath->query('//lemmata/entry/string[2]/text()'));
        $tt       = array_combine($indexes, $values);
        $indexes2 = $this->XMLtoArray($xpath->query('//categoriesAndCounts/entry/string[1]/text()'));
        $counts   = $this->XMLtoArray($xpath->query('//mutableInteger/text()'));
        $tt2      = array_combine($indexes2, $counts);
        asort($tt2, SORT_NUMERIC);
        end($tt2);
        return ['lemma' => $tt[key($tt2)], 'tag' => key($tt2)];
    }
    /**
     *
     * @param type $xml
     * @return type
     */
    private function parseSentimentAnalyzer($xml)
    {
        $dom           = new DOMDocument();
        $dom->loadXML($xml);
        $xpath         = new DOMXPath($dom);
        $feedbackValue = $xpath->query('//sentiment/text()')->item(0)->nodeValue;
        return [
            'feedback' => ($feedbackValue == 'positive' ? self::POSITIVE : ($feedbackValue == 'negative' ? self::NEGATIVE : self::NEUTRAL)),
            'score' => floatval($xpath->query('//scode/text()')->item(0)->nodeValue)
        ];
    }
    /**
     *
     * @param string $url
     * @param array $params
     * @return string
     */
    private function connector($url, array $params)
    {
        $this->curl->setDefaultHeaders([CURLOPT_TIMEOUT => 5]);
        /** @var cURLResponse $response */
        $response = $this->curl->get($this->curl->buildUrl($url, $params));
        return $response['body'];
    }
    /**
     *
     * @param DOMNodeList $list
     * @return array
     */
    private function XMLtoArray(DOMNodeList $list)
    {
        $array = [];
        foreach ($list as $node) {
            $array[] = $node->nodeValue;
        }
        return $array;
    }
}
