<?php

namespace AppBundle\Search;

use AppBundle\Search\QueryParser\Clause;
use AppBundle\Search\QueryParser\PhraseToken;
use AppBundle\Search\QueryParser\SpecialToken;
use AppBundle\Search\QueryParser\StringToken;
use AppBundle\Search\QueryParser\Token;
use ArrayObject;
use SplStack;

// Implicitly, everything the user types in the search bar is ANDed together.
// A search for 'galactic ghouls' should result in adventures that contain
// both terms. If the user really wants to search for 'galactic OR ghouls',
// the have to separate the terms by ' OR '.
// The order of terms is irrelevant: Searching for 'galactic ghouls' leads
// to the same results as searching for 'ghouls galactic'. We als support
// quoting terms ('"galactic ghouls"'), which does NOT match adventures with
//  'ghouls galactic' or adventures with 'galactic' and 'ghouls' in different
// fields.
class QueryParser
{
    private const WHITESPACE_CHARS = [' ', "\t", "\n", "\r", "\0", "\x0B"];
    private const ESCAPABLE_CHARACTERS = ['"', '(', ')', '\\'];
    private const WORD_BOUNDARY_CHARS = [
        // Whitespace
        ' ', "\t", "\n", "\r", "\0", "\x0B",
        // Begin/end of string
        '',
        '"', '(', ')',
    ];
    private const OPERATORS = ['AND', 'OR'];

    public static function parse(string $query, $fields): ?array
    {
        $tokens = self::parseTokens($query);
        $tokens = self::balanceParentheses($tokens);
        $tokens = self::sanitizeOperators($tokens);
        $tokens = self::convertParenthesesToArrays($tokens);
        $ast = self::buildAST($tokens);
        $query = self::toElasticSearchQuery($ast, $fields);

        return $query;
    }

    /**
     * Generates an array of tokens from a string. The string is split at
     * whitespace characters and parenthesis, while also taking character
     * escaping with "\" and phrase tokens into account.
     *
     * @return Token[]
     */
    public static function parseTokens(string $query): array
    {
        $query = trim($query, implode('', self::WHITESPACE_CHARS));

        $tokens = [];
        $currentToken = '';

        // Are we within a section enclosed with quotes?
        $insidePhraseToken = false;
        // Is the current character escaped by a preceding backslash?
        $characterIsEscaped = false;
        for ($i = 0; $i < mb_strlen($query); ++$i) {
            $c = mb_substr($query, $i, 1);
            $prevC = 0 === $i ? '' : mb_substr($query, $i - 1, 1);
            $nextC = mb_substr($query, $i + 1, 1);

            if ('\\' === $c && false === $characterIsEscaped) {
                // Check if next character can be escaped
                if (in_array($nextC, self::ESCAPABLE_CHARACTERS, true)) {
                    $characterIsEscaped = true;
                    continue;
                }
            }

            $isAtWordBoundary = in_array($prevC, self::WORD_BOUNDARY_CHARS, true)
                             || in_array($nextC, self::WORD_BOUNDARY_CHARS, true);

            if ($insidePhraseToken) {
                if ('"' === $c && !$characterIsEscaped && $isAtWordBoundary) {
                    // Phrase token end.
                    $tokens[] = new PhraseToken($currentToken);
                    $currentToken = '';
                    $insidePhraseToken = false;
                } else {
                    $currentToken .= $c;
                }
            } else {
                if ('"' === $c && !$characterIsEscaped && $isAtWordBoundary) {
                    // Literal token begin.
                    $insidePhraseToken = true;
                } elseif (in_array($c, self::WHITESPACE_CHARS, true)) {
                    // Token ends on whitespace characters.
                    $tokens[] = self::createToken($currentToken);
                    $currentToken = '';
                } elseif ($isAtWordBoundary && !$characterIsEscaped && ('(' === $c || ')' === $c)) {
                    $tokens[] = self::createToken($currentToken);
                    $currentToken = '';

                    $tokens[] = new SpecialToken($c);
                } else {
                    $currentToken .= $c;
                }
            }

            $characterIsEscaped = false;
        }
        // Add last token
        $tokens[] = self::createToken($currentToken);
        $currentToken = '';

        // Remove empty tokens and reindex by 0
        return array_values(array_filter($tokens, fn (Token $token) => '' !== $token->content));
    }

