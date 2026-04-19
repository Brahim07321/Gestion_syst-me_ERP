<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Détail Facture</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <style>
        body {
            background-color: #f8f9fa;
        }

        .invoice-container {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-top: 40px;
            max-width: 1100px;
            margin-left: auto;
            margin-right: auto;
        }

        h1 {
            font-weight: 700;
            color: #0d6efd;
            margin-bottom: 30px;
            text-align: center;
            letter-spacing: 2px;
        }

        label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 6px;
        }

        .table thead {
            background-color: #0d6efd;
            color: white;
        }

        .table tbody tr:hover {
            background-color: #e9f1ff;
        }

        .total-section p {
            font-size: 1.1rem;
            font-weight: 600;
            color: #212529;
            margin-bottom: 8px;
        }

        .total-section strong {
            font-size: 1.3rem;
            color: #0d6efd;
        }

        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        .btn-primary:hover {
            background-color: #084298;
            border-color: #084298;
        }

        .readonly-input {
            background-color: #f8f9fa;
        }

        .badge-status {
            font-size: 0.95rem;
            padding: 8px 14px;
        }

        .readonly-input {
            background: #fff !important;
            border: none !important;
        }

        .card {
            border-radius: 12px;
        }

        .badge {
            font-size: 0.85rem;
        }
    </style>
</head>

