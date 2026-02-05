<?php

namespace App\User\Infrastructure\Http\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class RegisterUserDto
{
    #[Assert\NotBlank(message: 'Email is required')]
    #[Assert\Email(message: 'Invalid email format')]
    public string $email;

    #[Assert\NotBlank(message: 'Password is required')]
    public string $password;

    #[Assert\Type('string')]
    public ?string $firstName = null;

    #[Assert\Type('string')]
    public ?string $lastName = null;

    #[Assert\Type('string')]
    public ?string $phoneNumber = null;
}
