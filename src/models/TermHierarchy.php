<?php

namespace tsmd\taxonomy\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the class for term hierarchy
 */
class TermHierarchy
{
    /**
     * @param string $taxonomy
     * @return bool
     */
    public static function deleteCache(string $taxonomy)
    {
        return Yii::$app->cache->delete(Term::getTableUniqueKey($taxonomy));
    }

    /**
     * @param string $taxonomy
     * @param string $index
     * @return array
     */
    public static function getTermsBy(string $taxonomy, string $index = 'termid')
    {
        $terms = Yii::$app->cache->get(Term::getTableUniqueKey($taxonomy));
        if ($terms === false) {
            $terms = (new TermQuery)
                ->where(['taxonomy' => $taxonomy])
                ->orderBy('tsort, termid')
                ->all();
            if ($terms) {
                Yii::$app->cache->set(Term::getTableUniqueKey($taxonomy), $terms, 3600);
            }
        }
        return $index ? ArrayHelper::index($terms, $index) : $terms;
    }

    /**
     * @param string $taxonomy
     * @param array $keepFields
     * @return array
     */
    public static function getTreeBy($taxonomy, $keepFields = [])
    {
        $tree = [];
        $terms = static::getTermsBy($taxonomy);
        if (empty($terms)) {
            return $terms;
        }
        // 字段过滤
        if ($keepFields) {
            $keepFields = array_merge(['termid', 'parentid'], $keepFields);
            array_walk($terms, function(&$term) use ($keepFields) {
                foreach ($term as $field => $value) {
                    if (!in_array($field, $keepFields)) {
                        unset($term[$field]);
                    }
                }
            });
        }

        foreach ($terms as $id => $term) {
            if ($term['parentid']) {
                // 存在父类，获取父类
                $hierarchy = $term;
                static::detectParent($hierarchy, $terms, $term['parentid']);
                static::markLevel($hierarchy, 0);

                $tree = ArrayHelper::merge($tree, ["termid{$hierarchy['termid']}" => $hierarchy]);
            } else {
                $term['level'] = 0;
                // 数组的键名使用 tid123 字符串标识，可避免数组合并的问题
                $tree["termid{$term['termid']}"] = $term;
            }
        }
        return $tree;
    }

    /**
     * 一层层地往上获取父类
     *
     * @param $hierarchy array 首次调用此方法时, $hierarchy 应为当前分类
     * @param $terms array
     * @param $parentid int
     */
    public static function detectParent(&$hierarchy, &$terms, $parentid)
    {
        // 将当前分类附属到父类
        $parent = $terms[$parentid];
        $parent['children']["termid{$hierarchy['termid']}"] = $hierarchy;

        // 父类标识为当前分类
        $hierarchy = $parent;

        // 如果当前分类存在父类，获取父类
        if ($parent['parentid']) {
            static::detectParent($hierarchy, $terms, $parent['parentid']);
        }
    }

    /**
     * 标记数组层级
     *
     * @param $hierarchy
     * @param int $level
     */
    public static function markLevel(&$hierarchy, $level)
    {
        $hierarchy['level'] = $level;
        if (isset($hierarchy['children']) && $hierarchy['children']) {
            $key = key($hierarchy['children']);
            static::markLevel($hierarchy['children'][$key], ++$level);
        }
    }

    /**
     * 将 tree 的 key 去掉，符合 json 格式
     *
     * @param array $tree
     * @return array
     */
    public static function formatTreeForJson($tree)
    {
        if (empty($tree)) {
            return $tree;
        }

        // 去掉 key
        $tree = array_values($tree);
        foreach ($tree as &$term) {
            // 去掉子类的 key
            if (!empty($term['children'])) {
                $term['children'] = static::formatTreeForJson($term['children']);
            }
        }
        return $tree;
    }

    /**
     * @param string $taxonomy
     * @param integer $termid
     * @param array $terms
     * @return array
     */
    public static function detectParentids($taxonomy, $termid, &$terms = null)
    {
        $terms = $terms ?: static::getTermsBy($taxonomy);
        $ids = [$termid];

        if (isset($terms[$termid]) && ($term = $terms[$termid]) && $term['parentid']) {
            return array_merge(
                static::detectParentids($taxonomy, $term['parentid'], $terms),
                $ids);
        }
        return $ids;
    }

    /**
     * @param string $taxonomy
     * @param integer $termid
     * @param string $field
     * @param array $terms
     * @return array
     */
    public static function detectParentsField($taxonomy, $termid, $field, &$terms = null)
    {
        $terms = $terms ?: static::getTermsBy($taxonomy);
        $ids = isset($terms[$termid][$field]) ? [$terms[$termid][$field]] : [];

        if (isset($terms[$termid]) && ($term = $terms[$termid]) && $term['parentid']) {
            return array_merge(
                static::detectParentsField($taxonomy, $term['parentid'], $field, $terms),
                $ids);
        }
        return $ids;
    }

    /**
     * 获取所有层级子类 termid
     *
     * @param string $taxonomy
     * @param int $parentid
     * @param array|null $parentidTerms
     * @return array
     */
    public static function detectSubsTermids($taxonomy, $parentid, &$parentidTerms = null)
    {
        if ($parentidTerms === null) {
            $parentidTerms = static::getTermsBy($taxonomy, '');
            $parentidTerms = ArrayHelper::index($parentidTerms, 'termid', 'parentid');
        }
        $subTermids = array_column($parentidTerms[$parentid] ?? [], 'termid');

        foreach ($subTermids as $termid) {
            if (isset($parentidTerms[$termid])) {
                $subTermids = array_merge($subTermids, static::detectSubsTermids($taxonomy, $termid, $parentidTerms));
            }
        }
        return $subTermids;
    }
}
