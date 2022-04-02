<?php

namespace Tests\AppBundle\Search;

use AppBundle\Search\QueryParser;
use AppBundle\Search\QueryParser\Clause;
use AppBundle\Search\QueryParser\SpecialToken;
use AppBundle\Search\QueryParser\StringToken;
use PHPUnit\Framework\TestCase;

class QueryParserTest extends TestCase
{
    /**
     * @dataProvider parseTokensProvider
     */
    public function testParseTokens($query, $result)
    {
        $tokens = QueryParser::parseTokens($query);
        $this->assertEquals($result, $tokens);
    }

    public function parseTokensProvider()
    {
        return [
            // Empty query
            ['', []],
            ['     ', []],

            // Basic tokens
            ['foo', ['foo']],
            ['foo   bar ', ['foo', 'bar']],

            // Literals
            ['"foo bar"', ['foo bar']],
            [' " foo  bar " ', [' foo  bar ']],
            ['blah "foo bar" hi', ['blah', 'foo bar', 'hi']],

            // Unterminated literals
            ['foo "bar baz', ['foo', 'bar baz']],

            // Quote/parentheses in the middle of a string
            ['foo"b(a)r', ['foo"b(a)r']],

            // Escaping
            ['foo\"bar "hey \" ho"', ['foo"bar', 'hey " ho']],
            ['foo\\\\ "bar baz"', ['foo\\', 'bar baz']],
            ['\(foo', ['(foo']],
            ['foo\)', ['foo)']],
            // Escaping a character that does not need to be escaped should
            // not swallow the backslash
            ['fo\\o', ['fo\\o']],

            // Parentheses
            ['(foo)', ['(', 'foo', ')']],
            [' ( foo ) ', ['(', 'foo', ')']],
            [' (((( foo ) ', ['(', '(', '(', '(', 'foo', ')']],
            ['( ))', ['(', ')', ')']],
            ['( "foo )")', ['(', 'foo )', ')']],
            ['\(\)', ['()']],
            ['\( \)', ['(', ')']],

            // Booleans (no special handling)
            ['A OR B AND (C OR D)', ['A', 'OR', 'B', 'AND', '(', 'C', 'OR', 'D', ')']],

            // TODO: 'AND' is a literal
            ['A "AND"', ['A', 'AND']],
        ];
    }

    /**
     * @dataProvider balanceParenthesesProvider
     */
    public function testBalanceParentheses($tokens, $result)
    {
        $this->assertEquals($result, QueryParser::balanceParentheses($tokens));
    }

    public function balanceParenthesesProvider()
    {
        $OP = new SpecialToken('(');
        $CL = new SpecialToken(')');
        $A = new StringToken('A');
        $B = new StringToken('B');

        return [
            [[], []],
            [['foo bar'], ['foo bar']],
            [[$OP, $A, $CL], [$OP, $A, $CL]],

            // missing closing parenthesis
            [[$OP, $A, $OP, $B, $CL], [$OP, $A, $OP, $B, $CL, $CL]],
            // missing opening paranthesis
            [[$OP, $A, $CL, $CL, $B], [$OP, $OP, $A, $CL, $CL, $B]],

            [[$CL, $OP], [$OP, $CL, $OP, $CL]],
            [[$OP, $CL, $OP], [$OP, $CL, $OP, $CL]],
            [[$CL, $OP, $CL], [$OP, $CL, $OP, $CL]],

            // ignores parentheses that are StringTokens
            [[new StringToken('('), $A], [new StringToken('('), $A]],
        ];
    }

    /**
     * @dataProvider sanitizeOperatorsProvider
     */
    public function testSanitizeOperators($tokens, $result)
    {
        $this->assertEquals($result, QueryParser::sanitizeOperators($tokens));
    }

