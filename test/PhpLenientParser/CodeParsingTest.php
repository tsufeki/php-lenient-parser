<?php declare(strict_types=1);

namespace PhpLenientParser;

use PhpParser\Error;
use PhpParser\ErrorHandler;
use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\NodeDumper;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Parser;

/**
 * @coversNothing
 */
class CodeParsingTest extends CodeTestAbstract
{
    /**
     * @dataProvider provideTestParse
     */
    public function testParse(string $name, string $code, string $expected, string $modeLine = null)
    {
        if (null !== $modeLine) {
            $modes = array_fill_keys(explode(',', $modeLine), true);
        } else {
            $modes = [];
        }

        $parserOptions = [];

        $lexer = new Lexer\Emulative(['usedAttributes' => [
            'startLine', 'endLine',
            'startFilePos', 'endFilePos',
            'startTokenPos', 'endTokenPos',
            'comments',
        ]]);
        $parser7 = (new LenientParserFactory())->create(LenientParserFactory::ONLY_PHP7, $lexer, $parserOptions);

        list($stmts7, $output7) = $this->getParseOutput($parser7, $code, $modes);

        if (!isset($modes['php5'])) {
            $this->assertSame($expected, $output7, $name);
        } else {
            $this->markTestSkipped();
        }

        $this->checkAttributes($stmts7);
    }

    /**
     * @param LenientParser $parser
     */
    public function getParseOutput($parser, string $code, array $modes): array
    {
        $dumpPositions = isset($modes['positions']);

        $errors = new ErrorHandler\Collecting();
        $stmts = $parser->parse($code, $errors);

        $output = '';
        foreach ($errors->getErrors() as $error) {
            $output .= $this->formatErrorMessage($error, $code) . "\n";
        }

        if (null !== $stmts) {
            $dumper = new NodeDumper(['dumpComments' => true, 'dumpPositions' => $dumpPositions]);
            $output .= $dumper->dump($stmts, $code);
        }

        return [$stmts, canonicalize($output)];
    }

    public function provideTestParse()
    {
        return $this->getTests(__DIR__ . '/../code/parser', 'test');
    }

    private function formatErrorMessage(Error $e, string $code): string
    {
        if ($e->hasColumnInfo()) {
            return $e->getMessageWithColumnInfo($code);
        }

        return $e->getMessage();
    }

    private function checkAttributes($stmts)
    {
        if ($stmts === null) {
            return;
        }

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new class() extends NodeVisitorAbstract {
            public function enterNode(Node $node)
            {
                $startLine = $node->getStartLine();
                $endLine = $node->getEndLine();
                $startFilePos = $node->getStartFilePos();
                $endFilePos = $node->getEndFilePos();
                $startTokenPos = $node->getStartTokenPos();
                $endTokenPos = $node->getEndTokenPos();
                if ($startLine < 0 || $endLine < 0 ||
                    $startFilePos < 0 || $endFilePos < 0 ||
                    $startTokenPos < 0 || $endTokenPos < 0
                ) {
                    throw new \Exception('Missing location information on ' . $node->getType());
                }

                if ($endLine < $startLine ||
                    $endFilePos < $startFilePos ||
                    $endTokenPos < $startTokenPos
                ) {
                    // Nops and error can have inverted order, if they are empty
                    if (!$node instanceof Stmt\Nop && !$node instanceof Expr\Error) {
                        throw new \Exception('End < start on ' . $node->getType());
                    }
                }
            }
        });
        $traverser->traverse($stmts);
    }
}
