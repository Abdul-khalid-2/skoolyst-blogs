<?php

/**
 * Minimal, dependency-free input validator.
 *
 * Usage:
 *   $v = new Validator($request->body, [
 *       'title' => 'required|max:255',
 *       'email' => 'required|email',
 *   ]);
 *   if ($v->fails()) {
 *       Response::error('Validation failed.', 422, $v->errors());
 *   }
 */
class Validator
{
    private array $data;
    private array $rules;
    private array $errors = [];

    public function __construct(array $data, array $rules)
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->run();
    }

    private function run(): void
    {
        foreach ($this->rules as $field => $ruleString) {
            $value = $this->data[$field] ?? null;
            $rules = explode('|', $ruleString);

            foreach ($rules as $rule) {
                $params = [];
                if (str_contains($rule, ':')) {
                    [$rule, $paramStr] = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                }

                $this->applyRule($field, $value, $rule, $params);
            }
        }
    }

    private function applyRule(string $field, mixed $value, string $rule, array $params): void
    {
        switch ($rule) {
            case 'required':
                if ($value === null || $value === '') {
                    $this->addError($field, "The {$field} field is required.");
                }
                break;

            case 'email':
                if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, "The {$field} field must be a valid email address.");
                }
                break;

            case 'max':
                if ($value !== null && mb_strlen((string) $value) > (int) $params[0]) {
                    $this->addError($field, "The {$field} field must not exceed {$params[0]} characters.");
                }
                break;

            case 'min':
                if ($value !== null && mb_strlen((string) $value) < (int) $params[0]) {
                    $this->addError($field, "The {$field} field must be at least {$params[0]} characters.");
                }
                break;

            case 'in':
                if ($value !== null && !in_array($value, $params, true)) {
                    $this->addError($field, "The {$field} field must be one of: " . implode(', ', $params) . '.');
                }
                break;

            case 'numeric':
                if ($value !== null && $value !== '' && !is_numeric($value)) {
                    $this->addError($field, "The {$field} field must be numeric.");
                }
                break;
        }
    }

    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
