<?php

declare(strict_types=1);

namespace Delirium\Core\Http;

use Psr\Http\Message\ResponseInterface;

class JsonResponse extends Response implements ResponseInterface
{
    /**
     * @param int $status Status code
     * @param array $headers Response headers
     * @param string|resource|StreamInterface|null $body Response body
     */
    public function __construct(
        int $status = 200,
        array $headers = [],
        mixed $body = null,
    ) {
        if (!isset($headers['Content-Type'])) {
            $headers['Content-Type'] = 'application/json';
        }

        parent::__construct($status, $headers, $this->content($body));
    }
}
