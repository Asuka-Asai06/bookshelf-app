<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\ReadingPlan\IndexReadingPlanRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class IndexReadingPlanRequestTest extends TestCase
{
    public function test_認証ユーザーはリクエストを許可される(): void
    {
        $request = new IndexReadingPlanRequest;

        $this->assertTrue($request->authorize());
    }

    public function test_ステータス未指定でもバリデーションに成功する(): void
    {
        $validator = Validator::make(
            [],
            (new IndexReadingPlanRequest)->rules()
        );

        $this->assertTrue($validator->passes());
    }

    public function test_存在しないステータスはエラーになる(): void
    {
        $validator = Validator::make(
            [
                'status' => 'invalid',
            ],
            (new IndexReadingPlanRequest)->rules()
        );

        $this->assertFalse($validator->passes());

        $this->assertTrue(
            $validator->errors()->has('status')
        );
    }
}
