<?php
namespace Vanio\CodingStandards\Sniffs;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Common;

class CommonSniff implements Sniff
{
    /**
     * @return int[]
     */
    public function register(): array
    {
        Common::$allowedTypes[] = 'bool';

        return [];
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     * @param File $file
     * @param int $pointer
     */
    public function process(File $file, $pointer): void
    {}
}
