<?php

declare(strict_types=1);

namespace App\Tests\Web\Login;

use App\Web\Login\LoginForm;
use PHPUnit\Framework\TestCase;
use Yiisoft\Validator\Validator;

final class LoginFormTest extends TestCase
{
    private Validator $validator;

    protected function setUp(): void
    {
        $this->validator = new Validator();
    }

    public function testEmptyPostIsRejected(): void
    {
        $result = $this->validator->validate(LoginForm::fromArray([]));
        $errors = $result->getFirstErrorMessagesIndexedByProperty();

        self::assertFalse($result->isValid());
        self::assertSame('Informe o usuário.', $errors['username'] ?? null);
        self::assertSame('Informe a senha.', $errors['password'] ?? null);
    }

    public function testExplicitEmptyValuesAreRejected(): void
    {
        $result = $this->validator->validate(LoginForm::fromArray([
            'username' => '',
            'password' => '',
        ]));
        $errors = $result->getFirstErrorMessagesIndexedByProperty();

        self::assertFalse($result->isValid());
        self::assertSame('Informe o usuário.', $errors['username'] ?? null);
        self::assertSame('Informe a senha.', $errors['password'] ?? null);
    }

    public function testWhitespaceOnlyUsernameIsRejected(): void
    {
        $form = LoginForm::fromArray([
            'username' => " \t\n ",
            'password' => 'x',
        ]);
        $result = $this->validator->validate($form);
        $errors = $result->getFirstErrorMessagesIndexedByProperty();

        self::assertSame('', $form->normalizedUsername());
        self::assertFalse($result->isValid());
        self::assertSame('Informe o usuário.', $errors['username'] ?? null);
    }

    public function testUsernameIsNormalizedBeforeValidationButPasswordIsPreserved(): void
    {
        $form = LoginForm::fromArray([
            'username' => '  123456  ',
            'password' => '  senha com espaços  ',
        ]);
        $result = $this->validator->validate($form);

        self::assertTrue($result->isValid());
        self::assertSame('123456', $form->normalizedUsername());
        self::assertSame('  senha com espaços  ', $form->passwordValue());
    }

    public function testWhitespaceOnlyPasswordIsRejectedWithoutBeingModified(): void
    {
        $form = LoginForm::fromArray([
            'username' => '123456',
            'password' => '   ',
        ]);
        $result = $this->validator->validate($form);
        $errors = $result->getFirstErrorMessagesIndexedByProperty();

        self::assertFalse($result->isValid());
        self::assertSame('Informe a senha.', $errors['password'] ?? null);
        self::assertSame('   ', $form->passwordValue());
    }

    public function testMalformedValuesAreRejected(): void
    {
        $form = LoginForm::fromArray([
            'username' => ['not-a-string'],
            'password' => ['not-a-string'],
        ]);
        $result = $this->validator->validate($form);
        $errors = $result->getFirstErrorMessagesIndexedByProperty();

        self::assertFalse($result->isValid());
        self::assertArrayHasKey('username', $errors);
        self::assertArrayHasKey('password', $errors);
    }

    public function testOversizedValuesAreRejected(): void
    {
        $form = LoginForm::fromArray([
            'username' => str_repeat('u', 129),
            'password' => str_repeat('p', 1025),
        ]);
        $result = $this->validator->validate($form);

        self::assertFalse($result->isValid());
        self::assertFalse($result->isPropertyValid('username'));
        self::assertFalse($result->isPropertyValid('password'));
    }
}
