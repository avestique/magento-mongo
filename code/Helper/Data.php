<?php

class Cm_Mongo_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * Get all mongo column renderers or just one.
     *
     * @param string $key
     * @return mixed
     */
    public function getGridColumnRenderers($key = NULL)
    {
        $renderers = array(
            'datestring' => 'mongo/adminhtml_widget_grid_column_renderer_datestring',
            'datetime' => 'mongo/adminhtml_widget_grid_column_renderer_datetime',
            'implode' => 'mongo/adminhtml_widget_grid_column_renderer_implode',
        );
        if ($key !== NULL) {
            return isset($renderers[$key]) ? $renderers[$key] : NULL;
        }
        return $renderers;
    }

    /**
     * Render the profiler contents as HTML
     *
     * @return string
     */
    public function getProfilerHtml()
    {
        return Mage::app()->getLayout()->createBlock('mongo/profiler')->toHtml();
    }

}
