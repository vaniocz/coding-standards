<?php
namespace Vanio\CodingStandards\Sniffs\WhiteSpace;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Standards\PEAR\Sniffs\WhiteSpace\ScopeClosingBraceSniff as BaseScopeClosingBraceSniff;
use SlevomatCodingStandard\Helpers\TokenHelper;
use Vanio\CodingStandards\Utility\TokenUtility;

class ScopeClosingBraceSniff extends BaseScopeClosingBraceSniff
{
    public const CODE_WHITESPACE_IN_EMPTY_BODY = 'WhiteSpaceInEmptyBody';
    public const CODE_BLANK_LINES_IN_FUNCTION = 'BlankLinesInFunction';

    public const MESSAGE_WHITESPACE_IN_EMPTY_BODY = 'Unexpected white space before closing brace';
    public const MESSAGE_BLANK_LINES_IN_FUNCTION = 'Unexpected blank lines before function closing brace';

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     * @param File $file
     * @param int $pointer
     */
    public function process(File $file, $pointer): void
    {
        $tokens = $file->getTokens();
        $token = $tokens[$pointer];

        if (!isset($token['scope_closer'])) {
            return;
        }

        $openingBracePointer = $token['scope_opener'];

        if ($tokens[$openingBracePointer]['code'] !== T_OPEN_CURLY_BRACKET) {
            return;
        }

        $closingBracePointer = $token['scope_closer'];
        $content = TokenHelper::getContent($file, $openingBracePointer + 1, $closingBracePointer - 1);

        if ($content === '') {
            return;
        } elseif (trim($content) === '') {
            $shouldFix = $file->addFixableError(
                self::MESSAGE_WHITESPACE_IN_EMPTY_BODY,
                $closingBracePointer,
                self::CODE_WHITESPACE_IN_EMPTY_BODY
            );

            if ($shouldFix) {
                TokenUtility::replaceWhiteSpaceBefore($file, $closingBracePointer);
            }

            return;
        }

        if (in_array($token['code'], [T_FUNCTION, T_CLOSURE])) {
            $this->processFunctionScope($file, $pointer);
        }

        parent::process($file, $pointer);
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     * @param File $file
     * @param int $pointer
     */
    private function processFunctionScope(File $file, $pointer): void
    {
        $tokens = $file->getTokens();
        $closingBracePointer = $tokens[$pointer]['scope_closer'];
        $previousContentToken = TokenUtility::findTokenBefore($file, $closingBracePointer, [], [T_WHITESPACE]);

        if ($tokens[$closingBracePointer]['line'] - $previousContentToken['line'] <= 1) {
            return;
        }

        $shouldFix = $file->addFixableError(
            self::MESSAGE_BLANK_LINES_IN_FUNCTION,
            $closingBracePointer,
            self::CODE_BLANK_LINES_IN_FUNCTION
        );

        if ($shouldFix) {
            for ($i = $previousContentToken['pointer'] + 1; $i < $closingBracePointer; $i++) {
                if ($tokens[$i]['line'] === $previousContentToken['line']) {
                    continue;
                } elseif ($tokens[$i]['line'] === $tokens[$closingBracePointer]['line']) {
                    break;
                }

                $file->fixer->replaceToken($i, '');
            }
        }
    }
}