    public function sanitizeOperatorsProvider()
    {
        return [
            [[
                new SpecialToken('AND'),
            ], [
            ]],

            [[
                new SpecialToken('('),
                new StringToken('A'),
                new SpecialToken(')'),
                new SpecialToken('AND'),
                new SpecialToken('('),
                new StringToken('B'),
                new SpecialToken(')'),
            ], [
                new SpecialToken('('),
                new StringToken('A'),
                new SpecialToken(')'),
                new SpecialToken('AND'),
                new SpecialToken('('),
                new StringToken('B'),
                new SpecialToken(')'),
            ]],

            [[
                new SpecialToken('AND'),
                new SpecialToken('AND'),
            ], [
            ]],

            [[
                new SpecialToken('AND'),
                new SpecialToken('('),
                new SpecialToken('AND'),
            ], [
                new SpecialToken('('),
            ]],

            [[
                new SpecialToken('AND'),
                new SpecialToken(')'),
                new SpecialToken('AND'),
            ], [
                new SpecialToken(')'),
            ]],

            [[
                new SpecialToken('AND'),
                new StringToken('A'),
            ], [
                new StringToken('A'),
            ]],

            [[
                new StringToken('A'),
                new SpecialToken('AND'),
            ], [
                new StringToken('A'),
            ]],

            [[
                new SpecialToken('AND'),
                new SpecialToken('AND'),
                new StringToken('A'),
                new SpecialToken('AND'),
                new SpecialToken('AND'),
            ], [
                new StringToken('A'),
            ]],

            [[
                new SpecialToken('('),
                new SpecialToken('AND'),
                new SpecialToken(')'),
            ], [
                new SpecialToken('('),
                new SpecialToken(')'),
            ]],

            [[
                new SpecialToken('('),
                new StringToken('A'),
                new SpecialToken('AND'),
                new SpecialToken(')'),
            ], [
                new SpecialToken('('),
                new StringToken('A'),
                new SpecialToken(')'),
            ]],

            [[
                new SpecialToken('('),
                new SpecialToken('AND'),
                new StringToken('A'),
                new SpecialToken(')'),
            ], [
                new SpecialToken('('),
                new StringToken('A'),
                new SpecialToken(')'),
            ]],

            [[
                new StringToken('A'),
                new SpecialToken('('),
                new SpecialToken('AND'),
                new SpecialToken(')'),
            ], [
                new StringToken('A'),
                new SpecialToken('AND'),
                new SpecialToken('('),
                new SpecialToken(')'),
            ]],

            [[
                new StringToken('A'),
                new SpecialToken('('),
                new SpecialToken('AND'),
                new StringToken('B'),
                new SpecialToken('AND'),
                new SpecialToken(')'),
                new StringToken('C'),
            ], [
                new StringToken('A'),
                new SpecialToken('AND'),
                new SpecialToken('('),
                new StringToken('B'),
                new SpecialToken(')'),
                new SpecialToken('AND'),
                new StringToken('C'),
            ]],

            [[
                new StringToken('A'),
                new StringToken('B'),
                new StringToken('C'),
                new SpecialToken('('),
                new StringToken('D'),
                new StringToken('E'),
                new SpecialToken(')'),
                new StringToken('F'),
            ], [
                new StringToken('A'),
                new SpecialToken('AND'),
                new StringToken('B'),
                new SpecialToken('AND'),
                new StringToken('C'),
                new SpecialToken('AND'),
                new SpecialToken('('),
                new StringToken('D'),
                new SpecialToken('AND'),
                new StringToken('E'),
                new SpecialToken(')'),
                new SpecialToken('AND'),
                new StringToken('F'),
            ]],
        ];
    }

    /**
     * @dataProvider convertParenthesesToArraysProvider
     */
    public function testConvertParenthesesToArrays($tokens, $result)
    {
        $this->assertEquals($result, QueryParser::convertParenthesesToArrays($tokens));
    }

    public function convertParenthesesToArraysProvider()
    {
        return [
            [[
            ], [
            ]],

            [[
                new SpecialToken('('),
                new SpecialToken(')'),
            ], [
                [],
            ]],

            [[
                new StringToken('A'),
            ], [
                new StringToken('A'),
            ]],

            [[
                new StringToken('A'),
                new SpecialToken('AND'),
                new StringToken('B'),
            ], [
                new StringToken('A'),
                new SpecialToken('AND'),
                new StringToken('B'),
            ]],

            [[
                new StringToken('A'),
                new SpecialToken('AND'),
                new SpecialToken('('),
                new StringToken('B'),
                new SpecialToken(')'),
            ], [
                new StringToken('A'),
                new SpecialToken('AND'),
                new StringToken('B'),
            ]],

            [[
                new StringToken('A'),
                new SpecialToken('AND'),
                new SpecialToken('('),
                new SpecialToken('('),
                new SpecialToken('('),
                new StringToken('B'),
                new SpecialToken(')'),
                new SpecialToken(')'),
                new SpecialToken(')'),
            ], [
                new StringToken('A'),
                new SpecialToken('AND'),
                new StringToken('B'),
            ]],

            [[
                new StringToken('A'),
                new SpecialToken('AND'),
                new SpecialToken('('),
                new StringToken('B'),
                new SpecialToken('OR'),
                new SpecialToken('('),
                new StringToken('C'),
                new SpecialToken(')'),
                new SpecialToken(')'),
            ], [
                new StringToken('A'),
                new SpecialToken('AND'),
                [
                    new StringToken('B'),
                    new SpecialToken('OR'),
                    new StringToken('C'),
                ],
            ]],
        ];
    }

