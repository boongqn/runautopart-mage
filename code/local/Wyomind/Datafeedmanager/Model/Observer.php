<?php

class Wyomind_Datafeedmanager_Model_Observer {

    public function scheduledGenerateFeeds($schedule) {

        $errors = array();
        $log = array();
        $log[] = "-------------------- CRON PROCESS --------------------";

        $collection = Mage::getModel('datafeedmanager/configurations')->getCollection();
        $cnt = 0;

        foreach ($collection as $feed) {

            try {

                $log[] = "--> Running profile : " . $feed->getFeedName() . ' [#' . $feed->getFeedId() . '] <--';


                $cron['curent']['localDate'] = Mage::getSingleton('core/date')->date('l Y-m-d H:i:s');
                $cron['curent']['gmtDate'] = Mage::getSingleton('core/date')->gmtDate('l Y-m-d H:i:s');
                $cron['curent']['localTime'] = Mage::getSingleton('core/date')->timestamp();
                $cron['curent']['gmtTime'] = Mage::getSingleton('core/date')->gmtTimestamp();


                $cron['file']['localDate'] = Mage::getSingleton('core/date')->date('l Y-m-d H:i:s', $feed->getFeedUpdatedAt());
                $cron['file']['gmtDate'] = $feed->getFeedUpdatedAt();
                $cron['file']['localTime'] = Mage::getSingleton('core/date')->timestamp($feed->getFeedUpdatedAt());
                $cron['file']['gmtTime'] = strtotime($feed->getFeedUpdatedAt());

                /* Magento getGmtOffset() is bugged and doesn't include daylight saving time, the following workaround is used */
                // date_default_timezone_set(Mage::app()->getStore()->getConfig('general/locale/timezone'));
                // $date = new DateTime();
                //$cron['offset'] = $date->getOffset() / 3600;
                $cron['offset'] = Mage::getSingleton('core/date')->getGmtOffset("hours");



                $log[] = '   * Last update : ' . $cron['file']['gmtDate'] . " GMT / " . $cron['file']['localDate'] . ' GMT' . $cron['offset'];
                $log[] = '   * Current date : ' . $cron['curent']['gmtDate'] . " GMT / " . $cron['curent']['localDate'] . ' GMT' . $cron['offset'];


                $cronExpr = json_decode($feed->getCronExpr());
                $i = 0;
                $done = false;

                foreach ($cronExpr->days as $d) {

                    foreach ($cronExpr->hours as $h) {
                        $time = explode(':', $h);
                        if (date('l', $cron['curent']['gmtTime']) == $d) {
                            $cron['tasks'][$i]['localTime'] = strtotime(Mage::getSingleton('core/date')->date('Y-m-d')) + ($time[0] * 60 * 60) + ($time[1] * 60);
                            $cron['tasks'][$i]['localDate'] = date('l Y-m-d H:i:s', $cron['tasks'][$i]['localTime']);
                        } else {
                            $cron['tasks'][$i]['localTime'] = strtotime("last " . $d, $cron['curent']['localTime']) + ($time[0] * 60 * 60) + ($time[1] * 60);
                            $cron['tasks'][$i]['localDate'] = date('l Y-m-d H:i:s', $cron['tasks'][$i]['localTime']);
                        }

                        if ($cron['tasks'][$i]['localTime'] >= $cron['file']['localTime'] && $cron['tasks'][$i]['localTime'] <= $cron['curent']['localTime'] && $done != true) {

                            $log[] = '   * Scheduled : ' . ($cron['tasks'][$i]['localDate'] . " GMT" . $cron['offset']);

                            if ($feed->generateFile()) {
                                $done = true;
                                $cnt++;
                                $log[] = '   * EXECUTED!';
                            }
                        }

                        $i++;
                    }
                }
            } catch (Exception $e) {
                $log[] = '   * ERROR! ' . ($e->getMessage());
            }
            if (!$done)
                $log[] = '   * SKIPPED!';
        }



        if (Mage::getStoreConfig("datafeedmanager/setting/enable_report")) {
            foreach (explode(',', Mage::getStoreConfig("datafeedmanager/setting/emails")) as $email) {
                try {
                    if ($cnt)
                        mail($email, Mage::getStoreConfig("datafeedmanager/setting/report_title"), "\n" . implode($log, "\n"));
                } catch (Exception $e) {
                    $log[] = '   * EMAIL ERROR! ' . ($e->getMessage());
                }
            }
        };
        if (isset($_GET['dfm']))
            echo "<br/>" . implode($log, "<br/>");
        Mage::log("\n" . implode($log, "\n"), null, "DataFeedManager-cron.log");
    }

    /*     * ****************************CyberDay MergeCSV START ******************************** */

    private $__csvPath;
    private $__file1 = "mercateo_products.csv";
    private $__file2 = "mercateo_products_kks.csv";
    private $__fileResult = "products_247_anhaltbauch_deu_UTF-8.csv";

    public function __construct() {
        $this->__csvPath = Mage::getBaseDir() . "/feeds/";
    }

    public function handleMerge() {
        $contentPart1 = file_get_contents($this->__csvPath . $this->__file1);
        $contentPart2 = file_get_contents($this->__csvPath . $this->__file2);

        $result = $contentPart1 . "\n" . $contentPart2;

        if (file_put_contents($this->__csvPath . $this->__fileResult, $result))
            return true;
        else
            false;
    }

    public function beforeGenerate($dataFeed) {
      
    }

    public function afterGenerate($dataFeed) {
       
        
    }

    public function afterUpload($dataFeed) {
      
    }

}
