<?php

namespace tsmd\taxonomy\models;

use tsmd\base\models\TsmdQueryTrait;

/**
 * This is the Query class for [[TermRelationship]].
 */
class TermRelationshipQuery extends \yii\db\Query
{
    use TsmdQueryTrait;

    /**
     * {@inheritdoc}
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->from(TermRelationship::tableName());
        $this->modelClass = TermRelationship::class;
    }
}
