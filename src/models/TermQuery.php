<?php

namespace tsmd\taxonomy\models;

use tsmd\base\models\TsmdQueryTrait;

/**
 * This is the Query class for [[Term]].
 */
class TermQuery extends \yii\db\Query
{
    use TsmdQueryTrait;

    /**
     * {@inheritdoc}
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->from(Term::tableName());
        $this->modelClass = Term::class;
    }
}
