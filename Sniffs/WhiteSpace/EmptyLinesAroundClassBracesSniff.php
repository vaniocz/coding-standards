<?php
namespace Vanio\CodingStandards\Sniffs\WhiteSpace;

use PHP_CodeSniffer\Files\File;
use SlevomatCodingStandard\Sniffs\Classes\EmptyLinesAroundClassBracesSniff as BaseEmptyLinesAroundClassBracesSniff;
use Vanio\CodingStandards\Utility\TokenUtility;

class EmptyLinesAroundClassBracesSniff extends BaseEmptyLinesAroundClassBracesSniff
{
    /** @var int */
    public $linesCountAfterOpeningBrace = 0;

    /** @var int */
    public $linesCountBeforeClosingBrace = 0;

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     * @param File $file
     * @param int $pointer
     */
    public function process(File $file, $pointer): void
    {
        $tokens = $file->getTokens();
        $closingBracePointer = $tokens[$pointer]['scope_closer'];
        $previousContentToken = TokenUtility::findTokenBefore($file, $closingBracePointer, [], [T_WHITESPACE]);

        if ($tokens[$closingBracePointer]['line'] !== $previousContentToken['line']) {
            parent::process($file, $pointer);
        }
    }
}
