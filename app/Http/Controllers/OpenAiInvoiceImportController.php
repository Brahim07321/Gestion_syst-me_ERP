<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Category;

class OpenAiInvoiceImportController extends Controller
{
    public function create()
    {
        return view('purchases.import-ai');
    }

    public function preview(Request $request)
    {
        $request->validate([
            'invoice_file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        $file = $request->file('invoice_file');

        $path = $file->store('invoice_ai', 'public');
        $absolutePath = Storage::disk('public')->path($path);

        $mimeType = $file->getMimeType();
        $base64 = base64_encode(file_get_contents($absolutePath));

        $imageDataUrl = "data:$mimeType;base64,$base64";

        $response = Http::withToken(config('services.nvidia.key'))
            ->timeout(120)
            ->post(config('services.nvidia.base_url') . '/chat/completions', [
                'model' => config('services.nvidia.model'),
                'temperature' => 0,
                'top_p' => 1,
                'max_tokens' => 4096,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => $this->invoicePrompt(),
                            ],
                            [
                                'type' => 'image_url',
                                'image_url' => [
                                    'url' => $imageDataUrl,
                                ],
                            ],
                        ],
                    ],
                ],
            ]);

        if (!$response->successful()) {
            return back()->withErrors([
                'invoice_file' => 'Erreur NVIDIA: ' . $response->body(),
            ]);
        }

        $jsonText = $response->json('choices.0.message.content');

        if (!$jsonText) {
            return back()->withErrors([
                'invoice_file' => 'NVIDIA n’a retourné aucun contenu.',
            ]);
        }

        $jsonText = $this->cleanJsonText($jsonText);
        $data = json_decode($jsonText, true);

        if (!$data) {
            return back()->withErrors([
                'invoice_file' => 'NVIDIA n’a pas retourné un JSON valide: ' . $jsonText,
            ]);
        }

        $data = $this->normalizeInvoiceData($data);

        $hasHeaderData =
            !empty($data['supplier_name']) ||
            !empty($data['invoice_number']) ||
            !empty($data['invoice_date']) ||
            !empty($data['total']);

        if (!$hasHeaderData && count($data['items']) === 0) {
            return back()->withErrors([
                'invoice_file' => 'AI n’a pas pu lire la facture. Essaie une image plus claire, bien cadrée et sans ombre.',
            ]);
        }

        /*
         * هنا كنقلبو على المنتجات الموجودة فـ table products
         * المرجع referonce هو الأساس.
         * designation غير مساعدة إذا reference ما خرجاتش.
         */
        $data = $this->attachProductMatches($data);

        $products = Product::orderBy('Referonce')
        ->orderBy('Designation')
        ->get(['id', 'Referonce', 'Designation', 'prace_bay']);
    
    $categories = Category::all();
    
    return view('purchases.import-ai-preview', [
        'data' => $data,
        'imagePath' => $path ?? null,
        'products' => $products,
        'categories' => $categories,
    ]);

    
    }

    public function confirm(Request $request)
    {
        $request->validate([
            'supplier_name' => ['nullable', 'string', 'max:255'],
            'invoice_number' => ['nullable', 'string', 'max:255'],
            'invoice_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
    
            'items' => ['required', 'array'],
            'items.*.product_action' => ['required', 'in:existing,create'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.category_id' => ['nullable', 'exists:categories,id'],
            'items.*.reference' => ['required', 'string', 'max:255'],
            'items.*.designation' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.line_total' => ['nullable', 'numeric'],
        ]);
    
        foreach ($request->items as $index => $item) {
            if ($item['product_action'] === 'existing' && empty($item['product_id'])) {
                return back()->withErrors([
                    'items' => 'La ligne ' . ($index + 1) . ' doit avoir un produit existant.',
                ])->withInput();
            }
    
            if ($item['product_action'] === 'create' && empty($item['category_id'])) {
                return back()->withErrors([
                    'items' => 'La ligne ' . ($index + 1) . ' doit avoir une catégorie.',
                ])->withInput();
            }
        }
    
        return DB::transaction(function () use ($request) {
            $supplier = null;
    
            if ($request->filled('supplier_name')) {
                $supplier = Supplier::firstOrCreate([
                    'name' => $request->supplier_name,
                ]);
            }
    
            $purchaseCode = $request->invoice_number;
    
            if ($purchaseCode && preg_match('/ACH-\d{8}-\d{6}/', $purchaseCode, $matches)) {
                $purchaseCode = $matches[0];
            }
    
            if (!$purchaseCode) {
                $purchaseCode = 'ACH-' . now()->format('Ymd-His');
            }
    
            $total = collect($request->items)->sum(function ($item) {
                return $item['line_total'] ?? ($item['quantity'] * $item['unit_price']);
            });
    
            $purchase = Purchase::create([
                'purchase_code' => $purchaseCode,
                'supplier_id' => $supplier?->id,
                'purchase_date' => $request->invoice_date ?? now()->toDateString(),
                'total' => $total,
                'status' => 'received',
            ]);
    
            foreach ($request->items as $item) {
                $quantity = (float) $item['quantity'];
                $unitPrice = (float) $item['unit_price'];
                $lineTotal = $item['line_total'] ?? ($quantity * $unitPrice);
    
                if ($item['product_action'] === 'existing') {
                    $product = Product::findOrFail($item['product_id']);
                } else {
                    $reference = trim($item['reference']);
    
                    $product = Product::where('Referonce', $reference)->first();
    
                    if (!$product) {
                        $product = Product::create([
                            'Category_ID' => $item['category_id'],
                            'code' => $reference,
                            'Referonce' => $reference,
                            'Designation' => $item['designation'],
                            'prace_bay' => $unitPrice,
                            'prace_sell' => $unitPrice,
                            'Quantite' => 0,
                        ]);
                    }
                }
    
                $purchase->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'buy_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ]);
    
                $product->update([
                    'prace_bay' => $unitPrice,
                ]);
    
                // خليه محيد إذا stock كيتزاد من logic آخر
                // $product->increment('Quantite', $quantity);
            }
    
            return redirect()
                ->route('purchases.index')
                ->with('success', 'Facture fournisseur importée avec succès.');
        });
    }
    private function invoicePrompt(): string
    {
        return <<<PROMPT
    You are an invoice OCR extraction engine for a stock management ERP.
    
    You MUST inspect the attached invoice image and extract only visible information.
    
    Return ONLY valid JSON.
    No markdown.
    No explanation.
    No json fences.
    
    Required JSON keys:
    supplier_name, invoice_number, invoice_date, due_date, currency, subtotal, tax, total, items.
    
    Rules:
    - invoice_number must contain ONLY the invoice number/code.
    - Never include labels like BON D'ACHAT, FACTURE, N°, Numéro, Invoice No.
    - Example: if the image shows BON D'ACHAT N° ACH-20260408-140022, return ACH-20260408-140022 only.
    - Extract the product reference if visible. Reference is very important.
    - Do not return the word null as text.
    - If a value is missing, return JSON null, not text null.
    - Do not create empty product rows.
    - Do not return product rows where designation is null or empty.
    - Extract real product lines only.
    - For each item return: reference, designation, quantity, unit_price, line_total.
    - Dates must be YYYY-MM-DD when possible.
    - Amounts must be numbers only, without MAD, DH, EUR, spaces or currency text.
    - If line_total is not visible but quantity and unit_price exist, calculate line_total.
    - If the invoice is not readable, return items as an empty array.
    
    Expected JSON structure:
    {
      "supplier_name": null,
      "invoice_number": null,
      "invoice_date": null,
      "due_date": null,
      "currency": null,
      "subtotal": null,
      "tax": null,
      "total": null,
      "items": [
        {
          "reference": null,
          "designation": "Product name",
          "quantity": 1,
          "unit_price": 100,
          "line_total": 100
        }
      ]
    }
    PROMPT;
    }

    private function cleanJsonText(string $jsonText): string
    {
        $jsonText = trim($jsonText);

        $jsonText = preg_replace('/^```json\s*/i', '', $jsonText);
        $jsonText = preg_replace('/^```\s*/', '', $jsonText);
        $jsonText = preg_replace('/\s*```$/', '', $jsonText);

        $firstBrace = strpos($jsonText, '{');
        $lastBrace = strrpos($jsonText, '}');

        if ($firstBrace !== false && $lastBrace !== false && $lastBrace > $firstBrace) {
            $jsonText = substr($jsonText, $firstBrace, $lastBrace - $firstBrace + 1);
        }

        return trim($jsonText);
    }

    private function normalizeInvoiceData(array $data): array
    {
        $cleanValue = function ($value) {
            if ($value === null) {
                return null;
            }

            if (is_string($value)) {
                $value = trim($value);

                if ($value === '' || strtolower($value) === 'null' || strtolower($value) === 'n/a') {
                    return null;
                }
            }

            return $value;
        };

        $normalized = [
            'supplier_name' => $cleanValue($data['supplier_name'] ?? null),
            'invoice_number' => $cleanValue($data['invoice_number'] ?? null),
            'invoice_date' => $cleanValue($data['invoice_date'] ?? null),
            'due_date' => $cleanValue($data['due_date'] ?? null),
            'currency' => $cleanValue($data['currency'] ?? null),
            'subtotal' => $cleanValue($data['subtotal'] ?? null),
            'tax' => $cleanValue($data['tax'] ?? null),
            'total' => $cleanValue($data['total'] ?? null),
            'items' => [],
        ];

        $normalized['items'] = collect($data['items'] ?? [])
            ->map(function ($item) use ($cleanValue) {
                $reference = $cleanValue($item['reference'] ?? null);
                $designation = $cleanValue($item['designation'] ?? null);
                $quantity = $cleanValue($item['quantity'] ?? null);
                $unitPrice = $cleanValue($item['unit_price'] ?? null);
                $lineTotal = $cleanValue($item['line_total'] ?? null);

                if ($lineTotal === null && $quantity !== null && $unitPrice !== null) {
                    $lineTotal = (float) $quantity * (float) $unitPrice;
                }

                return [
                    'reference' => $reference,
                    'designation' => $designation,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ];
            })
            ->filter(function ($item) {
                return !empty($item['designation']);
            })
            ->values()
            ->toArray();

        return $normalized;
    }

private function attachProductMatches(array $data): array
{
    $data['items'] = collect($data['items'] ?? [])
        ->map(function ($item) {
            $reference = trim((string) ($item['reference'] ?? ''));

            $product = null;
            $matchStatus = 'not_found';

            if ($reference !== '') {
                $product = Product::where('Referonce', $reference)->first();

                if ($product) {
                    $matchStatus = 'matched_by_reference';
                }
            }

            $item['product_id'] = $product?->id;
            $item['matched_reference'] = $product?->Referonce;
            $item['matched_designation'] = $product?->Designation;
            $item['match_status'] = $matchStatus;

            return $item;
        })
        ->values()
        ->toArray();

    return $data;
}}