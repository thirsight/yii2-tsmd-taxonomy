<?php

/**
 * ```
 * $ cd ../yii2-app-advanced/api # (the dir with codeception.yml)
 * $ ./codecept run api -g taxonomyBeTerm -d
 * $ ./codecept run api ../vendor/thirsight/yii2-tsmd-taxonomy/tests/api/v1frontend/BeTermCest -d
 * $ ./codecept run api ../vendor/thirsight/yii2-tsmd-taxonomy/tests/api/v1frontend/BeTermCest[:xxx] -d
 * ```
 */
class BeTermCest
{
    /**
     * @var int
     */
    public $termid;
    
    /**
     * @return string[]
     */
    public function _fixtures()
    {
        return [
            'users' => 'tsmd\base\tests\fixtures\UsersFixture',
        ];
    }

    /**
     * @group taxonomyBeTerm
     * @group taxonomyBeTermSearch
     * @group taxonomyBeTermDelete
     */
    public function trySearch(ApiTester $I)
    {
        $data = [
            'termid' => '1001',
            'taxonomy' => 'CHINA_REGIONS',
            'parentid' => '1000',
            'slug' => '',
            'name' => '',
        ];
        $url = $I->grabFixture('users')->wrapUrl('/taxonomy/v1backend/term/search', 'be');
        $I->sendGET($url, $data);
        $I->seeResponseContains('SUCCESS');

        $resp = $I->grabResponse();
        $this->termid = json_decode($resp, true)['list'][0]['termid'] ?? 0;
    }

    /**
     * @group taxonomyBeTerm
     * @group taxonomyBeTermPrepare
     */
    public function tryPrepare(ApiTester $I)
    {
        $data = ['parentid' => $this->termid ?: 1000];
        $url = $I->grabFixture('users')->wrapUrl('/taxonomy/v1backend/term/prepare', 'be');
        $I->sendGET($url, $data);
        $I->seeResponseContains('presetTaxonomies');
    }

    /**
     * @group taxonomyBeTerm
     * @group taxonomyBeTermCreate
     */
    public function tryCreate(ApiTester $I)
    {
        $data = [
            'taxonomy' => 'CHINA_REGIONS',
            'parentid' => '1000',
            'name' => '香港',
            'brief' => '',
            'slug' => 'cr-hk',
        ];
        $url = $I->grabFixture('users')->wrapUrl('/taxonomy/v1backend/term/create', 'be');
        $I->sendPOST($url, $data);
        $I->seeResponseContains('SUCCESS');

        $resp = $I->grabResponse();
        $this->termid = json_decode($resp, true)['model']['termid'] ?? 0;
    }

    /**
     * @group taxonomyBeTerm
     * @group taxonomyBeTermView
     */
    public function tryView(ApiTester $I)
    {
        $data = ['termid' => $this->termid];
        $url = $I->grabFixture('users')->wrapUrl('/taxonomy/v1backend/term/view', 'be');
        $I->sendGET($url, $data);
        $I->seeResponseContains($this->termid);
    }

    /**
     * @group taxonomyBeTerm
     * @group taxonomyBeTermUpdate
     */
    public function tryUpdate(ApiTester $I)
    {
        $data = [
            'termid' => $this->termid ?: '1032',
            'taxonomy' => 'CHINA_REGIONS',
            'parentid' => '1000',
            'name' => '香港',
            'brief' => '香港',
            'slug' => 'cr-hk',
        ];
        $url = $I->grabFixture('users')->wrapUrl('/taxonomy/v1backend/term/update', 'be');
        $I->sendPOST($url, $data);
        $I->seeResponseContains('SUCCESS');
    }

    /**
     * @group taxonomyBeTermDelete
     */
    public function tryDelete(ApiTester $I)
    {
        $data = ['termid' => $this->termid];
        $url = $I->grabFixture('users')->wrapUrl('/taxonomy/v1backend/term/delete', 'be');
        $I->sendPOST($url, $data);
        $I->seeResponseContains('SUCCESS');
    }
}
