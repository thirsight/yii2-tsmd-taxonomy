<?php

namespace tsmd\taxonomy\models;

use Yii;

/**
 * TermSearch represents the model behind the search form about `Term`.
 */
class TermSearch extends \yii\base\Model
{
    public $termid;
    public $taxonomy;
    public $parentid;
    public $slug;
    public $name;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['termid', 'integer'],
            ['taxonomy', 'string'],
            ['parentid', 'integer'],
            ['slug', 'string'],
            ['name', 'string'],
        ];
    }

    /**
     * @param array $params
     * @param bool $withCount
     * @return array
     */
    public function search(array $params, bool $withCount)
    {
        $this->load($params, '');
        if (!$this->validate()) {
            return [[], 0];
        }
        ($query = new TermQuery)
            ->andFilterWhere([
                'termid' => $this->termid,
                'taxonomy' => $this->taxonomy,
                'parentid' => $this->parentid,
                'slug' => $this->slug,
                'name' => $this->name]);

        $count = $withCount ? $query->count() : 0;
        $rows  = $query->addPaging()->orderBy('termid DESC')->all();
        return [$rows, $count];
    }
}
