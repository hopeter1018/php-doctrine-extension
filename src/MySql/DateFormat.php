<?php

namespace Hopeter1018\DoctrineExtension\MySql;

use Doctrine\ORM\Query\AST\Functions\FunctionNode,
    Doctrine\ORM\Query\Lexer;

/**
 * DateFormatFunction ::= "DATE_FORMAT" "(" ArithmeticPrimary "," ArithmeticPrimary ")"
 */
class DateFormat extends FunctionNode
{
    // (1)
    public $date = null;
    public $format = null;

    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER); // (2)
        $parser->match(Lexer::T_OPEN_PARENTHESIS); // (3)
        $this->date = $parser->ArithmeticPrimary(); // (4)
        $parser->match(Lexer::T_COMMA); // (5)
        $this->format = $parser->ArithmeticPrimary(); // (6)
        $parser->match(Lexer::T_CLOSE_PARENTHESIS); // (3)
    }

    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        return 'DATE_FORMAT(' .
            $this->date->dispatch($sqlWalker) . ', ' .
            $this->format->dispatch($sqlWalker) .
        ')'; // (7)
    }
}