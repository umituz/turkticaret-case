<?php

namespace Tests\Unit\Requests\Country;

use App\Http\Requests\Country\CountryListRequest;
use Tests\Base\BaseRequestUnitTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(CountryListRequest::class)]
#[Group('unit')]
#[Group('requests')]
#[Small]
class CountryListRequestTest extends BaseRequestUnitTest
{
    protected function getRequestClass(): string
    {
        return CountryListRequest::class;
    }

    protected function getValidData(): array
    {
        return [
            'active_only' => 'true',
        ];
    }

    protected function getInvalidDataCases(): array
    {
        return [
            'invalid_active_only_value' => [
                'data' => ['active_only' => 'invalid'],
                'expected_errors' => ['active_only']
            ],
            'invalid_active_only_number' => [
                'data' => ['active_only' => '1'],
                'expected_errors' => ['active_only']
            ],
            'invalid_active_only_boolean' => [
                'data' => ['active_only' => true],
                'expected_errors' => ['active_only']
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
        $this->assertRequestHasMethod('authorize');
        $this->assertRequestHasMethod('rules');
        $this->assertRequestHasMethod('messages');
    }

    #[Test]
    public function it_returns_true_for_authorize(): void
    {
        $this->assertAuthorizationResult(true);
    }

    #[Test]
    public function it_has_validation_rules(): void
    {
        $this->assertHasValidationRules();
        
        $rules = $this->request->rules();
        $this->assertArrayHasKey('active_only', $rules);
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
        $this->assertArrayHasKey('active_only.in', $messages);
        $this->assertEquals('Active only must be true or false.', $messages['active_only.in']);
    }

    #[Test]
    public function it_validates_active_only_field(): void
    {
        $this->assertValidationPasses([]);
        $this->assertValidationPasses(['active_only' => null]);
        $this->assertValidationPasses(['active_only' => 'true']);
        $this->assertValidationPasses(['active_only' => 'false']);
        
        $this->assertValidationFails(['active_only' => 'invalid'], ['active_only']);
        $this->assertValidationFails(['active_only' => '1'], ['active_only']);
        $this->assertValidationFails(['active_only' => true], ['active_only']);
        $this->assertValidationFails(['active_only' => false], ['active_only']);
    }


    #[Test]
    public function it_handles_filtering_use_case(): void
    {
        $this->assertValidationPasses([]);
        $this->assertValidationPasses(['active_only' => 'true']);
        $this->assertValidationPasses(['active_only' => 'false']);
    }
}