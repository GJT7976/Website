<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TaxRule;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaxRuleController extends Controller
{
    public function index(): View
    {
        return view('admin.tax-rules.index', [
            'taxRules' => TaxRule::orderByDesc('effective_date')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.tax-rules.form', ['taxRule' => new TaxRule]);
    }

    public function store(Request $request): RedirectResponse
    {
        $taxRule = TaxRule::create($this->validated($request));

        AuditLogger::record('tax_rule.created', $taxRule, null, $taxRule->only(['country', 'province', 'percentage']));

        return redirect()->route('admin.tax-rules.index')->with('status', 'Tax rule created.');
    }

    public function edit(TaxRule $taxRule): View
    {
        return view('admin.tax-rules.form', ['taxRule' => $taxRule]);
    }

    public function update(Request $request, TaxRule $taxRule): RedirectResponse
    {
        $before = $taxRule->only(['country', 'province', 'percentage', 'active']);
        $taxRule->update($this->validated($request));

        AuditLogger::record('tax_rule.updated', $taxRule, $before, $taxRule->only(['country', 'province', 'percentage', 'active']));

        return redirect()->route('admin.tax-rules.index')->with('status', 'Tax rule updated.');
    }

    public function destroy(TaxRule $taxRule): RedirectResponse
    {
        $before = $taxRule->only(['country', 'province', 'percentage']);
        $taxRule->delete();

        AuditLogger::record('tax_rule.deleted', $taxRule, $before, null);

        return back()->with('status', 'Tax rule deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'country' => ['required', 'string', 'size:2'],
            'province' => ['nullable', 'string', 'max:100'],
            'tax_name' => ['required', 'string', 'max:50'],
            'percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'effective_date' => ['required', 'date'],
            'expiry_date' => ['nullable', 'date', 'after:effective_date'],
            'active' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $data['country'] = strtoupper($data['country']);
        $data['active'] = $request->boolean('active');

        return $data;
    }
}
