<?php

declare(strict_types=1);

namespace Delirium\Core\Container\Exception;

use Psr\Container\NotFoundExceptionInterface;
use Exception;

class NotFoundException extends Exception implements NotFoundExceptionInterface
{
}
