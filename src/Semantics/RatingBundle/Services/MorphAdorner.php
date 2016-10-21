<?php

namespace Semantics\RatingBundle\Services;

use anlutro\cURL\cURL;
use DOMDocument;
use DOMNodeList;
use DOMXPath;
use Exception;
use Semantics\RatingBundle\Entity\Cache;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Bridge\Monolog\Logger;

/**
 * Description of MorphAdornerService
 *
 * @author Víctor Molero
 */
final class MorphAdorner
{
    const POSITIVE = 1;
    const NEUTRAL  = 0;
    const NEGATIVE = -1;

    private static $cache = [];
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
     * @var PorterStemmer
     */
    private $stemmer;
    /**
     *
     * @var RegistryInterface
     */
    private $doctrine;

    /**
     *
     * @param cURL $curl
     */
    public function __construct(RegistryInterface $orm, cURL $curl, PorterStemmer $stemmer, Logger $logger)
    {
        $this->curl     = $curl;
        $this->logger   = $logger;
        $this->stemmer  = $stemmer;
        $this->doctrine = $orm;
    }
    /**
     * @url http://classify.at.northwestern.edu/maserver/lexiconlookup
     * @access public
     *
     * @param string $word
     * @return array ['lemma' => <lemma>, 'tag' => 'vvb', 'class' => 'verb', 'word' => <word>]
     */
    public function lexiconLookup($word)
    {
        try {
            return $this->getCache('lexiconLookup_' . md5($word));
        } catch (Exception $ignore) {
            return $this->setCache('lexiconLookup_' . md5($word), ['word' => $word, 'stem' => $this->stemmer->stem($word)] + $this->parseLexiconLookup($this->connector('http://classify.at.northwestern.edu/maserver/lexiconlookup', ['corpusConfig' => 'ncf', 'media' => 'xml', 'spelling' => $word])));
        }
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
        try {
            return $this->getCache('sentimentAnalyzer_' . md5($text));
        } catch (Exception $ignore) {
            return $this->setCache('sentimentAnalyzer_' . md5($text), ['input' => $text] + $this->parseSentimentAnalyzer($this->connector('http://classify.at.northwestern.edu/maserver/sentimentanalyzer', ['includeInputText' => false, 'media' => 'xml', 'text' => $text])));
        }
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
        try {
            return $this->getCache('lemmatizer_' . md5($word));
        } catch (Exception $ignore) {
            return $this->setCache('lemmatizer_' . md5($word), ['word' => $word, 'stem' => $this->stemmer->Stem($word)] + $this->parseLemmatizer($this->connector('http://classify.at.northwestern.edu/maserver/lemmatizer', ['corpusConfig' => 'ncf', 'media' => 'xml', 'spelling' => $word, 'standardize' => true, 'wordClass' => $this->tagToClass($wordClass)])));
        }
    }
    /**
     *
     * @param string $key
     * @param array $value
     * @return array
     */
    private function getCache($key)
    {
        try {
            $this->logger->debug("Cache get($key): ");
            return self::$cache[$key];
        } catch (Exception $ignore) {
            $cached = $this->doctrine->getRepository('RatingBundle:Cache')->findOneBy(['key' => $key]);
            if ($cached instanceof Cache) {
                return json_decode($cached->getValue(), true);
            }
            throw new Exception("Key not found");
        }
    }
    public function setCache($key, $value)
    {
        $em     = $this->doctrine->getManager();
        $this->logger->debug("Cache set($key): " . json_encode($value));
        $cached = $this->doctrine->getRepository('RatingBundle:Cache')->findOneBy(['key' => $key]);
        if (!$cached instanceof Cache) {
            $cached = (new Cache())->setKey($key);
            $em->persist($cached);
        }
        $cached->setValue(json_encode($value));
        $em->flush();
        self::$cache[$key] = $value;
        return $value;
    }
    /**
     *
     * @param string $xml
     * @return array ['lemma' => 'find', 'tag' => 'vvb']
     */
    private function parseLexiconLookup($xml)
    {
        $dom     = new DOMDocument();
        $dom->loadXML($xml);
        $xpath   = new DOMXPath($dom);
        $indexes = $this->XMLtoArray($xpath->query('//lemmata/entry/string[1]/text()'));
        if (count($indexes)) {
            $values   = $this->XMLtoArray($xpath->query('//lemmata/entry/string[2]/text()'));
            $tt       = array_combine($indexes, $values);
            $tagIndex = $xpath->query('//largestCategory/text()')->item(0)->nodeValue;
            return ['lemma' => $tt[$tagIndex], 'tag' => $tagIndex, 'class' => $this->tagToClass($tagIndex)];
        }
        return [];
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
            'score' => round(floatval($xpath->query('//score/text()')->item(0)->nodeValue), 2)
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
        $this->logger->debug($url . ' POST ' . http_build_query($params));
        $this->curl->setDefaultHeaders([CURLOPT_TIMEOUT => 5, CURLOPT_HTTPHEADER => 'Content-Type: application/x-www-form-urlencoded']);
        /** @var cURLResponse $response */
        $response = $this->curl->post($url, $params);
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
            case preg_match('/^.*x$/', $tag):
                return 'negative';
            case preg_match('/^(n|an.*|np.*|n\d+.*)$/', $tag):
                return 'noun';
            case preg_match('/^av.*$/', $tag):
                return 'adverb';
            case preg_match('/^c(c|s).*$/', $tag):
                return 'conjunction';
            case preg_match('/^(d|dt.*)$/', $tag):
                return 'determiner';
            case preg_match('/^j.*$/', $tag):
                return 'adjective';
            case preg_match('/^p(f|p|-.*|c.*).*$/', $tag):
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
                return 'verb';
            case preg_match('/^zz.*$/', $tag):
                return 'undetermined';
            default:
                return $tag;
        }
    }
}
