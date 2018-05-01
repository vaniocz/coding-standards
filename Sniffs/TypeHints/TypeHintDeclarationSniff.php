<?php
namespace Vanio\CodingStandards\Sniffs\TypeHints;

use PHP_CodeSniffer\Files\File;
use SlevomatCodingStandard\Helpers\PropertyHelper;
use SlevomatCodingStandard\Helpers\StringHelper;
use SlevomatCodingStandard\Sniffs\TypeHints\TypeHintDeclarationSniff as BaseTypeHintDeclarationSniff;

class TypeHintDeclarationSniff extends BaseTypeHintDeclarationSniff
{
    /** @var string[] */
    public $usefulAnnotations = ['@internal', '@dataProvider', '@ORM\\', '@Assert\\', '@I18nRoute', '@Template'];

    /**
     * @param File $file
     * @param int $pointer
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     */
    public function process(File $file, $pointer): void
    {
        $token = $file->getTokens()[$pointer];

        if ($token['code'] === T_FUNCTION) {
            $this->callParentMethod('checkParametersTypeHints', $file, $pointer);

            if (!StringHelper::startsWith($file->getDeclarationName($pointer), 'test_')) {
                $this->callParentMethod('checkReturnTypeHints', $file, $pointer);
            }

            $this->callParentMethod('checkUselessDocComment', $file, $pointer);
        } elseif ($token['code'] === T_CLOSURE) {
            $this->callParentMethod('checkClosure', $file, $pointer);
        } elseif ($token['code'] === T_VARIABLE && PropertyHelper::isProperty($file, $pointer)) {
            $this->callParentMethod('checkPropertyTypeHint', $file, $pointer);
        }
    }

    /**
     * @param string $method
     * @param mixed ...$arguments
     * @return mixed
     */
    private function callParentMethod(string $method, ...$arguments)
    {
        $closure = function () use ($method, $arguments) {
            return $this->$method(...$arguments);
        };
        $closure = $closure->bindTo($this, parent::class);

        return $closure();
    }
}
