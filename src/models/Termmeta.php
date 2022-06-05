<?php

namespace tsmd\taxonomy\models;

use Yii;

/**
 * This is the model class for table "{{%termmeta}}".
 *
 * @property int $metaid
 * @property int $metaTermid
 * @property string $metaKey
 * @property string|null $metaValue
 *
 * @property Term $metaTerm
 */
class Termmeta extends \tsmd\base\models\ArModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%termmeta}}';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'metaid'     => 'metaid',
            'metaTermid' => 'Meta Termid',
            'metaKey'    => 'Meta Key',
            'metaValue'  => 'Meta Value',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['metaTermid', 'required'],
            ['metaTermid', 'integer'],

            ['metaKey', 'required'],
            ['metaKey', 'string', 'max' => 64],
            ['metaKey', 'unique', 'targetAttribute' => ['metaTermid', 'metaKey']],

            ['metaValue', 'safe'],
        ];
    }
}
