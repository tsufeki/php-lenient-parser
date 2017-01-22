<?php

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Parser\Tokens;
use PhpParser\Node;

class HereDoc extends Encapsed
{
    /**
     * @var bool
     */
    private $nowDoc;

    /**
     * @param Identifier $identifierParser
     * @param Variable $variableParser
     */
    public function __construct($identifierParser, $variableParser)
    {
        parent::__construct(Tokens::T_START_HEREDOC, Node\Scalar\Encapsed::class, $identifierParser, $variableParser);
    }

    public function parse(ParserStateInterface $parser)
    {
        $token = $parser->lookAhead();
        preg_match('/^[bB]?<<<[ \\t]*([\'"]?)([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)[\'"]?/',
            $token->value, $matches);
        $this->nowDoc = $matches[1] === '\'';
        $label = $matches[2];

        /** @var Node\Scalar\Encapsed */
        $encapsed = parent::parse($parser);
        /** @var Node\Scalar\EncapsedStringPart[]|Node\Expr[] */
        $parts = [];
        /** @var Node\Scalar\EncapsedStringPart|Node\Expr */
        foreach ($encapsed->parts as $part) {
            if (!($part instanceof Node\Scalar\EncapsedStringPart) || $part->value !== '') {
                $parts[] = $part;
            }
        }

        $node = null;
        if (count($parts) === 0) {
            $node = new Node\Scalar\String_('');
        } elseif (count($parts) === 1 && $parts[0] instanceof Node\Scalar\EncapsedStringPart) {
            $node = new Node\Scalar\String_($parts[0]->value);
        } else {
            $node = new Node\Scalar\Encapsed($parts);
        }

        $parser->setAttributes($node, $token, $parser->last());
        $node->setAttribute('kind', $this->nowDoc ? Node\Scalar\String_::KIND_NOWDOC : Node\Scalar\String_::KIND_HEREDOC);
        $node->setAttribute('docLabel', $label);

        return $node;
    }

    protected function getEndToken()
    {
        return Tokens::T_END_HEREDOC;
    }

    protected function parseStringPart(ParserStateInterface $parser)
    {
        $token = $parser->eat();
        $value = $token->value;
        $value = preg_replace('/(\\r\\n|\\n|\\r)\z/', '', $value);
        if (!$this->nowDoc) {
            $value = String_::replaceEscapes($value);
            $value = String_::replaceBackslashes($value);
        }

        return $parser->setAttributes(new Node\Scalar\EncapsedStringPart($value), $token, $token);
    }
}
