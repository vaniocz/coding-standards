<?php
namespace Vanio\CodingStandards\Sniffs\Commenting;

use PHP_CodeSniffer\Files\File;
use SlevomatCodingStandard\Helpers\DocCommentHelper;
use SlevomatCodingStandard\Sniffs\Commenting\DisallowOneLinePropertyDocCommentSniff;
use Vanio\CodingStandards\Utility\TokenUtility;

class DisallowOneLineDocCommentSniff extends DisallowOneLinePropertyDocCommentSniff
{
    public const CODE_ONE_LINE_COMMENT = 'OneLineComment';
    public const MESSAGE_ONE_LINE_COMMENT = 'One-line comments are required only for properties, use multi-line comment instead.';

    /**
     * @return int[]
     */
    public function register(): array
    {
        return [T_DOC_COMMENT_OPEN_TAG];
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @param int $docCommentStartPointer
     */
    public function process(File $file, $docCommentStartPointer): void
    {
        $tokens = $file->getTokens();

        if (DocCommentHelper::hasDocCommentDescription($file, $docCommentStartPointer)) {
            return;
        }

        $docCommentEndPointer = $tokens[$docCommentStartPointer]['comment_closer'];
        $lineDifference = $tokens[$docCommentEndPointer]['line'] - $tokens[$docCommentStartPointer]['line'];

        if ($lineDifference !== 0) {
            return;
        }

        $skippedTypes = [T_WHITESPACE, T_PUBLIC, T_PRIVATE, T_PROTECTED, T_STRING];

        if (TokenUtility::findTokenAfter($file, $docCommentEndPointer, [T_VARIABLE], $skippedTypes)) {
            return;
        }

        $shoudlFix = $file->addFixableError(
            self::MESSAGE_ONE_LINE_COMMENT,
            $docCommentStartPointer,
            self::CODE_ONE_LINE_COMMENT
        );

        if (!$shoudlFix) {
            return;
        }

        $commentWhitespaceToken = TokenUtility::findTokenBefore($file, $docCommentStartPointer, [T_WHITESPACE]);
        $indent = ($commentWhitespaceToken['content'] ?? '') . ' ';

        // Empty comment is not split into start & end tokens properly
        if ($tokens[$docCommentStartPointer]['content'] === '/***/') {
            $file->fixer->beginChangeset();
            $file->fixer->replaceToken($docCommentStartPointer, '/**');
            $file->fixer->addNewline($docCommentStartPointer);
            $file->fixer->addContent($docCommentStartPointer, $indent);
            $file->fixer->addContent($docCommentStartPointer, '*');
            $file->fixer->addNewline($docCommentStartPointer);
            $file->fixer->addContent($docCommentStartPointer, $indent);
            $file->fixer->addContent($docCommentStartPointer, '*/');
            $file->fixer->endChangeset();

            return;
        }

        $file->fixer->beginChangeset();
        $file->fixer->addNewline($docCommentStartPointer);
        $file->fixer->addContent($docCommentStartPointer, $indent);
        $file->fixer->addContent($docCommentStartPointer, '*');

        if ($docCommentEndPointer - 1 !== $docCommentStartPointer) {
            $file->fixer->replaceToken(
                $docCommentEndPointer - 1,
                rtrim($file->fixer->getTokenContent($docCommentEndPointer - 1), ' ')
            );
        }

        $file->fixer->addContentBefore($docCommentEndPointer, $indent);
        $file->fixer->addNewlineBefore($docCommentEndPointer);
        $file->fixer->endChangeset();
    }
}
