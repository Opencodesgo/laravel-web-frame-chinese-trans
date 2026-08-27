<?php
/**
 * Hamcrest，Xml，有 X路径
 */

namespace Hamcrest\Xml;

/*
 Copyright (c) 2009 hamcrest.org
 */
use Hamcrest\Core\IsEqual;
use Hamcrest\Description;
use Hamcrest\DiagnosingMatcher;
use Hamcrest\Matcher;

/**
 * Matches if XPath applied to XML/HTML/XHTML document either
 * evaluates to result matching the matcher or returns at least
 * one node, matching the matcher if present.
 * 如果将 XPath 应用于 XML/HTML/XHTML 文档，则该表达式要么返回与匹配器相匹配的结果，
 * 要么返回至少一个与匹配器相匹配的节点（如果存在的话）。
 */
class HasXPath extends DiagnosingMatcher
{

    /**
     * XPath to apply to the DOM.
	 * 将XPath应用于DOM
     *
     * @var string
     */
    private $_xpath;

    /**
     * Optional matcher to apply to the XPath expression result
     * or the content of the returned nodes.
	 * 可选的匹配器，用于应用于 XPath 表达式的结果或返回节点的内容。
     *
     * @var Matcher
     */
    private $_matcher;

    public function __construct($xpath, ?Matcher $matcher = null)
    {
        $this->_xpath = $xpath;
        $this->_matcher = $matcher;
    }

    /**
     * Matches if the XPath matches against the DOM node and the matcher.
	 * 如果XPath与DOM节点和匹配器匹配，则匹配。
     *
     * @param string|\DOMNode $actual
     * @param Description $mismatchDescription
     * @return bool
     */
    protected function matchesWithDiagnosticDescription($actual, Description $mismatchDescription)
    {
        if (is_string($actual)) {
            $actual = $this->createDocument($actual);
        } elseif (!$actual instanceof \DOMNode) {
            $mismatchDescription->appendText('was ')->appendValue($actual);

            return false;
        }
        $result = $this->evaluate($actual);
        if ($result instanceof \DOMNodeList) {
            return $this->matchesContent($result, $mismatchDescription);
        } else {
            return $this->matchesExpression($result, $mismatchDescription);
        }
    }

    /**
     * Creates and returns a <code>DOMDocument</code> from the given
     * XML or HTML string.
     *
     * @param string $text
     * @return \DOMDocument built from <code>$text</code>
     * @throws \InvalidArgumentException if the document is not valid
     */
    protected function createDocument($text)
    {
        $document = new \DOMDocument();
        if (preg_match('/^\s*<\?xml/', $text)) {
            if (!@$document->loadXML($text)) {
                throw new \InvalidArgumentException('Must pass a valid XML document');
            }
        } else {
            if (!@$document->loadHTML($text)) {
                throw new \InvalidArgumentException('Must pass a valid HTML or XHTML document');
            }
        }

        return $document;
    }

    /**
     * Applies the configured XPath to the DOM node and returns either
     * the result if it's an expression or the node list if it's a query.
	 * 将配置过的XPath应用于DOM节点并返回结果,如果它是一个表达式或节点列表,如果它是一个查询。
     *
     * @param \DOMNode $node context from which to issue query
     * @return mixed result of expression or DOMNodeList from query
     */
    protected function evaluate(\DOMNode $node)
    {
        if ($node instanceof \DOMDocument) {
            $xpathDocument = new \DOMXPath($node);

            return $xpathDocument->evaluate($this->_xpath);
        } else {
            $xpathDocument = new \DOMXPath($node->ownerDocument);

            return $xpathDocument->evaluate($this->_xpath, $node);
        }
    }

    /**
     * Matches if the list of nodes is not empty and the content of at least
     * one node matches the configured matcher, if supplied.
	 * 如果节点列表不是空的,则匹配配置的matcher,如果提供,则至少一个节点的内容匹配。
     *
     * @param \DOMNodeList $nodes selected by the XPath query
     * @param Description $mismatchDescription
     * @return bool
     */
    protected function matchesContent(\DOMNodeList $nodes, Description $mismatchDescription)
    {
        if ($nodes->length == 0) {
            $mismatchDescription->appendText('XPath returned no results');
        } elseif ($this->_matcher === null) {
            return true;
        } else {
            foreach ($nodes as $node) {
                if ($this->_matcher->matches($node->textContent)) {
                    return true;
                }
            }
            $content = array();
            foreach ($nodes as $node) {
                $content[] = $node->textContent;
            }
            $mismatchDescription->appendText('XPath returned ')
                                                    ->appendValue($content);
        }

        return false;
    }

    /**
     * Matches if the result of the XPath expression matches the configured
     * matcher or evaluates to <code>true</code> if there is none.
     *
     * @param mixed $result result of the XPath expression
     * @param Description $mismatchDescription
     * @return bool
     */
    protected function matchesExpression($result, Description $mismatchDescription)
    {
        if ($this->_matcher === null) {
            if ($result) {
                return true;
            }
            $mismatchDescription->appendText('XPath expression result was ')
                                                    ->appendValue($result);
        } else {
            if ($this->_matcher->matches($result)) {
                return true;
            }
            $mismatchDescription->appendText('XPath expression result ');
            $this->_matcher->describeMismatch($result, $mismatchDescription);
        }

        return false;
    }

    public function describeTo(Description $description)
    {
        $description->appendText('XML or HTML document with XPath "')
                                ->appendText($this->_xpath)
                                ->appendText('"');
        if ($this->_matcher !== null) {
            $description->appendText(' ');
            $this->_matcher->describeTo($description);
        }
    }

    /**
     * Wraps <code>$matcher</code> with {@link Hamcrest\Core\IsEqual)
     * if it's not a matcher and the XPath in <code>count()</code>
     * if it's an integer.
     *
     * @factory
     */
    public static function hasXPath($xpath, $matcher = null)
    {
        if ($matcher === null || $matcher instanceof Matcher) {
            return new self($xpath, $matcher);
        } elseif (is_int($matcher) && strpos($xpath, 'count(') !== 0) {
            $xpath = 'count(' . $xpath . ')';
        }

        return new self($xpath, IsEqual::equalTo($matcher));
    }
}
