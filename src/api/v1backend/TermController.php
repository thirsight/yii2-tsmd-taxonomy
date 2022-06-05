<?php

namespace tsmd\taxonomy\api\v1backend;

use tsmd\base\models\TsmdResult;
use tsmd\taxonomy\models\Term;
use tsmd\taxonomy\models\TermSearch;

/**
 * TermController implements the CRUD actions for Term model.
 */
class TermController extends \tsmd\base\controllers\RestBackendController
{
    /**
     * 分类查詢
     *
     * <kbd>API</kbd> <kbd>GET</kbd> <kbd>AUTH</kbd> `/taxonomy/v1backend/term/search`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * termid   | [[integer]] | No | termid
     * taxonomy | [[string]]  | No | taxonomy
     * parentid | [[integer]] | No | parentid
     * slug     | [[string]]  | No | slug
     * name     | [[string]]  | No | name
     *
     * @return array
     */
    public function actionSearch()
    {
        list($rows, $count) = ($search = new TermSearch)->search($this->getQueryParams(), true);
        return $search->hasErrors()
            ? TsmdResult::failed($search->firstErrors)
            : TsmdResult::response($rows, ['count' => $count]);
    }

    /**
     * 創建、更新分类所需相關預設數據
     *
     * <kbd>API</kbd> <kbd>GET</kbd> <kbd>AUTH</kbd> `/taxonomy/v1backend/term/prepare`
     *
     * @return array
     */
    public function actionPrepare(int $parentid = 0)
    {
        $presets = Term::presetTaxonomies();
        array_walk($presets, function(&$item, $key) {
            $item['value'] = $key;
        }, $presets);
        $term = $parentid ? $this->findModel($parentid)->toArray() : [];
        return TsmdResult::responseModel([
            'presetTaxonomies' => array_values($presets),
            'parentTerm' => $term,
        ]);
    }

    /**
     * 分类創建
     *
     * <kbd>API</kbd> <kbd>POST</kbd> <kbd>AUTH</kbd> `/taxonomy/v1backend/term/create`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * taxonomy | [[string]]  | Yes | taxonomy
     * parentid | [[integer]] | Yes | parentid
     * name     | [[string]]  | Yes | name
     * brief     | [[string]] | No  | brief
     * slug     | [[string]]  | No  | slug
     *
     * @return array
     */
    public function actionCreate()
    {
        $model = new Term();
        $model->load($this->getBodyParams(), '');
        $model->saveBat();
        return $model->hasErrors()
            ? TsmdResult::failed($model->firstErrors)
            : TsmdResult::responseModel($model->toArray());
    }

    /**
     * 分类查看
     *
     * <kbd>API</kbd> <kbd>GET</kbd> <kbd>AUTH</kbd> `/taxonomy/v1backend/term/view`
     *
     * @param int $termid
     * @return array
     */
    public function actionView(int $termid)
    {
        $model = $this->findModel($termid);
        return TsmdResult::responseModel($model->toArray());
    }

    /**
     * 分类更新
     *
     * <kbd>API</kbd> <kbd>GET</kbd> <kbd>AUTH</kbd> `/taxonomy/v1backend/term/update`
     *
     * @return array
     */
    public function actionUpdate()
    {
        $model = $this->findModel($this->getBodyParams('termid'));
        $model->load($this->getBodyParams(), '');
        $model->saveBat();
        return $model->hasErrors()
            ? TsmdResult::failed($model->firstErrors)
            : TsmdResult::responseModel($model->toArray());
    }

    /**
     * @param int $termid
     * @return Term the loaded model
     * @throws \yii\web\NotFoundHttpException if the model cannot be found
     */
    protected function findModel(int $termid)
    {
        if (($model = Term::findOne(['termid' => $termid])) !== null) {
            return $model;
        } else {
            throw new \yii\web\NotFoundHttpException('The requested `term` does not exist.');
        }
    }
}
