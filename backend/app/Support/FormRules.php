<?php

namespace App\Support;

use Closure;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

final class FormRules
{
    public const NAME_PATTERN = "/^[\pL\pM\s.'-]+$/u";

    public const CONTACT_NUMBER_PATTERN = '/^09\d{9}$/';

    public const SEXES = ['Male', 'Female'];

    public const HANDEDNESS = ['right', 'left', 'both', 'not_yet_established'];

    public const EDUCATION_LEVELS = ['Elementary', 'High School', 'College', 'Vocational', 'Postgraduate'];

    public static function name(bool $required = true): array
    {
        return [$required ? 'required' : 'nullable', 'string', 'max:255', 'regex:'.self::NAME_PATTERN];
    }

    public static function contactNumber(bool $required = true): array
    {
        return [$required ? 'required' : 'nullable', 'string', 'regex:'.self::CONTACT_NUMBER_PATTERN];
    }

    public static function password(bool $required = true): array
    {
        return [
            $required ? 'required' : 'nullable',
            'string',
            'confirmed',
            Password::min(12)->max(72),
            static function (string $attribute, mixed $value, Closure $fail): void {
                if (is_string($value) && strlen($value) > 72) {
                    $fail('The password may not exceed 72 bytes.');
                }
            },
        ];
    }

    /** The teacher's own details, shared by registration and the Settings profile form. Email is managed separately. */
    public static function teacherProfile(): array
    {
        return [
            'first_name' => self::name(),
            'middle_name' => self::name(false),
            'last_name' => self::name(),
            'sex' => ['required', Rule::in(self::SEXES)],
            'contact_number' => self::contactNumber(),
            'barangay' => ['required', 'string', 'max:255'],
            'municipality' => ['required', 'string', 'max:255'],
            'province' => ['required', 'string', 'max:255'],
            'region' => ['required', 'string', 'max:255'],
        ];
    }

    /** Maps validated teacherProfile() input to `teachers` columns, joining the address parts like the other forms. */
    public static function teacherAttributes(array $data): array
    {
        return [
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'] ?? null,
            'last_name' => $data['last_name'],
            'sex' => $data['sex'],
            'contact_number' => $data['contact_number'],
            'address' => self::joinAddress($data),
        ];
    }

    /** Splits an address saved by joinAddress() back into its form fields. */
    public static function splitAddress(?string $address): array
    {
        $parts = array_map('trim', explode(',', (string) $address));

        return [
            'barangay' => $parts[0] ?? '',
            'municipality' => $parts[1] ?? '',
            'province' => $parts[2] ?? '',
            'region' => $parts[3] ?? '',
        ];
    }

    public static function childProfile(string $prefix = '', array $currentEducation = []): array
    {
        $educationLevels = array_values(array_unique(array_filter(
            array_merge(self::EDUCATION_LEVELS, $currentEducation),
            fn ($level) => is_string($level) && $level !== ''
        )));

        return [
            "{$prefix}first_name" => self::name(),
            "{$prefix}middle_name" => self::name(false),
            "{$prefix}last_name" => self::name(),
            "{$prefix}sex" => ['required', Rule::in(self::SEXES)],
            "{$prefix}date_of_birth" => ['required', 'date', 'before_or_equal:today'],
            "{$prefix}barangay" => ['nullable', 'string', 'max:255'],
            "{$prefix}municipality" => ['nullable', 'string', 'max:255'],
            "{$prefix}province" => ['nullable', 'string', 'max:255'],
            "{$prefix}region" => ['nullable', 'string', 'max:255'],
            "{$prefix}handedness" => ['required', Rule::in(self::HANDEDNESS)],
            'is_studying' => ['nullable', 'boolean'],
            'school_name' => ['nullable', 'string', 'max:255'],
            'fathers_name' => self::name(false),
            'fathers_age' => ['nullable', 'integer', 'min:10', 'max:120'],
            'fathers_occupation' => ['nullable', 'string', 'max:255'],
            'fathers_education' => ['nullable', Rule::in($educationLevels)],
            'mothers_name' => self::name(false),
            'mothers_age' => ['nullable', 'integer', 'min:10', 'max:120'],
            'mothers_occupation' => ['nullable', 'string', 'max:255'],
            'mothers_education' => ['nullable', Rule::in($educationLevels)],
            'number_of_siblings' => ['nullable', 'integer', 'min:0', 'max:30'],
            'birth_order' => ['nullable', 'integer', 'min:1', 'max:31'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'mimetypes:image/jpeg,image/png,image/gif', 'max:2048'],
        ];
    }

    public static function joinAddress(array $data, string $prefix = ''): string
    {
        return implode(', ', array_filter([
            $data["{$prefix}barangay"] ?? null,
            $data["{$prefix}municipality"] ?? null,
            $data["{$prefix}province"] ?? null,
            $data["{$prefix}region"] ?? null,
        ], fn ($part) => is_string($part) && trim($part) !== ''));
    }

    public static function messages(): array
    {
        $nameMessage = 'The :attribute may only contain letters, spaces, periods, apostrophes and hyphens.';

        return [
            'contact_number.regex' => 'The contact number must be exactly 11 digits starting with 09 (e.g., 09171234567).',
            'date_of_birth.before_or_equal' => 'The date of birth cannot be in the future.',
            'child_date_of_birth.before_or_equal' => 'The child\'s date of birth cannot be in the future.',
            'photo.max' => 'The photo size must not exceed 2MB.',
            '*.regex' => $nameMessage,
        ];
    }
}
