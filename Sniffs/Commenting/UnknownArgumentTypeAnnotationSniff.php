<?php
namespace Vanio\CodingStandards\Sniffs\Commenting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use SlevomatCodingStandard\Helpers\Annotation\GenericAnnotation;
use SlevomatCodingStandard\Helpers\Annotation\ParameterAnnotation;
use SlevomatCodingStandard\Helpers\AnnotationHelper;
use SlevomatCodingStandard\Helpers\AnnotationTypeHelper;
use SlevomatCodingStandard\Helpers\DocCommentHelper;
use SlevomatCodingStandard\Helpers\FunctionHelper;
use SlevomatCodingStandard\Helpers\SuppressHelper;
use SlevomatCodingStandard\Helpers\TokenHelper;

class UnknownArgumentTypeAnnotationSniff implements Sniff
{
    public const CODE_UNKNOWN_ARGUMENT = 'UnknownArgument';
    public const MESSAGE_UNKNOWN_ARGUMENT = '%s %s() has @param annotation for an uknown argument %s.';

    /**
     * @return int[]
     */
    public function register(): array
    {
        return [T_FUNCTION];
    }

    /**
     * @param File $file
     * @param int $functionPointer
     */
    public function process(File $file, $functionPointer): void
    {
        $parameterNames = array_flip(FunctionHelper::getParametersNames($file, $functionPointer));

        foreach (FunctionHelper::getParametersAnnotations($file, $functionPointer) as $parameterAnnotation) {
            if (isset($parameterNames[$parameterAnnotation->getParameterName()])) {
                continue;
            }

            $shouldFix = $file->addFixableError(
                sprintf(
                    self::MESSAGE_UNKNOWN_ARGUMENT,
                    FunctionHelper::getTypeLabel($file, $functionPointer),
                    FunctionHelper::getFullyQualifiedName($file, $functionPointer),
                    $parameterAnnotation->getParameterName()
                ),
                $parameterAnnotation->getStartPointer(),
                self::CODE_UNKNOWN_ARGUMENT,
                [$parameterAnnotation->getParameterName()]
            );

            if ($shouldFix) {
                $this->removeDocCommentParameter($file, $functionPointer, $parameterAnnotation);
            }
        }
    }

    private function removeDocCommentParameter(
        File $file,
        int $functionPointer,
        ParameterAnnotation $parameterAnnotation
    ): void {
        $docCommentOpenPointer = DocCommentHelper::findDocCommentOpenToken($file, $functionPointer);
        $starPointer = TokenHelper::findPrevious(
            $file,
            T_DOC_COMMENT_STAR,
            $parameterAnnotation->getStartPointer() - 1,
            $docCommentOpenPointer
        );
        $changeStart = $starPointer ?? $docCommentOpenPointer + 1;
        $changeEnd = TokenHelper::findNext(
                $file,
                [T_DOC_COMMENT_CLOSE_TAG, T_DOC_COMMENT_STAR],
                $parameterAnnotation->getEndPointer() + 1
            ) - 1;
        $file->fixer->beginChangeset();

        for ($i = $changeStart; $i <= $changeEnd; $i++) {
            $file->fixer->replaceToken($i, '');
        }

        $file->fixer->endChangeset();
    }
}
