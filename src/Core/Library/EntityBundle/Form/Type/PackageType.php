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
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

class PackageType extends AbstractType
{
    protected $translatorService;

    public function __construct(Translator $translatorService)
    {
        $this->translatorService = $translatorService;
    }

    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('uniqueId', null, array('read_only' => true))
            ->add('description')
            ->add('published')
            ->add('publishStart', 'date', array(
            'format' => $this->translatorService->trans('package.publish_start.server.datetime_format', array(), 'package'),
            'input'  => 'datetime',
            'widget' => 'single_text'
        ))
            ->add('thumbnailContent')
            ->add('files', 'collection', array(
            'type'           => new PackageFileType(),
            'allow_delete'   => true,
            'allow_add'      => true,
            'prototype'      => true,
            'error_bubbling' => false
        ))
            ->add('user', 'entity', array(
            'class'         => 'Core\Library\EntityBundle\Entity\User',
            'property'      => 'usernameOrganisation',
            'query_builder' => function($repository)
            {
                return $repository->getAllPortalUsersQueryBuilder();
            },
        ))
            ->add('categories', 'collection', array(
            'type'         => 'entity',
            'options'      => array(
                'class' => 'Core\Library\EntityBundle\Entity\CourseCategory'
            ),
            'allow_delete' => true,
            'allow_add'    => true,
        ))
            ->add('metadata', 'collection', array(
            'type'           => new MetadataType(),
            'allow_delete'   => true,
            'allow_add'      => true,
            'prototype'      => true,
            'required'       => false,
            'error_bubbling' => false
        ));
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class'     => 'Core\Library\EntityBundle\Entity\Package',
            'error_bubbling' => false
        );
    }

    public function getName()
    {
        return 'package';
    }
}
