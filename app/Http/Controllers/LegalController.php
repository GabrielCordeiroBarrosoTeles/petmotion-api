<?php

namespace App\Http\Controllers;

use Illuminate\Support\Carbon;
use Illuminate\View\View;

class LegalController extends Controller
{
    public function privacy(): View
    {
        return view('legal.privacy', $this->shared());
    }

    public function terms(): View
    {
        return view('legal.terms', $this->shared());
    }

    private function shared(): array
    {
        $legal = config('petmotion.legal');

        // Campos não preenchidos no .env aparecem destacados para não irem ao ar em branco.
        $fill = fn (?string $value, string $label): string => $value
            ? e($value)
            : '<mark class="todo">['.e($label).']</mark>';

        return [
            'legal' => $legal,
            'fill' => $fill,
            'contact' => $fill($legal['email'], 'preencher LEGAL_EMAIL'),
            'updated' => Carbon::parse($legal['updated_at'])->translatedFormat('j \d\e F \d\e Y'),
        ];
    }
}
