<?php

namespace Millancore\Pesto\Compiler;

use Symfony\Component\DomCrawler\Crawler;

class Pesto
{
    private Crawler $crawler;

    public function __construct(string $html)
    {
        $this->crawler = new Crawler($html);
    }


    public function find(string $selector): NodeCollection
    {
        $crawlerNodes = $this->crawler->filter($selector);

        return new NodeCollection($crawlerNodes);
    }


    public function getCompiledTemplate(): string
    {
        $bodyNode = $this->crawler->filter('body')->getNode(0);
        if (!$bodyNode) {
            return '';
        }

        $output = '';
        foreach ($bodyNode->childNodes as $child) {
            $output .= $child->ownerDocument->saveXML($child);
        }

        return html_entity_decode($output, ENT_QUOTES, 'UTF-8');
    }


}