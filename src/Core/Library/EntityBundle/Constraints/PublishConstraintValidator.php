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
namespace Core\Library\EntityBundle\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

class PublishConstraintValidator extends ConstraintValidator
{
    protected $translatorService;

    public function __construct(Translator $translatorService)
    {
        $this->translatorService = $translatorService;
    }

    /**
     * Prevents the publishing of a package without files or a pre-defined release date
     *
     * @param mixed $value The value
     * @param \Symfony\Component\Validator\Constraint $constraint The constriant
     * @return bool The validation status
     */
    public function isValid($value, Constraint $constraint)
    {
        if (count($value->getFiles()) == 0 && $value->isPublished()) {
            $message = $this->translatorService->trans('validation.package.publish_without_files', array(), 'validators');
            $this->context->setPropertyPath('data.published');
            $this->context->addViolation($message, array(), null);
            return false;
        }

        if (is_null($value->getPublishStart()) && $value->isPublished()) {
            $message = $this->translatorService->trans('validation.package.publish_without_date', array(), 'validators');
            $this->context->setPropertyPath('data.publishStart');
            $this->context->addViolation($message, array(), null);
            return false;
        }

        return true;
    }
}
