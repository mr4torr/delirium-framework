<?php

declare(strict_types=1);

namespace Delirium\Core\Tests\Unit;

use Delirium\Core\AppFactory;
use Delirium\Core\Application;
use Delirium\Http\Attribute\MapRequestPayload;
use Delirium\Http\Attribute\Post;
use Delirium\Http\Exception\ValidationException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints as Assert;
use Nyholm\Psr7\Factory\Psr17Factory;

// --- DTOs and Entities ---

readonly class CreateUserDto
{
    public function __construct(
        public string $name,
        public int $age
    ) {}
}

class ProductEntity
{
    public string $name;
    public float $price;
}

class ValidatedDto
{
    public function __construct(
        #[Assert\Email]
        public string $email
    ) {}
}

use Delirium\Http\Attribute\Controller; // Added import

// --- Controller ---

#[\Delirium\Http\Attribute\Controller]
class MapPayloadResource
{
    #[Post('/test/dto')]
    public function dto(
        #[MapRequestPayload] CreateUserDto $dto
    ): array {
        return ['name' => $dto->name, 'age' => $dto->age];
    }

    #[Post('/test/entity')]
    public function entity(
        #[MapRequestPayload] ProductEntity $product
    ): array {
        return ['name' => $product->name, 'price' => $product->price];
    }

    #[Post('/test/mixed/{id}')]
    public function mixed(
        string $id,
        #[MapRequestPayload] CreateUserDto $dto
    ): array {
        return ['id' => $id, 'name' => $dto->name];
    }

    #[Post('/test/validated')]
    public function validated(
        #[MapRequestPayload] ValidatedDto $dto
    ): array {
        return ['email' => $dto->email];
    }
}

// --- Test Module ---

#[\Delirium\Core\Attribute\Module(
    controllers: [MapPayloadResource::class],
    providers: [],
    imports: []
)]
class TestPayloadModule {}

// --- Test Case ---

class MapRequestPayloadTest extends TestCase
{
    private static Application $app;

    public static function setUpBeforeClass(): void
    {
        $options = new \Delirium\Core\AppOptions(
            new \Delirium\Core\Options\DebugOptions(debug: true)
        );
        self::$app = AppFactory::create(TestPayloadModule::class, $options);
    }

    private function dispatch(string $method, string $uri, array $body = []): mixed
    {
        $container = self::$app->getContainer();
        $router = $container->get(\Delirium\Http\Router::class);

        $psr17Factory = new Psr17Factory();
        $request = $psr17Factory->createServerRequest($method, $uri);
        $request->getBody()->write(json_encode($body));
        $request->getBody()->rewind();

        return $router->dispatch($request);
    }

    public function testDtoMapping(): void
    {
        $response = $this->dispatch('POST', '/test/dto', ['name' => 'Alice', 'age' => 30]);
        $this->assertEquals(['name' => 'Alice', 'age' => 30], json_decode((string)$response->getBody(), true));
    }

    public function testLooseMapping(): void
    {
        $response = $this->dispatch('POST', '/test/dto', ['name' => 'Bob', 'age' => 40, 'extra' => 'ignore']);
        $this->assertEquals(['name' => 'Bob', 'age' => 40], json_decode((string)$response->getBody(), true));
    }

    public function testEntityMapping(): void
    {
        $response = $this->dispatch('POST', '/test/entity', ['name' => 'Widget', 'price' => 19.99]);
        $this->assertEquals(['name' => 'Widget', 'price' => 19.99], json_decode((string)$response->getBody(), true));
    }

    public function testMixedUsage(): void
    {
        $response = $this->dispatch('POST', '/test/mixed/123', ['name' => 'Charlie', 'age' => 25]);
        $this->assertEquals(['id' => '123', 'name' => 'Charlie'], json_decode((string)$response->getBody(), true));
    }

    public function testValidationFailure(): void
    {
        $this->expectException(ValidationException::class);
        $this->dispatch('POST', '/test/validated', ['email' => 'invalid-email']);
    }

    public function testValidationSuccess(): void
    {
        $response = $this->dispatch('POST', '/test/validated', ['email' => 'test@example.com']);
        $this->assertEquals(['email' => 'test@example.com'], json_decode((string)$response->getBody(), true));
    }
}