    /**
     * Given an array of tokens, add potentially missing parentheses so
     * that they are balanced properly.
     *
     * @return Token[]
     */
    public static function balanceParentheses(array $tokens): array
    {
        $nOpen = 0;
        $nClose = 0;
        $sum = 0;
        $minSum = 0;
        foreach ($tokens as $token) {
            if ($token instanceof SpecialToken) {
                if ('(' === $token->content) {
                    ++$sum;
                    ++$nOpen;
                } elseif (')' === $token->content) {
                    --$sum;
                    ++$nClose;
                }
                $minSum = min($sum, $minSum);
            }
        }

        if ($minSum < 0) {
            // more closing than opening parentheses
            $tokens = array_merge(array_fill(0, -$minSum, new SpecialToken('(')), $tokens);
            // adjust $sum based on number of newly inserted parentheses.
            $sum -= $minSum;
        }
        if ($sum > 0) {
            // more opening than closing parentheses
            $tokens = array_merge($tokens, array_fill(0, $sum, new SpecialToken(')')));
        }

        return $tokens;
    }

    /**
     * Collapses duplicated operators (e.g. 'foo OR AND bar' -> 'foo OR bar') and
     * adds 'AND' operators if there are none (i.e. 'foo bar' -> 'foo AND bar')
     *
     * @return Token[]
     */
    public static function sanitizeOperators(array $tokens): array
    {
        $newTokens = [];
        for ($i = 0; $i < count($tokens); ++$i) {
            $token = $tokens[$i];
            $prevToken = $i > 0 ? $tokens[$i - 1] : null;
            $nextToken = $i < count($tokens) - 1 ? $tokens[$i + 1] : null;

            $prevWasOperator = null === $prevToken
                || ($prevToken instanceof SpecialToken && in_array($prevToken->content, array_merge(self::OPERATORS, ['(']), true));

            $isOperator = $token instanceof SpecialToken
                && in_array($token->content, array_merge(self::OPERATORS, [')']), true);

            $nextIsOperator = null === $nextToken
                || ($nextToken instanceof SpecialToken
                    && in_array($nextToken->content, array_merge(self::OPERATORS, [')']), true));

            if ($prevWasOperator && $isOperator) {
                // Eat up multiple operators in a row.

                if ('(' === $token->content || ')' === $token->content) {
                    // Never mess with parentheses.
                    $newTokens[] = $token;
                }
            } else {
                if (!$prevWasOperator && !$isOperator) {
                    // Add 'AND' operators where there are none.
                    $newTokens[] = new SpecialToken('AND');
                }
                if (!($isOperator && $nextIsOperator)) {
                    $newTokens[] = $token;
                } else {
                    if ('(' === $token->content || ')' === $token->content) {
                        // Never mess with parentheses.
                        $newTokens[] = $token;
                    }
                }
            }
        }

        return $newTokens;
    }

    /**
     * Recursively converts tokens enclosed by parentheses into arrays, while
     * also automatically unwrapping parentheses with just a single token.
     *
     * ['A', 'AND', '(', 'B', 'OR', 'C', 'AND', '(', 'D', ')', ')']
     * ->
     * ['A', 'AND', ['B', 'OR', 'C', 'AND', 'D']]
     */
    public static function convertParenthesesToArrays(array $tokens): array
    {
        return self::_convertParenthesesToArrays((new ArrayObject($tokens))->getIterator());
    }

    private static function _convertParenthesesToArrays(\ArrayIterator $tokens): array
    {
        $tree = [];

        while ($tokens->valid()) {
            $token = $tokens->current();
            $tokens->next();

            if ($token instanceof SpecialToken) {
                if ('(' === $token->content) {
                    $token = self::_convertParenthesesToArrays($tokens);
                    if (1 === count($token)) {
                        // unwrap arrays with just one element (i.e., a single value enclosed by parentheses '( A )')
                        $token = $token[0];
                    }
                } elseif (')' === $token->content) {
                    return $tree;
                }
            }
            $tree[] = $token;
        }

        return $tree;
    }

