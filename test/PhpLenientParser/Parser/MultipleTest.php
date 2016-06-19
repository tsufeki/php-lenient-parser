<?php

namespace PhpLenientParser\Parser;

use PhpLenientParser\Error;
use PhpLenientParser\Lexer;
use PhpLenientParser\Node\Scalar\LNumber;
use PhpLenientParser\ParserTest;
use PhpLenientParser\Node\Expr;
use PhpLenientParser\Node\Stmt;

require_once __DIR__ . '/../ParserTest.php';

class MultipleTest extends ParserTest {
    // This provider is for the generic parser tests, just pick an arbitrary order here
    protected function getParser(Lexer $lexer) {
        return new Multiple([new Php5($lexer), new Php7($lexer)]);
    }

    private function getPrefer7() {
        $lexer = new Lexer(['usedAttributes' => []]);
        return new Multiple([new Php7($lexer), new Php5($lexer)]);
    }

    private function getPrefer5() {
        $lexer = new Lexer(['usedAttributes' => []]);
        return new Multiple([new Php5($lexer), new Php7($lexer)]);
    }

    /** @dataProvider provideTestParse */
    public function testParse($code, Multiple $parser, $expected) {
        $this->assertEquals($expected, $parser->parse($code));
        $this->assertSame([], $parser->getErrors());
    }

    public function provideTestParse() {
        return [
            [
                // PHP 7 only code
                '<?php class Test { function function() {} }',
                $this->getPrefer5(),
                [
                    new Stmt\Class_('Test', ['stmts' => [
                        new Stmt\ClassMethod('function')
                    ]]),
                ]
            ],
            [
                // PHP 5 only code
                '<?php global $$a->b;',
                $this->getPrefer7(),
                [
                    new Stmt\Global_([
                        new Expr\Variable(new Expr\PropertyFetch(new Expr\Variable('a'), 'b'))
                    ])
                ]
            ],
            [
                // Different meaning (PHP 5)
                '<?php $$a[0];',
                $this->getPrefer5(),
                [
                    new Expr\Variable(
                        new Expr\ArrayDimFetch(new Expr\Variable('a'), LNumber::fromString('0'))
                    )
                ]
            ],
            [
                // Different meaning (PHP 7)
                '<?php $$a[0];',
                $this->getPrefer7(),
                [
                    new Expr\ArrayDimFetch(
                        new Expr\Variable(new Expr\Variable('a')), LNumber::fromString('0')
                    )
                ]
            ],
        ];
    }

    public function testThrownError() {
        $this->setExpectedException('PhpLenientParser\Error', 'FAIL A');

        $parserA = $this->getMockBuilder('PhpLenientParser\Parser')->getMock();
        $parserA->expects($this->at(0))
            ->method('parse')->will($this->throwException(new Error('FAIL A')));

        $parserB = $this->getMockBuilder('PhpLenientParser\Parser')->getMock();
        $parserB->expects($this->at(0))
            ->method('parse')->will($this->throwException(new Error('FAIL B')));

        $parser = new Multiple([$parserA, $parserB]);
        $parser->parse('dummy');
    }

    public function testGetErrors() {
        $errorsA = [new Error('A1'), new Error('A2')];
        $parserA = $this->getMockBuilder('PhpLenientParser\Parser')->getMock();
        $parserA->expects($this->at(0))->method('parse');
        $parserA->expects($this->at(1))
            ->method('getErrors')->will($this->returnValue($errorsA));

        $errorsB = [new Error('B1'), new Error('B2')];
        $parserB = $this->getMockBuilder('PhpLenientParser\Parser')->getMock();
        $parserB->expects($this->at(0))->method('parse');
        $parserB->expects($this->at(1))
            ->method('getErrors')->will($this->returnValue($errorsB));

        $parser = new Multiple([$parserA, $parserB]);
        $parser->parse('dummy');
        $this->assertSame($errorsA, $parser->getErrors());
    }
}