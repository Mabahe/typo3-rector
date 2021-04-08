<?php

declare(strict_types=1);

namespace Ssch\TYPO3Rector\Rector\v10\v0;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Ssch\TYPO3Rector\Helper\TcaHelperTrait;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/10.0/Breaking-87989-TCAOptionSetToDefaultOnCopyRemoved.html
 */
final class RemoveTcaOptionSetToDefaultOnCopyRector extends AbstractRector
{
    use TcaHelperTrait;

    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('TCA option setToDefaultOnCopy removed', [new CodeSample(<<<'CODE_SAMPLE'
return [
    'ctrl' => [
        'selicon_field' => 'icon',
        'setToDefaultOnCopy' => 'foo'
    ],
    'columns' => [
    ],
];
CODE_SAMPLE
                , <<<'CODE_SAMPLE'
return [
    'ctrl' => [
        'selicon_field' => 'icon'
    ],
    'columns' => [
    ],
];
CODE_SAMPLE
            )]);
    }

    /**
     * @return array<class-string<\PhpParser\Node>>
     */
    public function getNodeTypes(): array
    {
        return [Return_::class];
    }

    /**
     * @param Return_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isTca($node)) {
            return null;
        }

        $ctrl = $this->extractCtrl($node);

        if (! $ctrl instanceof ArrayItem) {
            return null;
        }

        $items = $ctrl->value;

        if (! $items instanceof Array_) {
            return null;
        }

        $hasAstBeenChanged = false;
        foreach ($items->items as $fieldValue) {
            if (! $fieldValue instanceof ArrayItem) {
                continue;
            }

            if (null === $fieldValue->key) {
                continue;
            }

            if ($this->valueResolver->isValue($fieldValue->key, 'setToDefaultOnCopy')) {
                $this->removeNode($fieldValue);
                $hasAstBeenChanged = true;
            }
        }

        return $hasAstBeenChanged ? $node : null;
    }
}
