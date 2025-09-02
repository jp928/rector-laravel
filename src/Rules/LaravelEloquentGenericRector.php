<?php

declare(strict_types=1);

namespace RectorLaravelCustomRules\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \RectorLaravelCustomRules\Tests\LaravelEloquentGenericRectorTest
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
        'BelongsToMany',
        'HasManyThrough',
        'HasOneThrough',
        'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
        'Illuminate\\Database\\Eloquent\\Relations\\HasOne',
        'Illuminate\\Database\\Eloquent\\Relations\\HasMany',
        'Illuminate\\Database\\Eloquent\\Relations\\MorphOne',
        'Illuminate\\Database\\Eloquent\\Relations\\MorphMany',
        'Illuminate\\Database\\Eloquent\\Relations\\MorphTo',
        'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
        'Illuminate\\Database\\Eloquent\\Relations\\HasManyThrough',
        'Illuminate\\Database\\Eloquent\\Relations\\HasOneThrough',
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
                ),
            ]
        );
    }

    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    public function refactor(Node $node): ?Node
    {
        if (! $node instanceof ClassMethod) {
            return null;
        }

        // must declare a return type like BelongsTo, HasOne etc.
        if (! $node->returnType instanceof Name) {
            return null;
        }

        $relationType = $this->getName($node->returnType);

        if (! in_array($relationType, self::RELATION_TYPES, true)) {
            return null;
        }

        $relatedModel = $this->resolveRelatedModel($node);

        $phpDocInfoFactory = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);

        // Get the short class name for the generic type
        $shortRelationType = $this->getShortClassName($relationType);

        if ($relatedModel === null && $shortRelationType !== 'MorphTo') {
            return null;
        }

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
            if ($stmt instanceof Return_) {
                $expr = $stmt->expr;

                if ($expr instanceof MethodCall) {
                    // Find the root relation method call by traversing up the chain
                    $relationCall = $this->findRootRelationCall($expr);

                    if ($relationCall !== null) {
                        $firstArg = $relationCall->args[0]->value ?? null;

                        if ($firstArg instanceof ClassConstFetch && $firstArg->class instanceof Name) {
                            $fqdn = $firstArg->class->toString();

                            return end(explode('\\', $fqdn));
                        }
                    }
                }
            }
        }

        return null;
    }

    private function findRootRelationCall(MethodCall $expr): ?MethodCall
    {
        // Check if this is a relation method call
        if ($this->isRelationCall($expr)) {
            return $expr;
        }

        // If not, check the var (left side) recursively
        if ($expr->var instanceof MethodCall) {
            return $this->findRootRelationCall($expr->var);
        }

        return null;
    }

    private function isRelationCall(MethodCall $expr): bool
    {
        $funcName = $expr->name->toString();

        return in_array($funcName, [
            'belongsTo',
            'hasOne',
            'hasMany',
            'morphOne',
            'morphTo',
            'morphMany',
            'belongsToMany',
            'hasManyThrough',
            'hasOneThrough',
        ]);
    }

    private function getShortClassName(string $fullClassName): string
    {
        $parts = explode('\\', $fullClassName);

        return end($parts);
    }

    private function getGenericType(string $shortRelationType, ?string $relatedModel): GenericTypeNode
    {
        $normalizedRelationModel = $relatedModel === 'static' ? 'self' : $relatedModel;

        if (in_array($shortRelationType, [
            'HasMany',
            'HasOne',
            'BelongsToMany',
            'HasManyThrough',
            'HasOneThrough',
            'MorphOne',
            'MorphMany',
        ])) {
            return new GenericTypeNode(
                new IdentifierTypeNode($shortRelationType),
                [
                    new IdentifierTypeNode($normalizedRelationModel),
                ]
            );
        }

        if ($shortRelationType === 'MorphTo') {
            return new GenericTypeNode(
                new IdentifierTypeNode($shortRelationType),
                [
                    new IdentifierTypeNode('Model'),
                    new IdentifierTypeNode('self'),
                ]
            );
        }

        return new GenericTypeNode(
            new IdentifierTypeNode($shortRelationType),
            [
                new IdentifierTypeNode($normalizedRelationModel),
                new IdentifierTypeNode('self'),
            ]
        );
    }

    private function detect(ReturnTagValueNode $tagValueNode): bool
    {
        $type = $tagValueNode->type;

        // Handle IdentifierTypeNode (simple types like "BelongsTo")
        if ($type instanceof IdentifierTypeNode) {
            return in_array($type->name, self::RELATION_TYPES, true);
        }

        // Handle GenericTypeNode (types like "BelongsTo<Company, self>")
        if ($type instanceof GenericTypeNode && $type->type instanceof IdentifierTypeNode) {
            return in_array($type->type->name, self::RELATION_TYPES, true);
        }

        return false;
    }
}
