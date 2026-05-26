# **Filter Expression**
=============================

Filter expressions allow you to apply complex criteria when querying your API via the pagination bundle. They are passed as string values for filter parameters and are parsed to generate the corresponding database conditions (ORM, ODM, DBAL).

## Supported Filter Expressions

Below is the list of supported filter expressions and their syntax:

### 1. Equal (`eq`)
Matches values that are exactly equal to the provided value.
- **Syntax**: `eq(value)`
- **Example**: `?status=eq(active)`

### 2. Not Equal (`neq`)
Matches values that are not equal to the provided value.
- **Syntax**: `neq(value)`
- **Example**: `?status=neq(inactive)`

### 3. Is Null (`isNull`)
Matches rows where the field is `NULL`.
- **Syntax**: `isNull()`
- **Example**: `?deletedAt=isNull()`

### 4. Is Not Null (`isNotNull`)
Matches rows where the field is not `NULL`.
- **Syntax**: `isNotNull()`
- **Example**: `?publishedAt=isNotNull()`

### 5. In List (`in`)
Matches values that are present in a comma-separated list.
- **Syntax**: `in(value1,value2,...)`
- **Example**: `?role=in(ROLE_USER,ROLE_ADMIN)`

### 6. Like (`like`)
Matches values using the SQL `LIKE` operator. You can use `%` as a wildcard character. (For ODM/MongoDB, `_` is converted to `.?` and `%` is converted to `.*`).
- **Syntax**: `like(value)`
- **Example**: `?name=like(%John%)`

### 7. Not Like (`notLike`)
Matches values that do not match the specified `LIKE` pattern.
- **Syntax**: `notLike(value)`
- **Example**: `?name=notLike(%Doe%)`

### 8. Range (`range`)
Matches values within a specific range. It supports inclusive (`[` or `]`) and exclusive (`]` or `[`) bounds. You can omit a bound for an open-ended range.
- `[` Left inclusive (`>=`)
- `]` Left exclusive (`>`)
- `]` Right inclusive (`<=`)
- `[` Right exclusive (`<`)

**Syntax**: `range[start,end]`, `range]start,end[`, etc.
**Examples**:
- `?price=range[10,20]` (price >= 10 AND price <= 20)
- `?price=range]10,20[` (price > 10 AND price < 20)
- `?price=range[10,[` (price >= 10)
- `?price=range],20]` (price <= 20)

### 9. Regular Expression (`rLike`) *(ORM Only)*
Matches values using regular expressions (like `REGEXP_LIKE`). You can optionally provide a match type flag (e.g., `c`, `i`, `m`, `n`, `u`).
- **Syntax**: `rLike(pattern)` or `rLike(pattern,matchType)`
- **Examples**:
  - `?code=rLike(^DEF)` (starts with DEF)
  - `?code=rLike(^DEF,i)` (case-insensitive)

## Extensibility

Filter expressions are built on top of the `FilterExpressionHandler`. You can easily create your own filter expressions by implementing the `Oka\PaginationBundle\Pagination\FilterExpression\FilterExpressionInterface` and tagging your service (if autoconfiguration is not used).

```php
use Oka\PaginationBundle\Pagination\FilterExpression\FilterExpressionInterface;
use Oka\PaginationBundle\Pagination\FilterExpression\EvaluationResult;

class CustomFilterExpression implements FilterExpressionInterface
{
    public function supports(object $queryBuilder, $value): bool
    {
        return preg_match('#^custom\((.+)\)$#i', $value) === 1;
    }

    public function evaluate(object $queryBuilder, string $field, $value, string $castType, int &$boundCounter = 1): EvaluationResult
    {
        // Add your custom evaluation logic here
    }
}
```
