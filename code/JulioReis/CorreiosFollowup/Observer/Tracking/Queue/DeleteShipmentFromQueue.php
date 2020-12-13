<?php

namespace JulioReis\CorreiosFollowup\Observer\Tracking\Queue;

use Magento\Framework\Event\Observer;
use JulioReis\CorreiosFollowup\Model\Context as ModuleContext;

class DeleteShipmentFromQueue implements \Magento\Framework\Event\ObserverInterface
{
    /** @var ModuleContext */
    private $context;
    /** @var \JulioReis\CorreiosFollowup\Model\Tracking\QueueFactory */
    private $queueFactory;
    /** @var \JulioReis\CorreiosFollowup\Model\Tracking\QueueRepository */
    private $queueRepository;

    /**
     * AddShipmentToQueue constructor.
     * @param ModuleContext $context
     * @param \JulioReis\CorreiosFollowup\Model\Tracking\QueueFactory $queueFactory
     * @param \JulioReis\CorreiosFollowup\Model\Tracking\QueueRepository $queueRepository
     */
    public function __construct(
        ModuleContext $context,
        \JulioReis\CorreiosFollowup\Model\Tracking\QueueFactory $queueFactory,
        \JulioReis\CorreiosFollowup\Model\Tracking\QueueRepository $queueRepository
    ) {
        $this->context = $context;
        $this->queueRepository = $queueRepository;
        $this->queueFactory = $queueFactory;
    }

    /**
     * @param Observer $observer
     *
     * @return void
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $track = $observer->getTrack();

        /** get the tracking and delete from queue */
        $trackId = $track->getId();
        $queue = $this->queueRepository->loadByShipmentTrackId($trackId);
        $this->queueRepository->delete($queue);
        /** end */
    }
}
