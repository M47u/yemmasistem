<?php

namespace App\Core;

class Validator
{
    private array $errors = [];
    private array $data   = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public static function make(array $data, array $rules): self
    {
        $v = new self($data);
        foreach ($rules as $field => $ruleSet) {
            $v->applyRules($field, explode('|', $ruleSet));
        }
        return $v;
    }

    private function applyRules(string $field, array $rules): void
    {
        $value = $this->data[$field] ?? null;

        foreach ($rules as $rule) {
            [$name, $param] = array_pad(explode(':', $rule, 2), 2, null);

            $passed = match($name) {
                'required' => $value !== null && trim((string)$value) !== '',
                'email'    => filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
                'min'      => mb_strlen((string)$value) >= (int)$param,
                'max'      => mb_strlen((string)$value) <= (int)$param,
                'numeric'  => is_numeric($value),
                'integer'  => filter_var($value, FILTER_VALIDATE_INT) !== false,
                'positive' => is_numeric($value) && (float)$value > 0,
                'in'       => in_array($value, explode(',', $param ?? ''), true),
                'date'     => $this->isValidDate((string)$value),
                'optional' => true,
                default    => true,
            };

            if (!$passed) {
                $this->errors[$field][] = $this->message($name, $field, $param);
                break;
            }
        }
    }

    private function isValidDate(string $value): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $value);
        return $d && $d->format('Y-m-d') === $value;
    }

    private function message(string $rule, string $field, ?string $param): string
    {
        $label = ucfirst(str_replace('_', ' ', $field));
        return match($rule) {
            'required' => "$label es obligatorio.",
            'email'    => "$label debe ser un email válido.",
            'min'      => "$label debe tener al menos $param caracteres.",
            'max'      => "$label no puede superar $param caracteres.",
            'numeric'  => "$label debe ser un número.",
            'integer'  => "$label debe ser un número entero.",
            'positive' => "$label debe ser mayor a cero.",
            'in'       => "$label tiene un valor no permitido.",
            'date'     => "$label debe ser una fecha válida (AAAA-MM-DD).",
            default    => "$label no es válido.",
        };
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): string
    {
        foreach ($this->errors as $fieldErrors) {
            return $fieldErrors[0] ?? '';
        }
        return '';
    }

    public function validated(): array
    {
        return array_filter(
            $this->data,
            fn($key) => !isset($this->errors[$key]),
            ARRAY_FILTER_USE_KEY
        );
    }
}
