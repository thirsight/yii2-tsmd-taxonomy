<?php

namespace tsmd\taxonomy\models;

/**
 * This is the model class for table "{{%term_relationship}}".
 *
 * @property int $relid
 * @property int $relTermid
 * @property string $objTable
 * @property string $objid
 *
 * @property Term $relTerm
 */
class TermRelationship extends \tsmd\base\models\ArModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%term_relationship}}';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'relid'     => 'relid',
            'relTermid' => 'Rel Termid',
            'objTable'  => 'Rel Object Table',
            'objid'     => 'Rel Objid',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['relTermid', 'required'],
            ['relTermid', 'integer'],

            ['objTable', 'required'],
            ['objTable', 'string', 'max' => 64],

            ['objid', 'required'],
            ['objid', 'string', 'max' => 64],
            ['objid', 'unique', 'targetAttribute' => ['relTermid', 'objid', 'objTable']],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTerm()
    {
        return $this->hasOne(Term::class, ['termid' => 'relTermid']);
    }
}