    /**
     * Builds an AST with correct operator precedence based based on the
     * Shunting-yard algorithm.
     * https://www.wikiwand.com/en/Shunting-yard_algorithm
     */
    public static function buildAST(array $tokens)
    {
        $result = new SplStack();
        $operatorStack = new SplStack();

        $precedence = function (string $a, string $b) {
            if ($a === $b) {
                return 0;
            }

            return 'AND' === $a ? 1 : -1;
        };

        foreach ($tokens as $token) {
            if ($token instanceof StringToken) {
                $result->push($token);
            } elseif ($token instanceof SpecialToken) {
                while (
                    !$operatorStack->isEmpty()
                    && (
                        1 === $precedence($operatorStack->top()->content, $token->content)
                        || (
                            0 === $precedence($operatorStack->top()->content, $token->content)
                                && true /* the token is left associative */
                        )
                    )
                ) {
                    $result->push($operatorStack->pop());
                    $result->push(Clause::fromStack($result));
                }
                $operatorStack->push($token);
            } elseif (is_array($token)) {
                $result->push(self::buildAST($token));
            } else {
                throw new \LogicException('Should not be reaced.');
            }
        }

        while (!$operatorStack->isEmpty()) {
            $result->push($operatorStack->pop());
            $result->push(Clause::fromStack($result));
        }

        if ($result->isEmpty()) {
            return null;
        }

        $clause = $result->pop();
        if (!$result->isEmpty()) {
            throw new \LogicException('This should never happen.');
        }

        return $clause;
    }

    public static function toElasticSearchQuery($element, array $fields, $isRoot = true): ?array
    {
        if (null === $element) {
            if ($isRoot) {
                return null;
            }

            return [
                'match_none' => new \stdClass(),
            ];
        }
        if ($element instanceof StringToken) {
            if ($element instanceof PhraseToken) {
                return [
                    'multi_match' => [
                        'query' => $element->content,
                        'fields' => $fields,
                        'type' => 'phrase',
                        // fuzziness is not supported (and makes no sense) for
                        // phrase tokens
                    ],
                ];
            }

            return [
                'multi_match' => [
                    'query' => $element->content,
                    'fields' => $fields,
                    // 'most_fields' combines the scores of all fields that
                    // contain the search term: If the term appears in title,
                    // description, and edition, the score of all of these
                    // occurrences is combined. This is better than using
                    // the default 'best_fields', which simply takes field
                    // with the highest score, discarding all lower scores.
                    'type' => 'most_fields',
                    // Fuzziness is helpful for typos and finding plural
                    // versions of the same word. We do not currently stem
                    // the description and title, which is why using some
                    // fuzziness is essential.
                    // Setting prefix_length to 2 causes fuzziness to not
                    // change the first 2 characters of search terms. As
                    // an example, take the search for 'ghouls':
                    // 'ghouls' only has an edit distanc of 2 to the term
                    // 'should'. We don't want searches for 'ghouls' to
                    // also match 'should', which is why we restrict the
                    // fuzziness to start after the second character.
                    'fuzziness' => 'AUTO',
                    'prefix_length' => 2,
                ],
            ];
        }
        $childQueries = array_map(function ($child) use ($fields) {
            return self::toElasticSearchQuery($child, $fields);
        }, $element->children);
        switch ($element->operator->content) {
            case 'AND':
                // All terms that are part of this clause have to be ANDed together.
                // Given the search query 'galactic ghouls', we don't care if both
                // 'galactic' and 'ghouls' appear in the same field (e.g., the title)
                // or appear on their own in different fields (e.g., 'galactic' in
                // the title and 'ghouls' in the description). That is why we can't
                // simply use a single 'multi_match' query with the operator set to
                // 'and' like this:
                // ['multi_match' => [
                //     'query' => 'galactic ghouls',
                //     'fields' => $fields,
                //     'type' => 'most_fields'
                //     'fuzziness' => 'AUTO',
                //     'prefix_length' => 2,
                //      'operator' => 'and'
                // ]]
                // This query would only return results where both terms appear in
                // the same field. We also can't use 'cross_fields' (instead of
                // 'most_fields'): While that allows terms to be distributed across
                // fields, it doesn't allow using fuzziness.
                //
                // That is why we create a multi_match query per term and AND them
                // together using a 'bool => 'must' query.
                return [
                    'bool' => [
                        'must' => $childQueries,
                    ],
                ];
            case 'OR':
                // Combine the collected OR conditions.
                // At least one of them must match for an adventure to be returned.
                // The adventure will get a higher score if more than one matches.
                return [
                    'bool' => [
                        'should' => $childQueries,
                        'minimum_should_match' => 1,
                    ],
                ];
            default:
                throw new \LogicException('Should not be reached.');
        }
    }

    private static function createToken($content): Token
    {
        if (in_array($content, self::OPERATORS, true)) {
            return new SpecialToken($content);
        }

        return new StringToken($content);
    }
}
