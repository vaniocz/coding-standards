<?php
namespace Vanio\CodingStandards\Utility;

use PHP_CodeSniffer\Files\File;

class TokenUtility
{
    public static function replaceWhiteSpaceBefore(File $file, int $pointer, string $content = '')
    {
        self::replaceWhiteSpace($file, $pointer, $content, -1);
    }

    public static function replaceWhiteSpaceAfter(File $file, int $pointer, string $content = '')
    {
        self::replaceWhiteSpace($file, $pointer, $content, 1);
    }

    /**
     * @param File $file
     * @param int $pointer
     * @param int[] $types
     * @param int[] $skippedTypes
     * @return mixed[]|null
     */
    public static function findTokenBefore(File $file, int $pointer, array $types, array $skippedTypes = [])
    {
        return self::findToken($file, $pointer, $types, $skippedTypes, -1);
    }

    /**
     * @param File $file
     * @param int $pointer
     * @param int[] $types
     * @param int[] $skippedTypes
     * @return mixed[]|null
     */
    public static function findTokenAfter(File $file, int $pointer, array $types, array $skippedTypes = [])
    {
        return self::findToken($file, $pointer, $types, $skippedTypes, 1);
    }

    private static function replaceWhiteSpace(File $file, int $pointer, string $content, int $increment)
    {
        $tokens = $file->getTokens();

        while (true) {
            if (!$token = self::findToken($file, $pointer, [T_WHITESPACE], [], $increment)) {
                return;
            }

            $pointer = $token['pointer'];
            $file->fixer->replaceToken($pointer, '');
        }
    }

    /**
     * @param File $file
     * @param int $pointer
     * @param int[] $types
     * @param int[] $skippedTypes
     * @param int $increment
     * @return mixed[]|null
     */
    private static function findToken(File $file, int $pointer, array $types, array $skippedTypes, int $increment)
    {
        $types = array_combine($types, $types);
        $skippedTypes = array_combine($skippedTypes, $skippedTypes);
        $tokens = $file->getTokens();

        while (true) {
            $pointer += $increment;

            if (!$token = $tokens[$pointer] ?? []) {
                return null;
            }

            $token += ['pointer' => $pointer];

            if (isset($types[$token['code']])) {
                return $token;
            } elseif (!isset($skippedTypes[$token['code']])) {
                return $types ? null : $token;
            }
        }
    }
}
