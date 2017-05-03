<?php

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

    /**
     * @param Name       $nameParser
     * @param Identifier $identifierParser
     */
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
            $trait = $this->nameParser->parserOrNull($parser);
            if ($trait !== null) {
                $traits[] = $trait;
            }
        } while ($trait !== null && $parser->eat(ord(',')) !== null);

        $adaptations = [];
        if ($parser->eat(ord('{')) === null) {
            $parser->assert(ord(';'));
        } else {
            while ($parser->lookAhead()->type !== ord('}')) {
                $first = $parser->lookAhead();

                $name = null;
                if (
                    $parser->lookAhead()->type === Tokens::T_NS_SEPARATOR ||
                    $parser->lookAhead(1)->type === Tokens::T_NS_SEPARATOR ||
                    $parser->lookAhead(1)->type === Tokens::T_PAAMAYIM_NEKUDOTAYIM
                ) {
                    $name = $this->nameParser->parserOrNull($parser);
                }
                $parser->eat(Tokens::T_PAAMAYIM_NEKUDOTAYIM);
                $method = $this->identifierParser->parse($parser);

                if ($parser->eat(Tokens::T_AS) !== null) {
                    $modifier = null;
                    if ($parser->eat(Tokens::T_PUBLIC) !== null) {
                        $modifier = Node\Stmt\Class_::MODIFIER_PUBLIC;
                    } elseif ($parser->eat(Tokens::T_PROTECTED) !== null) {
                        $modifier = Node\Stmt\Class_::MODIFIER_PROTECTED;
                    } elseif ($parser->eat(Tokens::T_PRIVATE) !== null) {
                        $modifier = Node\Stmt\Class_::MODIFIER_PRIVATE;
                    }
                    $newMethod = $this->identifierParser->parse($parser);
                    $parser->assert(ord(';'));

                    $adaptations[] = $parser->setAttributes(
                        new Node\Stmt\TraitUseAdaptation\Alias($name, $method, $modifier, $newMethod),
                        $first, $parser->last()
                    );
                } elseif ($name !== null) {
                    $parser->assert(Tokens::T_INSTEADOF);
                    $insteadofs = [];
                    do {
                        $insteadof = $this->nameParser->parserOrNull($parser);
                        if ($insteadof !== null) {
                            $insteadofs[] = $insteadof;
                        }
                    } while ($insteadof !== null && $parser->eat(ord(',')) !== null);
                    $parser->assert(ord(';'));

                    $adaptations[] = $parser->setAttributes(
                        new Node\Stmt\TraitUseAdaptation\Precedence($name, $method, $insteadofs),
                        $first, $parser->last()
                    );
                } else {
                    break;
                }
            }
            $parser->assert(ord('}'));
        }

        return $parser->setAttributes(new Node\Stmt\TraitUse($traits, $adaptations), $token, $parser->last());
    }

    public function getToken()
    {
        return Tokens::T_USE;
    }
}
