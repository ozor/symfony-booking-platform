<?php

declare(strict_types=1);

namespace App\Tests\User\Infrastructure\Http\Controller;

use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Integration test for RegisterController.
 * Requires database connection.
 */
#[Group('integration')]
#[Group('database')]
class RegisterControllerTest extends WebTestCase
{
    public function testSuccessfulRegistration(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'newuser@example.com',
                'password' => 'SecurePassword123',
                'firstName' => 'John',
                'lastName' => 'Doe'
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertJson($client->getResponse()->getContent());

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertEquals('user_created', $responseData['status']);
    }

    public function testRegistrationWithInvalidEmail(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'invalid-email',
                'password' => 'SecurePassword123',
                'firstName' => 'John',
                'lastName' => 'Doe'
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testRegistrationWithMissingData(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testRegistrationWithExistingEmail(): void
    {
        $client = static::createClient();

        // First registration
        $client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'duplicate@example.com',
                'password' => 'SecurePassword123',
                'firstName' => 'John',
                'lastName' => 'Doe',
            ])
        );

        // Should succeed
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        // Attempt to register with same email
        $client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'duplicate@example.com',
                'password' => 'AnotherPassword123',
                'firstName' => 'John',
                'lastName' => 'Doe',
            ])
        );

        // Should fail with 400 Bad Request
        $this->assertGreaterThanOrEqual(400, $client->getResponse()->getStatusCode());
    }
}
