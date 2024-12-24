<?php

declare(strict_types=1);

/**
 * This file is part of Esi\CloudflareTurnstile.
 *
 * (c) Eric Sizemore <admin@secondversion.com>
 *
 * This source file is subject to the MIT license. For the full copyright and
 * license information, please view the LICENSE file that was distributed with
 * this source code.
 */

namespace Esi\CloudflareTurnstile\Definitions;

enum ErrorCodes: string
{
    case BadRequest           = 'bad-request';
    case InternalError        = 'internal-error';
    case InvalidInputResponse = 'invalid-input-response';
    case InvalidInputSecret   = 'invalid-input-secret';
    case MissingInputResponse = 'missing-input-response';
    case MissingInputSecret   = 'missing-input-secret';
    case TimeoutOrDuplicate   = 'timeout-or-duplicate';
}
