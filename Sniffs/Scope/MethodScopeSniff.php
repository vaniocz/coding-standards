<?php
namespace Vanio\CodingStandards\Sniffs\Scope;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\AbstractScopeSniff;
use PHP_CodeSniffer\Util\Tokens;
use SlevomatCodingStandard\Helpers\StringHelper;
use Vanio\CodingStandards\Utility\TokenUtility;

class MethodScopeSniff extends AbstractScopeSniff
{
    const CODE_UNNECESSARY = 'Unnecessary';
    const CODE_MISSING = 'Missing';

    const MESSAGE_UNNECESSARY = 'Unnecessary visibility on %s method "%s"';
    const MESSAGE_MISSING = 'Visibility must be declared on method "%s"';

    public function __construct()
    {
        parent::__construct([T_CLASS, T_INTERFACE, T_TRAIT], [T_FUNCTION]);
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     * @param File $file
     * @param int $pointer
     * @param int $scope
     */
    protected function processTokenWithinScope(File $file, $pointer, $scope)
    {
        $tokens = $file->getTokens();

        if (!$method = $file->getDeclarationName($pointer)) {
            return;
        }

        $isVisibilityRequired = $tokens[$scope]['code'] !== T_INTERFACE && !StringHelper::startsWith($method, 'test_');
        $skippedTypes = Tokens::$emptyTokens + Tokens::$methodPrefixes;
        $visibilityToken = TokenUtility::findTokenBefore($file, $pointer, Tokens::$scopeModifiers, $skippedTypes);

        if ($visibilityToken && !$isVisibilityRequired) {
            $data = [$tokens[$scope]['code'] === T_INTERFACE ? 'interface' : 'test', $method];

            if ($file->addFixableError(self::MESSAGE_UNNECESSARY, $pointer, self::CODE_UNNECESSARY, $data)) {
                $file->fixer->replaceToken($visibilityToken['pointer'], '');
                TokenUtility::replaceWhiteSpaceAfter($file, $visibilityToken['pointer']);
            }
        } elseif (!$visibilityToken && $isVisibilityRequired) {
            $file->addError(self::MESSAGE_MISSING, $pointer, self::CODE_MISSING, [$method]);
        }
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     * @param File $file
     * @param int $pointer
     */
    protected function processTokenOutsideScope(File $file, $pointer)
    {}
}
