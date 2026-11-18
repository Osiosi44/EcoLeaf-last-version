<?php
require_once __DIR__ . '/../db/app.php';

$check = static function (bool $condition, string $message): void {
    if (!$condition) throw new RuntimeException($message);
};
$letter = "\u{1200}";
$check(text_length(str_repeat($letter, 100)) === 100, 'Unicode text must be counted as characters.');
$check(text_length("\xFF") === PHP_INT_MAX, 'Invalid UTF-8 must fail text validation.');
$errors = account_errors(str_repeat($letter, 100), 'unicode_fixture', 'unicode@example.test', '+251900000001', str_repeat($letter, 255), 'valid-password-for-tests');
$check(!isset($errors['name']) && !isset($errors['address']), 'Valid Unicode names and addresses must fit their character limits.');
$errors = account_errors(str_repeat($letter, 101), 'unicode_fixture', 'unicode@example.test', '+251900000001', str_repeat($letter, 256), 'valid-password-for-tests');
$check(isset($errors['name'], $errors['address']), 'Overlong Unicode names and addresses must be rejected.');
$check(cents('1.75') === 175 && cents('20') === 2000, 'Decimal prices must convert exactly to cents.');
try {
    cents('1.234');
    throw new RuntimeException('Overprecise prices must be rejected.');
} catch (InvalidArgumentException $expected) {
}
echo "Unicode text boundaries, invalid encoding, and decimal validation passed.\n";
