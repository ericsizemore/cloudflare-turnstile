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

enum ErrorCodeDescriptions: string
{
    case BadRequest           = 'The request was rejected because it was malformed.';
    case InternalError        = 'An internal error happened while validating the response. The request can be retried.';
    case InvalidInputResponse = 'The response parameter (token) is invalid or has expired. Most of the time, this means a fake token has been used. If the error persists, contact customer support.';
    case InvalidInputSecret   = 'The secret parameter was invalid, did not exist, or is a testing secret key with a non-testing response.';
    case MissingInputResponse = 'The response parameter (token) was not passed.';
    case MissingInputSecret   = '	The secret parameter was not passed.';
    case TimeoutOrDuplicate   = 'The response parameter (token) has already been validated before. This means that the token was issued five minutes ago and is no longer valid, or it was already redeemed.';
}
