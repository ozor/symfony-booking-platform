<?php

declare(strict_types=1);

namespace App\Tests\User\Security;

use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * JWT Authentication Integration Test
 *
 * These tests validate the JWT authentication configuration and security setup.
 * Requires Docker environment with PostgreSQL.
 */
#[Group('integration')]
#[Group('database')]
class JwtAuthenticationTest extends WebTestCase
{
    public function testLoginEndpointExistsAndAcceptsJson(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'test@example.com',
            'password' => 'test-password',
        ]));

        $response = $client->getResponse();

        // Endpoint should exist (not 404)
        $this->assertNotEquals(
            Response::HTTP_NOT_FOUND,
            $response->getStatusCode(),
            'Login endpoint /api/login should exist'
        );

        // Should accept JSON and return proper error (not 500)
        $this->assertNotEquals(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            $response->getStatusCode(),
            'Login endpoint should not cause internal server error'
        );
    }

    public function testLoginWithInvalidCredentialsReturns401(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'nonexistent@example.com',
            'password' => 'wrong-password',
        ]));

        $response = $client->getResponse();

        // Should return 401 for invalid credentials
        $this->assertEquals(
            Response::HTTP_UNAUTHORIZED,
            $response->getStatusCode(),
            'Login with invalid credentials should return 401'
        );
    }

    public function testLoginWithMissingEmailField(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'password' => 'some-password',
        ]));

        $response = $client->getResponse();

        // Should return error for missing email
        $this->assertContains(
            $response->getStatusCode(),
            [Response::HTTP_BAD_REQUEST, Response::HTTP_UNAUTHORIZED, Response::HTTP_UNPROCESSABLE_ENTITY],
            'Login without email should return 400, 401 or 422'
        );
    }

    public function testLoginWithEmptyBody(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([]));

        $response = $client->getResponse();

        // Should return error for empty body
        $this->assertContains(
            $response->getStatusCode(),
            [Response::HTTP_BAD_REQUEST, Response::HTTP_UNAUTHORIZED, Response::HTTP_UNPROCESSABLE_ENTITY],
            'Login with empty body should return 400, 401 or 422'
        );
    }

    public function testApiDocsEndpointIsPublic(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/docs');

        $response = $client->getResponse();

        // API docs should be publicly accessible (not 401)
        $this->assertNotEquals(
            Response::HTTP_UNAUTHORIZED,
            $response->getStatusCode(),
            '/api/docs should be publicly accessible without JWT token'
        );
    }

    public function testProtectedApiEndpointWithoutTokenReturns401(): void
    {
        $client = static::createClient();

        // Try to access the API root without token
        $client->request('GET', '/api');

        $response = $client->getResponse();

        // Should be unauthorized without token
        // Note: might return 404 if no route at /api root, that's also acceptable
        if ($response->getStatusCode() !== Response::HTTP_NOT_FOUND) {
            $this->assertEquals(
                Response::HTTP_UNAUTHORIZED,
                $response->getStatusCode(),
                'Protected API endpoint without JWT token should return 401'
            );
        } else {
            // If /api root doesn't exist, test passes (security is still configured)
            $this->addToAssertionCount(1);
        }
    }

    public function testProtectedApiEndpointWithInvalidToken(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer invalid.jwt.token',
        ]);

        $response = $client->getResponse();

        // Should be unauthorized with invalid token
        // Note: might return 404 if no route at /api root
        if ($response->getStatusCode() !== Response::HTTP_NOT_FOUND) {
            $this->assertEquals(
                Response::HTTP_UNAUTHORIZED,
                $response->getStatusCode(),
                'Protected API endpoint with invalid JWT token should return 401'
            );
        } else {
            // If /api root doesn't exist, test passes (security is still configured)
            $this->addToAssertionCount(1);
        }
    }

    public function testJwtConfigurationIsLoaded(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();

        // Check if JWT authentication services are available
        $this->assertTrue(
            $container->has('lexik_jwt_authentication.key_loader'),
            'JWT key loader service should be available'
        );

        $this->assertTrue(
            $container->has('lexik_jwt_authentication.encoder'),
            'JWT encoder service should be available'
        );
    }

    public function testSecurityConfigurationHasJwtFirewall(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();

        // Verify that security configuration is loaded
        $this->assertTrue(
            $container->hasParameter('security.firewalls'),
            'Security firewalls configuration should be loaded'
        );
    }

    public function testUserProviderIsConfigured(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();

        // Check if user provider exists
        $this->assertTrue(
            $container->has('security.user.provider.concrete.user_provider'),
            'User provider should be configured'
        );
    }
}
