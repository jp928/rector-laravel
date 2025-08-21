<?php

declare(strict_types=1);

namespace RectorLaravel\Rules;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Scalar\String_;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\Rector\AbstractRector;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;

/**
 * @see \RectorLaravel\Tests\LaravelEloquentGenericRectorTest
 */
final class LaravelEloquentGenericRector extends AbstractRector
{
    
    const array RELATION_TYPES = [
        'BelongsTo',
        'HasOne', 
        'HasMany',
        'MorphOne',
        'MorphMany',
        'MorphTo',
        'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
        'Illuminate\\Database\\Eloquent\\Relations\\HasOne',
        'Illuminate\\Database\\Eloquent\\Relations\\HasMany',
        'Illuminate\\Database\\Eloquent\\Relations\\MorphOne',
        'Illuminate\\Database\\Eloquent\\Relations\\MorphMany',
        'Illuminate\\Database\\Eloquent\\Relations\\MorphTo',
    ];
    
    public function __construct(
        private readonly PhpDocInfoFactory $phpDocInfoFactory,
        private readonly DocBlockUpdater $docBlockUpdater,
    ) {}

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Add generic type to Laravel Eloquent relationships',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Model
{
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Model
{
    /**
     * @return BelongsTo<Company, self>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

CODE_SAMPLE
                )
            ]
        );
    }

    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    public function refactor(Node $node): ?Node
    {
        if (!$node instanceof ClassMethod) {
            return null;
        }

        // must declare a return type like BelongsTo, HasOne etc.
        if (!$node->returnType instanceof Name) {
            return null;
        }

        $relationType = $this->getName($node->returnType);

        if (!in_array($relationType, self::RELATION_TYPES, true)) {
            return null;
        }

        $relatedModel = $this->resolveRelatedModel($node);

        if ($relatedModel === null) {
            return null;
        }

        $phpDocInfoFactory = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);

        // Get the short class name for the generic type
        $shortRelationType = $this->getShortClassName($relationType);

        $returnTagValue = $phpDocInfoFactory->getReturnTagValue();

        if ($returnTagValue === null) {
            // Create a return tag with the generic type
            $genericType = $this->getGenericType($shortRelationType, $relatedModel);

            $returnTagValue = new ReturnTagValueNode($genericType, '');
            $returnTag = new PhpDocTagNode('@return', $returnTagValue);

            $phpDocInfoFactory->addPhpDocTagNode($returnTag);
        }

        // Existing return tag value is a relation type
        if ($this->detect($returnTagValue)) {
            $returnTagValue->type = $this->getGenericType($shortRelationType, $relatedModel);
        }

        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);

        return $node;
    }

    private function resolveRelatedModel(ClassMethod $method): ?string
    {
        if ($method->stmts === null) {
            return null;
        }

        foreach ($method->stmts as $stmt) {
            if ($stmt instanceof Node\Stmt\Return_) {
                $expr = $stmt->expr;

                if ($expr instanceof MethodCall) {
                    $firstArg = $expr->args[0]->value ?? null;

                    if ($firstArg instanceof ClassConstFetch && $firstArg->class instanceof Name) {
                        $fqdn = $firstArg->class->toString();

                        return end(explode('\\', $fqdn));
                    }

                    if ($firstArg instanceof String_) {
                        return $firstArg->value;
                    }
                }
            }
        }

        return null;
    }

    private function getShortClassName(string $fullClassName): string
    {
        $parts = explode('\\', $fullClassName);
        return end($parts);
    }

    private function getGenericType(string $shortRelationType, string $relatedModel): GenericTypeNode
    {
        if (in_array($shortRelationType, ['HasMany', 'MorphMany', 'HasOne', 'MorphOne'])) {
            return new GenericTypeNode(
                new IdentifierTypeNode($shortRelationType),
                [
                    new IdentifierTypeNode($relatedModel),
                ]
            );
        }

        return new GenericTypeNode(
            new IdentifierTypeNode($shortRelationType),
            [
                new IdentifierTypeNode($relatedModel),
                new IdentifierTypeNode('self'),
            ]
        );
    }

    private function detect(ParamTagValueNode|ReturnTagValueNode $tagValueNode): bool
    {
        return in_array($tagValueNode->type->name, self::RELATION_TYPES);
    }
}
