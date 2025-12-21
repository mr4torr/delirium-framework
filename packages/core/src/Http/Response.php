<?php

declare(strict_types=1);

namespace Delirium\Core\Http;

use Nyholm\Psr7\Stream;
use Nyholm\Psr7\Response as Psr7Response;
use Psr\Http\Message\ResponseInterface;

class Response extends Psr7Response implements ResponseInterface
{
    /**
     * Set the response body with automatic type conversion.
     *
     * @param string|array|resource|StreamInterface|null $content Response body
     * @return static
     */
    public function body(mixed $content, HttpStatusEnum $code = HttpStatusEnum::Ok): static
    {
        return $this
            ->withStatus($code->code(), $code->reasonPhrase())
            ->withBody(Stream::create($this->content($content)));
    }

    protected function content(mixed $body): string|bool
    {
        if (is_array($body) || is_object($body)) {
            return json_encode($body, JSON_THROW_ON_ERROR);
        } elseif (!is_bool($body)) {
            return (string) $body;
        }

        return $body;
    }
}
