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
namespace Core\Library\EntityBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Core\Library\EntityBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

class MobileUserType extends AbstractType
{
    protected $translatorService;

    public function __construct(Translator $translatorService)
    {
        $this->translatorService = $translatorService;
    }

    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('username')
            ->add('state', 'choice', array(
            'choices'  => array(
                User::Preactivation => $this->translatorService->trans('state.preactivation', array(), 'user'),
                User::Activated     => $this->translatorService->trans('state.activated', array(), 'user'),
                User::Disabled      => $this->translatorService->trans('state.disabled', array(), 'user')),
            'required' => true
        ));
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Core\Library\EntityBundle\Entity\User'
        );
    }

    public function getName()
    {
        return 'user';
    }
}
