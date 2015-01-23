<?php

namespace Hopeter1018\DoctrineExtension\MySql;

use Doctrine\ORM\Query\AST\Functions\FunctionNode,
    Doctrine\ORM\Query\Lexer;

/**
 * DateFunction ::= "DATE" "(" ArithmeticPrimary ")"
 * * Salty
 * @author Peter Ho <peter.ho@westcomzivo.com>
 */
class Date extends FunctionNode
{
    // (1)
    public $dateString = null;
    public $secondDateExpression = null;

    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER); // (2)
        $parser->match(Lexer::T_OPEN_PARENTHESIS); // (3)
        $this->dateString = $parser->ArithmeticPrimary(); // (4)
        $parser->match(Lexer::T_CLOSE_PARENTHESIS); // (3)
    }

    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        return "DATE(" .
            $this->dateString->dispatch($sqlWalker) .
        ')'; // (7)
    }
}