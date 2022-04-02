<?php

declare(strict_types=1);

namespace AppBundle\Search\QueryParser;

class Clause implements \JsonSerializable
{
    public array $children;

    public SpecialToken $operator;

    public function __construct(SpecialToken $operator, array $children)
    {
        $this->operator = $operator;
        $this->children = $children;
    }

    public static function fromStack(\SplStack $stack): Clause
    {
        $operator = $stack->pop();
        $right = $stack->pop();
        $left = $stack->pop();

        if ($left instanceof Clause && $right instanceof StringToken) {
            if ($left->operator->content === $operator->content) {
                $left->children[] = $right;

                return $left;
            }
        } elseif ($left instanceof StringToken && $right instanceof Clause) {
            if ($right->operator->content === $operator->content) {
                $right->children[] = $left;

                return $right;
            }
        }

        return new self($operator, [$left, $right]);
    }

    public function jsonSerialize()
    {
        return [
            'type' => 'clause',
            'operator' => $this->operator->content,
            'children' => $this->children,
        ];
    }
}
