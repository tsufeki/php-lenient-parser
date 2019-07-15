<?php declare(strict_types=1);

namespace PhpLenientParser\Statement;

use PhpLenientParser\ParserStateInterface;
use PhpParser\Node;
use PhpParser\Parser\Tokens;

class MemberModifier implements StatementInterface
{
    private const MODIFIERS = [
        Tokens::T_PUBLIC => Node\Stmt\Class_::MODIFIER_PUBLIC,
        Tokens::T_PROTECTED => Node\Stmt\Class_::MODIFIER_PROTECTED,
        Tokens::T_PRIVATE => Node\Stmt\Class_::MODIFIER_PRIVATE,
        Tokens::T_STATIC => Node\Stmt\Class_::MODIFIER_STATIC,
        Tokens::T_ABSTRACT => Node\Stmt\Class_::MODIFIER_ABSTRACT,
        Tokens::T_FINAL => Node\Stmt\Class_::MODIFIER_FINAL,
    ];

    private const MODIFIER_NAMES = [
        Node\Stmt\Class_::MODIFIER_STATIC => 'static',
        Node\Stmt\Class_::MODIFIER_ABSTRACT => 'abstract',
        Node\Stmt\Class_::MODIFIER_FINAL => 'final',
    ];

    private const NOT_STATIC_METHODS = [
        '__construct' => 'Constructor',
        '__destruct' => 'Destructor',
        '__clone' => 'Clone method',
    ];

    /**
     * @var StatementParserInterface
     */
    private $classMemberStatementsParser;

    public function __construct(StatementParserInterface $classMemberStatementsParser)
    {
        $this->classMemberStatementsParser = $classMemberStatementsParser;
    }

    public function parse(ParserStateInterface $parser)
    {
        $first = $parser->lookAhead();
        $modifierTokens = [];
        while (isset(self::MODIFIERS[$parser->lookAhead()->type])) {
            $modifierTokens[] = $parser->eat();
        }

        /** @var Node\Stmt\ClassConst|Node\Stmt\ClassMethod|Node\Stmt\Property|null */
        $node = $this->classMemberStatementsParser->parse($parser)[0] ?? null;
        if ($node === null) {
            return null;
        }

        $flags = 0;
        foreach ($modifierTokens as $token) {
            $modifier = self::MODIFIERS[$token->type];

            $error = $this->checkModifiers($flags, $modifier, $node);
            if ($error !== null) {
                $parser->addError($error, $token->getAttributes());
            }

            $flags |= $modifier;
        }

        $node->flags |= $flags;
        $node->setAttributes($parser->getAttributes($first, $node, $node->getAttributes()));

        return $node;
    }

    private function checkModifiers(int $flags, int $modifier, Node $node): ?string
    {
        if (($modifier & Node\Stmt\Class_::VISIBILITY_MODIFIER_MASK) && ($flags & Node\Stmt\Class_::VISIBILITY_MODIFIER_MASK)) {
            return 'Multiple access type modifiers are not allowed';
        }

        if (($modifier & Node\Stmt\Class_::MODIFIER_ABSTRACT) && ($flags & Node\Stmt\Class_::MODIFIER_ABSTRACT)) {
            return 'Multiple abstract modifiers are not allowed';
        }

        if (($modifier & Node\Stmt\Class_::MODIFIER_STATIC) && ($flags & Node\Stmt\Class_::MODIFIER_STATIC)) {
            return 'Multiple static modifiers are not allowed';
        }

        if (($modifier & Node\Stmt\Class_::MODIFIER_FINAL) && ($flags & Node\Stmt\Class_::MODIFIER_FINAL)) {
            return 'Multiple final modifiers are not allowed';
        }

        $mask = (Node\Stmt\Class_::MODIFIER_FINAL | Node\Stmt\Class_::MODIFIER_ABSTRACT);
        if (($modifier & $mask) && ($flags & $mask)) {
            return 'Cannot use the final modifier on an abstract class member';
        }

        if (($modifier & Node\Stmt\Class_::MODIFIER_STATIC) && $node instanceof Node\Stmt\ClassMethod) {
            $name = $node->name->toString();
            $description = self::NOT_STATIC_METHODS[strtolower($name)] ?? null;
            if ($description !== null) {
                return "$description $name() cannot be static";
            }
        }

        $mask = (Node\Stmt\Class_::MODIFIER_FINAL | Node\Stmt\Class_::MODIFIER_ABSTRACT);
        if (($modifier & $mask) && $node instanceof Node\Stmt\Property) {
            $modifierName = self::MODIFIER_NAMES[$modifier];

            return "Properties cannot be declared $modifierName";
        }

        $mask = (Node\Stmt\Class_::MODIFIER_FINAL | Node\Stmt\Class_::MODIFIER_ABSTRACT | Node\Stmt\Class_::MODIFIER_STATIC);
        if (($modifier & $mask) && $node instanceof Node\Stmt\ClassConst) {
            $modifierName = self::MODIFIER_NAMES[$modifier];

            return "Cannot use '$modifierName' as constant modifier";
        }

        return null;
    }

    public function getToken(): ?int
    {
        return null;
    }
}
