<?php

declare(strict_types=1);

namespace App\Repository\ORM\Functions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\TokenType;


class EarthBoxContains extends FunctionNode
{
    public $center = null;
    public $radius = null;
    public $point = null;

    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);
        $this->center = $parser->ArithmeticExpression();
        $parser->match(TokenType::T_COMMA);
        $this->radius = $parser->ArithmeticExpression();
        $parser->match(TokenType::T_COMMA);
        $this->point = $parser->ArithmeticExpression();

        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        return sprintf(
            'earth_box(%s, %s) @> %s',
            $this->center->dispatch($sqlWalker),
            $this->radius->dispatch($sqlWalker),
            $this->point->dispatch($sqlWalker)
        );
    }
}