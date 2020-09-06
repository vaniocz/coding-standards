<?php
namespace Vanio\CodingStandards\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Standards\PSR1\Sniffs\Methods\CamelCapsMethodNameSniff;
use SlevomatCodingStandard\Helpers\StringHelper;

class ValidFunctionNameSniff extends CamelCapsMethodNameSniff
{
    public const CODE_NOT_SNAKE_CASE = 'NotSnakeCase';
    public const MESSAGE_NOT_SNAKE_CASE = 'Global function "%s" is not in snake case format';

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @param int $pointer
     * @param int $scope
     */
    protected function processTokenWithinScope(File $file, $pointer, $scope): void
    {
        $function = $file->getDeclarationName($pointer);

        if ($function !== null && !StringHelper::startsWith($function, 'test_')) {
            parent::processTokenWithinScope($file, $pointer, $scope);
        }
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @param int $pointer
     */
    protected function processTokenOutsideScope(File $file, $pointer): void
    {
        $function = $file->getDeclarationName($pointer);

        if (strtolower($function) !== $function) {
            $file->addError(self::MESSAGE_NOT_SNAKE_CASE, $pointer, self::CODE_NOT_SNAKE_CASE, [$function]);
        }
    }
}
