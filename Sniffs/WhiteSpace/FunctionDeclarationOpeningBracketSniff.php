<?php
namespace Vanio\CodingStandards\Sniffs\WhiteSpace;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use Vanio\CodingStandards\Utility\TokenUtility;

class FunctionDeclarationOpeningBracketSniff implements Sniff
{
    const CODE_WHITESPACE = 'WhiteSpace';
    const MESSAGE_WHITESPACE = 'Unexpected white space before opening bracket in declaration of function "%s"';

    /**
     * @return int[]
     */
    public function register(): array
    {
        return [T_FUNCTION];
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     * @param File $file
     * @param int $pointer
     */
    public function process(File $file, $pointer)
    {
        $tokens = $file->getTokens();
        $openingBracketPointer = $tokens[$pointer]['parenthesis_opener'];

        if (
            $tokens[$pointer]['code'] !== T_FUNCTION
            || $openingBracketPointer === null
            || $tokens[$openingBracketPointer - 1]['code'] !== T_WHITESPACE
        ) {
            return;
        }

        if (!$function = $file->getDeclarationName($pointer)) {
            return;
        }

        $data = [$function];

        if ($file->addFixableError(self::MESSAGE_WHITESPACE, $openingBracketPointer, self::CODE_WHITESPACE, $data)) {
            TokenUtility::replaceWhiteSpaceBefore($file, $openingBracketPointer);
        }
    }
}
