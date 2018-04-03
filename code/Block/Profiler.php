<?php

class Cm_Mongo_Block_Profiler extends Mage_Core_Block_Abstract
{

    protected function _toHtml()
    {
        if (!$this->_beforeToHtml()
            || !Mage::getStoreConfigFlag('dev/debug/mongo_profiler')
            || !Mage::helper('core')->isDevAllowed()) {
            return '';
        }

        $timers = Cm_Mongo_Profiler::getTimers();
        $allSum = 0.0;
        $allCount = 0;

        ob_start();
        echo '<div style="background:white; text-align: left; padding: 1em;">';
        echo '<style type="text/css">#mongo_profiler td { padding: 1px 3px; } #mongo_profiler th { padding: 3px; font-size: 120%; text-align: center; } </style>';
        echo "<a href=\"#\" onclick=\"$('mongo_profiler').toggle(); return false;\">[mongo profiler]</a>";
        echo '<table id="mongo_profiler" border="1" cellpadding="2" style="width:auto; border-collapse: collapse; margin: 1em;">';
        echo '<tr><th>Query</th><th>Time</th><th>Count</th></tr>';
        foreach ($timers as $name => $timer) {
            $sum = Cm_Mongo_Profiler::fetch($name, 'sum');
            $count = Cm_Mongo_Profiler::fetch($name, 'count');
            $allSum += $sum;
            $allCount += $count;
            echo '<tr>'
                . '<td align="left">' . $name . '</td>'
                . '<td>' . number_format($sum, 4) . '</td>'
                . '<td align="right">' . $count . '</td>'
                . '</tr>';
        }
        echo '<tr><td align="right"><b>Total</b></td><td><b>' . number_format($allSum, 4) . '</b></td><td align="right"><b>' . $allCount . '</b></td></tr>';
        echo '</table>';
        echo '</div>';

        return ob_get_clean();
    }

}
