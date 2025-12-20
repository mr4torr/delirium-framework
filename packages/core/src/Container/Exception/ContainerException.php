<?php

declare(strict_types=1);

namespace Delirium\Core\Container\Exception;

use Psr\Container\ContainerExceptionInterface;
use Exception;

class ContainerException extends Exception implements ContainerExceptionInterface
{
}
