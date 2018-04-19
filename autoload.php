<?php
use PHP_CodeSniffer\Util\Common;

if (class_exists(Common::class)) {
    Common::$allowedTypes[] = 'int';
    Common::$allowedTypes[] = 'bool';
}
