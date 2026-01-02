<?php

declare(strict_types=1);

namespace Delirium\Http\Enum;

enum ResponseTypeEnum: string
{
    case JSON = 'json';
    case XML = 'xml';
    case HTML = 'html';
    case STREAM = 'stream';
    case RAW = 'raw';
}
