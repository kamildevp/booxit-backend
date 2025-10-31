<?php

declare(strict_types=1);

namespace App\Repository\ORM\Functions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\TokenType;

class EarthDistance extends FunctionNode
{
    public $pointA = null;
    public $pointB = null;

    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);
        $this->pointA = $parser->ArithmeticExpression();
        $parser->match(TokenType::T_COMMA);
        $this->pointB = $parser->ArithmeticExpression();
        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        return sprintf(
            'earth_distance(%s, %s)',
            $this->pointA->dispatch($sqlWalker),
            $this->pointB->dispatch($sqlWalker)
        );
    }
}