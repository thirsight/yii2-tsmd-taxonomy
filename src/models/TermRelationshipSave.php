<?php

namespace tsmd\taxonomy\models;

use yii\base\Model;

/**
 * Create or update class for TermRelationship
 *
 * ```php
 * $res = (new TermRelationshipSave(['1001', '1002'], 'objTable', 'objid'))->save();
 * ```
 */
class TermRelationshipSave extends Model
{
    /**
     * @var array
     */
    private $termids;
    /**
     * @var string
     */
    private $objTable;
    /**
     * @var string
     */
    private $objid;

    /**
     * @var integer[]
     */
    private $termidsCurrent;
    /**
     * @var integer[]
     */
    private $termidsInsert;
    /**
     * @var integer[]
     */
    private $termidsDelete;

    /**
     * @param array $termids
     * @param string $objTable
     * @param string $objid
     * @param array $config
     */
    public function __construct(array $termids, string $objTable, string $objid, $config = [])
    {
        parent::__construct($config);

        $this->objTable = $objTable;
        $this->objid    = $objid;

        $this->setTermids($termids);
        $this->setTermidsCurrent();
        $this->setTermidsInsert();
        $this->setTermidsDelete();
    }

    /**
     * 重置分类 termids
     */
    public function setTermids(array $termids)
    {
        $rows = Term::find()
            ->select('termid')
            ->where(['termid' => $termids])
            ->asArray()
            ->all();
        $this->termids = array_column($rows, 'termid');
    }

    /**
     * 获取已存在的关系 termids
     */
    public function setTermidsCurrent()
    {
        $rows = TermRelationship::find()
            ->select('relTermid')
            ->where(['objid' => $this->objid, 'objTable' => $this->objTable])
            ->asArray()
            ->all();
        $this->termidsCurrent = array_column($rows, 'relTermid');
    }

    /**
     * 需要插入的分类 termids
     */
    public function setTermidsInsert()
    {
        $this->termidsInsert = array_diff($this->termids, $this->termidsCurrent);
    }

    /**
     * 需要删除的分类 termids
     */
    public function setTermidsDelete()
    {
        $this->termidsDelete = array_diff($this->termidsCurrent, $this->termids);
    }

    /**
     * @return bool|int
     */
    public function save()
    {
        foreach ($this->termidsInsert as $termid) {
            $sql[] = TermRelationship::getDb()->createCommand()->insert(TermRelationship::tableName(), [
                'relTermid' => $termid,
                'objid' => $this->objid,
                'objTable' => $this->objTable,
            ])->getRawSql();
        }
        if (!empty($sql)) {
            $resi = TermRelationship::getDb()->createCommand(implode(";\n", $sql))->execute();
        }
        if ($this->termidsDelete) {
            $resd = TermRelationship::deleteAll([
                'relTermid' => $this->termidsDelete,
                'objid' => $this->objid,
                'objTable' => $this->objTable,
            ]);
        }
        return $resi ?? ($resd ?? false);
    }
}
