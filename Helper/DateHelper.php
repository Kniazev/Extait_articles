<?php

namespace Extait\Articles\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class DateHelper extends AbstractHelper
{
    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * DateHelper constructor.
     * @param Context $context
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        Context $context,
        TimezoneInterface $timezone
    ) {
        $this->timezone = $timezone;
        parent::__construct($context);
    }

    /**
     * @param $date
     * @return string
     */
    public function convertDate($date)
    {
        $convertedDate = $this->timezone->formatDateTime($date, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::NONE);
        return $convertedDate;
    }
}
