<?php

namespace PhpLenientParser\Expression;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

class Identifier
{
    const TOKENS = [
        Tokens::T_STRING => true,

        Tokens::T_ARRAY => true,
        Tokens::T_AS => true,
        Tokens::T_BREAK => true,
        Tokens::T_CALLABLE => true,
        Tokens::T_CASE => true,
        Tokens::T_CATCH => true,
        Tokens::T_CLASS => true,
        Tokens::T_CLASS_C => true,
        Tokens::T_CLONE => true,
        Tokens::T_CONST => true,
        Tokens::T_CONTINUE => true,
        Tokens::T_DECLARE => true,
        Tokens::T_DEFAULT => true,
        Tokens::T_DIR => true,
        Tokens::T_DO => true,
        Tokens::T_ECHO => true,
        Tokens::T_ELSE => true,
        Tokens::T_ELSEIF => true,
        Tokens::T_EMPTY => true,
        Tokens::T_ENDDECLARE => true,
        Tokens::T_ENDFOR => true,
        Tokens::T_ENDFOREACH => true,
        Tokens::T_ENDIF => true,
        Tokens::T_ENDSWITCH => true,
        Tokens::T_ENDWHILE => true,
        Tokens::T_EVAL => true,
        Tokens::T_EXIT => true,
        Tokens::T_EXTENDS => true,
        Tokens::T_FILE => true,
        Tokens::T_FINALLY => true,
        Tokens::T_FOR => true,
        Tokens::T_FOREACH => true,
        Tokens::T_FUNCTION => true,
        Tokens::T_FUNC_C => true,
        Tokens::T_GLOBAL => true,
        Tokens::T_GOTO => true,
        Tokens::T_HALT_COMPILER => true,
        Tokens::T_IF => true,
        Tokens::T_IMPLEMENTS => true,
        Tokens::T_INCLUDE => true,
        Tokens::T_INCLUDE_ONCE => true,
        Tokens::T_INSTANCEOF => true,
        Tokens::T_INSTEADOF => true,
        Tokens::T_INTERFACE => true,
        Tokens::T_ISSET => true,
        Tokens::T_LINE => true,
        Tokens::T_LIST => true,
        Tokens::T_LOGICAL_AND => true,
        Tokens::T_LOGICAL_OR => true,
        Tokens::T_LOGICAL_XOR => true,
        Tokens::T_METHOD_C => true,
        Tokens::T_NAMESPACE => true,
        Tokens::T_NEW => true,
        Tokens::T_NS_C => true,
        Tokens::T_PRINT => true,
        Tokens::T_REQUIRE => true,
        Tokens::T_REQUIRE_ONCE => true,
        Tokens::T_RETURN => true,
        Tokens::T_SWITCH => true,
        Tokens::T_THROW => true,
        Tokens::T_TRAIT => true,
        Tokens::T_TRAIT_C => true,
        Tokens::T_TRY => true,
        Tokens::T_UNSET => true,
        Tokens::T_USE => true,
        Tokens::T_VAR => true,
        Tokens::T_WHILE => true,
        Tokens::T_YIELD => true,

        Tokens::T_ABSTRACT => true,
        Tokens::T_FINAL => true,
        Tokens::T_PRIVATE => true,
        Tokens::T_PROTECTED => true,
        Tokens::T_PUBLIC => true,
        Tokens::T_STATIC => true,
    ];

    /**
     * @param ParserStateInterface $parser
     *
     * @return Node|null
     */
    public function parse(ParserStateInterface $parser)
    {
        $token = $parser->lookAhead();

        if (isset(self::TOKENS[$token->type])) {
            $parser->eat();
            $id = $token->value;
            if ($parser->getOption('useIdentifierNodes')) {
                $id = new Node\Identifier($id);
                $parser->setAttributes($node, $token, $token);
            }

            return $id;
        }

        return null;
    }
}
