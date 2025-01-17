<?php
/*
 * Copyright (c) Ouzo contributors, https://github.com/letsdrink/ouzo
 * This file is made available under the MIT License (view the LICENSE file for more information).
 */
namespace Ouzo\View;

use Twig_Environment;

interface TwigInitializer
{
    public function initialize(Twig_Environment $environment): void;
}