<body>
    <div class="invoice-container">
        <h1>Détail de la Facture</h1>
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif
        @if ($facture->status == 'annulée')
            <div class="alert alert-dark border-0 shadow-sm rounded-4">
                <i class="fas fa-ban me-2"></i>
                Cette facture est annulée. Elle n’est plus prise en compte dans le stock, les ventes et les rapports.
            </div>
        @endif

        @if ($facture->trashed())
            <div class="alert alert-danger border-0 shadow-sm rounded-4">
                Cette facture a été supprimée, mais reste visible dans les archives.
            </div>
        @endif

        <div class="row mb-4 p-3 shadow-sm rounded" style="background:#f8f9fa;">

            <div class="col-md-6 border-end">
                <label class="text-muted small">Client</label>
                <input type="text" class="form-control readonly-input border-0 bg-transparent fw-bold"
                    value="{{ $facture->client_name }}" readonly>
            </div>



            <div class="col-md-3 border-end">
                <label class="text-muted small">Date Facture</label>
                <input type="text" class="form-control readonly-input border-0 bg-transparent fw-bold"
                    value="{{ \Carbon\Carbon::parse($facture->date_facture)->format('d/m/Y') }}" readonly>
            </div>

            <div class="col-md-3">
                <label class="text-muted small">Statut</label>

                <div class="form-control readonly-input border-0 bg-transparent text-center">



                    @if ($facture->status == 'payée')
                        <span class="badge bg-success px-3 py-2">✔ Payée</span>
                    @elseif($facture->status == 'partiellement payée')
                        <span class="badge bg-warning text-dark px-3 py-2">⏳ Partielle</span>
                    @elseif($facture->status == 'annulée')
                        <span class="badge bg-dark px-3 py-2">✖ Annulée</span>
                    @else
                        <span class="badge bg-danger px-3 py-2">✖ Non payée</span>
                    @endif

                    <div class="mt-2 small">
                        <span class="text-success fw-bold">
                            {{ number_format($facture->paid_amount ?? 0, 2) }} MAD
                        </span>
                        /
                        <span class="text-danger fw-bold">
                            {{ number_format($facture->remaining_amount ?? 0, 2) }} MAD
                        </span>
                    </div>

                </div>
            </div>

        </div>
        <div class="mb-4 text-center p-3 shadow-sm rounded" style="background:#f8f9fa;">
            <label class="text-muted small">Numéro Facture</label>
            <h4 class="fw-bold text-primary mt-2" id="invoice_number">
                {{ $facture->code_facture }}
            </h4>
        </div>
        <h3 class="mb-3">Articles</h3>

        <div class="table-responsive">
            <table class="table table-bordered align-middle" id="items-table">
                <thead>
                    <tr>
                        <th style="width: 18%;">Référence</th>
                        <th style="width: 30%;">Désignation</th>
                        <th style="width: 15%;">Prix Unitaire (MAD)</th>
                        <th style="width: 10%;">Quantité</th>
                        <th style="width: 15%;">Total (MAD)</th>
                    </tr>
                </thead>
                <tbody id="items-list">
                    @forelse($facture->items as $item)
                        <tr>
                            <td class="product-reference">{{ $item->referonce }}</td>
                            <td class="product-designation">{{ $item->designation }}</td>
                            <td class="product-price">{{ number_format($item->price, 2, '.', '') }}</td>
                            <td class="product-quantity">{{ $item->quantity }}</td>
                            <td class="product-total">{{ number_format($item->line_total, 2, '.', '') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Aucun produit trouvé</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-4">
                <h4 class="mb-3">Historique des Paiements</h4>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Montant</th>
                                <th>Note</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($facture->payments as $payment)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') }}</td>
                                    <td>{{ number_format($payment->amount, 2) }} MAD</td>
                                    <td>{{ $payment->note ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">Aucun paiement enregistré.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @php
            $subtotal = $facture->total / 1;
            $tax = $facture->total - $subtotal;
        @endphp

        <div class="total-section text-end">
            <p>Sous-total : <span id="subtotal">{{ number_format($subtotal, 2, '.', '') }}</span> MAD</p>
            <p>Taxe (20%) : <span id="tax">{{ number_format($tax, 2, '.', '') }}</span> MAD</p>
            <p><strong>Total Général : <span id="grand-total">{{ number_format($facture->total, 2, '.', '') }}</span>
                    MAD</strong></p>
        </div>

        <div class="text-center mt-4">

            @php
                $previous = url()->previous();
            @endphp

            <a href="{{ $previous && !str_contains($previous, 'factures/') ? $previous : route('factures.index') }}"
                class="btn btn-secondary btn-lg px-4 me-2">
                <i class="fas fa-arrow-left"></i> Retour
            </a>

            <button type="button" class="btn btn-info me-2" id="save-pdf">
                <i class="btn fas fa-file-pdf"></i> Enregistrer en PDF
            </button>

            @if ($facture->status === 'payée')
                <button type="button" class="btn btn-success me-2" disabled>
                    <i class="fas fa-money-bill-wave"></i> Déjà payée
                </button>
            @else
                @if (!$facture->trashed())
                    @if ($facture->status != 'annulée')
                        <button type="button" class="btn btn-success me-2" data-bs-toggle="modal"
                            data-bs-target="#paymentModal">
                            <i class="fas fa-money-bill-wave"></i> Ajouter Paiement
                        </button>
                    @endif
                @endif
            @endif
        </div>
    </div>
    @if ($facture->status != 'annulée')
        <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('payments.store', $facture->id) }}">
                        @csrf

                        <div class="modal-header">
                            <h5 class="modal-title" id="paymentModalLabel">Ajouter un Paiement</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="mb-3">
                                <label>Montant</label>
                                <input type="number" step="0.01" min="0.01" name="amount"
                                    class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label>Date Paiement</label>
                                <input type="date" name="payment_date" class="form-control"
                                    value="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="mb-3">
                                <label>Note</label>
                                <input type="text" name="note" class="form-control" placeholder="Optionnel">
                            </div>

                            <div class="alert alert-info">
                                <strong>Reste à payer:</strong>
                                {{ number_format($facture->remaining_amount ?? $facture->total - ($facture->paid_amount ?? 0), 2) }}
                                MAD
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                            <button type="submit" class="btn btn-success">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @php
        $formattedDate = \Carbon\Carbon::parse($facture->date_facture)->format('d/m/Y');
    @endphp
    <script>
        document.getElementById('save-pdf').addEventListener('click', function() {
            const {
                jsPDF
            } = window.jspdf;
            const doc = new jsPDF('p', 'mm', 'a4');

            const pageWidth = doc.internal.pageSize.getWidth();
            const pageHeight = doc.internal.pageSize.getHeight();

            const invoiceNumber = @json($facture->code_facture);
            const invoiceDate = @json($formattedDate);
            const customerName = @json($facture->client_name);

            const customerAddress = @json($customer->address ?? '');
            const grandTotal = @json($facture->total);

            const logoUrl = @json(!empty($company?->logo) ? asset('storage/' . $company->logo) : asset('images/img.png'));
            const companyName = @json($company->company_name ?? '');
            const companyCity = @json($company->city ?? 'Agader');
            const companyAddress = @json($company->address ?? '');
            const companyPhone = @json($company->phone ?? '0661247414');
            const companyEmail = @json($company->email ?? '');
            const footerNote = @json($company->footer_note ?? 
            'Notre société est spécialisée dans la vente de pièces pour machines industrielles. Nous nous engageons à fournir des produits de qualité, fiables et adaptés à vos besoins.Nous vous remercions pour votre confiance.');
            const footerContact = @json(
                $company->footer_contact ?? 'Tell: 0661247414  || 0680661043');

            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.src = logoUrl;

            img.onload = function() {

                // =========================
                // LOGO
                // =========================
                const logoWidth = pageWidth;
                const logoHeight = 35; // بدلها 30 أو 40 حسب الصورة
                const logoX = 0;
                const logoY = 0;

                doc.addImage(img, 'PNG', logoX, logoY, logoWidth, logoHeight);


                // =========================
                // NOM SOCIETE
                // =========================
                doc.setFont('helvetica', 'bold');
                doc.setFontSize(16);
                doc.setTextColor(70, 85, 110);
                doc.text(companyName.toUpperCase(), pageWidth / 2, 42, {
                    align: 'center'
                });

                doc.setDrawColor(0, 102, 204);
                doc.setLineWidth(0.4);
                doc.line(65, 46, pageWidth - 65, 46);



                // =========================
                // HEADER
                // =========================
                doc.setTextColor(0, 0, 0);
                doc.setFontSize(10);

                doc.setFont('helvetica', 'bold');


                doc.text(`BON DE LIVRAISON N° ${invoiceNumber}`, 12, 56);

                doc.setFont('helvetica', 'normal');
                doc.text(`${companyCity}, le : ${invoiceDate}`, pageWidth - 19, 56, {
                    align: 'right'
                });



                // =========================
                // CLIENT BOX
                // =========================
                doc.setDrawColor(170, 170, 170);
                doc.setLineWidth(0.25);
                doc.roundedRect(120, 58, 72, 24, 3, 3);

                // 🔹 CLIENT LABEL
                doc.setFont('helvetica', 'bold');
                doc.setFontSize(9);
                doc.setTextColor(90, 100, 125);

                const labelX = 126;
                const labelY = 64;

                doc.text('CLIENT :', labelX, labelY);

                // 🔹 نحسب طول "CLIENT :"
                const labelWidth = doc.getTextWidth('CLIENT :');

                // 🔹 POSITION ديال الاسم
                const nameX = labelX + labelWidth + 3;

                // 🔹 CLIENT NAME
                doc.setFont('helvetica', 'bold');
                doc.setFontSize(11);
                doc.setTextColor(0, 0, 0);

                doc.text(customerName.toUpperCase(), nameX, 64, {
                    maxWidth: 50
                });

                // 🔹 CLIENT ADDRESS
                if (customerAddress) {
                    doc.setFont('helvetica', 'normal');
                    doc.setFontSize(8);

                    const addressLines = doc.splitTextToSize(customerAddress, 50);

                    doc.text(addressLines, nameX, 72);
                }

                // =========================
                // TABLE DATA
                // =========================
                const rows = [];

                @foreach ($facture->items as $item)
                    rows.push([
                        "{{ $item->referonce }}",
                        "{{ $item->designation }}",
                        "{{ $item->quantity }}",
                        "{{ number_format($item->price, 2, ',', '') }}",
                        "{{ number_format($item->line_total, 2, ',', '') }}"
                    ]);
                @endforeach

                while (rows.length < 15) {
                    rows.push(['', '', '', '', '']);
                }

                doc.autoTable({
                    startY: 84,
                    head: [
                        [
                            'Référence',
                            'Désignation',
                            'Quantité',
                            'P.U Net',
                            'Montant Net'
                        ]
                    ],
                    body: rows,
                    theme: 'plain',
                    styles: {
                        font: 'helvetica',
                        fontSize: 9,
                        textColor: [0, 0, 0],
                        cellPadding: 3,
                        overflow: 'linebreak',
                        valign: 'middle',
                        lineWidth: 0
                    },
                    headStyles: {
                        fontStyle: 'bold',
                        halign: 'center',
                        textColor: [70, 85, 110],
                        fillColor: false,
                        lineWidth: 0
                    },
                    columnStyles: {
                        0: {
                            cellWidth: 28,
                            halign: 'left'
                        },
                        1: {
                            cellWidth: 80,
                            halign: 'left'
                        },
                        2: {
                            cellWidth: 22,
                            halign: 'center'
                        },
                        3: {
                            cellWidth: 24,
                            halign: 'center'
                        },
                        4: {
                            cellWidth: 26,
                            halign: 'center'
                        }
                    },
                    margin: {
                        left: 12,
                        right: 12
                    },
                    didDrawPage: function(data) {
                        const x = data.settings.margin.left;
                        const y = data.settings.startY;
                        const tableWidth = 180;
                        const rowHeight = 9;
                        const totalHeight = rowHeight * (rows.length + 1);

                        // outer border
                        doc.setDrawColor(170, 170, 170);
                        doc.setLineWidth(0.2);
                        doc.rect(x, y, tableWidth, totalHeight);

                        // vertical lines only
                        const col1 = x + 28;
                        const col2 = x + 108;
                        const col3 = x + 130;
                        const col4 = x + 154;

                        doc.line(col1, y, col1, y + totalHeight);
                        doc.line(col2, y, col2, y + totalHeight);
                        doc.line(col3, y, col3, y + totalHeight);
                        doc.line(col4, y, col4, y + totalHeight);

                        // header separator only
                        doc.line(x, y + rowHeight, x + tableWidth, y + rowHeight);
                    }
                });

                // =========================
                // TOTAL
                // =========================
                doc.setFont('helvetica', 'bold');
                doc.setFontSize(10);

                // قياسات table نفسها
                const tableX = 12;
                const tableWidth = 180;
                const tableRight = tableX + tableWidth;

                // قياسات total box
                const totalLabelW = 28;
                const totalAmountW = 34;
                const totalH = 10;
                const totalGroupW = totalLabelW + totalAmountW;

                // TOTAL خاصها تلصق فاليمين ديال table
                const totalX = tableRight - totalGroupW;

                // TOTAL خاصها تلصق مباشرة تحت table
                const totalY = (doc.lastAutoTable.finalY || 200) - 0.5;

                // label TOTAL
                doc.setFillColor(0, 102, 204);
                doc.setTextColor(255, 255, 255);
                doc.rect(totalX, totalY, totalLabelW, totalH, 'F');
                doc.text('TOTAL', totalX + (totalLabelW / 2), totalY + 6.5, {
                    align: 'center'
                });

                // amount box
                doc.setFillColor(255, 255, 255);
                doc.setTextColor(0, 0, 0);
                doc.rect(totalX + totalLabelW, totalY, totalAmountW, totalH);

                // format total
                const numberValue = Number(grandTotal) || 0;
                const parts = numberValue.toFixed(2).split('.');
                const integerPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
                const totalText = integerPart + ',' + parts[1];

                // text amount
                doc.text(totalText, totalX + totalLabelW + totalAmountW - 2, totalY + 6.5, {
                    align: 'right'
                });

                // =========================
                // FOOTER
                // =========================
                let footerY = pageHeight - 22;

                doc.setDrawColor(200, 200, 200);
                doc.line(12, footerY - 6, pageWidth - 12, footerY - 6);

                doc.setFont('helvetica', 'bold');
                doc.setFontSize(7.5);
                doc.setTextColor(90, 90, 90);
                doc.text(footerNote, pageWidth / 2, footerY - 1, {
                    align: 'center',
                    maxWidth: 180
                });

                doc.setFont('helvetica', 'normal');
                doc.setFontSize(7.5);

                if (companyAddress) {
                    doc.text(companyAddress, pageWidth / 2, footerY + 7, {
                        align: 'center'
                    });
                }

                doc.text(
                    footerContact || `${companyPhone} - ${companyEmail}`,
                    pageWidth / 2,
                    footerY + 12, {
                        align: 'center'
                    }
                );

                const printDate = new Date().toLocaleString('fr-FR');

                doc.setFont('helvetica', 'normal');
                doc.setFontSize(7);
                doc.setTextColor(120, 120, 120);

                doc.text(`Imprimé le : ${printDate}`, 12, pageHeight - 5);

                doc.save(`facture_${invoiceNumber}.pdf`);
            };
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
</body>

</html>
