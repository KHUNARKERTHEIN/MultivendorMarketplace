<?php
/**
 * Ksolves
 *
 * @category  Ksolves
 * @package   Ksolves_MultivendorMarketplace
 * @author    Ksolves Team
 * @copyright Copyright (c) Ksolves India Limited (https://www.ksolves.com/)
 * @license   https://store.ksolves.com/magento-license
 */

namespace Ksolves\MultivendorMarketplace\Model\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Eav\Model\Entity\Attribute\Source\SourceInterface;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * KsGender
 */
class KsGender extends AbstractSource implements SourceInterface, OptionSourceInterface
{
    /**#@+
     * Item KsGender values
     */
    const KS_GENDER_MALE = 1;
    const KS_GENDER_FEMALE = 2;
    const KS_GENDER_NOT_SPECIFIED = 3;

    /**#@-*/

    /**
     * Retrieve option array
     *
     * @return string[]
     */
    public static function getOptionArray()
    {
        return [self::KS_GENDER_MALE => __('Male'), self::KS_GENDER_FEMALE => __('Female'), self::KS_GENDER_NOT_SPECIFIED => __('Not Specified')];
    }

    /**
     * Retrieve option array with empty value
     *
     * @return string[]
     */
    public function getAllOptions()
    {
        $result = [];

        foreach (self::getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }
        return $result;
    }
}
