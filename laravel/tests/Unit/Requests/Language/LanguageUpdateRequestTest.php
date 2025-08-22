<?php

namespace Tests\Unit\Requests\Language;

use App\Http\Requests\Language\LanguageUpdateRequest;
use Tests\Base\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;

/**
 * Unit tests for LanguageUpdateRequest
 * Tests validation rules and authorization logic for language updates
 */
#[CoversClass(LanguageUpdateRequest::class)]
#[Group('unit')]
#[Group('requests')]
#[Small]
class LanguageUpdateRequestTest extends UnitTestCase
{
    protected $request;

    protected function setUp(): void
    {
        parent::setUp();
        $this->request = new LanguageUpdateRequest();
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