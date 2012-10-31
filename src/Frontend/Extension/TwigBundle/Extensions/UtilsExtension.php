<?php
/*
 * Copyright (c) 2012, TATRC and Tribal
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * * Redistributions of source code must retain the above copyright
 *   notice, this list of conditions and the following disclaimer.
 * * Redistributions in binary form must reproduce the above copyright
 *   notice, this list of conditions and the following disclaimer in the
 *   documentation and/or other materials provided with the distribution.
 * * Neither the name of TATRC or TRIBAL nor the
 *   names of its contributors may be used to endorse or promote products
 *   derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL TATRC OR TRIBAL BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */
namespace Frontend\Extension\TwigBundle\Extensions;

use Symfony\Component\Translation\TranslatorInterface;

class UtilsExtension extends \Twig_Extension
{
    protected $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getFilters()
    {
        return array();
    }

    public function getFunctions()
    {
        return array(
            'render_first_error' => new \Twig_Filter_Method($this, 'renderFirstError'),
            'yes_no'             => new \Twig_Filter_Method($this, 'yesNo'),
            'user_state'         => new \Twig_Filter_Method($this, 'userState'),
        );
    }

    /**
     * Helper to render just the first error of a field
     *
     * @param $field
     * @return string
     */
    function renderFirstError($field)
    {
        $errors = $field->get('errors');

        if (count($errors) == 0) return '';

        return '<ul class="error_list"><li>' . $this->translator->trans($errors[0]->getMessageTemplate(), $errors[0]->getMessageParameters()) . '</li></ul>';
    }

    /**
     * Yes/No rendering helper if supplied $value exists
     *
     * @param $value
     * @return string
     */
    function yesNo($value)
    {
        return $this->translator->trans($value ? "yes" : "no", array(), "system");
    }

    /**
     * User state view helper based on supplied $value
     *
     * @param $value
     * @return string
     */
    function userState($value)
    {
        switch ($value) {
            case 'A':
                return $this->translator->trans("state.activated", array(), "user");
                ;
            case 'D':
                return $this->translator->trans("state.disabled", array(), "user");
                ;
            case 'P':
                return $this->translator->trans("state.preactivation", array(), "user");
                ;
        }
    }

    public function getName()
    {
        return 'utils_extension';
    }
}