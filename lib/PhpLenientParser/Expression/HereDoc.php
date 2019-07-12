<?php declare(strict_types=1);

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

class HereDoc extends Encapsed
{
    /**
     * @var bool
     */
    private $nowDoc;

    public function __construct(Identifier $identifierParser, Variable $variableParser)
    {
        parent::__construct(Tokens::T_START_HEREDOC, Node\Scalar\Encapsed::class, $identifierParser, $variableParser);
    }

    public function parse(ParserStateInterface $parser): ?Node\Expr
    {
        $token = $parser->lookAhead();
        preg_match('/^[bB]?<<<[ \\t]*([\'"]?)([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)[\'"]?/',
            $token->value, $matches);
        $this->nowDoc = $matches[1] === '\'';
        $label = $matches[2];

        /** @var Node\Scalar\Encapsed $encapsed */
        $encapsed = parent::parse($parser);
        $partsCount = count($encapsed->parts);
        if ($partsCount !== 0 && $encapsed->parts[$partsCount - 1] instanceof Node\Scalar\EncapsedStringPart) {
            $value = $encapsed->parts[$partsCount - 1]->value;
            $value = preg_replace('/(\\r\\n|\\n|\\r)\z/', '', $value);
            assert($value !== null);
            $encapsed->parts[$partsCount - 1]->value = $value;
        }

        /** @var Node\Scalar\EncapsedStringPart[]|Node\Expr[] $parts */
        $parts = [];
        /** @var Node\Scalar\EncapsedStringPart|Node\Expr $part */
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

    protected function getEndToken(): int
    {
        return Tokens::T_END_HEREDOC;
    }

    protected function parseStringPart(ParserStateInterface $parser): Node\Scalar\EncapsedStringPart
    {
        $token = $parser->eat();
        $value = $token->value;
        if (!$this->nowDoc) {
            $value = String_::replaceEscapes($value);
            $value = String_::replaceBackslashes($value);
        }

        $node = new Node\Scalar\EncapsedStringPart($value);
        $parser->setAttributes($node, $token, $token);

        return $node;
    }
}
