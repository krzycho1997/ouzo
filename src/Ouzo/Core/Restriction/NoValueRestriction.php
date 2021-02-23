<?php
/*
 * Copyright (c) Ouzo contributors, http://ouzoframework.org
 * This file is made available under the MIT License (view the LICENSE file for more information).
 */

namespace Ouzo\Restriction;

abstract class NoValueRestriction extends Restriction
{
    public function getValues(): array
    {
        return [];
    }
}
