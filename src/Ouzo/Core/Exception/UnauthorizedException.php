<?php
/*
 * Copyright (c) Ouzo contributors, http://ouzoframework.org
 * This file is made available under the MIT License (view the LICENSE file for more information).
 */

namespace Ouzo\Exception;

use Ouzo\ExceptionHandling\Error;
use Ouzo\ExceptionHandling\OuzoException;

class UnauthorizedException extends OuzoException
{
    const HTTP_CODE = 401;

    /**
     * @param Error[]|Error $errors
     * @param string[] $headers
     */
    public function __construct(array|Error $errors, array $headers = [])
    {
        parent::__construct(self::HTTP_CODE, "Unauthorized.", $errors, $headers);
    }
}
