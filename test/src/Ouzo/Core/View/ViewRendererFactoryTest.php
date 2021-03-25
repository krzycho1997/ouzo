<?php
/*
 * Copyright (c) Ouzo contributors, http://ouzoframework.org
 * This file is made available under the MIT License (view the LICENSE file for more information).
 */
use Ouzo\Config;
use Ouzo\View\PhtmlRenderer;
use Ouzo\View\ViewRenderer;
use Ouzo\View\ViewRendererFactory;

class DummyRenderer implements ViewRenderer
{
    public function render(): string
    {
    }

    public function getViewPath(): string
    {
    }
}

use PHPUnit\Framework\TestCase; 

class ViewRendererFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function shouldCreatePhtmlRendererWhenRendererWasNotConfigured()
    {
        //when
        $renderer = ViewRendererFactory::create('my_view', []);

        //then
        $this->assertInstanceOf(PhtmlRenderer::class, $renderer);
    }

    /**
     * @test
     */
    public function shouldCreateDefaultRendererAsSetInConfiguration()
    {
        //given
        Config::overrideProperty('renderer', 'default')->with('DummyRenderer');

        //when
        $renderer = ViewRendererFactory::create('my_view', []);

        //then
        $this->assertInstanceOf('DummyRenderer', $renderer);
    }

    /**
     * @test
     */
    public function shouldCreateRendererAsSetInConfigurationForParticularView()
    {
        //given
        Config::overrideProperty('renderer', 'my_view')->with('DummyRenderer');

        //when
        $renderer = ViewRendererFactory::create('my_view', []);

        //then
        $this->assertInstanceOf('DummyRenderer', $renderer);
    }

    /**
     * @test
     */
    public function shouldCreateRendererAsSetInConfigurationForParticularViewEvenThoughDefaultRendererIsSpecified()
    {
        //given
        Config::overrideProperty('renderer', 'default')->with('DefaultRenderer');
        Config::overrideProperty('renderer', 'my_view')->with('DummyRenderer');

        //when
        $renderer = ViewRendererFactory::create('my_view', []);

        //then
        $this->assertInstanceOf('DummyRenderer', $renderer);
    }
}
