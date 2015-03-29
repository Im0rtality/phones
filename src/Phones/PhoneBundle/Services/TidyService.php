<?php

namespace Phones\PhoneBundle\Services;

use \tidy;

class TidyService
{
    /** @var  tidy */
    private $tidy;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->tidy = new tidy();
    }

    /**
     * Tide the html content
     *
     * @param $content
     * @return Tidy
     */
    public function tidyTheContent($content)
    {
        $options = array('indent' => true);
        $this->tidy->parseString($content, $options, 'UTF8');
        $this->tidy->cleanRepair();

        return $this->tidy;
    }

    /**
     * @param \DOMDocument $domDoc
     * @param string       $query
     *
     * @return string
     */
    public function getInnerHtmlByQuery($domDoc, $query)
    {
        $finder = new \DomXPath($domDoc);
        $nodes = $finder->query($query);

        $tmpDom = new \DOMDocument();
        foreach ($nodes as $node) {
            $tmpDom->appendChild($tmpDom->importNode($node, true));
        }

        $innerHTML = trim($tmpDom->saveHTML());

        return $innerHTML;
    }
}
