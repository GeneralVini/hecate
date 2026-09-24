<?php

declare(strict_types=1);

namespace App\Web\Login;

use Yiisoft\Validator\Rule\Length;
use Yiisoft\Validator\Rule\Required;

/**
 * HTTP input model for login credentials.
 *
 * Validation and normalization are intentionally separated. Username is
 * normalized only after validation. Password is never trimmed or otherwise
 * modified because whitespace may be part of a valid credential.
 */
final readonly class LoginForm
{
    public function __construct(
        #[Required(message: 'Informe o usuário.', notPassedMessage: 'Informe o usuário.')]
        #[Length(
            max: 128,
            incorrectInputMessage: 'O usuário deve ser texto.',
            greaterThanMaxMessage: 'O usuário deve ter no máximo {max} caracteres.',
        )]
        public mixed $username = null,
        #[Required(message: 'Informe a senha.', notPassedMessage: 'Informe a senha.')]
        #[Length(
            max: 1024,
            incorrectInputMessage: 'A senha deve ser texto.',
            greaterThanMaxMessage: 'A senha excede o tamanho permitido.',
        )]
        public mixed $password = null,
    ) {
    }

    /**
     * @param array<array-key, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            username: $data['username'] ?? null,
            password: $data['password'] ?? null,
        );
    }

    public function normalizedUsername(): string
    {
        return is_string($this->username) ? trim($this->username) : '';
    }

    public function passwordValue(): string
    {
        return is_string($this->password) ? $this->password : '';
    }

    public function usernameForDisplay(): string
    {
        return $this->normalizedUsername();
    }
}
