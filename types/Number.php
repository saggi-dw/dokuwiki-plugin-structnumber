<?php
/**
 * DokuWiki Plugin structprogress
 * Most Code is taken from decimal Type: https://github.com/cosmocode/dokuwiki-plugin-struct/blob/5c37a46b990a9bc0e314c8faa228db6012387b5f/types/Decimal.php
 *
 * @author: saggi <saggi@gmx.de>
 */

namespace dokuwiki\plugin\structnumber\types;

use dokuwiki\plugin\struct\meta\QueryBuilder;
use dokuwiki\plugin\struct\meta\QueryBuilderWhere;
use dokuwiki\plugin\struct\meta\ValidationException;
use dokuwiki\plugin\struct\types\AbstractMultiBaseType;

/**
 * Class Number
 *
 * A field accepting decimal numbers
 *
 */
class Number extends AbstractMultiBaseType
{
    protected $config = array(
        'min' => '',
        'max' => '',
        'format' => "%01.2f",
        'prefix' => '',
        'postfix' => ''
    );

    /**
     * Output the stored data
     *
     * @param string|int $value the value stored in the database
     * @param \Doku_Renderer $R the renderer currently used to render the data
     * @param string $mode The mode the output is rendered in (eg. XHTML)
     * @return bool true if $mode could be satisfied
     */
    public function renderValue($value, \Doku_Renderer $R, $mode)
    {
        $value = $this->checkFormat($value, $this->config['format']);

        $R->cdata($this->config['prefix'] . $value . $this->config['postfix']);
        return true;
    }

    /**
     * @param int|string $rawvalue
     * @return int|string
     * @throws ValidationException
     */
    public function validate($rawvalue)
    {
        $rawvalue = parent::validate($rawvalue);
        $rawvalue = str_replace(',', '.', $rawvalue); // we accept both

        if ((string)$rawvalue != (string)floatval($rawvalue)) {
            throw new ValidationException('Decimal needed');
        }

        if ($this->config['min'] !== '' && floatval($rawvalue) < floatval($this->config['min'])) {
            throw new ValidationException('Decimal min', floatval($this->config['min']));
        }

        if ($this->config['max'] !== '' && floatval($rawvalue) > floatval($this->config['max'])) {
            throw new ValidationException('Decimal max', floatval($this->config['max']));
        }

        return $rawvalue;
    }

    /**
     * Check the format config
     *
     * @param $number
     * @param $format
     * @return string
     */
    protected function checkFormat($number, $format)
    {
        // heck if its a valid sprintf format
        if (preg_match("/^%(?:['+-:\.]?\D?\d*\.?\d*)?[bdeEfFu]$/",$format)===0) {
            $format = '%01.2f';
        }
        return sprintf($format, $number);
    }

    /**
     * Decimals need to be casted to the proper type for sorting
     *
     * @param QueryBuilder $QB
     * @param string $tablealias
     * @param string $colname
     * @param string $order
     */
    public function sort(QueryBuilder $QB, $tablealias, $colname, $order)
    {
        $QB->addOrderBy("CAST($tablealias.$colname AS DECIMAL) $order");
    }

    /**
     * Decimals need to be casted to proper type for comparison
     *
     * @param QueryBuilderWhere $add
     * @param string $tablealias
     * @param string $colname
     * @param string $comp
     * @param string|\string[] $value
     * @param string $op
     */
    public function filter(QueryBuilderWhere $add, $tablealias, $colname, $comp, $value, $op)
    {
        $add = $add->where($op); // open a subgroup
        $add->where('AND', "$tablealias.$colname != ''"); // make sure the field isn't empty
        $op = 'AND';

        /** @var QueryBuilderWhere $add Where additionional queries are added to */
        if (is_array($value)) {
            $add = $add->where($op); // sub where group
            $op = 'OR';
        }

        foreach ((array)$value as $item) {
            $pl = $add->getQB()->addValue($item);
            $add->where($op, "CAST($tablealias.$colname AS DECIMAL) $comp CAST($pl AS DECIMAL)");
        }
    }

    /**
     * Only exact matches for numbers
     *
     * @return string
     */
    public function getDefaultComparator()
    {
        return '=';
    }
}
