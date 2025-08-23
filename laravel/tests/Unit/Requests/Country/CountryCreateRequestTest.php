<?php

namespace Tests\Unit\Requests\Country;

use App\Http\Requests\Country\CountryCreateRequest;
use Tests\Base\BaseRequestUnitTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;

#[CoversClass(CountryCreateRequest::class)]
#[Group('unit')]
#[Group('requests')]
#[Small]
class CountryCreateRequestTest extends BaseRequestUnitTest
{
    protected function getRequestClass(): string
    {
        return CountryCreateRequest::class;
    }

    protected function getValidData(): array
    {
        return [
            'code' => 'US',
            'name' => 'United States',
            'locale' => 'en_US',
            'currency_uuid' => null,
            'is_active' => true,
        ];
    }

    protected function getInvalidDataCases(): array
    {
        return [
            'missing_code' => [
                'data' => ['name' => 'United States'],
                'expected_errors' => ['code']
            ],
            'missing_name' => [
                'data' => ['code' => 'US'],
                'expected_errors' => ['name']
            ],
        ];
    }

    #[Test]
    public function it_is_form_request(): void
    {
        $this->assertIsFormRequest();
    }

    #[Test]
    public function it_has_required_methods(): void
    {
        $this->assertTrue(method_exists($this->request, 'authorize'));
        $this->assertTrue(method_exists($this->request, 'rules'));
        $this->assertTrue(method_exists($this->request, 'messages'));
    }

    #[Test]
    public function it_returns_true_for_authorize(): void
    {
        $this->assertAuthorizationResult(true);
    }

    #[Test]
    public function it_has_validation_rules(): void
    {
        $rules = $this->request->rules();
        
        $this->assertIsArray($rules);
        $expectedFields = ['code', 'name', 'locale', 'currency_uuid', 'is_active'];
        
        foreach ($expectedFields as $field) {
            $this->assertArrayHasKey($field, $rules);
        }
    }

    #[Test]
    public function it_validates_with_valid_data(): void
    {
        $this->assertValidationPasses($this->getValidData());
    }

    #[Test]
    public function it_has_custom_error_messages(): void
    {
        $messages = $this->request->messages();
        
        $this->assertIsArray($messages);
        $this->assertArrayHasKey('code.required', $messages);
        $this->assertArrayHasKey('code.size', $messages);
        $this->assertArrayHasKey('code.unique', $messages);
        $this->assertArrayHasKey('name.required', $messages);
        $this->assertArrayHasKey('name.max', $messages);
        $this->assertArrayHasKey('currency_uuid.exists', $messages);
        $this->assertArrayHasKey('is_active.boolean', $messages);
    }

    #[Test]
    public function it_validates_code_field(): void
    {
        $validData = $this->getValidData();
        
        unset($validData['code']);
        $this->assertValidationFails($validData, ['code']);
        
        $validData['code'] = 'U';
        $this->assertValidationFails($validData, ['code']);
        
        $validData['code'] = 'USA';
        $this->assertValidationFails($validData, ['code']);
        
        $validData['code'] = 'TR';
        $this->assertValidationPasses($validData);
    }

    #[Test]
    public function it_validates_name_field(): void
    {
        $validData = $this->getValidData();
        
        unset($validData['name']);
        $this->assertValidationFails($validData, ['name']);
        
        $validData['name'] = str_repeat('a', 256);
        $this->assertValidationFails($validData, ['name']);
        
        $validData['name'] = 'Turkey';
        $this->assertValidationPasses($validData);
    }

    #[Test]
    public function it_validates_currency_uuid_field(): void
    {
        $validData = $this->getValidData();
        
        unset($validData['currency_uuid']);
        $this->assertValidationPasses($validData);
        
        $validData['currency_uuid'] = null;
        $this->assertValidationPasses($validData);
    }

    #[Test]
    public function it_validates_is_active_field(): void
    {
        $validData = $this->getValidData();
        
        $validData['is_active'] = 'invalid';
        $this->assertValidationFails($validData, ['is_active']);
        
        $validData['is_active'] = true;
        $this->assertValidationPasses($validData);
        
        unset($validData['is_active']);
        $this->assertValidationPasses($validData);
    }

    #[Test]
    public function it_validates_with_minimal_required_data(): void
    {
        $data = [
            'code' => 'TR',
            'name' => 'Turkey',
            'locale' => 'tr_TR'
        ];
        
        $this->assertValidationPasses($data);
    }
}