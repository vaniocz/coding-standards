<?php
namespace Vanio\CodingStandards\Sniffs\Scope;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\AbstractScopeSniff;
use PHP_CodeSniffer\Util\Tokens;
use SlevomatCodingStandard\Helpers\FunctionHelper;
use SlevomatCodingStandard\Helpers\StringHelper;
use Vanio\CodingStandards\Utility\TokenUtility;

class MethodScopeSniff extends AbstractScopeSniff
{
    public const CODE_UNNECESSARY = 'Unnecessary';
    public const CODE_MISSING = 'Missing';

    public const MESSAGE_UNNECESSARY = 'Unnecessary visibility on %s method %s';
    public const MESSAGE_MISSING = 'Visibility must be declared on method %s';

    public function __construct()
    {
        parent::__construct([T_CLASS, T_INTERFACE, T_TRAIT], [T_FUNCTION]);
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @param int $pointer
     * @param int $scope
     */
    protected function processTokenWithinScope(File $file, $pointer, $scope): void
    {
        $tokens = $file->getTokens();

        if (!$method = $file->getDeclarationName($pointer)) {
            return;
        }

        $isVisibilityRequired = $tokens[$scope]['code'] !== T_INTERFACE && !StringHelper::startsWith($method, 'test_');
        $skippedTypes = Tokens::$emptyTokens + Tokens::$methodPrefixes;
        $visibilityToken = TokenUtility::findTokenBefore($file, $pointer, Tokens::$scopeModifiers, $skippedTypes);

        if ($visibilityToken && !$isVisibilityRequired) {
            $data = [
                $tokens[$scope]['code'] === T_INTERFACE ? 'interface' : 'test',
                FunctionHelper::getFullyQualifiedName($file, $pointer),
            ];

            if ($file->addFixableError(self::MESSAGE_UNNECESSARY, $pointer, self::CODE_UNNECESSARY, $data)) {
                $file->fixer->replaceToken($visibilityToken['pointer'], '');
                TokenUtility::replaceWhiteSpaceAfter($file, $visibilityToken['pointer']);
            }
        } elseif (!$visibilityToken && $isVisibilityRequired) {
            $file->addError(self::MESSAGE_MISSING, $pointer, self::CODE_MISSING, [$method]);
        }
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @param int $pointer
     */
    protected function processTokenOutsideScope(File $file, $pointer): void
    {}
}