    /**
     * @dataProvider buildASTDataProvider
     */
    public function testBuildAST($tokens, $result)
    {
        $this->assertEquals($result, QueryParser::buildAST($tokens));
    }

    public function buildASTDataProvider()
    {
        return [
            [[], null],

            [[
                new StringToken('A'),
            ],
                new StringToken('A'),
            ],

            [[
                new StringToken('A'),
                new SpecialToken('OR'),
                new StringToken('B'),
                new SpecialToken('AND'),
                [],
            ],
                new Clause(
                    new SpecialToken('OR'),
                    [
                        new StringToken('A'),
                        new Clause(
                            new SpecialToken('AND'),
                            [
                                new StringToken('B'),
                                null,
                            ]
                        ),
                    ]
                ),
            ],

            [[
                new StringToken('A'),
                new SpecialToken('AND'),
                new StringToken('B'),
            ],
                new Clause(
                    new SpecialToken('AND'),
                    [
                        new StringToken('A'),
                        new StringToken('B'),
                    ]
                ),
            ],

            [[
                new StringToken('A'),
                new SpecialToken('AND'),
                new StringToken('B'),
                new SpecialToken('AND'),
                new StringToken('C'),
                new SpecialToken('AND'),
                new StringToken('D'),
            ],
                new Clause(
                    new SpecialToken('AND'),
                    [
                        new StringToken('A'),
                        new StringToken('B'),
                        new StringToken('C'),
                        new StringToken('D'),
                    ]
                ),
            ],

            [[
                new StringToken('A'),
                new SpecialToken('AND'),
                new StringToken('B'),
                new SpecialToken('OR'),
                new StringToken('C'),
                new SpecialToken('AND'),
                new StringToken('D'),
            ],
                new Clause(
                    new SpecialToken('OR'),
                    [
                        new Clause(
                            new SpecialToken('AND'),
                            [
                                new StringToken('A'),
                                new StringToken('B'),
                            ]
                        ),
                        new Clause(
                            new SpecialToken('AND'),
                            [
                                new StringToken('C'),
                                new StringToken('D'),
                            ]
                        ),
                    ]
                ),
            ],

            [[
                new StringToken('A'),
                new SpecialToken('AND'),
                new StringToken('B'),
                new SpecialToken('OR'),
                new StringToken('C'),
            ],
                new Clause(
                    new SpecialToken('OR'),
                    [
                        new Clause(
                            new SpecialToken('AND'),
                            [
                                new StringToken('A'),
                                new StringToken('B'),
                            ]
                            ),
                        new StringToken('C'),
                    ]
                ),
            ],

            [[
                new StringToken('A'),
                new SpecialToken('OR'),
                new StringToken('B'),
                new SpecialToken('AND'),
                new StringToken('C'),
            ],
                new Clause(
                    new SpecialToken('OR'),
                    [
                        new StringToken('A'),
                        new Clause(
                            new SpecialToken('AND'),
                            [
                                new StringToken('B'),
                                new StringToken('C'),
                            ]
                        ),
                    ]
                ),
            ],

            [[
                [
                    new StringToken('A'),
                    new SpecialToken('OR'),
                    new StringToken('B'),
                ],
                new SpecialToken('AND'),
                new StringToken('C'),
            ],
                new Clause(
                    new SpecialToken('AND'),
                    [
                        new Clause(
                            new SpecialToken('OR'),
                            [
                                new StringToken('A'),
                                new StringToken('B'),
                            ]
                        ),
                        new StringToken('C'),
                    ]
                ),
            ],

            [[
                [
                    new StringToken('A'),
                    new SpecialToken('OR'),
                    new StringToken('B'),
                ],
            ],
                new Clause(
                    new SpecialToken('OR'),
                    [
                        new StringToken('A'),
                        new StringToken('B'),
                    ]
                ),
            ],

            [[
                [
                    new StringToken('A'),
                    new SpecialToken('OR'),
                    new StringToken('B'),
                ],
                new SpecialToken('AND'),
                [
                    new StringToken('C'),
                    new SpecialToken('OR'),
                    new StringToken('D'),
                ],
            ],
                new Clause(
                    new SpecialToken('AND'),
                    [
                        new Clause(
                            new SpecialToken('OR'),
                            [
                                new StringToken('A'),
                                new StringToken('B'),
                            ]
                        ),
                        new Clause(
                            new SpecialToken('OR'),
                            [
                                new StringToken('C'),
                                new StringToken('D'),
                            ]
                        ),
                    ]
                ),
            ],
        ];
    }
}
