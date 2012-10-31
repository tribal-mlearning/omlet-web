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
namespace Service\TrackBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\ContainerAwareEventDispatcher;
use Symfony\Component\Validator\Validator;
use Symfony\Component\Security\Core\SecurityContext;
use Core\Library\EntityBundle\Services\EntityLibrary;
use Service\TrackBundle\Events\ProcessTrackEvent;
use Monolog\Logger;

class ProcessCommand extends ContainerAwareCommand
{
    protected $eventDispatcherService;
    protected $entityLibraryService;
    protected $validatorService;
    protected $securityContext;
    protected $loggerService;
    protected $em;

    /**
     * mf:tracks:process command
     */
    protected function configure()
    {
        $this
            ->setName('mf:tracks:process')
            ->setDescription('Process all track raw data');
    }

    /**
     * Pre-loads necessary services
     */
    private function loadServices()
    {
        $container = $this->getContainer();
        $this->eventDispatcherService = $container->get('event_dispatcher');
        $this->entityLibraryService = $container->get('core.entity_library');
        $this->validatorService = $container->get('validator');
        $this->securityContext = $container->get('security.context');
        $this->loggerService = $container->get('logger');
        $this->em = $this->entityLibraryService->getManager();
    }

    /**
     * Starts the execution of the command
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->loadServices();
        $output->writeln('Starting track processing');
        $validEntries = $this->processAll($output);
        $processedIds = '';
        foreach ($validEntries as $validEntry) {
            $processedIds .= $validEntry->getId() . '; ';
        }
        $output->writeln('Track processing finished');
        $output->writeln('Processed track ids: ' . $processedIds);
    }

    /**
     * Process all system Tracks which has the status UNPROCESSED.
     * The process consists of the following algorithm:
     * - Change the Track status to PROCESSING
     * - Dispatch a "service_track.process_track" event
     * - Ignore any errors of listeners binded to the dispatched event, but log them in debug mode.
     * - If none of the listeners generated an exception and the Track still consistent, then it gets marked as PROCESSED
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output The interface to write the process output
     *
     * @return array An array of valid and processed Tracks
     */
    private function processAll(OutputInterface $output)
    {
        $invalidEntries = array();
        $validEntries = array();
        $unprocessedTracks = $this->entityLibraryService->get('Track')->findByProcessStatus(\Core\Library\EntityBundle\Entity\Track::STATUS_UNPROCESSED);
        $output->writeln('Number of tracks to be processed: ' . sizeof($unprocessedTracks));

        $output->write('Progress status: ');
        foreach ($unprocessedTracks as $track) {
            $output->write('.');
            $track->setProcessStatus($track->getValueOf('STATUS_PROCESSING'));
            $this->em->persist($track);
            $this->em->flush();
            try {
                $event = new ProcessTrackEvent($track);
                $this->eventDispatcherService->dispatch('service_track.process_track', $event);
            } catch (\Exception $e) {
                $dispatcherError = array(
                    'content' => $track,
                    'errors'  => array('ListenerException' => $e),
                );
                $this->loggerService->debug('ServiceTrackBundle:ProcessCommand::processAll() [eventDispatcherError]', array(
                    'dispatcherError' => $dispatcherError
                ));
                $invalidEntries[] = $dispatcherError;
                continue;
            }
            $track->setProcessStatus($track->getValueOf('STATUS_PROCESSED'));
            $errors = $this->validatorService->validate($track);
            if (count($errors) == 0) {
                $this->em->persist($track);
                $validEntries[] = $track;
            } else {
                $invalidEntries[] = array(
                    'content' => $track,
                    'errors'  => $errors,
                );
            }
        }
        $this->em->flush();
        $output->writeln('');

        $this->loggerService->debug('ServiceTrackBundle:ProcessCommand::processAll()', array(
            'returnData'     => $validEntries,
            'invalidEntries' => $invalidEntries,
        ));
        return $validEntries;
    }
}