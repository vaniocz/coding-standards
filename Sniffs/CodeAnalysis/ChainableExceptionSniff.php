<?php
namespace Vanio\CodingStandards\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use SlevomatCodingStandard\Helpers\FunctionHelper;
use SlevomatCodingStandard\Helpers\StringHelper;
use SlevomatCodingStandard\Helpers\TokenHelper;

class ChainableExceptionSniff implements Sniff
{
    const CODE_MISSING_TYPEHINT = 'MissingTypehint';
    const CODE_NOT_CHAINABLE = 'NotChainable';

    const MESSAGE_MISSING_TYPEHINT = 'Exception is not chainable. It must have optional \Throwable as last constructor argument.';
    const MESSAGE_NOT_CHAINABLE = 'Exception is not chainable. It must have optional \Throwable as last constructor argument and has "%s".';

    /**
     * @return int[]
     */
    public function register(): array
    {
        return [T_CLASS, T_INTERFACE];
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     * @param File $file
     * @param int $pointer
     */
    public function process(File $file, $pointer)
    {
        $extendedClass = $file->findExtendedClassName($pointer);

        if ($extendedClass === false || !StringHelper::endsWith($extendedClass, 'Exception')) {
            return;
        } elseif (!$constructorPointer = $this->findConstructorPointer($file, $pointer)) {
            return;
        }

        $typeHints = FunctionHelper::getParametersTypeHints($file, $constructorPointer);

        if (!$lastTypeHint = array_pop($typeHints)) {
            $file->addError(self::MESSAGE_MISSING_TYPEHINT, $constructorPointer, self::CODE_MISSING_TYPEHINT);

            return;
        }

        $lastTypeHint = $lastTypeHint->getTypeHint();

        if (
            $lastTypeHint === '\Throwable'
            || StringHelper::endsWith($lastTypeHint, 'Exception')
            || StringHelper::endsWith($lastTypeHint, 'Error')
        ) {
            return;
        }

        $file->addError(self::MESSAGE_NOT_CHAINABLE, $constructorPointer, self::CODE_NOT_CHAINABLE, [$lastTypeHint]);
    }

    /**
     * @return int|null
     */
    private function findConstructorPointer(File $file, int $pointer)
    {
        while ($pointer = TokenHelper::findNext($file, T_FUNCTION, $pointer)) {
            if (FunctionHelper::getName($file, $pointer) === '__construct') {
                return $pointer;
            }

            $pointer++;
        }

        return null;
    }
}
