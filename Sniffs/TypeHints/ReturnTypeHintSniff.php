<?php
namespace Vanio\CodingStandards\Sniffs\TypeHints;

use PHP_CodeSniffer\Files\File;
use SlevomatCodingStandard\Helpers\DocCommentHelper;
use SlevomatCodingStandard\Helpers\FunctionHelper;
use SlevomatCodingStandard\Helpers\StringHelper;
use SlevomatCodingStandard\Helpers\TokenHelper;
use SlevomatCodingStandard\Sniffs\TypeHints\ReturnTypeHintSniff as BaseReturnTypeHintSniff;

class ReturnTypeHintSniff extends BaseReturnTypeHintSniff
{
    public const CODE_TEST_METHOD_RETURN_TYPE_HINT = 'TestMethodReturnTypeHint';
    public const MESSAGE_TEST_METHOD_RETURN_TYPE_HINT = 'Test %s %s has "%s" type hint, return type hints for test methods are forbidden';

    public const CODE_TEST_METHOD_RETURN_ANNOTATION = 'TestMethodReturnAnnotation';
    public const MESSAGE_TEST_METHOD_RETURN_ANNOTATION = 'Test %s %s has%s @return annotation, @return annotations for test methods are forbidden';

    /**
     * @param int $pointer
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function process(File $file, $pointer): void
    {
        $token = $file->getTokens()[$pointer];

        if ($token['code'] !== T_FUNCTION || !StringHelper::startsWith($file->getDeclarationName($pointer), 'test_')) {
            parent::process($file, $pointer);

            return;
        }

        if ($returnTypeHint = FunctionHelper::findReturnTypeHint($file, $pointer)) {
            $shouldFix = $file->addFixableError(
                self::MESSAGE_TEST_METHOD_RETURN_TYPE_HINT,
                $pointer,
                self::CODE_TEST_METHOD_RETURN_TYPE_HINT,
                [
                    $this->resolveFunctionTypeLabel($file, $pointer),
                    FunctionHelper::getFullyQualifiedName($file, $pointer),
                    $returnTypeHint->getTypeHint(),
                ]
            );

            if ($shouldFix) {
                $this->removeReturnTypeHint($file, $pointer);
            }
        }

        if ($returnAnnotation = FunctionHelper::findReturnAnnotation($file, $pointer)) {
            $annotation = $returnAnnotation->getContent() === null
                ? ''
                : sprintf(' "%s"', $returnAnnotation->getContent());
            $shouldFix = $file->addFixableError(
                self::MESSAGE_TEST_METHOD_RETURN_ANNOTATION,
                $returnAnnotation->getStartPointer(),
                self::CODE_USELESS_RETURN_ANNOTATION,
                [
                    $this->resolveFunctionTypeLabel($file, $pointer),
                    FunctionHelper::getFullyQualifiedName($file, $pointer),
                    $annotation,
                ]
            );

            if ($shouldFix) {
                $this->removeReturnAnnotation($file, $pointer);
            }
        }
    }

    private function resolveFunctionTypeLabel(File $file, int $pointer): string
    {
        return FunctionHelper::isMethod($file, $pointer) ? 'method' : 'function';
    }

    private function removeReturnTypeHint(File $file, int $functionPointer): void
    {
        $tokens = $file->getTokens();
        $nextPointer = TokenHelper::findNext($file, T_COLON, $tokens[$functionPointer]['parenthesis_closer'] + 1);
        $isAbstract = FunctionHelper::isAbstract($file, $functionPointer);
        $abstractExcludedTypes = TokenHelper::$ineffectiveTokenCodes;
        $abstractExcludedTypes[] = T_SEMICOLON;

        do {
            $file->fixer->replaceToken($nextPointer, '');
            $nextPointer = $isAbstract
                ? TokenHelper::findNextLocalExcluding($file, $abstractExcludedTypes, $nextPointer + 1)
                : TokenHelper::findNextExcluding(
                    $file,
                    TokenHelper::$ineffectiveTokenCodes,
                    $nextPointer + 1,
                    $tokens[$functionPointer]['scope_opener'] - 1
                );
        } while ($nextPointer);
    }

    private function removeReturnAnnotation(File $file, int $functionPointer): void
    {
        $tokens = $file->getTokens();
        $docCommentOpenPointer = DocCommentHelper::findDocCommentOpenToken($file, $functionPointer);
        $docCommentClosePointer = $tokens[$docCommentOpenPointer]['comment_closer'];

        for ($i = $docCommentOpenPointer + 1; $i < $docCommentClosePointer; $i++) {
            if ($tokens[$i]['code'] !== T_DOC_COMMENT_TAG || $tokens[$i]['content'] !== '@return') {
                continue;
            }

            $startPointer = TokenHelper::findPrevious($file, [T_DOC_COMMENT_STAR], $i - 1, $docCommentOpenPointer);
            $endPointer = TokenHelper::findNext(
                $file,
                [T_DOC_COMMENT_CLOSE_TAG, T_DOC_COMMENT_STAR],
                $i - 1,
                $docCommentClosePointer + 1
            );

            for ($j = $startPointer; $j < $endPointer; $j++) {
                $file->fixer->replaceToken($j, '');
            }

            break;
        }
    }
}
