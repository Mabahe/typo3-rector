<?php

declare(strict_types=1);

namespace Ssch\TYPO3Rector\Rector\v7\v4;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use TYPO3\CMS\Core\Localization\LocalizationFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @see https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/7.4/Deprecation-68122-GeneralUtilityReadLLfile.html
 */
final class MethodReadLLFileToLocalizationFactoryRector extends AbstractRector
{
    public function getNodeTypes(): array
    {
        return [StaticCall::class];
    }

    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isMethodStaticCallOrClassMethodObjectType($node, GeneralUtility::class)) {
            return null;
        }

        if (! $this->isName($node->name, 'readLLfile')) {
            return null;
        }

        return $this->nodeFactory->createMethodCall(
            $this->nodeFactory->createStaticCall(
                GeneralUtility::class,
                'makeInstance',
                [$this->nodeFactory->createClassConstReference(LocalizationFactory::class)]
            ),
            'getParsedData',
            $node->args
        );
    }

    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Use LocalizationFactory->getParsedData instead of GeneralUtility::readLLfile', [
            new CodeSample(<<<'PHP'
use TYPO3\CMS\Core\Utility\GeneralUtility;
$locallangs = GeneralUtility::readLLfile('EXT:foo/locallang.xml', 'de');
PHP
                , <<<'PHP'
use TYPO3\CMS\Core\Localization\LocalizationFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
$locallangs = GeneralUtility::makeInstance(LocalizationFactory::class)->getParsedData('EXT:foo/locallang.xml', 'de');
PHP
            ),
        ]);
    }
}
