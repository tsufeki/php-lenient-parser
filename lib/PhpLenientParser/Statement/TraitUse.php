<?php declare(strict_types=1);

namespace PhpLenientParser\Statement;

use PhpLenientParser\Expression\Identifier;
use PhpLenientParser\Expression\Name;
use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

class TraitUse implements StatementInterface
{
    /**
     * @var Name
     */
    private $nameParser;

    /**
     * @var Identifier
     */
    private $identifierParser;

    public function __construct(Name $nameParser, Identifier $identifierParser)
    {
        $this->nameParser = $nameParser;
        $this->identifierParser = $identifierParser;
    }

    public function parse(ParserStateInterface $parser)
    {
        $token = $parser->eat();

        $traits = [];
        do {
            $trait = $this->nameParser->parse($parser);
            if ($trait !== null) {
                $traits[] = $trait;
            }
        } while ($trait !== null && $parser->eatIf(ord(',')) !== null);

        $adaptations = [];
        if ($parser->eatIf(ord('{')) === null) {
            $parser->assert(ord(';'));
        } else {
            while (!$parser->isNext(ord('}'))) {
                $first = $parser->lookAhead();

                $name = null;
                if (
                    $parser->lookAhead()->type === Tokens::T_NS_SEPARATOR ||
                    $parser->lookAhead(1)->type === Tokens::T_NS_SEPARATOR ||
                    $parser->lookAhead(1)->type === Tokens::T_PAAMAYIM_NEKUDOTAYIM
                ) {
                    $name = $this->nameParser->parse($parser);
                }
                $parser->eatIf(Tokens::T_PAAMAYIM_NEKUDOTAYIM);
                $method = $this->identifierParser->parse($parser) ?? $this->identifierParser->makeEmpty($parser);

                if ($parser->eatIf(Tokens::T_AS) !== null) {
                    $modifier = null;
                    if ($parser->eatIf(Tokens::T_PUBLIC) !== null) {
                        $modifier = Node\Stmt\Class_::MODIFIER_PUBLIC;
                    } elseif ($parser->eatIf(Tokens::T_PROTECTED) !== null) {
                        $modifier = Node\Stmt\Class_::MODIFIER_PROTECTED;
                    } elseif ($parser->eatIf(Tokens::T_PRIVATE) !== null) {
                        $modifier = Node\Stmt\Class_::MODIFIER_PRIVATE;
                    }

                    $newMethod = $this->identifierParser->parse($parser);
                    $parser->assert(ord(';'));

                    $adaptation = new Node\Stmt\TraitUseAdaptation\Alias($name, $method, $modifier, $newMethod);
                    $parser->setAttributes($adaptation, $first, $parser->last());
                    $adaptations[] = $adaptation;
                } elseif ($name !== null) {
                    $parser->assert(Tokens::T_INSTEADOF);
                    $insteadofs = [];
                    do {
                        $insteadof = $this->nameParser->parse($parser);
                        if ($insteadof !== null) {
                            $insteadofs[] = $insteadof;
                        }
                    } while ($insteadof !== null && $parser->eatIf(ord(',')) !== null);
                    $parser->assert(ord(';'));

                    $adaptation = new Node\Stmt\TraitUseAdaptation\Precedence($name, $method, $insteadofs);
                    $parser->setAttributes($adaptation, $first, $parser->last());
                    $adaptations[] = $adaptation;
                } else {
                    break;
                }
            }
            $parser->assert(ord('}'));
        }

        $node = new Node\Stmt\TraitUse($traits, $adaptations);
        $parser->setAttributes($node, $token, $parser->last());

        return $node;
    }

    public function getToken(): ?int
    {
        return Tokens::T_USE;
    }
}
