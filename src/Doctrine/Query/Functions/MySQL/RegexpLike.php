<?php

namespace Oka\PaginationBundle\Doctrine\Query\Functions\MySQL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class RegexpLike extends FunctionNode
{
    protected $expression;
    protected $pattern;
    protected $matchType;

    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);

        $this->expression = $parser->StringPrimary();

        $parser->match(TokenType::T_COMMA);

        $this->pattern = $parser->StringPrimary();

        if ($parser->getLexer()->isNextToken(TokenType::T_COMMA)) {
            $parser->match(TokenType::T_COMMA);

            $this->matchType = $parser->StringPrimary();
        }

        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker): string
    {
        $expression = $sqlWalker->walkStringPrimary($this->expression);
        $pattern = $sqlWalker->walkStringPrimary($this->pattern);

        if (null === $this->matchType) {
            return sprintf('REGEXP_LIKE(%s, %s)', $expression, $pattern);
        } else {
            return sprintf('REGEXP_LIKE(%s, %s, %s)', $expression, $pattern, $sqlWalker->walkStringPrimary($this->matchType));
        }
    }
}
