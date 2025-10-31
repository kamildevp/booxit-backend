<?php

declare(strict_types=1);

namespace App\Repository\ORM\Functions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\TokenType;

class EarthPoint extends FunctionNode
{
    public $latitude = null;
    public $longitude = null;

    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);

        $this->latitude = $parser->ArithmeticExpression();
        $parser->match(TokenType::T_COMMA);
        $this->longitude = $parser->ArithmeticExpression();

        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        return sprintf(
            'll_to_earth(%s, %s)',
            $this->latitude->dispatch($sqlWalker),
            $this->longitude->dispatch($sqlWalker)
        );
    }
}