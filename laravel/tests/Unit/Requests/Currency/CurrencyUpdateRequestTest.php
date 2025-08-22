<?php

namespace Tests\Unit\Requests\Currency;

use App\Http\Requests\Currency\CurrencyUpdateRequest;
use Tests\Base\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;

/**
 * Unit tests for CurrencyUpdateRequest
 * Tests validation rules and authorization logic for currency updates
 */
#[CoversClass(CurrencyUpdateRequest::class)]
#[Group('unit')]
#[Group('requests')]
#[Small]
class CurrencyUpdateRequestTest extends UnitTestCase
{
    protected $request;

    protected function setUp(): void
    {
        parent::setUp();
        $this->request = new CurrencyUpdateRequest();
    }

    #[Test]
    public function request_is_form_request(): void
    {
        $this->assertInstanceOf(\Illuminate\Foundation\Http\FormRequest::class, $this->request);
    }

    #[Test]
    public function authorize_returns_true(): void
    {
        $this->assertTrue($this->request->authorize());
    }
}