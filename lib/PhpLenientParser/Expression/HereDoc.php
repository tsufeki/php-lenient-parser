<?php declare(strict_types=1);

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpLenientParser\Token;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

class HereDoc extends Encapsed
{
    public function __construct(Identifier $identifierParser, Variable $variableParser)
    {
        parent::__construct(Tokens::T_START_HEREDOC, Node\Scalar\Encapsed::class, $identifierParser, $variableParser);
    }

    public function parse(ParserStateInterface $parser): ?Node\Expr
    {
        $token = $parser->lookAhead();
        preg_match('/\\A[bB]?<<<[ \\t]*([\'"]?)([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)[\'"]?/', $token->value, $matches);

        if ($matches[1] === '\'') {
            $node = $this->parseNowDoc($parser, ['docLabel' => $matches[2], 'kind' => Node\Scalar\String_::KIND_NOWDOC]);
        } else {
            $node = $this->parseHereDoc($parser, ['docLabel' => $matches[2], 'kind' => Node\Scalar\String_::KIND_HEREDOC]);
        }

        return $node;
    }

    protected function getEndToken(): int
    {
        return Tokens::T_END_HEREDOC;
    }

    private function parseNowDoc(ParserStateInterface $parser, array $attrs): Node\Scalar\String_
    {
        $token = $parser->eat();
        $value = '';
        if (!$parser->isNext($this->getEndToken())) {
            $value = $parser->eat()->value;
            $value = preg_replace('/(\\r\\n|\\n|\\r)\z/', '', $value) ?? $value;
        }

        $parser->assert($this->getEndToken());
        $indent = $attrs['docIndentation'] = $this->getIndent($parser);
        $value = $this->stripIndent($value, $indent, true, true, $parser, $token);

        return new Node\Scalar\String_($value, $parser->getAttributes($token, $parser->last(), $attrs));
    }

    private function parseHereDoc(ParserStateInterface $parser, array $attrs): Node\Expr
    {
        $token = $parser->lookAhead();
        /** @var Node\Scalar\Encapsed $encapsed */
        $encapsed = parent::parse($parser);
        $indent = $attrs['docIndentation'] = $this->getIndent($parser);

        $parts = [];
        foreach ($encapsed->parts as $i => $part) {
            $first = $i === 0;
            $last = $i === count($encapsed->parts) - 1;
            if ($part instanceof Node\Scalar\EncapsedStringPart) {
                if ($last) {
                    $part->value = preg_replace('/(\\r\\n|\\n|\\r)\z/', '', $part->value) ?? $part->value;
                }

                $part->value = $this->stripIndent($part->value, $indent, $first, $last, $parser, $part);
                $part->value = String_::replaceEscapes($part->value);
                $part->value = String_::replaceBackslashes($part->value);
                if ($part->value !== '') {
                    $parts[] = $part;
                }
            } else {
                if ($first) {
                    // Collect errors when there is no initial indent
                    $this->stripIndent('', $indent, true, false, $parser, $part);
                }
                $parts[] = $part;
            }
        }

        $attrs = $parser->getAttributes($token, $parser->last(), $attrs);
        if (count($parts) === 0) {
            $node = new Node\Scalar\String_('', $attrs);
        } elseif (count($parts) === 1 && $parts[0] instanceof Node\Scalar\EncapsedStringPart) {
            $node = new Node\Scalar\String_($parts[0]->value, $attrs);
        } else {
            $node = new Node\Scalar\Encapsed($parts, $attrs);
        }

        return $node;
    }

    private function getIndent(ParserStateInterface $parser): string
    {
        $token = $parser->last();
        if ($token->type !== $this->getEndToken()) {
            return '';
        }

        preg_match('/\\A[ \\t]*/', $token->value, $matches);

        if (strpos($matches[0], ' ') !== false && strpos($matches[0], "\t") !== false) {
            $parser->addError(
                'Invalid indentation - tabs and spaces cannot be mixed',
                $token->getAttributes()
            );

            return '';
        }

        return $matches[0];
    }

    /**
     * @param Node|Token $node
     */
    private function stripIndent(
        string $value,
        string $indent,
        bool $first,
        bool $last,
        ParserStateInterface $parser,
        $node
    ): string {
        $start = $first ? '(?:\A|(?<=[\\r\\n]))' : '(?<=[\\r\\n])';
        $end = $last ? '(?:\z|(?=[\\r\\n]))' : '(?=[\\r\\n])';
        $len = strlen($indent);

        $addError = function (string $msg) use ($parser, $node) {
            $parser->addError($msg, $node->getAttributes());
        };

        $value = preg_replace_callback(
            "/$start([ \\t]{0,$len})($end)?/",
            function ($matches) use ($indent, $addError) {
                return $this->stripIndentFromLine($matches[1], isset($matches[2]), $indent, $addError);
            },
            $value
        ) ?? '';

        return $value;
    }

    private function stripIndentFromLine(string $actualIndent, bool $isWholeLine, string $indent, callable $addError): string
    {
        if ($indent !== '' && strpos($actualIndent, $indent[0] === ' ' ? "\t" : ' ') !== false) {
            $addError('Invalid indentation - tabs and spaces cannot be mixed');

            return '';
        }

        if (!$isWholeLine && $actualIndent !== $indent) {
            $len = strlen($indent);
            $addError("Invalid body indentation level (expecting an indentation level of at least $len)");

            return '';
        }

        return '';
    }

    protected function parseStringPart(ParserStateInterface $parser): Node\Scalar\EncapsedStringPart
    {
        $token = $parser->eat();
        $value = $token->value;

        return new Node\Scalar\EncapsedStringPart($value, $parser->getAttributes($token, $token));
    }
}
