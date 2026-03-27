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
    </style>
</head>
<body>
    <div class="invoice-container">
        <h1>Détail de la Facture</h1>

        <div class="row mb-4">
            <div class="col-md-6">
                <label>Client</label>
                <input type="text" class="form-control readonly-input" value="{{ $facture->client_name }}" readonly>
            </div>

            <div class="col-md-3">
                <label>Date Facture</label>
                <input type="text" class="form-control readonly-input" value="{{ $facture->date_facture }}" readonly>
            </div>

            <div class="col-md-3">
                <label>Statut</label>
                <div class="form-control readonly-input  align-items-center">
                    @if($facture->status === 'payée')
                        <span class="badge bg-success badge-status">Payée</span>
                    @else
                        <span class="badge bg-danger badge-status">Non payée</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="mb-4">
            <label>Numéro Facture</label>
            <input type="text" id="invoice_number" class="form-control readonly-input" value="{{ $facture->code_facture }}" readonly>
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
        </div>

        @php
            $subtotal = $facture->total / 1;
            $tax = $facture->total - $subtotal;
        @endphp

        <div class="total-section text-end">
            <p>Sous-total : <span id="subtotal">{{ number_format($subtotal, 2, '.', '') }}</span> MAD</p>
            <p>Taxe (20%) : <span id="tax">{{ number_format($tax, 2, '.', '') }}</span> MAD</p>
            <p><strong>Total Général : <span id="grand-total">{{ number_format($facture->total, 2, '.', '') }}</span> MAD</strong></p>
        </div>

        <div class="text-center mt-4">
            <a href="{{ route('factures.index') }}" class="btn btn-secondary btn-lg px-4 me-2">
                <i class="fas fa-arrow-left"></i> Retour
            </a>

            <button type="button" class="btn btn-info me-2" id="save-pdf">
                <i class="btn fas fa-file-pdf"></i> Enregistrer en PDF
            </button>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

    <script>
        document.getElementById('save-pdf').addEventListener('click', function() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('p', 'mm', 'a4');

            const pageWidth = doc.internal.pageSize.getWidth();
            const pageHeight = doc.internal.pageSize.getHeight();

            const invoiceNumber = document.getElementById('invoice_number').value || 'BL-0001';
            const invoiceDate = @json($facture->date_facture);
            const customerName = @json($facture->client_name);
            const grandTotal = document.getElementById('grand-total').textContent || '0.00';

            const logoUrl = '{{ asset("images/img.png") }}';
            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.src = logoUrl;

            img.onload = function() {
                const logoWidth = 85;
                const logoHeight = 28;
                const logoX = (pageWidth - logoWidth) / 2;
                const logoY = 8;

                doc.addImage(img, 'PNG', logoX, logoY, logoWidth, logoHeight);

                doc.setDrawColor(120, 120, 120);
                doc.setLineWidth(0.3);
                doc.line(12, 40, pageWidth - 12, 40);

                doc.setFont('helvetica', 'bold');
                doc.setFontSize(10);
                doc.text(`BON DE LIVRAISON N° ${invoiceNumber}`, 12, 48);
                doc.text(`BON DE COMMANDE N°`, 12, 55);

                doc.text(`Marrakech, le : ${invoiceDate}`, pageWidth - 17, 44, {
                    align: 'right'
                });

                doc.rect(122, 46, 70, 20);
                doc.text(String(customerName).toUpperCase(), 157, 60, {
                    align: 'center'
                });

                const rows = [];
                document.querySelectorAll('#items-list tr').forEach(row => {
                    const referonce = row.querySelector('.product-reference')?.textContent?.trim() || '';
                    const designation = row.querySelector('.product-designation')?.textContent?.trim() || '';
                    const quantity = row.querySelector('.product-quantity')?.textContent?.trim() || '1';
                    const price = row.querySelector('.product-price')?.textContent?.trim() || '0.00';
                    const total = row.querySelector('.product-total')?.textContent?.trim() || '0.00';

                    if (referonce || designation) {
                        rows.push([
                            referonce,
                            designation,
                            quantity.replace('.', ','),
                            price.replace('.', ','),
                            total.replace('.', ',')
                        ]);
                    }
                });

                const minRows = 15;
                while (rows.length < minRows) {
                    rows.push(['', '', '', '', '']);
                }

                doc.autoTable({
                    startY: 70,
                    head: [[
                        'Référence',
                        'Désignation',
                        'Quantité',
                        'P.U Net',
                        'Montant Net'
                    ]],
                    body: rows,
                    theme: 'plain',
                    styles: {
                        font: 'helvetica',
                        fontSize: 9,
                        textColor: [0, 0, 0],
                        cellPadding: 2,
                        overflow: 'linebreak',
                        valign: 'middle',
                        lineWidth: 0
                    },
                    headStyles: {
                        fontStyle: 'bold',
                        halign: 'center',
                        textColor: [0, 0, 0],
                        fillColor: false,
                        lineWidth: 0
                    },
                    columnStyles: {
                        0: { cellWidth: 28, halign: 'left' },
                        1: { cellWidth: 80, halign: 'left' },
                        2: { cellWidth: 22, halign: 'center' },
                        3: { cellWidth: 24, halign: 'right' },
                        4: { cellWidth: 26, halign: 'right' }
                    },
                    margin: {
                        left: 12,
                        right: 12
                    },
                    didDrawPage: function(data) {
                        const x = data.settings.margin.left;
                        const y = data.settings.startY;
                        const tableWidth = 180;
                        const rowHeight = 8;

                        doc.setDrawColor(120, 120, 120);
                        doc.setLineWidth(0.2);

                        const bodyRowsCount = rows.length > 0 ? rows.length : 1;
                        const totalHeight = rowHeight * (bodyRowsCount + 1);

                        doc.rect(x, y, tableWidth, totalHeight);

                        const col1 = x + 28;
                        const col2 = x + 108;
                        const col3 = x + 130;
                        const col4 = x + 154;

                        doc.line(col1, y, col1, y + totalHeight);
                        doc.line(col2, y, col2, y + totalHeight);
                        doc.line(col3, y, col3, y + totalHeight);
                        doc.line(col4, y, col4, y + totalHeight);

                        doc.line(x, y + rowHeight, x + tableWidth, y + rowHeight);
                    }
                });

                const totalY = 200;

                doc.setFont('helvetica', 'bold');
                doc.setFontSize(10);

                doc.setFillColor(0, 102, 204);
                doc.rect(142, totalY, 28, 10, 'F');
                doc.setTextColor(255, 255, 255);
                doc.text('TOTAL', 156, totalY + 6.5, { align: 'center' });

                doc.setFillColor(255, 255, 255);
                doc.setTextColor(0, 0, 0);
                doc.rect(170, totalY, 22, 10);
                doc.text(String(grandTotal).replace('.', ','), 190, totalY + 6.5, {
                    align: 'right'
                });

                let footerY = pageHeight - 24;

                doc.setFont('helvetica', 'bold');
                doc.setFontSize(8);
                doc.text(
                    "LES TURBOCHARGEURS, LES PIÈCES ÉLECTRONIQUES ET HYDRAULIQUES NE SONT PAS COUVERTS PAR LA",
                    pageWidth / 2,
                    footerY,
                    { align: 'center' }
                );
                doc.text(
                    "GARANTIE AUCUN RETOUR OU AVOIR N'EST ACCEPTÉ",
                    pageWidth / 2,
                    footerY + 4,
                    { align: 'center' }
                );

                doc.setFont('helvetica', 'normal');
                doc.setFontSize(8);
                doc.text(
                    "Siège Social : 14 Magasin 1 Lot Taisir Quartier Sidi Ghanem - Marrakech",
                    pageWidth / 2,
                    footerY + 10,
                    { align: 'center' }
                );
                doc.text(
                    "Tél. : 0524 33 65 14 / 06 61 28 44 87 - E-mail : italopieces2015@gmail.com",
                    pageWidth / 2,
                    footerY + 14,
                    { align: 'center' }
                );

                doc.save(`facture_${invoiceNumber}.pdf`);
            };

            img.onerror = function() {
                alert("Erreur lors du chargement du logo.");
            };
        });
    </script>
</body>
</html>