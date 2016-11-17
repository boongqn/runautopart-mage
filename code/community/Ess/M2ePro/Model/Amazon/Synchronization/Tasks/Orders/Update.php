<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
*/

class Ess_M2ePro_Model_Amazon_Synchronization_Tasks_Orders_Update extends Ess_M2ePro_Model_Amazon_Synchronization_Tasks
{
    const PERCENTS_START = 0;
    const PERCENTS_END = 100;
    const PERCENTS_INTERVAL = 100;

    const LOCK_ITEM_PREFIX = 'synchronization_amazon_orders_update';

    // we have a limit on the server to retrieve only last 30 orders
    // so if we will update 30 or more orders at a time, we will not be able to receive all updated orders next time
    const MAX_UPDATES_PER_TIME = 25;

    //####################################

    // ->__('Amazon Orders Update Synchronization')
    private $name = 'Amazon Orders Update Synchronization';

    /** @var Ess_M2ePro_Model_Config_Synchronization */
    private $config = NULL;

    //####################################

    public function __construct()
    {
        $this->config = Mage::helper('M2ePro/Module')->getSynchronizationConfig();

        parent::__construct();
    }

    //####################################

    public function process()
    {
        // PREPARE SYNCH
        //---------------------------
        $this->prepareSynch();
        //---------------------------

        // RUN SYNCH
        //---------------------------
        $this->execute();
        //---------------------------

        // CANCEL SYNCH
        //---------------------------
        $this->cancelSynch();
        //---------------------------
    }

    //####################################

    private function prepareSynch()
    {
        $this->_lockItem->activate();
        $this->_logs->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::SYNCH_TASK_ORDERS);

