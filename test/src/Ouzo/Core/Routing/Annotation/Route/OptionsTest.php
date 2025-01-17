<?php
/*
 * Copyright (c) Ouzo contributors, https://github.com/letsdrink/ouzo
 * This file is made available under the MIT License (view the LICENSE file for more information).
 */

use Ouzo\Http\HttpMethod;
use Ouzo\Http\HttpStatus;
use Ouzo\Routing\Annotation\Route;
use Ouzo\Routing\Annotation\Route\Options;
use PHPUnit\Framework\TestCase;

class OptionsTest extends TestCase
{
    /**
     * @test
     */
    public function shouldExtendRouteAnnotationClass()
    {
        //then
        $this->assertInstanceOf(Route::class, new Options(''));
    }

    /**
     * @test
     * @dataProvider getValidParameters
     */
    public function testRouteParameters(string $path, ?int $httpResponseCode)
    {
        //when
        $route = new Options($path, $httpResponseCode);

        //then
        $this->assertEquals([HttpMethod::OPTIONS], $route->getHttpMethods());
        $this->assertEquals($path, $route->getPath());
        $this->assertEquals($httpResponseCode, $route->getHttpResponseCode());
    }

    public function getValidParameters(): array
    {
        return [
            ['/foo', null],
            ['/foo', HttpStatus::OK],
        ];
    }
}
