<?php

namespace tsmd\taxonomy\models;

use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%term}}".
 *
 * @property int $termid
 * @property string $taxonomy
 * @property int $parentid
 * @property string $parentids
 * @property string|null $slug
 * @property string $name
 * @property string $brief
 * @property int $tsort
 * @property int $subCount
 * @property int $relCount
 * @property int $createdTime
 * @property int $updatedTime
 *
 * @property Term $parentTerm
 * @property Term[] $parentTerms
 * @property Term[] $subTerms
 * @property Termmeta[] $termmeta
 * @property TermRelationship[] $termRelationships
 */
class Term extends \tsmd\base\models\ArModel
{
    const T_CHINA_REGIONS = 'CHINA_REGIONS';

    const T_BE_SIDE_MENU   = 'BE_SIDE_MENU';
    const T_BE_HEADER_MENU = 'BE_HEADER_MENU';
    const T_BE_FOOTER_MENU = 'BE_FOOTER_MENU';

    const T_FE_SIDE_L_MENU = 'FE_SIDE_L_MENU';
    const T_FE_SIDE_R_MENU = 'FE_SIDE_R_MENU';
    const T_FE_HEADER_MENU = 'FE_HEADER_MENU';
    const T_FE_FOOTER_MENU = 'FE_FOOTER_MENU';

    /**
     * @var string
     */
    private $oldTaxonomy;
    /**
     * @var int|null
     */
    private $oldParentid;
    /**
     * @var string
     */
    private $oldParentids;
    /**
     * @var array
     */
    private $subsTermids = [];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%term}}';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'termid'      => 'termid',
            'taxonomy'    => 'Taxonomy',
            'parentid'    => 'Parent termid',
            'parentids'   => 'Parent termids',
            'slug'        => 'Slug',
            'name'        => 'Name',
            'brief'       => 'Brief',
            'tsort'       => 'Sort',
            'subCount'    => 'Sub Term Count',
            'relCount'    => 'Related Object Count',
            'createdTime' => 'Created Time',
            'updatedTime' => 'Updated Time',
        ];
    }

    /**
     * 预定义分类法
     * @param null $key
     * @param null $default
     * @return array|mixed
     */
    public static function presetTaxonomies($key = null, $default = null)
    {
        $data = [
            self::T_CHINA_REGIONS  => ['name' => 'China Regions'],
            self::T_BE_SIDE_MENU   => ['name' => 'Backend SIDE MENU'],
            self::T_BE_HEADER_MENU => ['name' => 'Backend HEADER MENU'],
            self::T_BE_FOOTER_MENU => ['name' => 'Backend FOOTER MENU'],
            self::T_FE_SIDE_L_MENU => ['name' => 'Frontend LEFT SIDE MENU'],
            self::T_FE_SIDE_R_MENU => ['name' => 'Frontend RIGHT SIDE MENU'],
            self::T_FE_HEADER_MENU => ['name' => 'Frontend HEADER MENU'],
            self::T_FE_FOOTER_MENU => ['name' => 'Frontend FOOTER MENU'],
        ];
        return $key === null ? $data : ArrayHelper::getValue($data, $key, $default);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParentTerm()
    {
        return $this->hasOne(Term::class, ['termid' => 'parentid']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParentTerms()
    {
        return Term::find()->where(['termid' => explode(',', $this->parentids)]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubTerms()
    {
        return $this->hasMany(Term::class, ['parentid' => 'termid']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTermmeta()
    {
        return $this->hasMany(Termmeta::class, ['metaTermid' => 'termid']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTermRelationships()
    {
        return $this->hasMany(TermRelationship::class, ['relTermid' => 'termid']);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['taxonomy', 'required', 'when' => function ($model) {
                return empty($this->parentid);
            }],
            ['taxonomy', 'string', 'max' => 32],

            ['parentid', 'integer'],

            ['name', 'required'],
            ['name', 'string', 'max' => 128],
            ['brief', 'string', 'max' => 255],

            ['tsort', 'integer'],

            ['slug', 'string', 'max' => 128],
            ['slug', 'unique', 'skipOnError' => true],
        ];
    }

    /**
     * 设置层级父类值，半角逗号隔开
     */
    public function resetParentids()
    {
        if ($this->parentid && $this->parentid == $this->termid) {
            $this->addError('ParentidItself', 'Parent term can not be itself.');
            return false;
        }
        if ($this->parentid && $this->parentTerm === null) {
            $this->addError('ParentTermNotExists', 'Parent term does not exists.');
            return false;
        }
        if (stripos($this->parentTerm->parentids . ',', $this->termid . ',') !== false) {
            $this->addError('ParentidItselfNested', 'Parent term can not be itself nested.');
            return false;
        }
        if ($this->parentid) {
            $this->parentids = trim($this->parentTerm->parentids . ',' . $this->parentid, ',');
            $this->taxonomy = $this->parentTerm->taxonomy;
        } else {
            $this->parentid = null;
            $this->parentids = '';
        }

        $this->oldTaxonomy  = $this->taxonomy != $this->getOldAttribute('taxonomy') ? $this->getOldAttribute('taxonomy') : null;
        $this->oldParentid  = $this->parentid != $this->getOldAttribute('parentid') ? $this->getOldAttribute('parentid') : null;
        $this->oldParentids = $this->parentid != $this->getOldAttribute('parentid') ? $this->getOldAttribute('parentids') : null;
        if ($this->oldTaxonomy || $this->oldParentid) {
            $this->subsTermids = TermHierarchy::detectSubsTermids($this->getOldAttribute('taxonomy'), $this->termid);
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    protected function saveInput()
    {
        parent::saveInput();

        if (empty($this->parentid)) {
            $this->parentid = null;
        }
        if (empty($this->slug)) {
            $this->slug = null;
        }
    }

    /**
     * 统计当前类的父类的下一级子类个数
     */
    public function calcParentSubCount()
    {
        if ($this->oldParentid) {
            $rows = static::find()
                ->select('parentid, COUNT(*) AS subCount')
                ->where(['parentid' => [$this->parentid, $this->oldParentid]])
                ->groupBy('parentid')
                ->all();
            foreach ($rows as $r) {
                static::updateAll(['subCount' => $r['subCount']], ['termid' => $r['parentid']]);
            }
        }
    }

    /**
     * 同步设置子类 taxonomy, parentids
     */
    public function syncToSubs()
    {
        if (empty($this->subsTermids)) {
            return false;
        }
        if ($this->oldTaxonomy) {
            static::updateAll(
                ['taxonomy' => $this->taxonomy],
                ['termid' => $this->subsTermids, 'taxonomy' => $this->oldTaxonomy]
            );
        }
        if ($this->oldParentids) {
            static::updateAll(
                ['parentids' => new Expression("REPLACE(parentids, '{$this->oldParentids}', '{$this->parentids}')")],
                ['and',
                    ['termid' => $this->subsTermids],
                    ['like', 'parentids', $this->oldParentids . '%', false]
                ]
            );
        }
        return true;
    }

    /**
     * @return bool
     */
    public function saveBat()
    {
        if (!$this->validate() || !$this->resetParentids()) {
            return false;
        }
        $res = $this->save(false);
        if ($res) {
            $this->calcParentSubCount();
            $this->syncToSubs();

            TermHierarchy::deleteCache($this->taxonomy);
            if ($this->oldTaxonomy) TermHierarchy::deleteCache($this->taxonomy);
        }
        return $res;
    }
}
