<?php
namespace Vanio\CodingStandards\Sniffs\Scope;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use Vanio\CodingStandards\Utility\TokenUtility;

class ScopeOrderSniff implements Sniff
{
    const CODE_INVALID_ORDER = 'InvalidOrder';
    const MESSAGE_INVALID_ORDER = 'Declare public methods first, then protected ones and finally private ones';

    const SCOPES = [T_PUBLIC, T_PROTECTED, T_PRIVATE];
    const WHITELISTED_METHODS = ['__construct', 'setup', 'teardown', 'initialize'];

    /**
     * @return int[]
     */
    public function register(): array
    {
        return [T_CLASS, T_INTERFACE, T_ANON_CLASS];
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     * @param File $file
     * @param int $pointer
     */
    public function process(File $file, $pointer)
    {
        $tokens = $file->getTokens();
        $closingBracePointer = $tokens[$pointer]['scope_closer'] ?? null;

        while ($pointer = $file->findNext([T_ANON_CLASS, T_FUNCTION], $pointer + 1, $closingBracePointer)) {
            if ($tokens[$pointer]['code'] === T_ANON_CLASS) {
                $pointer = $tokens[$pointer]['scope_closer'];
                continue;
            }

            if (!$method = $file->getDeclarationName($pointer)) {
                continue;
            }

            if (!$scope = TokenUtility::findTokenBefore($file, $pointer - 1, self::SCOPES, [T_FUNCTION])) {
                continue;
            }

            if (in_array(strtolower($method), self::WHITELISTED_METHODS)) {
                continue;
            }

            $currentScopeOrder = array_search($scope['code'], self::SCOPES);

            if (isset($previousScopeOrder) && $currentScopeOrder < $previousScopeOrder) {
                $file->addError(self::MESSAGE_INVALID_ORDER, $scope['pointer'], self::CODE_INVALID_ORDER);
            }

            $previousScopeOrder = $currentScopeOrder;
        }
    }
}
