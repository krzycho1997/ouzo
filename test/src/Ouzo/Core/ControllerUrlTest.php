<?php
/*
 * Copyright (c) Ouzo contributors, https://github.com/letsdrink/ouzo
 * This file is made available under the MIT License (view the LICENSE file for more information).
 */

use Ouzo\Config;
use Ouzo\ControllerUrl;
use PHPUnit\Framework\TestCase;

class ControllerUrlTest extends TestCase
{
    /**
     * @test
     */
    public function shouldCreateCorrectUrl()
    {
        //given
        $defaults = Config::getValue('global');

        //when
        $url = ControllerUrl::createUrl(['controller' => 'users', 'action' => 'add']);

        //then
        $this->assertEquals($defaults['prefix_system'] . '/users/add', $url);
    }

    /**
     * @test
     */
    public function shouldCreateCorrectUrlFromString()
    {
        //given
        $defaults = Config::getValue('global');

        //when
        $url = ControllerUrl::createUrl(['string' => '/users/add']);

        //then
        $this->assertEquals($defaults['prefix_system'] . '/users/add', $url);
    }

    /**
     * @test
     */
    public function shouldCreateCorrectUrlWithExtraParams()
    {
        //given
        $defaults = Config::getValue('global');

        //when
        $url = ControllerUrl::createUrl([
            'controller' => 'users',
            'action' => 'add',
            'extraParams' => ['id' => 5, 'name' => 'john']
        ]);

        //then
        $this->assertEquals($defaults['prefix_system'] . '/users/add/id/5/name/john', $url);
    }
}
