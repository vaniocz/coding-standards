<?php
namespace Vanio\CodingStandards\Sniffs\WhiteSpace;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use SlevomatCodingStandard\Helpers\TokenHelper;
use Vanio\CodingStandards\Utility\TokenUtility;

class ClosureEmptyBodyOpeningBraceSniff implements Sniff
{
    public const CODE_WHITESPACE = 'WhiteSpace';
    public const MESSAGE_WHITESPACE = 'Incorrect white space before closure empty body opening brace';

    /**
     * @return int[]
     */
    public function register(): array
    {
        return [T_CLOSURE];
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
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
        $closingBracePointer = $token['scope_closer'];

        if (
            $token['line'] !== $tokens[$openingBracePointer]['line']
            || trim(TokenHelper::getContent($file, $openingBracePointer + 1, $closingBracePointer - 1)) !== ''
            || $tokens[$openingBracePointer - 1]['content'] === ' '
        ) {
            return;
        } elseif ($file->addFixableError(self::MESSAGE_WHITESPACE, $openingBracePointer, self::CODE_WHITESPACE)) {
            TokenUtility::replaceWhiteSpaceBefore($openingBracePointer, ' ');
        }
    }
}