        $this->_profiler->addEol();
        $this->_profiler->addTitle($this->name);
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__, 'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setTitle(Mage::helper('M2ePro')->__($this->name));
        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('Task "%s" is started. Please wait...', $this->name));
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('Task "%s" is finished. Please wait...', $this->name));

        $this->_profiler->decreaseLeftPadding(5);
        $this->_profiler->addEol();
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->saveTimePoint(__CLASS__);

        $this->_logs->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::SYNCH_TASK_UNKNOWN);
        $this->_lockItem->activate();
    }

    //####################################

    public function execute()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Update orders on Amazon');

        // Prepare last time
        $this->prepareSynchLastTime();

        // Check locked last time
        if ($this->isSynchLocked() &&
            $this->_initiator != Ess_M2ePro_Model_Synchronization_Run::INITIATOR_USER &&
            $this->_initiator != Ess_M2ePro_Model_Synchronization_Run::INITIATOR_DEVELOPER) {
            return;
        }

        // delete changes, which were processed 3 or more times
        //------------------------------
        Mage::getResourceModel('M2ePro/Order_Change')
            ->deleteByProcessingAttemptCount(
                Ess_M2ePro_Model_Order_Change::MAX_ALLOWED_PROCESSING_ATTEMPTS,
                Ess_M2ePro_Helper_Component_Amazon::NICK
            );
        //------------------------------

        /** @var $accountsCollection Mage_Core_Model_Mysql4_Collection_Abstract */
        $accountsCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Account');
        $accountsCollection->addFieldToFilter('orders_mode', Ess_M2ePro_Model_Amazon_Account::ORDERS_MODE_YES);
        $accountsTotalCount = (int)$accountsCollection->getSize();
        $accountIteration = 1;

        $percentsForAccount = self::PERCENTS_INTERVAL;
        if ($accountsTotalCount > 0) {
            $percentsForAccount = $percentsForAccount/$accountsTotalCount;
        }

        foreach ($accountsCollection->getItems() as $account) {

            /** @var Ess_M2ePro_Model_Account $account */

            /** @var Ess_M2ePro_Model_Marketplace[] $marketplaces */
            $marketplace = $account->getChildObject()->getMarketplace();

            if (!$this->isLockedAccountMarketplace($account->getId(), $marketplace->getId())) {
                $this->processAccountMarketplace($account, $marketplace);
            }

            $this->_lockItem->setPercents(self::PERCENTS_START + $percentsForAccount*$accountIteration++);
            $this->_lockItem->activate();
        }

        $this->setSynchLastTime(Mage::helper('M2ePro')->getCurrentGmtDate(true));
        $this->_profiler->saveTimePoint(__METHOD__);
    }

    //####################################

    private function processAccountMarketplace(
        Ess_M2ePro_Model_Account $account,
        Ess_M2ePro_Model_Marketplace $marketplace
    ) {
        $title = 'Starting account "'.$account->getTitle().'" and marketplace "'.$marketplace->getTitle().'"';
        $this->_profiler->addTitle($title);
        $this->_profiler->addTimePoint(__METHOD__.'send'.$account->getId(),'Update orders on Amazon');

        $status = 'Task "%s" for Amazon "%s" Account and "%s" marketplace is started. Please wait...';
        $status = Mage::helper('M2ePro')->__($status, $this->name, $account->getTitle(), $marketplace->getTitle());
        $this->_lockItem->setStatus($status);

        $changesCollection = Mage::getModel('M2ePro/Order_Change')->getCollection();
        $changesCollection->addAccountFilter($account->getId());
        $changesCollection->addProcessingAttemptDateFilter();
        $changesCollection->addFieldToFilter('component', Ess_M2ePro_Helper_Component_Amazon::NICK);
        $changesCollection->addFieldToFilter('action', Ess_M2ePro_Model_Order_Change::ACTION_UPDATE_SHIPPING);
        $changesCollection->setPageSize(self::MAX_UPDATES_PER_TIME);
        $changesCollection->getSelect()->group(array('order_id'));

        if ($changesCollection->getSize() == 0) {
            return;
        }

        // Update orders shipping status on Rakuten.com
        //---------------------------
        $items = array();

        foreach ($changesCollection as $change) {
            $changeParams = $change->getParams();

            $items[] = array(
                'order_id'         => $change->getOrderId(),
                'change_id'        => $change->getId(),
                'amazon_order_id'  => $changeParams['amazon_order_id'],
                'tracking_number'  => $changeParams['tracking_number'],
                'carrier_name'     => $changeParams['carrier_name'],
                'fulfillment_date' => $changeParams['fulfillment_date'],
                'shipping_method'  => isset($changeParams['shipping_method']) ? $changeParams['shipping_method'] : null,
                'items'            => $changeParams['items']
            );
        }

        if (count($items) == 0) {
            return;
        }

        Mage::getResourceModel('M2ePro/Order_Change')->incrementAttemptCount($changesCollection->getAllIds());

        /** @var $dispatcherObject Ess_M2ePro_Model_Connector_Server_Amazon_Dispatcher */
        $dispatcherObject = Mage::getModel('M2ePro/Connector_Server_Amazon_Dispatcher');
        $dispatcherObject->processConnector(
            'orders', 'update', 'items', array('items' => $items), $marketplace, $account
        );
        //---------------------------

        $this->_profiler->saveTimePoint(__METHOD__.'send'.$account->getId());
        $this->_profiler->addEol();
    }

    //####################################

    private function prepareSynchLastTime()
    {
        $lastTime = $this->config->getGroupValue('/amazon/orders/update/', 'last_time');

        if (!empty($lastTime)) {
            return;
        }

        $lastTime = new DateTime('now', new DateTimeZone('UTC'));
        $lastTime->modify('-1 year');

        $this->setSynchLastTime($lastTime);
    }

    private function isSynchLocked()
    {
        $lastTime = $this->config->getGroupValue('/amazon/orders/update/', 'last_time');
        $lastTime = strtotime($lastTime);

        $interval = (int)$this->config->getGroupValue('/amazon/orders/update/','interval');

        if ($lastTime + $interval > Mage::helper('M2ePro')->getCurrentGmtDate(true)) {
            return true;
        }

        return false;
    }

    private function setSynchLastTime($time)
    {
        if ($time instanceof DateTime) {
            $time = (int)$time->format('U');
        }
        if (is_int($time)) {
            $oldTimezone = date_default_timezone_get();
            date_default_timezone_set('UTC');
            $time = strftime('%Y-%m-%d %H:%M:%S', $time);
            date_default_timezone_set($oldTimezone);
        }

        $this->config->setGroupValue('/amazon/orders/update/', 'last_time', $time);
    }

    //####################################

    private function isLockedAccountMarketplace($accountId, $marketplaceId)
    {
        /** @var $lockItem Ess_M2ePro_Model_LockItem */
        $lockItem = Mage::getModel('M2ePro/LockItem');
        $lockItem->setNick(self::LOCK_ITEM_PREFIX.'_'.$accountId.'_'.$marketplaceId);

        $maxDeactivateTime = (int)$this->config->getGroupValue('/amazon/orders/update/', 'max_deactivate_time');
        $lockItem->setMaxDeactivateTime($maxDeactivateTime);

        return $lockItem->isExist();
    }

    //####################################
}
