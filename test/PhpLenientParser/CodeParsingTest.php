<?php

namespace PhpLenientParser;

use PhpParser\Error;
use PhpParser\ErrorHandler;
use PhpParser\Lexer;
use PhpParser\NodeDumper;
use PhpParser\Parser;

require_once __DIR__ . '/CodeTestAbstract.php';

class CodeParsingTest extends CodeTestAbstract
{
    /**
     * @dataProvider provideTestParse
     */
    public function testParse($name, $code, $expected, $modeLine)
    {
        if (null !== $modeLine) {
            $modes = array_fill_keys(explode(',', $modeLine), true);
        } else {
            $modes = [];
        }

        $parserOptions = [];

        $lexer = new Lexer\Emulative(['usedAttributes' => [
            'startLine', 'endLine', 'startFilePos', 'endFilePos', 'comments',
        ]]);
        $parser7 = (new LenientParserFactory())->create(LenientParserFactory::ONLY_PHP7, $lexer, $parserOptions);

        $output7 = $this->getParseOutput($parser7, $code, $modes);

        if (!isset($modes['php5'])) {
            $this->assertSame($expected, $output7, $name);
        } else {
            $this->markTestSkipped();
        }
    }

    public function getParseOutput(Parser $parser, $code, array $modes)
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

        return canonicalize($output);
    }

    public function provideTestParse()
    {
        return $this->getTests(__DIR__ . '/../code/parser', 'test');
    }

    private function formatErrorMessage(Error $e, $code)
    {
        if ($e->hasColumnInfo()) {
            return $e->getMessageWithColumnInfo($code);
        } else {
            return $e->getMessage();
        }
    }
}
