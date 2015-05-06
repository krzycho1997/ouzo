<?php
/*
 * Copyright (c) Ouzo contributors, http://ouzoframework.org
 * This file is made available under the MIT License (view the LICENSE file for more information).
 */
namespace Ouzo\View;

use Twig_Extension;
use Twig_SimpleFunction;

class OuzoTwigExtension extends Twig_Extension
{
    public function getFunctions()
    {
        $uriHelperFunctions = allGeneratedUriNames();

        $helperFunctions = array('t', 'linkTo', 'linkButton', 'formButton', 'translatableOptions', 'labelTag',
            'hiddenTag', 'textFieldTag', 'textAreaTag', 'checkboxTag', 'selectTag', 'optionTag', 'passwordFieldTag',
            'radioButtonTag', 'endFormTag', 'formTag', 'formFor', 'showErrors', 'showNotices', 'showSuccess',
            'showWarnings');

        return array_merge(
            $this->toSimpleFunctions($helperFunctions, array('is_safe' => array('html'))),
            $this->toSimpleFunctions($uriHelperFunctions)
        );
    }

    public function getName()
    {
        return 'ouzo';
    }

    private function toSimpleFunctions($functions, array $options = array())
    {
        $result = array();
        foreach ($functions as $function) {
            $result[] = new Twig_SimpleFunction($function, $function, $options);
        }
        return $result;
    }
}
