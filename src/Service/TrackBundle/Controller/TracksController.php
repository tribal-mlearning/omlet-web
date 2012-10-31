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
namespace Service\TrackBundle\Controller;

use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use FOS\RestBundle\View\View;

class TracksController extends Controller
{
    /**
     * REST POST /tracks
     * Store a raw track-data into the database for further processing.
     *
     * Request parameters (must be inside a JSON "content" object):
     *  Name               Description
     *   objectId           The object id of package and resource that all these entry will belong to
     *   sender             Which context triggered the post to the tracks services (default: "mf")
     *   deviceTimestamp    Device current time when generated this entry
     *   addInfo            Free-text (default: a json object escaped string)
     *
     * JSON response example:
     *    [
     *      {
     *        "addInfo": "[{\"foo\":\"bar\",\"status\":0},{\"baz\":\"qux\",\"status\":1}]",
     *        "deviceTimestamp": 1327664806,
     *        "objectId": "sample",
     *        "sender": "mf"
     *      }
     *    ]
     *
     * Response:
     *  Code   Description
     *   201    Successfully created all the track entries, all of them are returned.
     *   202    Partially executed, system has failed to create some tracks. The valid ones are returned.
     *   400    Bad request, none of input data was created.
     * @return \FOS\RestBundle\View\View
     */
    public function postTracksAction()
    {
        $authUser = $this->get('security.context')->getToken()->getUser();
        $data = $this->get('request')->request->all();

        $loggerService = $this->get('logger');

        $entries = array();
        $invalidEntries = array();
        foreach ($data as $entry) {
            $dataType = $this->get('core.entity_library')->getType('Track');
            $form = $this->createForm($dataType, null);
            $form->bind($entry);
            if ($form->isValid()) { // treats the rest submit validation
                $response = $this->get('service_track.tracks')->createTrack($entry);
                if ($response['success']) { // treats the entity persist validation
                    $entries[] = $entry;
                } else {
                    $invalidEntries[] = array('content' => $entry,
                                              'errors'  => $response['errors']); // in this case will be entity validation errors
                }
            } else {
                $entryErrorsMessage = array();
                $entryErrors = $form->getErrors();
                foreach ($entryErrors as $entryError) {
                    $entryErrorsMessage[] = $entryError->getMessageTemplate();
                }
                $invalidEntries[] = array('content' => $entry,
                                          'errors'  => $entryErrorsMessage); // in this case will be form validation errors
            }
        }

        $loggerService->debug('ServiceTrackBundle:TracksController::postTracksAction()', array(
            'userId'         => $authUser->getId(),
            'receivedData'   => $data,
            'returnData'     => $entries,
            'invalidEntries' => $invalidEntries
        ));

        $view = View::create()
            ->setData($entries);

        if (sizeof($invalidEntries) == 0)
            $view->setStatusCode(201); // Created
        else if (sizeof($invalidEntries) == sizeof($data))
            $view->setStatusCode(400); // Bad request, none of input data was created
        else
            $view->setStatusCode(202); // Accepted, partial executed due to invalidEntries

        return $view;
    }
}