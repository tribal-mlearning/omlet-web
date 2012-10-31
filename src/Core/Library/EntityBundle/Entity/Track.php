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
namespace Core\Library\EntityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Core\Library\EntityBundle\Entity\User as User;

/**
 * Core\Library\EntityBundle\Entity\Track
 *
 * @ORM\Table(name="tracks",indexes={@ORM\index(name="search_idx", columns={"id"})})
 * @ORM\Entity(repositoryClass="Core\Library\EntityBundle\EntityRepository\TrackRepository")
 */
class Track
{
    const STATUS_UNPROCESSED = 0;
    const STATUS_PROCESSED = 1;
    const STATUS_PROCESSING = 2;
    const SENDER_MOBILEFRAMEWORK = 'mf';

    public $valueOf = array(
        'STATUS_UNPROCESSED'     => self::STATUS_UNPROCESSED,
        'STATUS_PROCESSED'       => self::STATUS_PROCESSED,
        'STATUS_PROCESSING'      => self::STATUS_PROCESSED,
        'SENDER_MOBILEFRAMEWORK' => self::SENDER_MOBILEFRAMEWORK,
    );

    /**
     * Helper method to be used inside twig (it's easier than constant())
     *
     * @param $key
     * @return mixed
     */
    public function getValueOf($key)
    {
        return $this->valueOf[$key];
    }

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var User $user
     *
     * @ORM\ManyToOne(targetEntity="Core\Library\EntityBundle\Entity\User", inversedBy="tracks")
     */
    private $user;

    /**
     * @var string $objectId
     *
     * @ORM\Column(name="object_id", type="string", length=255)
     */
    private $objectId;

    /**
     * @var string $sender
     *
     * @ORM\Column(name="sender", type="string", length=255)
     */
    private $sender;

    /**
     * @var datetime $deviceTimestamp
     *
     * @ORM\Column(name="device_timestamp", type="datetime")
     */
    private $deviceTimestamp;

    /**
     * @var string $addInfo
     *
     * @ORM\Column(name="add_info", type="text", nullable="true")
     */
    private $addInfo;

    /**
     * @var integer $processStatus
     *
     * @ORM\Column(name="process_status", type="integer")
     */
    private $processStatus;

    public function __construct()
    {
        $this->processStatus = false;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set user
     *
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set objectId
     *
     * @param string $objectId
     */
    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;
    }

    /**
     * Get objectId
     *
     * @return string
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * Set sender
     *
     * @param string $sender
     */
    public function setSender($sender)
    {
        $this->sender = $sender;
    }

    /**
     * Get sender
     *
     * @return string
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * Set deviceTimestamp
     *
     * @param datetime $deviceTimestamp
     */
    public function setDeviceTimestamp($deviceTimestamp)
    {
        $this->deviceTimestamp = $deviceTimestamp;
    }

    /**
     * Get deviceTimestamp
     *
     * @return datetime
     */
    public function getDeviceTimestamp()
    {
        return $this->deviceTimestamp;
    }

    /**
     * Set addInfo
     *
     * @param string $addInfo
     */
    public function setAddInfo($addInfo)
    {
        $this->addInfo = $addInfo;
    }

    /**
     * Get addInfo
     *
     * @return string
     */
    public function getAddInfo()
    {
        return $this->addInfo;
    }

    /**
     * Set processStatus
     *
     * @param integer $processStatus
     */
    public function setProcessStatus($status)
    {
        $this->processStatus = $status;
    }

    /**
     * Get processStatus
     *
     * @return integer
     */
    public function getProcessStatus()
    {
        return $this->processStatus;
    }
}