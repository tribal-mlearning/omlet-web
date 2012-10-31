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
namespace Service\TrackBundle\Services;

use Symfony\Bundle\FrameworkBundle\ContainerAwareEventDispatcher;
use Symfony\Component\Validator\Validator;
use Symfony\Component\Security\Core\SecurityContext;
use Core\Library\EntityBundle\Services\EntityLibrary;
use Monolog\Logger;
use Service\TrackBundle\Events\CreateTrackEvent;

class TracksService
{
    protected $eventDispatcherService;
    protected $entityLibraryService;
    protected $validatorService;
    protected $securityContext;
    protected $loggerService;
    protected $em;

    public function __construct(Logger $loggerService, EntityLibrary $entityLibraryService, SecurityContext $securityContext, Validator $validatorService, ContainerAwareEventDispatcher $eventDispatcherService)
    {
        $this->eventDispatcherService = $eventDispatcherService;
        $this->entityLibraryService = $entityLibraryService;
        $this->validatorService = $validatorService;
        $this->securityContext = $securityContext;
        $this->loggerService = $loggerService;
        $this->em = $entityLibraryService->getManager();
    }

    /**
     * Create a raw track data for further processing
     *
     * It dispatches a "service_track.create_track" event when a valid track is persisted.
     *
     * Data available keys:
     *  Name               Description
     *   objectId           The object id of package and resource that all these entry will belong to
     *   sender             Which context triggered the post to the tracks services (default: "mf")
     *   deviceTimestamp    Device current time when generated this entry
     *   addInfo            Free-text (default: a json object escaped string)
     *
     * @param $data An array with track data information
     *
     * @return array An array with 'success' status, if true it also returns the 'data' persisted, otherwise 'errors' is available
     */
    public function createTrack($data)
    {
        $authUser = $this->securityContext->getToken()->getUser();

        $track = $this->entityLibraryService->getNew('Track');
        $track->setUser($authUser);
        $track->setObjectId($data['objectId']);
        $track->setSender($data['sender']);
        $deviceTimestamp = new \DateTime();
        $deviceTimestamp->setTimestamp($data['deviceTimestamp']);
        $track->setDeviceTimestamp($deviceTimestamp);
        $track->setAddInfo($data['addInfo']);

        $errors = $this->validatorService->validate($track);

        if (count($errors) == 0) {
            $this->em->persist($track);
            $this->em->flush();

            try {
                $event = new CreateTrackEvent($track);
                $this->eventDispatcherService->dispatch('service_track.create_track', $event);
            } catch (\Exception $e) {
                $dispatcherError = array(
                    'content' => $track,
                    'errors'  => array('ListenerException' => $e),
                );
                $this->loggerService->debug('ServiceTrackBundle:TracksService::createTrack() [eventDispatcherError]', array(
                    'dispatcherError' => $dispatcherError
                ));
            }

            $returnData = array(
                'success' => TRUE,
                'data'    => $data,
            );
        } else {
            $returnData = array(
                'success' => FALSE,
                'errors'  => $errors,
            );
        }

        $this->loggerService->debug('ServiceTrackBundle:TracksService::createTrack()', array(
            'userId'       => $authUser->getId(),
            'receivedData' => $data,
            'returnData'   => $returnData
        ));
        return $returnData;
    }
}
