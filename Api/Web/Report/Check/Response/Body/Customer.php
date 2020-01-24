<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Api\Web\Report\Check\Response\Body;

class Customer
    extends \Praxigento\Core\Data
{
    const A_ID = 'id';
    const A_LEVEL = 'level';
    const A_LEVEL_COMPRESSED = 'levelCompressed';
    const A_MLM_ID = 'mlm_id';
    const A_NAME = 'name';
    const A_RANK = 'rank';

    /**
     * @return int
     */
    public function getId(): int
    {
        $result = parent::get(self::A_ID);
        return $result;
    }

    /**
     * Absolute customer level int the downline tree.
     *
     * @return int
     */
    public function getLevel(): int
    {
        $result = parent::get(self::A_LEVEL);
        return $result;
    }

    /**
     * Absolute customer level int the compressed downline tree.
     *
     * @return int|null
     */
    public function getLevelCompressed()
    {
        $result = parent::get(self::A_LEVEL_COMPRESSED);
        return $result;
    }

    /**
     * @return string
     */
    public function getMlmId(): string
    {
        $result = parent::get(self::A_MLM_ID);
        return $result;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        $result = parent::get(self::A_NAME);
        return $result;
    }

    /**
     * @return string|null
     */
    public function getRank()
    {
        $result = parent::get(self::A_RANK);
        return $result;
    }

    /**
     * @param int $data
     * @return void
     */
    public function setId($data)
    {
        parent::set(self::A_ID, $data);
    }

    /**
     * Absolute customer level int the downline tree.
     *
     * @param int $data
     * @return void
     */
    public function setLevel($data)
    {
        parent::set(self::A_LEVEL, $data);
    }

    /**
     * Absolute customer level int the compressed downline tree.
     *
     * @param int $data
     * @return void
     */
    public function setLevelCompressed($data)
    {
        parent::set(self::A_LEVEL_COMPRESSED, $data);
    }

    /**
     * @param string $data
     * @return void
     */
    public function setMlmId($data)
    {
        parent::set(self::A_MLM_ID, $data);
    }

    /**
     * @param string $data
     * @return void
     */
    public function setName($data)
    {
        parent::set(self::A_NAME, $data);
    }

    /**
     * @param string $data
     * @return void
     */
    public function setRank($data)
    {
        parent::set(self::A_RANK, $data);
    }
}