<?php

namespace AppBundle\Services;

use anlutro\cURL\cURL;
use DOMDocument;
use DOMNodeList;
use DOMXPath;
use Symfony\Bridge\Monolog\Logger;

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
     * @var Logger
     */
    private $logger;

    /**
     *
     * @param cURL $curl
     */
    public function __construct(cURL $curl, Logger $logger)
    {
        $this->curl   = $curl;
        $this->logger = $logger;
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
        return ['word' => $word] + $this->parseLexiconLookup($this->connector('http://classify.at.northwestern.edu/maserver/lexiconlookup', ['corpusConfig' => 'eme', 'media' => 'xml', 'spelling' => $word]));
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
     * @url http://classify.at.northwestern.edu/maserver/lemmatizer
     * @access public
     *
     * @param string $word
     * @param string $wordClass (verb, noun, adjetive..)
     * @return array
     */
    public function lemmatizer($word, $wordClass)
    {
        return ['word' => $word] + $this->parseLemmatizer($this->connector('http://classify.at.northwestern.edu/maserver/lemmatizer', ['corpusConfig' => 'eme', 'media' => 'xml', 'spelling' => $word, 'standardize' => true, 'wordClass' => $this->tagToClass($wordClass)]));
    }
    /**
     *
     * @param string $xml
     * @return array ['lemma' => 'find', 'tag' => 'vvb']
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
        return ['lemma' => $tt[key($tt2)], 'tag' => key($tt2), 'class' => $this->tagToClass(key($tt2))];
    }
    /**
     *
     * @param string $xml
     * @return array
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
     * @param string $xml
     * @return array ['lemma' => <lemma>, 'stem' => <stem>]
     */
    private function parseLemmatizer($xml)
    {
        $dom   = new DOMDocument();
        $dom->loadXML($xml);
        $xpath = new DOMXPath($dom);
        return [
            'lemma' => $xpath->query('//lemma/text()')->item(0)->nodeValue,
            'stem' => $xpath->query('//porterStem/text()')->item(0)->nodeValue
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
        $this->logger->debug($url . http_build_query($params));
        $this->curl->setDefaultHeaders([CURLOPT_TIMEOUT => 5]);
        /** @var cURLResponse $response */
        $response = $this->curl->get($this->curl->buildUrl($url, $params));
        $this->logger->debug('Response: ' . json_encode($response));
        return $response->body;
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
    /**
     *
     * @param string $tag
     * @return string
     */
    private function tagToClass($tag)
    {
        switch (1) {
            case preg_match('/^(n|an.*|np.*)$/', $tag):
                return 'noun';
            case preg_match('/^av.*$/', $tag):
                return 'adverb';
            case preg_match('/^c(c|s).*$/', $tag):
                return 'conjunction';
            case preg_match('/^(d|dt.*)$/', $tag):
                return 'determiner';
            case preg_match('/^j.*$/', $tag):
                return 'adjective';
            case preg_match('/^p(f|p).*$/', $tag):
                return 'preposition';
            case preg_match('/^p(i.*|n.*|o.*|x.*)$/', $tag):
                return 'pronoun';
            case preg_match('/^pu.*$/', $tag):
                return 'punctuation';
            case preg_match('/^sy.*$/', $tag):
                return 'symbol';
            case preg_match('/^uh.*$/', $tag):
                return 'interjection';
            case preg_match('/^v(a.*|m.*|v.*|b.*)$/', $tag):
                return 'interjection';
            case preg_match('/^xx.*$/', $tag):
                return 'negative';
            case preg_match('/^zz.*$/', $tag):
                return 'undetermined';
            default:
                return $tag;
        }
    }
}
