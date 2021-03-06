<?php

/**
 * JulioReis_CorreiosFollowup
 *
 * Do not edit this file if you want to update this module for future new versions.
 *
 * @category  JulioReis
 * @package   JulioReis_CorreiosFollowup
 *
 * @copyright Copyright (c) 2018 Julio Reis (www.rapidets.com.br)
 *
 * @author    Julio Reis <julioreis.si@gmail.com>
 */

namespace JulioReis\CorreiosFollowup\Cron;

use JulioReis\CorreiosFollowup\Model\Context as ModuleContext;
use Magento\Cron\Model\Schedule;

class Tracker
{
    /**
     * @var ModuleContext
     */
    private $context;
    /**
     * @var \JulioReis\CorreiosFollowup\Model\Tracking\QueueFactory
     */
    private $queueFactory;
    /**
     * @var \JulioReis\CorreiosFollowup\Model\Tracking\QueueRepository
     */
    private $queueRepository;
    /**
     * @var \Magento\Sales\Model\Order\Shipment\TrackRepository
     */
    private $trackRepository;
    /**
     * @var \JulioReis\CorreiosFollowup\Helper\Tracking\Queue
     */
    private $trackingQueueHelper;
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\ShipmentCommentSender
     */
    private $shipmentRepository;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\ShipmentCommentSenderFactory
     */
    private $shipmentCommentSenderFactory;

    /**
     * Tracker constructor.
     * @param ModuleContext $context
     * @param \JulioReis\CorreiosFollowup\Model\Tracking\QueueFactory $queueFactory
     * @param \JulioReis\CorreiosFollowup\Model\Tracking\QueueRepository $queueRepository
     * @param \Magento\Sales\Model\Order\Shipment\TrackRepository $trackRepository
     * @param \JulioReis\CorreiosFollowup\Helper\Tracking\Queue $trackingQueueHelper
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Model\Order\ShipmentRepository $shipmentRepository
     * @param \Magento\Sales\Model\Order\Email\Sender\ShipmentCommentSenderFactory $shipmentCommentSenderFactory
     */
    public function __construct(
        ModuleContext $context,
        \JulioReis\CorreiosFollowup\Model\Tracking\QueueFactory $queueFactory,
        \JulioReis\CorreiosFollowup\Model\Tracking\QueueRepository $queueRepository,
        \Magento\Sales\Model\Order\Shipment\TrackRepository $trackRepository,
        \JulioReis\CorreiosFollowup\Helper\Tracking\Queue $trackingQueueHelper,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\Order\ShipmentRepository $shipmentRepository,
        \Magento\Sales\Model\Order\Email\Sender\ShipmentCommentSenderFactory $shipmentCommentSenderFactory
    ) {
        $this->context = $context;
        $this->queueFactory = $queueFactory;
        $this->queueRepository = $queueRepository;
        $this->trackRepository = $trackRepository;
        $this->trackingQueueHelper = $trackingQueueHelper;
        $this->orderRepository = $orderRepository;
        $this->shipmentRepository = $shipmentRepository;
        $this->shipmentCommentSenderFactory = $shipmentCommentSenderFactory;
    }

    /**
     * @param Schedule $schedule
     */
    public function execute(Schedule $schedule)
    {
        if (!$this->context->moduleConfig()->getModuleConfig('enabled')) {
            return;
        }

        /** 1. iterate queue registers (registers from x days ago to now, and correios_status is not delivered) */
        $collection = $this->queueRepository->getPendingTracks();
        foreach ($collection as $queue) {
            try {
                $shipmentTrackId = $queue->getShipmentTrackId();
                $shipmentTrack = $this->trackRepository->get($shipmentTrackId);
                $trackNumber = $shipmentTrack->getTrackNumber();

                /** 2. search each register at the correios service */
                $trackingStatuses = $this->context->correiosService()->getTrackingStatuses($trackNumber);
                $liveStatusesQty = count($trackingStatuses);

                /** 3. if has any track update: (if not, do nothing) */
                if ($queue->getStatusesQty() >= $liveStatusesQty) {
                    continue;
                }

                /** 4. update queue register (update correios_status, statuses_qty, updated_at) */
                $statusesToUpdateQty = $liveStatusesQty - $queue->getStatusesQty();
                $statusesToUpdate = array_slice($trackingStatuses, 0, $statusesToUpdateQty);

                $auxCount = 0;
                foreach ($statusesToUpdate as $statusToUpdate) {
                    /** 5. put the new status on delivery comment and notify customer by email */
                    $shipment = $this->shipmentRepository->get($shipmentTrack->getParentId());

                    $notifyCustomer = false;
                    if ($auxCount == 0) {
                        if ($this->context->moduleConfig()->getModuleConfig('notify_mail')) {
                            $notifyCustomer = true;
                            try {
                                $shipmentCommentSender = $this->shipmentCommentSenderFactory->create();
                                $shipmentCommentSender->send($shipment, $notifyCustomer, $statusToUpdate[1]);
                            } catch (\Exception $ex) {
                                $notifyCustomer = false;
                                $this->context->logger()->error($ex->getMessage());
                            }
                        }
                    }
                    $shipment->addComment("Novo Status Correios: {$statusToUpdate[1]}", $notifyCustomer, true);
                    /** end */
                    $auxCount++;
                }
                $this->shipmentRepository->save($shipment);

                krsort($statusesToUpdate);

                $lastStatusToUpdate = end($statusesToUpdate);
                $lastCorreiosStatusFlag = $this->trackingQueueHelper->getCorreiosStatus($lastStatusToUpdate[1]);
                $queue->setCorreiosStatus($lastCorreiosStatusFlag);
                $queue->setStatusesQty($liveStatusesQty);
                $this->queueRepository->save($queue);

                /** 6. update sales_order status (possibly to delivered_to_customer if the order is delivered) */
                if ($lastCorreiosStatusFlag == \JulioReis\CorreiosFollowup\Model\Tracking\Queue::CORREIOS_STATUS_DELIVERED) {
                    $this->processDeliveredOrderState($shipmentTrack->getOrderId());
                }
            } catch (\Exception $ex) {
                $this->context->logger()->error($ex->getMessage());
            }
        }
    }

    /**
     * @param $orderId
     */
    private function processDeliveredOrderState($orderId)
    {
        if (!$this->context->moduleConfig()->getModuleConfig('change_status')) {
            return;
        }

        if (!$status = $this->context->moduleConfig()->getModuleConfig('delivered_order_status')) {
            return;
        }

        $order = $this->orderRepository->get($orderId);
        $order
            ->addStatusHistoryComment('Order delivered.', $status);

        $this->orderRepository->save($order);
    }
}
