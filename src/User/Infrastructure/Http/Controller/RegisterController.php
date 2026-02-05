<?php

namespace App\User\Infrastructure\Http\Controller;


use App\User\Application\Command\RegisterUserCommand;
use App\User\Infrastructure\Http\Dto\RegisterUserDto;
use Exception;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class RegisterController
{
    public function __construct(
        private MessageBusInterface $bus,
        private ValidatorInterface  $validator,
        private SerializerInterface $serializer
    ) {}

    /**
     * @throws ExceptionInterface
     */
    #[Route('/api/register', name: 'user_register', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $dto = $this->serializer->deserialize(
                $request->getContent(),
                RegisterUserDto::class,
                'json'
            );
        } catch (SerializerExceptionInterface|Exception) {
            return new JsonResponse(['errors' => 'Invalid JSON data'], 400);
        }

        // Валидация DTO
        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], 400);
        }

        try {
            $this->bus->dispatch(new RegisterUserCommand(
                email: $dto->email,
                password: $dto->password,
                firstName: $dto->firstName ?? '',
                lastName: $dto->lastName ?? '',
                phoneNumber: $dto->phoneNumber ?? null
            ));
        } catch (InvalidArgumentException $e) {
            return new JsonResponse(['errors' => $e->getMessage()], 400);
        }

        return new JsonResponse(['status' => 'user_created'], 201);
    }
}
